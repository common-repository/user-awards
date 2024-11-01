<?php
namespace UserAwards\PluginLogic\PostType;

class MetaBoxes {
	private $post_type;
	private $UserAwards;

	function __construct( $post_type, $UserAwards ) {
		$this->post_type = $post_type;
		$this->UserAwards = $UserAwards;
	}
/**
 * Main function to output our post meta box fields
 */
	public function PostTypeMetaBoxes() {
		$this->_addGrammarMeta(); // Trigger Meta Box
		$this->_applyAwardToUserMeta();
		$this->_addAutoGiveAwardMeta();
	}

	private function _addGrammarMeta() {
		add_meta_box(
			$this->post_type . "_grammar", // CSS ID Attribute
			'Award Trigger', // Title
			[$this, '_grammarMetaHTML'], // Callback
			$this->post_type, // Page
			'advanced', // Context
			'default', // priority
			null // Callback Args
		);
	}

	/**
	 * HTML for the entity area of our grammar meta
	 * @return string HTML string.
	 */
	function _grammarMetaEntityHTML( $name ) {
		return <<<HTML
		<span class="award-grammar-section smaller">
			<label for="grammar-meta-entity">Entity</label>
			<input id="grammar-meta-entity" type="text" class="invisible-text-input no-padding-l" value="CURRENT_USER_META" readonly="readonly" name="{$name}"/>
		</span>
HTML;
	}

	/**
	 * HTML for the trigger type of our grammar meta box
	 * @param  $value Value for our trigger string entity
	 * @return string HTML string.
	 */
	function _grammarMetaTriggerTypeHTML( $value, $name ) {
?>
		<span class="award-grammar-section">
			<label for="grammar-meta-trigger-type">Trigger Type</label>
			<select id="grammar-meta-trigger-type" name="<?php echo $name; ?>">
				<option value="updated" <?php selected($value, "updated"); ?>>UPDATED</option>
				<option value="created" <?php selected($value, "created"); ?>>CREATED</option>
				<option value="assigned" <?php selected($value, "assigned"); ?>>ASSIGNED</option>
			</select>
		</span>
<?php
	}

	function _grammarMetaWhere( $name ) {
		return <<<HTML
		<span class="award-grammar-section smaller where-container">
			<input type="text" name="{$name}" class="invisible-text-input" value="WHERE" readonly="readonly">
		</span>
HTML;
	}

	/**
	 * HTML for the trigger descriptor of our grammar meta box
	 * @param  $value Value for our trigger string entity
	 * @return string HTML string
	 */
	function _grammarMetaTriggerDescriptorHTML( $value, $name ) {
		return <<<HTML
		<span class="award-grammar-section">
			<label for="grammar-meta-trigger-descriptor">Trigger Descriptor</label>
			<input id="grammar-meta-trigger-descriptor" type="text" name="{$name}" value="{$value}"/>
		</span>
HTML;
	}

	/**
	 * HTML for the trigger operator of our grammar meta box
	 * @param  $value Value for our trigger string entity
	 * @return string HTML string
	 */
	function _grammarMetaTriggerOperatorHTML( $value, $name ) {
?>
		<span class="award-grammar-section">
			<label for="grammar-meta-trigger-operator">Trigger Operator</label>
			<select id="grammar-meta-trigger-operator" name="<?php echo $name; ?>">
				<option value="gt" <?php selected($value, "gt"); ?>>GT</option>
				<option value="lt" <?php selected($value, "lt"); ?>>LT</option>
				<option value="eq" <?php selected($value, "eq"); ?>>EQ</option>
				<option value="gteq" <?php selected($value, "gteq"); ?>>GTEQ</option>
				<option value="lteq" <?php selected($value, "lteq"); ?>>LTEQ</option>
			</select>
		</span>
<?php
	}

	/**
	 * HTML for the trigger control of our grammar meta box
	 * @param  $value Value for our trigger string entity
	 * @return string HTML string
	 */
	function _grammarMetaTriggerControlHTML( $value, $name ) {
		return <<<HTML
		<span class="award-grammar-section">
			<label for="grammar-meta-trigger-control">Trigger Control</label>
			<input id="grammar-meta-trigger-control" type="text" name="{$name}" value="{$value}"/>
		</span>
HTML;
	}

