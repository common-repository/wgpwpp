<?php
if (!defined('ABSPATH'))
  exit;

/**
 * Fired during plugin activation
 *
 * @link       https://www.wedos.cz
 * @since      1.0.0
 *
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Activator
{
  /**
   * Required WordPress version
   *
   * @since 1.0.0
   */
  const WP_VERSION_REQUIRED = '5.6';

  /**
   * Required PHP version
   *
   * @since 1.0.0
   */
  const PHP_VERSION_REQUIRED = '7.4';


	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
   * @param bool $networkwide
	 * @since    1.0.0
	 */
	public static function activate($networkwide)
  {
    // register test endpoint
    add_action('rest_api_init', function() {
      register_rest_route('wgpwpp/v1', '/activation', [
        'methods'             => 'GET',
        'callback'            => function() { return true; },
        'permission_callback' => '__return_true'
      ]);
    }, 1);

    // PHP version check
    $php_version = phpversion();
    if (!self::test_php_version($php_version))
    {
      $error = __('PHP version needs to be at least %s to activate this plugin. Your PHP version is %s.', 'wgpwpp');
      wp_die(
        sprintf($error, self::PHP_VERSION_REQUIRED, $php_version),
        'WGPWPP PHP version test'
      );
    }

    // WordPress version check
    $wp_version = get_bloginfo('version');
    if (!self::test_wp_version($wp_version))
    {
      $error = __('WordPress version needs to be at least %s to activate this plugin. Your WordPress version is %s.', 'wgpwpp');
      wp_die(
        sprintf($error, self::WP_VERSION_REQUIRED, $wp_version),
        'WGPWPP WordPress version test'
      );
    }

    // WordPress REST API check
    $result = [];
    if (!self::test_rest_availability($result))
    {
      $error = __('WordPress REST API is unavailable. It must be available for the plugin to work properly.', 'wgpwpp');
      wp_die($error, 'WGPWPP WordPress REST API test.', $result);
    }

    // MultiSite Activation
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
          self::activate_blog();
          restore_current_blog();
        }
      }
      else
      {
        if (!Wgpwpp::is_multisite_subdomain())
        {
          wp_die(
            __('This plugin can be activated only from Network Administration panel in WordPress Multisite installation with Subdirectories network type.','wgpwpp'),
            'Wordpress Multisite Subdirectories activation restriction'
          );
        }

        if (false === current_user_can('activate_plugins'))
          return;

        self::activate_blog();
      }
    }
    else
    {
      self::activate_blog();
    }
	}


  /**
   * Blog-level activation
   *
   * @since 1.1.0
   * @return void
   */
  private static function activate_blog()
  {
    // Re-install cache on activation
    Wgpwpp_Option::_set(Wgpwpp_Option::OPTION_WP_CACHE_STATUS, null);

    // Record the plugin activation time
    $curr_time = date('Y-m-d H:i:s');
    update_option('wgpwpp_activation_time', $curr_time);
  }


  /**
   * Checks WordPress version
   *
   * @since 1.0.0
   * @param string $wp_version current WordPress version
   * @return bool
   */
  private static function test_wp_version(string $wp_version): bool
  {
    return self::test_version($wp_version, self::WP_VERSION_REQUIRED);
  }


  /**
   * Checks PHP version
   *
   * @since 1.0.0
   * @param string $php_version current PHP version
   * @return bool
   */
  public static function test_php_version(string $php_version = ''): bool
  {
    if (!$php_version)
      $php_version = phpversion();

    return self::test_version($php_version, self::PHP_VERSION_REQUIRED);
  }


  /**
   * Compares version
   *
   * Returns TRUE if required version is higher than current.
   *
   * @since 1.0.0
   * @param string $current current version
   * @param string $required required version
   * @return bool
   */
  private static function test_version(string $current, string $required): bool
  {
    $current_parsed = explode('.', $current);
    $required_parsed = explode('.', $required);

    if (count($required_parsed) > count($current_parsed))
    {
      for ($i = 0; $i < (count($required_parsed) - count($current_parsed)); $i++)
        $current_parsed[] = 0;
    }

    foreach ($required_parsed as $k => $v)
    {
      if ($current_parsed[$k] < $v)
        return false;

      if ($current_parsed[$k] > $v)
        return true;
    }

    return true;
  }


	/**
	 * Checks WP HTTP REST API availability
	 *
	 * @since 1.0.0
   * @param array $result test result (reference)
	 * @return bool
	 */
	private static function test_rest_availability(array &$result): bool
	{
		$result = [
			'label'       => __( 'The REST API is available', 'wgpwpp'),
			'status'      => 'success',
			'description' => '',
		];

		$request = new WP_REST_Request('GET', '/wgpwpp/v1/activation');
		$response = rest_do_request($request);

		if ($response->is_error())
		{
			$error = $response->as_error();

			$result['status'] = 'error';

			$result['label'] = __( 'The REST API encountered an error', 'wgpwpp');

			$result['description'] = sprintf(
				'<p>%s</p><p>%s<br>%s</p>',
				__( 'When testing the REST API, an error was encountered:' , 'wgpwpp'),
				sprintf(
				// translators: %s: The REST API URL.
					__( 'REST API Endpoint: %s' , 'wgpwpp'),
					'/wp/v2/plugins'
				),
				sprintf(
				// translators: 1: The WordPress error code. 2: The WordPress error message.
					__( 'REST API Response: (%1$s) %2$s' , 'wgpwpp'),
					$error->get_error_code(),
					$error->get_error_message()
				)
			);

      return false;
		}

		return true;
	}

}
