<?php
/**
 * Wgpwpp advanced-cache.php dropin
 *
 * Based on work https://github.com/kovshenin/surge
 *
 * @package Wgpwpp_Cache
 */

namespace Wgpwpp_Cache;

$filename = WP_CONTENT_DIR . '/plugins/wgpwpp/includes/wpcache/serve.php';
if ( defined( 'WP_PLUGIN_DIR' ) ) {
	$filename = WP_PLUGIN_DIR . '/wgpwpp/includes/wpcache/serve.php';
}

if (!file_exists($filename))
  return;

include_once( $filename );
