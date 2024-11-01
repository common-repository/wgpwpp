<?php

use const Wgpwpp_Cache\CACHE_DIR;

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://www.wedos.cz
 * @since      1.0.0
 *
 * @package    Wgpwpp
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WPINC') || !defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

// Plugin dependencies loader
require plugin_dir_path(__FILE__).'loader.php';

// WP Cache
include_once __DIR__ . '/includes/wpcache/common.php';

// destroy all plugins settings and data
if (Wgpwpp::is_multisite())
{
  if (false === is_super_admin())
    exit;

  $blog_ids = Wgpwpp::get_multisite_ids();

  foreach ($blog_ids as $blog_id)
  {
    switch_to_blog($blog_id);
    uninstall_blog();
    restore_current_blog();
  }
}
else
{
  if (false === current_user_can('activate_plugins'))
    exit;

  uninstall_blog();
}


// Remove advanced-cache.php only if its ours.
if ( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) ) {
  $contents = file_get_contents( WP_CONTENT_DIR . '/advanced-cache.php' );
  if ( strpos( $contents, 'namespace Wgpwpp_Cache;' ) !== false ) {
    unlink( WP_CONTENT_DIR . '/advanced-cache.php' );
  }
}


function delete( $path )  {
  if ( is_file( $path ) ) {
    unlink( $path );
    return;
  }

  if ( ! is_dir( $path ) ) {
    return;
  }

  $entries = scandir( $path );
  foreach ( $entries as $entry ) {
    if ( $entry == '.' || $entry == '..' ) {
      continue;
    }

    delete( $path . '/' . $entry );
  }

  rmdir( $path );
};

delete( CACHE_DIR );


/**
 * Uninstall callback
 *
 * @since 1.0.4
 * @return void
 */
function uninstall_blog()
{
  $plugin = new Wgpwpp();
  $plugin->option->reset();
  unset($plugin);
}