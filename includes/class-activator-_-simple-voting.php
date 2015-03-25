<?php

/**
 * Fired during plugin activation
 *
 * @link       http://seoringer.com/simple-voting-plugin-for-wordpress/
 * @since      1.0.0
 *
 * @package    Simple_Voting
 * @subpackage Simple_Voting/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Simple_Voting
 * @subpackage Simple_Voting/includes
 * @author     Seoringer <seoringer@gmail.com>
 */
class Activator___Simple_Voting {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;

		$table_name = $wpdb->prefix . 'simple_voting_subjects';
		$sql = "CREATE TABLE $table_name (
			`id` INT NULL AUTO_INCREMENT DEFAULT NULL,
			`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`content` VARCHAR(500) NOT NULL,
			`URL` VARCHAR(300) NOT NULL,
			`randomKey` CHAR(3) NOT NULL,
			`rating` DECIMAL( 4, 2 ) NOT NULL,
			`votes` INT NOT NULL,
			PRIMARY KEY (`id`)
		);";

		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'simple_voting_votes';
		$sql = "CREATE TABLE $table_name (
			`id` INT NULL AUTO_INCREMENT DEFAULT NULL,
			`voteTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`id_subject` INT NOT NULL,
			`voteValue` TINYINT NOT NULL,
			`userName` VARCHAR(250) NOT NULL,
			`userEmail` VARCHAR(250) NOT NULL,
			`userComment` MEDIUMTEXT NULL DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `UserVote` (`id_subject`,`userEmail`)
		);";

		dbDelta( $sql );
	}

}
