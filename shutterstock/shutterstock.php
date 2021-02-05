<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.shutterstock.com
 * @since             1.0.0
 * @package           Shutterstock
 *
 * @wordpress-plugin
 * Plugin Name:       Shutterstock
 * Description:       Access exceptional, royalty-free content straight from WordPress.
 * Version:           1.3.4
 * Author:            Shutterstock
 * License:           MIT
 * License URI:       http://opensource.org/licenses/mit-license.html
 * Text Domain:       shutterstock
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SHUTTERSTOCK_VERSION', '1.3.4' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-shutterstock-activator.php
 */
function activate_shutterstock() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shutterstock-activator.php';
	Shutterstock_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-shutterstock-deactivator.php
 */
function deactivate_shutterstock() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shutterstock-deactivator.php';
	Shutterstock_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_shutterstock' );
register_deactivation_hook( __FILE__, 'deactivate_shutterstock' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-shutterstock.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_shutterstock() {

	$plugin = new Shutterstock();
	$plugin->run();

}
run_shutterstock();
