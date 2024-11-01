<?php
namespace UserAwards\PluginLogic;

// Including our WP_List_Table class if it is not currently available.

if( ! class_exists('WP_List_Table') ) require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class UserAwardsTable extends \WP_List_Table {
	private $UserAwards;

	function __construct( $UserAwards )
	{
		parent::__construct([
			'singular' => 'UserAward',
			'plural' => 'UserAwards',
			'ajax' => false
		]);

		$this->UserAwards = $UserAwards;
	}

	/**
	 * COLUMN SPECIFIC FUNCTIONS
	 */

	/**
	 * Define the colums that will be used in the table to display items.
	 * @return array - Columns in our table
	 */
	function get_columns() {
		return [
			'cb' => __('<input type="checkbox" />', 'user-awards'),
			'award' => __('Award', 'user-awards'),
			'user' => __('User', 'user-awards'),
			'date_assigned' => __('Date Award Assigned', 'user-awards'),
			'date_given' => __('Date Award Given', 'user-awards'),
		];
	}

	/**
	 * "Award" column handling.
	 * We also include action links within the table cell
	 * @param  array $item - Singular Award
	 * @return string      - Column value in string form
	 */
	function column_award( $item ) {
		$actions = array(
			'edit' => sprintf(
				'<a href="%s/post.php?post=%s&action=%s">%s</a>',
				admin_url(),
				esc_attr($item->award_id),
				"edit",
				"Edit"
			),
			'remove' => sprintf(
				'<a href="?post_type=%s&page=%s&action=%s&%s=%s&UserAwards_User_Id=%s&_wpnonce=%s">%s</a>',
				esc_attr( $_REQUEST['post_type'] ),
				esc_attr( $_REQUEST['page'] ),
				"UserAwards_Remove",
				$this->_args['singular'],
				$item->award_id,
				$item->user_id,
				wp_create_nonce("UserAwards_Remove_" . $item->award_id . "_" . $item->user_id),
				"Remove From User"
			)
		);

		$post = get_post( $item->award_id );

		return sprintf("%s%s",
			apply_filters( 'the_title', $post->post_title ),
			$this->row_actions($actions)
		);
	}

	/**
	 * Want to display the username instead of an id
	 * @param  [type] $item [description]
	 * @return [type]       [description]
	 */
	function column_user( $item ) {
		$user = get_user_by('id', $item->user_id);
		return esc_html(ucfirst($user->data->user_nicename));
	}

	/**
	 * "Date Assigned" column handling
	 * @param  array $item - Singular Award
	 * @return string      - Column value in string form
	 */
	function column_date_assigned( $item ) {
		return esc_html($item->date_assigned);
	}

	/**
	 * "Date Given" column handling
	 * @param  array $item - Singular Award
	 * @return string      - Column value in string form
	 */
	function column_date_given( $item ) {
		return ( empty( $item->date_given ) ) ?
			sprintf(
				'<a class="button button-primary" href="?post_type=%s&page=%s&action=%s&%s=%s&UserAwards_User_Id=%s&_wpnonce=%s"/>%s</a>',
				esc_attr( $_REQUEST['post_type'] ),
				esc_attr( $_REQUEST['page'] ),
				"UserAwards_Give",
				$this->_args['singular'],
				$item->award_id,
				$item->user_id,
				wp_create_nonce("UserAwards_Give_" . $item->award_id . "_" . $item->user_id),
				"Give Award"
			)
			:
			$item->date_given;
	}

	/**
	 * Provide a checkbox here that is filled with award ids.
	 * When a user selects one (or multiple) awards, then we are going to be
	 * adding an array to our URL as part of the query to specify which awards we are doing an action with.
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%s[%d][]" value="%d"/>',
			esc_attr($this->_args['plural']),
			esc_attr($item->user_id),
			esc_attr($item->award_id)
		);
	}

	/**
	 * Bulk actions available to use on this table.
	 */
	function get_bulk_actions() {
		return array(
			'UserAwards_Remove_Many' => "Remove Awards"
		);
	}

	function process_bulk_actions() {
		/**
		 * Process the REMOVE_AWARD bulk action in which remove the awards that have been selected
		 */
		switch ($this->current_action()) {
			case "UserAwards_Remove_Many":
				# This value should have award_id and user_id separated by a "|" character.
				if ( ! empty($_POST[$this->_args['plural']]) )
				{
					$user_award_array = $_POST[$this->_args['plural']];

					if ( ! is_array($user_award_array) )
					{
						return;
					}

					foreach( $user_award_array as $user_id => $award_ids )
					{
						if ( ! call_user_func(['UserAwards\Utility','CheckUserInput_UserID'], $user_id) )
						{
							continue;
						}

						foreach( $award_ids as $award_id )
						{
							if (! call_user_func(['UserAwards\Utility', 'CheckUserInput_PostID'], $award_id) )
							{
								continue;
							}

							$this->UserAwards->RemoveUserAward( $user_id, $award_id );
						}
					}
				}
				break;

			default:
				# code...
				break;
		}
	}

	function process_singular_actions() {
		$award_id = NULL;
		$user_id = NULL;
		$nonce = NULL;

		// Checking at first to see if the user that's performing this action can actually perform it
		if ( ! current_user_can('manage_options') )
		{
			return;
		}
		if ( ! empty($_GET[$this->_args['singular']]) )
		{
			if (! call_user_func(['UserAwards\Utility', 'CheckUserInput_PostID'], $_GET[$this->_args['singular']]) )
			{
				return;
			}

			$award_id = $_GET[$this->_args['singular']];
		}

		if ( ! empty($_GET['UserAwards_User_Id']) )
		{
			if ( ! call_user_func(['UserAwards\Utility','CheckUserInput_UserID'], $_GET['UserAwards_User_Id']) )
			{
				return;
			}

			$user_id = $_GET['UserAwards_User_Id'];
		}

		if ( ! empty($_GET['_wpnonce']) )
		{
			$nonce = sanitize_text_field($_GET['_wpnonce']);
		}

		if ( ! $award_id || ! $user_id )
		{
			return;
		}

		switch ($this->current_action()) {
			case "UserAwards_Remove":
				if ( ! wp_verify_nonce( $nonce, "UserAwards_Remove_" . $award_id . "_" . $user_id) )
				{
					wp_die("You are not able to perform this removal action");
				}

				if ( $award_id && $user_id )
				{
					$this->UserAwards->RemoveUserAward( $user_id, $award_id );
				}
				break;
			case "UserAwards_Give":
				if ( ! wp_verify_nonce( $nonce, "UserAwards_Give_" . $award_id . "_" . $user_id) )
				{
					wp_die("You are not able to perform this give action");
				}

				if ( $award_id && $user_id )
				{
					$this->UserAwards->GiveAward( $user_id, $award_id );
				}

			default:
				# code...
				break;
		}
	}

	/**
	 * Bulk action processing
	 */
	function process_actions() {
		/**
		 * WE SHOULD BE PROCESSING OUR NONCE HERE MY DUDES
		 */

		if ( ! current_user_can('manage_options') )
		{
			return;
		}

		$this->process_bulk_actions();
		$this->process_singular_actions();
	}

	/**
	 * Handles admin notices for our table currently.
	 */
	static function UserAwardsTableAdminNotices() {
		$awards_removed_string_array = NULL;

		/**
		 * Based on the awards removed, indicate how many awards were removed from which person.
		 */
		if ( isset($_REQUEST['action']) && $_REQUEST['action'] === "UserAwards_Remove_Many" )
		{
			if ( ! empty($_REQUEST['userawards']) )
			{
				// Validating userawards array

				foreach( $_REQUEST['userawards'] as $valid_user_id => $valid_post_ids )
				{
					if ( ! call_user_func(['UserAwards\Utility','CheckUserInput_UserID'], $valid_user_id) )
					{
						return;
					}

					foreach ( $valid_post_ids as $valid_post_id )
					{
						if ( ! call_user_func(['UserAwards\Utility', 'CheckUserInput_PostID'], $valid_post_id) )
						{
							return;
						}
					}
				}

				$awards_removed_string_array = array_map(function( $user_id, $post_ids ) {
					$user = get_user_by("ID", $user_id);
					return sprintf(
						"%d award%s removed from %s",
						count($post_ids),
						(count($post_ids) > 1) ? 's' : '', // Plural / Singular
						ucfirst($user->user_nicename)
					);
				}, array_keys($_REQUEST['userawards']), $_REQUEST['userawards']);
			}
		}

		if ( ! is_null( $awards_removed_string_array ) )
		{
			$awards_removed_string = "";

			foreach( $awards_removed_string_array as $index => $removed_string )
			{
				if ( $index )
				{
					$awards_removed_string .= " and ";
				}

				$awards_removed_string .= $removed_string;
			}

			$message_string = '<div id="message" class="updated notice is-dismissible"><p>%s</p></div>';

			printf( $message_string, $awards_removed_string );
		}
	}

	/** End Column Specific Functions */

	function prepare_items()
	{
		global $wpdb;

		$this->process_actions();

		$columns = $this->get_columns();

		$this->_column_headers = [
			$columns, // Visible Columns
			[], // Hidden Columns
			[] // Sortable Columns
		];

		$query = "SELECT * FROM {$wpdb->prefix}" . USER_AWARDS_TABLE_USER_AWARDS;

		$this->items = $wpdb->get_results($query);
	}
}
?>
