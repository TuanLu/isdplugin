<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://ishopdesign.com
 * @since             1.0.0
 * @package           iShopDesign
 *
 * @wordpress-plugin
 * Plugin Name:       iShopDesign
 * Plugin URI:        http://ishopdesign.com
 * Description:       iShopDesign is a page builder which will help you create unique page quickly and fancy
 * Version:           1.0.0
 * Author:            iShopDesign
 * Author URI:        http://ishopdesign.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ishopdesign
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ISD IN PLUGIN
 */

define( 'ISD_PLUGIN', true );

/**
 * Currently pligin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ISHOPDESIGN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ishopdesign-activator.php
 */
function activate_ishopdesign() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ishopdesign-activator.php';
	iShopDesign_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ishopdesign-deactivator.php
 */
function deactivate_ishopdesign() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ishopdesign-deactivator.php';
	iShopDesign_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ishopdesign' );
register_deactivation_hook( __FILE__, 'deactivate_ishopdesign' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ishopdesign.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new iShopDesign();
	$plugin->run();

}
run_plugin_name();

