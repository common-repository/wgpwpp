<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
const WGPWPP_VERSION = '1.2.2';
const WGPWPP_PLUGIN_NAME = 'wgpwpp';
const WGPWPP_PLUGIN_FILE = __FILE__;


/**
 * Plugin Autoloader
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wgpwpp-autoloader.php';


/**
 * Latte Autoloader
 */
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';