	/**
	 * HTML for grammar meta text input for awards
	 * @param  WP_Award $post - The full post that is being edited currently
	 * @return void
	 */
	function _grammarMetaHTML( $post ) {
		$grammarString = get_post_meta( $post->ID, USER_AWARDS_GRAMMAR_META_TYPE, true);
		$eGrammarString = esc_attr( $grammarString );

		// Parse our string out
		$parser = new \UserAwards\Grammar\PluginParser();
		$parser->parse($grammarString);

		wp_nonce_field( plugin_basename(__FILE__), 'UserAwards_Save_Grammar_Meta');

		echo $this->_grammarMetaEntityHTML(USER_AWARDS_GRAMMAR_META_ENTITY);
		echo $this->_grammarMetaTriggerTypeHTML($parser->trigger_type, USER_AWARDS_GRAMMAR_META_TRIGGER_TYPE);
		echo $this->_grammarMetaWhere( USER_AWARDS_GRAMMAR_META_WHERE );
		echo $this->_grammarMetaTriggerDescriptorHTML(
			$parser->trigger->descriptor,
			USER_AWARDS_GRAMMAR_META_TRIGGER_DESCRIPTOR
		);
		echo $this->_grammarMetaTriggerOperatorHTML(
			$parser->trigger->operator,
			USER_AWARDS_GRAMMAR_META_TRIGGER_OPERATOR
		);
		echo $this->_grammarMetaTriggerControlHTML(
			$parser->trigger->control,
			USER_AWARDS_GRAMMAR_META_TRIGGER_CONTROL
		);
	}

	/**
	 * Adds in a meta box for our "Auto Give Award" functionality
	 */
	private function _addAutoGiveAwardMeta() {
		add_meta_box(
			$this->post_type . "_auto_give", // CSS ID Attribute
			'Auto Give Award', // Title
			[$this, '_autoGiveAwardHTML'], // Callback
			$this->post_type, // Page
			'side', // Context
			'default', // priority
			null // Callback Args
		);
	}

	/**
	 * HTML for auto give award checkbox for award
	 * @param  WP_Post $post - The full post that is being edited currently
	 * @return void
	 */
	function _autoGiveAwardHTML( $post ) {
		$auto_give_award_value = get_post_meta( $post->ID, 'UserAwards_Auto_Give', true );
		$checked_box = checked( $auto_give_award_value, 'on', false );

		// Outputting Nonce
		wp_nonce_field( plugin_basename(__FILE__), 'UserAwards_Save_Auto_Give_Meta');
		echo <<<HTML
		<input type="checkbox" name="UserAwards_Auto_Give" id="UserAwards_Auto_Give" value="on" {$checked_box}/>
		<label for="UserAwards_Auto_Give">Checking this box will automatically give award to user when they trigger the award</label>
HTML;
	}

	function _applyAwardToUserMeta() {
		add_meta_box(
			$this->post_type . "_apply_award", // CSS ID Attribute
			'Apply/Give Award To User', // Title
			[$this, '_applyAwardToUserHTML'], // Callback
			$this->post_type, // Page
			'side', // Context
			'default', // priority
			null // Callback Args
		);
	}

	/**
	 * Meta box will display a list of the the users that are available.
	 * Enabling the ability to select a user out of the bunch, and apply the award to a user.
	 * @param  WP_Post $post - Post we are currently editing
	 * @return void
	 */
	function _applyAwardToUserHTML( $post ) {
		$users = get_users(); // Array of WP_User objects
		$submit_button = get_submit_button( 'Apply', 'primary large', 'submit', false, '' );
		$UserSelectHTML = call_user_func(["UserAwards\Utility", "UserSelectHTML"], "UserAwards_User_Apply");

		// Haha, what the fuck even is PHP?
		wp_nonce_field( plugin_basename(__FILE__), 'UserAwards_Apply_Award_To_User');
		echo <<<HTML
		<label for="UserAwards_User_Apply">Select a user from this dropdown and submit in order to apply this award to the user.</label>
		<br/>
		{$UserSelectHTML}
		<div class="UserAwards_Actions">
			<span class="give-award-checkbox">
				<label for="UserAwards_User_Give">Check box to give award to your user</label>
				<input id="UserAwards_User_Give" name="UserAwards_User_Give" type="checkbox"/>
			</span>
			{$submit_button}
		</div>
HTML;
	}

