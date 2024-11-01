<?php
namespace UserAwards\PluginLogic;

class RegistrationHooks {
	/** Function to handle plugin activation */
	static function Activate() {
		global $wpdb;
		$wpdb_table = $wpdb->prefix . USER_AWARDS_TABLE_USER_AWARDS;
		$wp_posts_table = $wpdb->prefix . "posts";
		$wpdb_collation = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb_table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				award_id bigint(20) unsigned NOT NULL,
				date_assigned datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				date_given datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				FOREIGN KEY  fk_posts(award_id)
				REFERENCES  {$wp_posts_table}(ID)
			) {$wpdb_collation};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // for dbDelta
		dbDelta( $sql );

		add_option( USER_AWARDS_DB_VERSION_KEY, USER_AWARDS_DB_VERSION_VALUE );
	}

	static function Deactivate() {
		// Don't really know what I'd do for deactivation.
	}

	/** Function to handle plugin uninstallation */
	static function Uninstall() {
		global $wpdb;
		$wpdb_table = $wpdb->prefix . USER_AWARDS_TABLE_USER_AWARDS;

		$sql = "DROP TABLE IF EXISTS {$wpdb_table}";
		$wpdb->query( $sql );
		delete_option( USER_AWARDS_DB_VERSION_KEY );
	}
}
?>
