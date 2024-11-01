<?php
/**
Plugin Name: User Awards
Description: Let your registered members know how much you appreciate them! Enhances your site with the abilty to assign and give awards to members based on the actions that they take. Your members will have a lifetime supply of cherished memories that they can hold dear to their hearts for years to come.
Author: Kellan Martin
Version: 0.1.1
Author URI: http://kellanmartin.com
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * ###	Notes for the devs 	###
 *
 * While I was creating this plugin I had it in my mind that I would be naming this plugin something like UserAwardss or something around there, so that's why that prefix is pretty much all over the place. Please forgive me and don't kill my family for some weird naming. It had to be done... And it can always be refactored!!!
 */

// Plugin Constants
require_once plugin_dir_path( __FILE__ ) . "/constants.php";

// Include our scripts
require_once plugin_dir_path( __FILE__ ) . "/vendor/autoload.php";

// Grab our database instance
global $wpdb;

// Global variable acessible throughout WP in order to apply awards to users.
global $UserAwards;

// Activation, Deactivation, Uninstall
register_activation_hook( __FILE__, [ 'UserAwards\PluginLogic\RegistrationHooks', 'Activate' ] );
register_deactivation_hook( __FILE__, [ 'UserAwards\PluginLogic\RegistrationHooks', 'Deactivate' ] );
register_uninstall_hook( __FILE__, [ 'UserAwards\PluginLogic\RegistrationHooks', 'Uninstall' ] );

// Enqueuing our plugin assets such as styles and scripts (if needed)
add_action( 'admin_enqueue_scripts', 'EnqueueUserAwardsPluginAssets' );

// Wait until the plugins loaded action in order to have wp_get_current_user() function
add_action( 'plugins_loaded', 'ApplyUserAwardsListeners' );

// Holds our user awards business logic
$UserAwards = new UserAwards\BusinessLogic\Core($wpdb);

// Plugin meta box handling
$UserAwardsMetaBoxes = new UserAwards\PluginLogic\PostType\MetaBoxes( USER_AWARDS_POST_TYPE, $UserAwards );

// Holds our plugin logic, which includes Post and Meta type additions
$UserAwardsPlugin = new UserAwards\PluginLogic\Core( $UserAwards, $UserAwardsMetaBoxes );

/**
 * Loop through all the defined awards.
 * This requires us to have a current_user which, if available, will
 * have an award listener applied to that user. This in turn allows us to automatically
 * assign an award to a user based on the actions that they are taking.
 */

function ApplyUserAwardsListeners() {
	global $UserAwards;

	$current_user = wp_get_current_user();

	if ( $current_user->ID > 0 )
	{
		$awards = get_posts([ 'post_type' => USER_AWARDS_POST_TYPE ]);

		foreach( $awards as $award )
		{
			$grammar = new UserAwards\Grammar\Core();
			$award_grammar = get_post_meta( $award->ID, USER_AWARDS_GRAMMAR_META_TYPE, true );

			// The parse function can throw errors, so wrap it in a try block
			try
			{
				$grammar->parse( $award_grammar );
			}
			catch( Exception $e )
			{
				continue;
			}

			// Apply our listener. Set it and forget it. Include a parsed grammar and inject the UserAwards dependency
			$listener = new UserAwards\Listener\Core( $award->ID, $grammar, $UserAwards );
			$listener->add_listeners( $current_user );

		}
	}
}

/**
 * Function used to enqueue any required JS or CSS assets
 */
function EnqueueUserAwardsPluginAssets( $hook ) {
	// Enqueue these scripts on post specific pages, i.e. New Post, Edit Post.
	// Awards are a custom post type so that's why this matters.
	if ( $hook === "post.php" || $hook === "edit.php" || $hook === "post-new.php" )
	{
		wp_enqueue_style(
			'general_award_styles',
			plugins_url( 'assets/styles/user_awards_styles.css', __FILE__ ),
			[],
			false,
			false
		);
		wp_enqueue_script(
			'UserAwards_Edit_Bulk_Scripts',
			plugins_url( 'assets/scripts/user_awards_page_edit_scripts.js', __FILE__ ),
			array('common')
		);
	}

	if ( $hook === "user_awards_cpt_page_user-awards-admin-view" )
	{
		wp_enqueue_script(
			'UserAwards_Admin_View_Scripts',
			plugins_url( 'assets/scripts/user_awards_admin_view_scripts.js', __FILE__ ),
			array('common')
		);
	}
}
?>
