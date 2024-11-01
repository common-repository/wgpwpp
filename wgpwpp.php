<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.wedos.cz
 * @since             1.0.0
 * @package           Wgpwpp
 *
 * @wordpress-plugin
 * Plugin Name:       WEDOS Global (CDN Cache & Security)
 * Plugin URI:        https://www.wedos.com/protection/#wgp-plugin
 * Description:       Activate and use the WEDOS Global service. WEDOS Global brings global security for your WordPress website, ensures low latency and minimal loading time.
 * Version:           1.2.2
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            WEDOS
 * Author URI:        https://www.wedos.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wgpwpp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// cache request
if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
  include_once( __DIR__ . '/includes/wpcache/cache.php' );
}

/**
 * Plugin Dependencies Loader
 */
require plugin_dir_path( __FILE__ ) . 'loader.php';

/**
 * Activation Hook
 */
register_activation_hook( __FILE__, ['Wgpwpp_Activator', 'activate']);

/**
 * Deactivation Hook
 */
register_deactivation_hook( __FILE__, ['Wgpwpp_Deactivator', 'deactivate']);

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wgpwpp() {

  // PHP version check
  if (!Wgpwpp_Activator::test_php_version())
    return;

  $plugin = new Wgpwpp();
	$plugin->run();

}
run_wgpwpp();