	/**
	 * Function that checks to see if a user is posting
	 * grammar meta.
	 * @return bool - Whether or not user is posting all
	 *                of the items associated with a
	 *                grammar meta.
	 */
	function userPostingGrammarMeta() {
		$grammarInputs = [
			USER_AWARDS_GRAMMAR_META_ENTITY,
			USER_AWARDS_GRAMMAR_META_TRIGGER_TYPE,
			USER_AWARDS_GRAMMAR_META_WHERE,
			USER_AWARDS_GRAMMAR_META_TRIGGER_DESCRIPTOR,
			USER_AWARDS_GRAMMAR_META_TRIGGER_OPERATOR,
			USER_AWARDS_GRAMMAR_META_TRIGGER_CONTROL
		];

		foreach( $grammarInputs as $grammarInput )
		{
			if ( ! isset( $_POST[$grammarInput]) )
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Used to save UserAwards specific meta values
	 * @param int $post_id - ID of the post we have saved.
	 */
	function UserAwardsSaveMetaBoxes( $post_id ) {

		if ( ! current_user_can( 'manage_options' )	)
		{
			return;
		}

		$skip_autosave_actions = [
			USER_AWARDS_GRAMMAR_META_ENTITY,
			USER_AWARDS_GRAMMAR_META_TRIGGER_TYPE,
			USER_AWARDS_GRAMMAR_META_WHERE,
			USER_AWARDS_GRAMMAR_META_TRIGGER_DESCRIPTOR,
			USER_AWARDS_GRAMMAR_META_TRIGGER_OPERATOR,
			USER_AWARDS_GRAMMAR_META_TRIGGER_CONTROL,
			USER_AWARDS_AUTO_GIVE_TYPE,
			USER_AWARDS_USER_APPLY_TYPE
		];

		// Reduce an array to a truthy/falsy boolean that will indicate whether any of our skip_autosave_actions are occuring.
		$performing_skip_autosave_action = array_reduce( $skip_autosave_actions, function( $acc, $current ) {
			if ( ! $acc )
			{
				return in_array( $current, $_POST );
			}
		}, false);

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE && $performing_skip_autosave_action )
		{
			return;
		}

		// Are we posting an UserAwards_Grammar?
		if ( $this->userPostingGrammarMeta() )
		{
			// Check our nonce field to see if they're good.
			check_admin_referer( plugin_basename(__FILE__), 'UserAwards_Save_Grammar_Meta' );

			// This area obtains all of our input data.
			$grammarEntity = sanitize_text_field($_POST[USER_AWARDS_GRAMMAR_META_ENTITY]);
			$grammarTrType = sanitize_text_field($_POST[USER_AWARDS_GRAMMAR_META_TRIGGER_TYPE]);
			$grammarWhere = sanitize_text_field($_POST[USER_AWARDS_GRAMMAR_META_WHERE]);
			$grammarTrDesc = sanitize_text_field($_POST[USER_AWARDS_GRAMMAR_META_TRIGGER_DESCRIPTOR]);
			$grammarTrOp = sanitize_text_field($_POST[USER_AWARDS_GRAMMAR_META_TRIGGER_OPERATOR]);
			$grammarTrCn = sanitize_text_field($_POST[USER_AWARDS_GRAMMAR_META_TRIGGER_CONTROL]);

			// ex. CURRENT_USER_META UPDATED WHERE key=hours EQ 2
			$grammarString = "{$grammarEntity} {$grammarTrType} {$grammarWhere} {$grammarTrDesc} {$grammarTrOp} {$grammarTrCn}";

			// Save the meta box data as post meta
			update_post_meta( $post_id, USER_AWARDS_GRAMMAR_META_TYPE, $grammarString );
		}

		// Are we posting a UserAwards Auto Give value?
		if ( isset( $_POST['UserAwards_Auto_Give'] ) )
		{
			// Check our nonce fields to see if they're good.
			check_admin_referer( plugin_basename(__FILE__), 'UserAwards_Save_Auto_Give_Meta' );

			// Save the meta box data as post meta
			update_post_meta( $post_id, 'UserAwards_Auto_Give', sanitize_text_field($_POST['UserAwards_Auto_Give'] ) );
		}
		/**
		 * We are not posting a UserAwards Auto Give Value, which means that the user has NOT selected
		 * it in the admin view. This is an innate functionality of <input type="checkbox">'es
		 */
		else if ( get_post_type( $post_id ) === $this->post_type )
		{
			delete_post_meta( $post_id, 'UserAwards_Auto_Give');
		}

		// Are we trying to apply awards to users?
		if ( isset( $_POST['UserAwards_User_Apply'] ) )
		{
			check_admin_referer( plugin_basename(__FILE__), 'UserAwards_Apply_Award_To_User' );

			if ( ! call_user_func('UserAwards\Utility::CheckUserInput_UserID', $_POST['UserAwards_User_Apply']) )
			{
				return;
			}

			if ( isset( $_POST['UserAwards_User_Give']) )
			{
				$this->UserAwards->GiveAward( sanitize_text_field($_POST['UserAwards_User_Apply']), $post_id );
			}
			else
			{
				// Assign the award to the given user
				$this->UserAwards->AssignAward( sanitize_text_field($_POST['UserAwards_User_Apply']), $post_id );
			}
		}
	}
}
?>
