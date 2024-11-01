<?php
if (!defined('ABSPATH'))
  exit;

/**
 * Class - class responsible for collection information about current WordPress installation
 *
 * @since      1.1.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_WPInfo extends Wgpwpp_REST_API
{
  /**
   * Name of scope for this REST API Endpoint authorization
   *
   * @since 1.1.0
   */
  const SCOPE = 'wgp-wp-info';


  /**
   * Constructor
   *
   * @since 1.1.0
   * @param Wgpwpp $plugin
   */
  public function __construct(Wgpwpp $plugin)
  {
    parent::__construct($plugin);
    $this->define_hooks();
  }


  /**
   * Register all of the hooks related to the collection of WP information.
   *
   * @since 1.1.0
   * @return void
   */
  private function define_hooks()
  {
    $this->plugin->get_loader()->add_action('rest_api_init', $this, 'register_api_endpoint');
  }


  /**
   * Register REST API endpoint related to the collection of WP information.
   *
   * @since 1.1.0
   * @return void
   */
  public function register_api_endpoint()
  {
    register_rest_route( $this->plugin->get_plugin_name() . '/v1', '/wpinfo', [
      'methods'             => 'GET',
      'callback'            => [ $this, 'process_info_request' ],
      'permission_callback' => '__return_true'
    ]);
  }


  /**
   * Process request to collect information about current WordPress installation
   *
   * @since 1.1.0
   * @param WP_REST_Request $request request object
   * @return array
   */
  public function process_info_request(WP_REST_Request $request): array
  {
    // validation of incoming REST API request
    $error_msg = '';
    if (!$this->authorize_request($request, [self::SCOPE], $error_msg))
    {
      return [
        'code' => 2000,
        'msg'  => "Unauthorized request. ".$error_msg,
      ];
    }

    return [
      'code'  => 1000,
      'data'  => $this->collect_wpinfo(),
    ];
  }


  /**
   * Collects all information about current WordPress information
   *
   * @since 1.1.0
   * @return array
   */
  private function collect_wpinfo(): array
  {
    return [
      'wordpress'       => $this->get_wp_info(),
      'service'         => $this->get_service_info(),
      'plugins'         => $this->get_plugins_info(),
      'themes'          => $this->get_themes_info(),
      //'server'          => $this->get_server_info(),
      'reports_setting' => $this->get_reports_setting(),
    ];
  }


  /**
   * Return information about webserver
   *
   * @since 1.1.0
   * @return array
   */
  private function get_server_info(): array
  {
    ob_start();
    phpinfo();
    $phpinfo = ob_get_clean();

    return [
      'server_info' => $_SERVER,
      'php_info'    => $phpinfo,
    ];
  }


  /**
   * Return information about WGP service
   *
   * @since 1.1.0
   * @return array
   */
  private function get_service_info(): array
  {
    $service = $this->plugin->get_service();

    return [
      'name'        => $service->get_service_name(),
      'tld'         => $service->get_service_tld(),
      'id'          => $service->get_service_id(),
      'type'        => $service->get_service_type(),
      'state'       => $service->get_service_state(),
      'state_data'  => $service->get_service_state_data(),
    ];
  }


  /**
   * Returns information about WordPress installation
   *
   * @since 1.1.0
   * @return array
   */
  private function get_wp_info(): array
  {
    $active_plugins = [];
    $plugins = get_plugins();
    foreach (array_keys($plugins) as $slug)
    {
      if (is_plugin_active($slug))
        $active_plugins[] = $slug;
    }

    $wpinfo = [
      'server_software'     => $_SERVER['SERVER_SOFTWARE'] ?? '',
      'php_version'         => $this->plugin->get_php_version(),
      'wp_version'          => $this->plugin->get_wp_version(),
      'multisite'           => Wgpwpp::is_multisite(),
      'network_name'        => $this->plugin->get_blog_name(true),
      'network_admin_email' => $this->plugin->get_blog_admin_email(true),
      'cdn_cache_active'    => $this->plugin->get_service()->get_service_cache_status(),
      'wp_cache_active'     => $this->plugin->wp_cache->is_active(),
      'wp_cache_status'     => $this->plugin->wp_cache->get_status(),
      'blog_data'           => [
        'name'            => $this->plugin->get_blog_name(),
        'lang'            => get_option('wplang'),
        'timezone'        => get_option('timezone_string'),
        'gmt_offset'      => get_option('gmt_offset'),
        'charset'         => get_option('blog_charset'),
        'admin_email'     => $this->plugin->get_blog_admin_email(),
        'home_url'        => get_home_url(),
        'site_url'        => get_site_url(),
        'rest_url'        => get_rest_url(),
        'active_plugins'  => $active_plugins,
      ],
    ];

    $theme = wp_get_theme();
    if ($theme)
      $wpinfo['blog_data']['active_theme'] = $theme->get_template();

    return $wpinfo;
  }


  /**
   * Returns info about installed WordPress plugins
   *
   * @since 1.1.0
   * @return array|array[]
   */
  private function get_plugins_info(): array
  {
    return get_plugins();
  }


  /**
   * Returns information about installed WordPress themes
   *
   * @since 1.1.0
   * @return array
   */
  private function get_themes_info(): array
  {
    $themes = wp_get_themes();
    if (!is_array($themes) || empty($themes))
      return [];

    $themes_info = [];

    foreach ($themes as $wp_theme)
    {
      $themes_info[$wp_theme->get_template()] = [
        'Name'        => $wp_theme->get('Name'),
        'ThemeURI'    => $wp_theme->get('ThemeURI'),
        'Description' => $wp_theme->get('Description'),
        'Author'      => $wp_theme->get('Author'),
        'AuthorURI'   => $wp_theme->get('AuthorURI'),
        'Version'     => $wp_theme->get('Version'),
        'Template'    => $wp_theme->get('Template'),
        'Status'      => $wp_theme->get('Status'),
        'Tags'        => $wp_theme->get('Tags'),
        'TextDomain'  => $wp_theme->get('TextDomain'),
        'DomainPath'  => $wp_theme->get('DomainPath'),
      ];
    }

    return $themes_info;
  }


  /**
   * Returns reports setting
   *
   * @since 1.1.0
   * @return array
   */
  private function get_reports_setting(): array
  {
    $reports_setting = get_option('wgpwpp_reports');
    if (!is_array($reports_setting))
      $reports_setting = [];

    return $reports_setting;
  }
}