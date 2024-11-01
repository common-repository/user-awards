<?php

namespace UserAwards;

class Utility {
	/**
	 * Displays this plugins's formatted method of username (with e-mail)
	 * output
	 * @param WP_User $user - WP_User object
	 */
	static function FormatUserDisplay( $user ) {
		$user_nicename = ucfirst($user->data->user_nicename);
		$user_email = $user->data->user_email;

		return "{$user_nicename} - ({$user_email})";
	}

	/**
	 * Function that outputs a <select> and nested <option> elements that correspond
	 * to a listing of all of the users
	 * @param string $name        - Name and ID of the <select>
	 * @param string $initialText - Text seen initially as the first <option> element
	 * @param mixed $users        - Can start off as null, but will always end up being a list of
	 *                            WP_User objects
	 */
	static function UserSelectHTML( $name, $initialText = "Select A User", $users = NULL ) {
		$users = ( empty( $users ) ) ? get_users() : $users;
		$name = esc_attr($name);
		$returnHTML = <<<HTML
		<select id="{$name}" name="{$name}">
		<option value="0">{$initialText}</option>
HTML;
		foreach( $users as $user ) {
			$ID = esc_attr( $user->ID );
			$FormattedUserHTML = esc_html(call_user_func(["UserAwards\Utility","FormatUserDisplay"], $user));
			$returnHTML .= <<<HTML
			<option value="{$ID}">{$FormattedUserHTML}</option>
HTML;
		}

		$returnHTML .= <<<HTML
		</select>
HTML;

		return $returnHTML;
	}

	static function CheckUserInput_UserID( $user_id ) {
		// Are our inputted user ids invals?
		if ( ! intval( $user_id ) )
		{
			return false;
		}


		// Are our inputted user ids actual users?
		if ( ! get_userdata( $user_id ) )
		{
			return false;
		}

		return true;
	}

	static function CheckUserInput_PostID( $post_id ) {
		// Are our award id's invals?
		if ( ! intval( $post_id ) )
		{
			return false;
		}

		// Does our award exist?
		if ( ! get_post( $post_id ) )
		{
			return false;
		}

		return true;
	}
}

?>