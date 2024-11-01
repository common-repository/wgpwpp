<?php
if (!defined('ABSPATH'))
  exit;

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.wedos.cz
 * @since      1.0.0
 *
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
   * @since    1.0.0
   * @param bool $networkwide
   * @return void
	 */
	public static function deactivate($networkwide)
  {
    if (Wgpwpp::is_multisite())
    {
      if ($networkwide)
      {
        if (false === is_super_admin())
          return;

        $blog_ids = Wgpwpp::get_multisite_ids();

        foreach ($blog_ids as $blog_id)
        {
          switch_to_blog($blog_id);
          self::deactivate_blog();
          restore_current_blog();
        }
      }
      else
      {
        if (false === current_user_can('activate_plugins'))
          return;

        self::deactivate_blog();
      }
    }
    else
    {
      self::deactivate_blog();
    }

    // Remove advanced-cache.php only if its ours.
    if ( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) )
    {
      $contents = file_get_contents( WP_CONTENT_DIR . '/advanced-cache.php' );
      if ( strpos( $contents, 'namespace Wgpwpp_Cache;' ) !== false ) {
        unlink( WP_CONTENT_DIR . '/advanced-cache.php' );
      }
    }
	}


  /**
   * Blog-level deactivation
   *
   * @since 1.0.4
   * @return void
   */
  private static function deactivate_blog()
  {
    // Remove advanced-cache.php on deactivation
    Wgpwpp_Option::_set(Wgpwpp_Option::OPTION_WP_CACHE_STATUS, null);

    // Remove the activation time record
    if (get_option('wgpwpp_activation_time')) delete_option('wgpwpp_activation_time');
    if (get_option('wgpwpp_rating_dismissed')) delete_option('wgpwpp_rating_dismissed');
  }
}
