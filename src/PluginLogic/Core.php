<?php
/**
 * Namespace: PluginLogic
 * Name: Core
 * Description: Contains our logic for providing WordPress specific logic for our plugin.
 * This includes entities such as Post Types, Meta Boxes, and the required functionality for each of these entities.
 */
namespace UserAwards\PluginLogic;

class Core {
	private $UserAwards; // Business Logic Layer
	private $MetaBoxes; // Meta box

	function __construct( $UserAwards = NULL, $MetaBoxes = NULL ) {
		$this->UserAwards = $UserAwards;
		$this->MetaBoxes = $MetaBoxes;

		if ( empty( $this->UserAwards ) )
		{
			return new \WP_Error("Missing UserAwards Dependency");
		}

		if ( empty( $this->MetaBoxes ) )
		{
			return new \WP_Error("Missing MetaBoxes Dependency");
		}

		/**
		 * Actions
		 */

		// Actions that need a post type reference to function
		if ( defined('USER_AWARDS_POST_TYPE') )
		{
			// Adds our custom post type
			add_action('init', [$this, 'PostType']);

			// Add meta boxes to post type and provide option to edit those values
			add_action('add_meta_boxes_' . USER_AWARDS_POST_TYPE, [$this->MetaBoxes, 'PostTypeMetaBoxes']);

			// Adding in "User Awards" admin interface
			add_action('admin_menu', [$this, 'UserAwardsPage']);

			// Adding in data for each of our columns
			add_action('manage_' . USER_AWARDS_POST_TYPE . '_posts_custom_column', [$this, 'PostTypeAdminColumnsData'] , 10, 2);

			// Adding in custom columns to our post type.
			add_filter('manage_' . USER_AWARDS_POST_TYPE . '_posts_columns', [$this, 'PostTypeAdminColumns']);
		}

		add_action('save_post', [$this->MetaBoxes, 'UserAwardsSaveMetaBoxes']); // Adds ability to save our meta values with our post


		// Adding in Custom Modal through the "admin-notices" action
		add_action('admin_notices', [$this, 'ModalGetUser']);

		// Adding in actual admin notices handling
		add_action('admin_notices', [$this, 'UserAwardsPostAdminNotices']);

		// Adding in UserAwardsTable admin notices
		add_action('admin_notices', 'UserAwards\PluginLogic\UserAwardsTable::UserAwardsTableAdminNotices');

		/**
		 * Filters
		 */


		// Defining BULK ACTIONS fors our custom post type edit window.
		add_filter('bulk_actions-edit-' . USER_AWARDS_POST_TYPE, [$this, 'RegisterUserAwardsCptBulkActions']);

		// Handling submission of the bulk action
		add_filter('handle_bulk_actions-edit-' . USER_AWARDS_POST_TYPE, [$this, 'HandleUserAwardsCptBulkActions'], 10, 3 );
	}


	/**
	 * Creates Wordpress Post Type
	 */
	public function PostType() {
		$args = [
			'labels' => [
				'name' => 'Awards', // Name of our custom post type
				'singular_name' => 'Award', // Singular version of the name
				'add_new' => 'Add New Award',
				'add_new_item' => 'Add New Award', // Add New Page Header
				'edit_item' => 'Edit Award', // Edit Page
				'view_item' => 'View Award', // Text for viewing a single entry
				'search_items' => 'Search Awards', // Text displayed for searching
				'not_found' => 'No Awards Found', // Text displayed when no awards were found in a search
				'not_found_in_trash' => 'No Awards Found in Trash', // Test shown when awards found in trash
				'menu_name' => 'Awards',
			],
			'menu_icon' => plugins_url( '/assets/icons/icon-16x16.png', dirname(dirname(__FILE__)) ),
			'show_ui' => true
		];

		register_post_type(USER_AWARDS_POST_TYPE, $args);
	}

	/**
	 * Creates User Awards Submenu Page
	 */
	public function UserAwardsPage() {
		add_submenu_page(
			'edit.php?post_type=' . USER_AWARDS_POST_TYPE,
			'User Awards',
			'User Awards',
			'manage_options',
			'user-awards-admin-view',
			[$this, 'UserAwardsPageHTML']
		);
	}

	/**
	 * HTML For the user awards page.
	 * This is used to display our table that shows the awards that are assigned to users.
	 */
	public function UserAwardsPageHTML() {
		$userAwardsTable = new UserAwardsTable( $this->UserAwards );
		$userAwardsTable->prepare_items();
	?>
		<div class="wrap">
			<h1>User Awards</h1>
			<p>This window shows you which awards are assigned to specific users.</p>
			<p>You may also specifically <em>give</em> awards to users. To do this, click on the "Give to User" button </p>
			<!-- Include this table inside a form if we want to enable bulk actions for the table -->
			<form id="user-awards-filter" method="POST">
				<?php $userAwardsTable->display(); ?>
			</form>
		</div>
	<?php
	}

	function RegisterUserAwardsCptBulkActions( $bulk_actions ) {
		$bulk_actions['UserAwards_Assign'] = __('Assign to User', 'user_awards');
		$bulk_actions['UserAwards_Give'] = __('Give to User', 'user_awards');
		return $bulk_actions;
	}

