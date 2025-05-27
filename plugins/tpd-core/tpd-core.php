<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wbcomdesigns.com/
 * @since             1.0.0
 * @package           Tpd_Core
 *
 * @wordpress-plugin
 * Plugin Name:       TPD Core
 * Plugin URI:        https://wbcomdesigns.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            WBCOM Team
 * Author URI:        https://wbcomdesigns.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tpd-core
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
define( 'TPD_CORE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tpd-core-activator.php
 */
function activate_tpd_core() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tpd-core-activator.php';
	Tpd_Core_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tpd-core-deactivator.php
 */
function deactivate_tpd_core() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tpd-core-deactivator.php';
	Tpd_Core_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tpd_core' );
register_deactivation_hook( __FILE__, 'deactivate_tpd_core' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tpd-core.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tpd_core() {

	$plugin = new Tpd_Core();
	$plugin->run();

}
run_tpd_core();
