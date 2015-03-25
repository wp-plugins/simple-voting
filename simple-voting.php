<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://seoringer.com
 * @since             1.0.0
 * @package           Simple_Voting
 *
 * @wordpress-plugin
 * Plugin Name:       Simple voting
 * Plugin URI:        http://seoringer.com/simple-voting-plugin-for-wordpress/
 * Description:       Simple voting and rating system. In simplest case you just need to insert in your text one word: `[voting]`.
 * Version:           1.0.0
 * Author:            Seoringer
 * Author URI:        http://seoringer.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simple-voting
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activator-_--simple-voting.php
 */
function activate_simple_voting() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator-_-simple-voting.php';
	Activator___Simple_Voting::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator-_-simple-voting.php
 */
function deactivate_simple_voting() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator-_-simple-voting.php';
	Deactivator___Simple_Voting::deactivate();
}

register_activation_hook( __FILE__, 'activate_simple_voting' );
register_deactivation_hook( __FILE__, 'deactivate_simple_voting' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-starter-_-simple-voting.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_simple_voting() {

	$plugin = new Starter___Simple_Voting();
	$plugin->run();

}

run_simple_voting();