	/**
	 * Handle any bulk actions on the EDIT page Wordpress Awards Custom Post Type View
	 * @param  string $redirect_url - URL browser will change to after we complete the bulk action.
	 * @param  string $doaction      - The action being taken
	 * @param  array $items    - Items to take the action on
	 * @return string          - $redirect_url is returned here after some transformations
	 */
	function HandleUserAwardsCptBulkActions( $redirect_url, $doaction, $items )
	{
		if ( ! current_user_can('manage_options') )
		{
			return $redirect_url;
		}

		$UserAwards_UserID = ( call_user_func('UserAwards\Utility::CheckUserInput_UserID', $_GET['UserAwards_UserID']) ) ?
			$_GET['UserAwards_UserID'] // Ternary to get User ID and assign it.
			:
			NULL;

		if ( isset( $_GET['UserAwards_UserID'] ) )
		{
			if ( $doaction === 'UserAwards_Assign' )
			{
				$bulkAction = "Assigned";
				$action_completed = $this->UserAwards->AssignAwards( $UserAwards_UserID, $items );
			}
			elseif( $doaction === 'UserAwards_Give' )
			{
				$bulkAction = "Gave";
				$action_completed = $this->UserAwards->GiveAwards( $UserAwards_UserID, $items );
			}

			$redirect_url = add_query_arg([
				'UserAwards_Users_Affected' => count( $items ),
				'UserAwards_UserID' => $UserAwards_UserID,
				'UserAwards_Bulk_Action' => $bulkAction
			], $redirect_url);
		}

		return $redirect_url;
	}

	/**
	 * Adding custom columns to post lists
	 */
	public function PostTypeAdminColumns( $columns )
	{
		$columns = [
			'cb' => $columns['cb'],
			'title' => $columns['title'],
			'trigger' => 'Trigger',
			'auto_give' => 'Auto Give',
			'date' => $columns['date']
		];

		return $columns;
	}

	/**
	 * Function to handle the values that are in our edit specific admin columns.
	 */
	public function PostTypeAdminColumnsData( $column, $post_id )
	{
		$parser = new \UserAwards\Grammar\PluginParser();
		switch( $column ) {
			case 'trigger':
				$value = get_post_meta( $post_id, USER_AWARDS_GRAMMAR_META_TYPE, true);
				$parser->parse($value);

				$value = ($parser->nonUsableGrammar()) ? "[No Trigger Currently]" : $value;
				break;
			case 'auto_give':
				$value = empty( get_post_meta( $post_id, USER_AWARDS_AUTO_GIVE_TYPE, true) ) ? "No" : "Yes";
				break;
		}

		echo $value;
	}

	/**
	 * Outputs a modal that we will use to select our user
	 *
	 * @see user_awards_page_edit_scripts.js
	 */
	function ModalGetUser() {
		$UserSelectHTML = call_user_func(["UserAwards\Utility", "UserSelectHTML"], "UserAwards_UserID", "Choose Here");
		add_thickbox();
		echo <<<HTML
		<a id="modal-get-user-link" href="#TB_inline?width=250&height=250&inlineId=modal-get-user" style="display:none;" class="thickbox"></a>
		<div id="modal-get-user" style="display:none;">
		    <h2>User Selection</h2>
		    <p>Select a user below and then click on the submit button to assign or give an award to that user, depending on which bulk action you have taken</p>
		    <table class="form-table">
		    	<tbody>
			    	<tr>
				    	{$UserSelectHTML}
			    	</tr>
		    	</tbody>
		    </table>
		    <p class="submit">
		    	<button class="button-primary">Submit</button>
		    	<button class="button-secondary">Cancel</button>
	    	</p>
		</div>
HTML;
	}

	function UserAwardsPostAdminNotices()
	{
		$output_format = "";
		$output_params_array = [];

		if ( ! empty($_REQUEST['UserAwards_Bulk_Action']) )
		{
			// $bulk_action =
			$output_format .= "%s "; // Assigned, Gave, Removed
			$output_params_array[] = (string) esc_html($_REQUEST['UserAwards_Bulk_Action']);
		}

		if ( ! empty($_REQUEST['UserAwards_Users_Affected']) )
		{
			$output_format .= "%d awards ";
			$output_params_array[] = (int) esc_html($_REQUEST['UserAwards_Users_Affected']);
		}

		if ( ! empty($_REQUEST['UserAwards_UserID']) )
		{
			$user_assigned = get_user_by( 'ID', (int) $_REQUEST['UserAwards_UserID'] );
			$output_format .= "to %s";
			$output_params_array[] = esc_html(ucfirst($user_assigned->user_nicename));

		}

		// Putting our output format string
		if ( count($output_params_array ) )
		{
			$message_string = '<div id="message" class="updated notice is-dismissible"><p>%s</p></div>';
			array_unshift($output_params_array, sprintf($message_string, $output_format));

			call_user_func_array('printf', $output_params_array);
		}
	}
}
?>
