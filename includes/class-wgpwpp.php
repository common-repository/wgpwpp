<?php
if (!defined('ABSPATH'))
  exit;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.wedos.cz
 * @since      1.0.0
 *
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp
{
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wgpwpp_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected Wgpwpp_Loader $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected string $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected string $version;

	/**
	 * Plugin option
	 *
	 * @since 1.0.0
	 * @var Wgpwpp_Option
	 */
	public Wgpwpp_Option $option;

	/**
	 * Log
	 *
	 * @since 1.0.0
	 * @var Wgpwpp_Log
	 */
	public Wgpwpp_Log $log;

	/**
	 * Veřejná sekce pluginu
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var Wgpwpp_Public $public_section
	 */
	public Wgpwpp_Public $public_section;

	/**
	 * Admin sekce pluginu
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var Wgpwpp_Admin
	 */
	public Wgpwpp_Admin $admin_section;

	/**
	 * Plugin localization instance
	 *
	 * @since 1.0.0
	 * @access public
	 * @var Wgpwpp_i18n
	 */
	public Wgpwpp_i18n $localization;

	/**
	 * OAuth2 client
	 *
	 * @since 1.0.0
	 * @var Wgpwpp_Client
	 */
	private Wgpwpp_Client $client;

	/**
	 * Service
	 *
	 * @since 1.0.0
	 * @var Wgpwpp_Service
	 */
	private Wgpwpp_Service $service;

  /**
   * Page caching
   *
   * @since 1.1.0
   * @var Wgpwpp_WP_Cache
   */
  public Wgpwpp_WP_Cache $wp_cache;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function __construct()
	{
		$this->plugin_name = WGPWPP_PLUGIN_NAME;
		$this->version = WGPWPP_VERSION;

		$this->option = new Wgpwpp_Option($this);
		$this->log = new Wgpwpp_Log($this);
		$this->loader = new Wgpwpp_Loader($this);
		$this->public_section = new Wgpwpp_Public($this);
		$this->admin_section = new Wgpwpp_Admin($this);

    $this->wp_cache = new Wgpwpp_WP_Cache($this);
    new Wgpwpp_WPInfo($this);
    new Wgpwpp_Notify($this);

		$this->set_locale();
	}


	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wgpwpp_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$this->localization = new Wgpwpp_i18n($this);
		$this->loader->add_action( 'plugins_loaded', $this->localization, 'load_plugin_textdomain' );
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name(): string
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wgpwpp_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader(): Wgpwpp_Loader
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version(): string
	{
		return $this->version;
	}


  /**
   * Returns path to plugin directory
   *
   * @since 1.0.0
   * @return string
   */
	public function get_plugin_dir(): string
	{
		return plugin_dir_path(WGPWPP_PLUGIN_FILE);
	}


	/**
	 * Return WP domain
	 *
	 * @since 1.0.0
	 * @return string|WP_Error
	 */
	public function get_host()
	{
		$wp_url = get_site_url();
		$wp_host = parse_url($wp_url, PHP_URL_HOST);
		$server_host = $_SERVER['HTTP_HOST'];

		if ($wp_host !== $server_host)
			return new WP_Error(1, 'Failed to parse WordPress HTTP host.');

		return $server_host;
	}


  /**
   * Returns plugin`s URL in administration panel
   *
   * @param array $params required query parameters
   * @return string
   * @since 1.0.0
   */
	public function get_admin_page_url(array $params = []): string
	{
    $url = admin_url('admin.php?page='.$this->plugin_name);

    if (empty($params))
      return $url;

    $url .= '&'.http_build_query($params);

		return $url;
	}


  /**
   * Return URL to the plugin directory
   *
   * @since 1.0.0
   * @return string
   */
	public function get_plugin_url(): string
	{
		return sprintf('%s/%s', WP_PLUGIN_URL, $this->get_plugin_name());
	}


  /**
   * Returns URL to the plugins images directory
   *
   * @since 1.0.0
   * @return string
   */
	public function get_plugin_admin_images_url(): string
	{
		$img_dir = $this->get_plugin_dir().'admin/partials/wp-wgp/src/img/';
    $img_dir_parsed = explode('/', $img_dir);
    $wp_content_dir_parsed = explode('/', WP_CONTENT_DIR.'/');
    return '/'.implode('/', array_slice($img_dir_parsed, count($wp_content_dir_parsed)-2));
	}


	/**
	 * Returns nonce unique for this plugin and action
	 *
	 * @since 1.0.0
	 * @param string $action required action
	 * @return string
	 */
	public function get_nonce_name(string $action): string
	{
		return $this->plugin_name.'-nonce-'.$action;
	}


	/**
	 * Return instance of OAuth2 client
	 *
	 * @since 1.0.0
	 * @return Wgpwpp_Client
	 */
	public function get_client(): Wgpwpp_Client
	{
		if (empty($this->client))
			$this->client = new Wgpwpp_Client($this);

		return $this->client;
	}


  /**
   * Destroys client
   *
   * @return void
   */
  public function destroy_client()
  {
    $this->get_client()->destroy();
    unset($this->client);
  }


	/**
	 * Return instance of OAuth2 client
	 *
	 * @since 1.0.0
	 * @return Wgpwpp_Service
	 */
	public function get_service(): Wgpwpp_Service
	{
		if (empty($this->service))
			$this->service = new Wgpwpp_Service($this);

		return $this->service;
	}


  /**
   * Deletes service data
   *
   * @return void
   */
  public function delete_service()
  {
    $this->get_service()->delete();
    unset($this->service);
  }


  /**
   * Returns WP admin email address
   *
   * @since 1.0.0
   * @return string
   */
	public function get_admin_email(): string
	{
    $user = wp_get_current_user();
    return $user->user_email;
	}


  /**
   * Returns WP blog admin email address
   *
   * @since 1.1.0
   * @param bool $multisite
   * @return string
   */
  public function get_blog_admin_email(bool $multisite = false): string
  {
    if ($multisite && self::is_multisite())
      return (string)get_site_option('admin_email');
    else
      return (string)get_option('admin_email');
  }


  /**
   * Returns blog name
   *
   * @since 1.1.0
   * @param bool $multisite
   * @return string
   */
  public function get_blog_name(bool $multisite = false): string
  {
    if ($multisite && self::is_multisite())
      return (string)get_site_option('site_name');
    else
      return (string)get_option('blogname');
  }



  /**
   * Return current WP version
   *
   * @since 1.0.0
   * @return string
   */
  public function get_wp_version(): string
  {
    return get_bloginfo('version');
  }


  /**
   * Returns PHP version
   *
   * @since 1.0.0
   * @return string
   */
  public function get_php_version(): string
  {
    return phpversion();
  }


  /**
   * Checks WP MultiSite is active
   *
   * @since 1.0.4
   * @return bool
   */
  public static function is_multisite(): bool
  {
    return function_exists('is_multisite') && is_multisite() && function_exists('get_sites');
  }


  /**
   * Checks if WP Multisite type is subdomain
   *
   * @since 1.0.4
   * @return bool
   */
  public static function is_multisite_subdomain(): bool
  {
    return self::is_multisite() && defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL;
  }


  /**
   * Checks if WP Multisite type is subdirectories
   *
   * @since 1.0.4
   * @return bool
   */
  public static function is_multisite_subdirectories(): bool
  {
    return self::is_multisite() && !self::is_multisite_subdomain();
  }


  /**
   * Returns IDs of all blogs in MultiSite
   *
   * @since 1.0.4
   * @return int[]
   */
  public static function get_multisite_ids(): array
  {
    $ids = [1];

    if (!self::is_multisite())
      return $ids;

    $sites = get_sites();
    foreach ($sites as $site)
      $ids[] = $site->blog_id;

    return $ids;
  }
}
