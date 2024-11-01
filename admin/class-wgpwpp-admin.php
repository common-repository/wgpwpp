<?php

if (!defined('ABSPATH'))
  exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.wedos.cz
 * @since      1.0.0
 *
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Admin
{
	/**
	 * Objekt pluginu
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var Wgpwpp
	 */
	public Wgpwpp $plugin;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private string $version;

	/**
	 * Admin notices
	 *
	 * @since 1.0.0
	 * @var Wgpwpp_Notices
	 */
	public Wgpwpp_Notices $notices;

	/**
	 * Admin layout
	 *
	 * @since 1.0.0
	 * @var Wgpwpp_Service_Layout
	 */
	public Wgpwpp_Service_Layout $layout;

    /**
	 * Dashboard layout
	 *
	 * @since 1.2.0
	 * @var Wgpwpp_Dashboard_Layout
	 */
    public Wgpwpp_Dashboard_Layout $dashboard;

	/**
	 * Authorization
	 *
	 * @since 1.0.0
	 * @var Wgpwpp_Authorization
	 */
	public Wgpwpp_Authorization $authorization;

  /**
   * Reports setting
   *
   * @since 1.1.0
   * @var Wgpwpp_Reports_Setting
   */
  public Wgpwpp_Reports_Setting $reports;

  /**
   * Cache setting
   *
   * @since 1.1.0
   * @var Wgpwpp_Cache_Setting
   */
  public Wgpwpp_Cache_Setting $cache;


	/**
	 * Initialize the class and set its properties.
	 * 
   * @changed  1.2.0
	 * @since    1.0.0
	 * @param    Wgpwpp $plugin objekt pluginu
	 */
	public function __construct(Wgpwpp $plugin)
	{
		$this->plugin = $plugin;
		$this->plugin_name = $plugin->get_plugin_name();
		$this->version = $plugin->get_version();

		/**
		 * The class responsible for admin notices
		 */
		$this->notices = new Wgpwpp_Notices($this->plugin);

		/**
		 * The class responsible for admin layout
		 */
		$this->layout = new Wgpwpp_Service_Layout($this->plugin);

        /**
		 * The class responsible for dashboard layout
		 */
		$this->dashboard = new Wgpwpp_Dashboard_Layout($this->plugin);

    /**
     * The class responsible for reports setting page
     */
    $this->reports = new Wgpwpp_Reports_Setting($this->plugin);

    /**
     * Cache setting
     */
    $this->cache = new Wgpwpp_Cache_Setting(($this->plugin));

		/**
		 * The class responsible for plugin authorization
		 */
		$this->authorization = new Wgpwpp_Authorization($plugin);

		$this->define_hooks();
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook)
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wgpwpp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wgpwpp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

    wp_enqueue_style(
      $this->plugin->get_plugin_name().'-font-awesome-dashboard',
      plugins_url('admin/css/fontawesome-free-6.5.2-web/css/fontawesome.min.css', WGPWPP_PLUGIN_FILE),
      array(),
      $this->plugin->get_version(),
      'all'
    );

    wp_enqueue_style(
      $this->plugin->get_plugin_name().'-font-awesome-dashboard-old',
      plugins_url('admin/css/font-awesome-4.7.0/css/font-awesome.min.css', WGPWPP_PLUGIN_FILE),
      [$this->plugin->get_plugin_name().'-font-awesome-dashboard'],
      $this->plugin->get_version(),
      'all'
    );

    wp_enqueue_style(
      $this->plugin->get_plugin_name().'-font-awesome-solid-dashboard',
      plugins_url('admin/css/fontawesome-free-6.5.2-web/css/solid.min.css', WGPWPP_PLUGIN_FILE),
      [$this->plugin->get_plugin_name().'-font-awesome-dashboard'],
      $this->plugin->get_version(),
      'all'
    );

    wp_enqueue_style(
      $this->plugin_name . '_css_main',
      plugins_url('admin/css/wgpwpp-admin.css', WGPWPP_PLUGIN_FILE),
      [],
      $this->version,
      'all'
    );

    if ($hook === 'wedos-global_page_wgpwpp')
    {
      wp_enqueue_style($this->plugin_name . '_css_tailwind', plugins_url('admin/partials/wp-wgp/dist/mini.css', WGPWPP_PLUGIN_FILE), [], $this->version, 'all');
      wp_enqueue_style($this->plugin_name . '_css_service', plugins_url('admin/css/wgpwpp-service.css', WGPWPP_PLUGIN_FILE), [], $this->version, 'all');
    }
	}


	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook)
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wgpwpp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wgpwpp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

    wp_enqueue_script(
      $this->plugin->get_plugin_name().'-admin',
      plugin_dir_url( __FILE__ ).'js/wgpwpp-admin.js',
      [ 'jquery' ],
      $this->version, false
    );

    $allowed_hooks = [
      'wedos-global_wgpwpp_cache',
      'wedos-global_page_wgpwpp',
      'wedos-global_page_wgpwpp_reports',
    ];

    if (!in_array($hook, $allowed_hooks))
      return;

    if ($hook === 'wedos-global_page_wgpwpp')
    {
      wp_enqueue_script(
        $this->plugin_name . '_css_tailwind',
        plugin_dir_url(__FILE__) . 'partials/wp-wgp/src/js/app.js',
        [],
        $this->version,
        'all'
      );
    }
	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks()
	{
		// action after plugin upgrade
		$this->plugin->get_loader()->add_action('upgrader_process_complete', $this, 'wp_upgrade_completed', 10, 2);

    $this->plugin->get_loader()->add_action('plugins_loaded', $this, 'plugin_upgraded');

		$this->plugin->get_loader()->add_action('in_admin_header', $this, 'init_notices', 10000);

		// CSS and JS
		$this->plugin->get_loader()->add_action('admin_enqueue_scripts', $this, 'enqueue_styles', 100);
		$this->plugin->get_loader()->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts', 100);

		// admin menu
		$this->plugin->get_loader()->add_action('admin_menu', $this, 'add_plugin_admin_menu');

		// settings link
		$plugin_basename = plugin_basename( $this->plugin->get_plugin_dir() . $this->plugin->get_plugin_name() . '.php');
		$this->plugin->get_loader()->add_filter('plugin_action_links_'.$plugin_basename, $this, 'add_action_links');

		$this->plugin->get_loader()->add_action('admin_footer_text', $this, 'remove_footer_text');

    $this->plugin->get_loader()->add_action('init', $this, 'init');
	}


  /**
   * Registers GET parameters for plugin special actions
   *
   * @since 1.0.0
   * @return void
   */
  public function init()
  {
    $log = isset($_GET['log']) ? sanitize_text_field($_GET['log']) : false;
    if (in_array($log, ['send', 'download', 'delete']))
    {
      // append timestamp to the query string to ensure not cached result
      if (!isset($_GET['lt']))
        wp_redirect($this->plugin->get_admin_page_url(['log' => $log, 'lt' => time(),]));

      // log action
      switch ($log)
      {
        case 'download': // download log file
          if (!$this->plugin->log->download())
            $this->notices->error(__('Failed to download debug log', 'wgpwpp'));
          else
            wp_redirect($this->plugin->get_admin_page_url());

          break;

        case 'send': // send log file to wedos support
          if ($this->plugin->log->send_log())
            $this->notices->info(__('Debug log was sent to WEDOS support', 'wgpwpp'), true);
          else
            $this->notices->error(__('Failed to send debug log to WEDOS support', 'wgpwpp'), true);

          wp_redirect($this->plugin->get_admin_page_url());
          break;

        case 'delete': // delete log file
          $this->plugin->log->delete();
          $this->notices->info(__('Debug log was deleted', 'wgpwpp'), true);

          wp_redirect($this->plugin->get_admin_page_url());
          break;
      }
    }

    // reset plugin to its default setting - delete all settings
    $reset = isset($_GET['reset']) && sanitize_text_field($_GET['reset']);
    if ($reset)
    {
      $this->notices->warning(__('The plugin has been reset.', 'wgppwpp'), true);
      $this->plugin->log->delete();
      $this->plugin->option->reset();
      wp_redirect($this->plugin->get_admin_page_url());
    }
  }


	/**
	 * Removes WP footer from the bottom of the plugin page
	 *
	 * @since 1.0.0
	 * @param $footer_text
	 * @return string
	 */
	public function remove_footer_text($footer_text)
	{
		remove_action('update_footer', 'core_update_footer');
		return '';
	}


	/**
	 * Removes system and other plugins notices
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_notices()
	{
		remove_all_actions('admin_notices');
		remove_all_actions('all_admin_notices');
		remove_all_actions('user_admin_notices');
		remove_all_actions('network_admin_notices');
	}


	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu()
	{
    if (Wgpwpp::is_multisite_subdirectories() && false === is_super_admin())
      return;

		add_menu_page(
			'WEDOS Global',
			'WEDOS Global',
			'manage_options',
			$this->plugin->get_plugin_name().'_dashboard_layout',
			[$this, 'display_dashboard_layout'],
			'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9Im5vbmUiIHN0cm9rZT0iY3VycmVudENvbG9yIiBzdHJva2Utd2lkdGg9IjIiIGFyaWEtaGlkZGVuPSJ0cnVlIiBjbGFzcz0idy0yNCBoLTI0IG14LWF1dG8gYm9yZGVyLTQgcm91bmRlZC1mdWxsIHAtMiIgdmlld0JveD0iMCAwIDI0IDI0Ij48cGF0aCBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIGQ9Im05IDEyIDIgMiA0LTRtNS42MTgtNC4wMTZBMTEuOTU1IDExLjk1NSAwIDAgMSAxMiAyLjk0NGExMS45NTUgMTEuOTU1IDAgMCAxLTguNjE4IDMuMDRBMTIuMDIgMTIuMDIgMCAwIDAgMyA5YzAgNS41OTEgMy44MjQgMTAuMjkgOSAxMS42MjIgNS4xNzYtMS4zMzIgOS02LjAzIDktMTEuNjIyIDAtMS4wNDItLjEzMy0yLjA1Mi0uMzgyLTMuMDE2eiIvPjwvc3ZnPg==',
		);

    remove_submenu_page($this->plugin->get_plugin_name().'_dashboard_layout', $this->plugin->get_plugin_name().'_dashboard_layout');
    add_submenu_page($this->plugin->get_plugin_name().'_dashboard_layout', __('WEDOS Global - Dashboard', 'wgpwpp'), __('Dashboard', 'wgpwpp'), 'manage_options', $this->plugin->get_plugin_name().'_dashboard_layout', [$this, 'display_dashboard_layout']);
    add_submenu_page($this->plugin->get_plugin_name().'_dashboard_layout', __('WEDOS Global - Cache setting', 'wgpwpp'), __('Cache setting', 'wgpwpp'), 'manage_options', $this->plugin->get_plugin_name().'_cache', [$this, 'display_cache_setting_layout']);
    add_submenu_page($this->plugin->get_plugin_name().'_dashboard_layout', __('WEDOS Global - Service status', 'wgpwpp'), __('Service status', 'wgpwpp'), 'manage_options', $this->plugin->get_plugin_name(), [$this, 'display_service_layout']);

    if ($this->plugin->get_client()->is_registered())
    {
      add_submenu_page($this->plugin->get_plugin_name().'_dashboard_layout', __('WEDOS Global - Security reports setting', 'wgpwpp'), __('Security reports', 'wgpwpp'), 'manage_options', $this->plugin->get_plugin_name() . '_reports', [$this, 'display_reports_layout']);
    }
	}


	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since  1.0.0
	 * @param array $links links
	 * @return array
	 */
	public function add_action_links(array $links): array
	{
    if (Wgpwpp::is_multisite_subdirectories() && false === is_super_admin())
      return $links;

		/*
		*  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
		*/
		$settings_link = [
			'<a href="'.admin_url('admin.php?page='.$this->plugin_name.'_dashboard_layout').'">'.__('Dashboard','wgpwpp').'</a>',
			'<a href="'.admin_url('admin.php?page='.$this->plugin_name).'">'.__('Settings','wgpwpp').'</a>',
		];

		return array_merge($settings_link, $links);
	}


    	/**
	 * Renders the service layout
	 *
	 * @since    1.2.0
	 * @return void
	 */
	public function display_dashboard_layout()
	{
		// General check for user permissions.
		if (!current_user_can('activate_plugins'))
			wp_die(__('Access denied!', 'wgpwpp'));

		$this->dashboard->render();
	}


	/**
	 * Renders the service layout
	 *
   * @changed  1.1.0
	 * @since    1.0.0
	 * @return void
	 */
	public function display_service_layout()
	{
		// General check for user permissions.
		if (!current_user_can('activate_plugins'))
			wp_die(__('Access denied!', 'wgpwpp'));

		$this->layout->render();
	}


  /**
   * Renders the reports layout
   *
   * @since 1.1.0
   * @return void
   */
  public function display_reports_layout()
  {
    $this->reports->render();
  }


  /**
   * Renders the cache setting layout
   *
   * @since 1.1.0
   * @return void
   */
  public function display_cache_setting_layout()
  {
    $this->cache->render();
  }


  /**
   * Get key for store plugins upgrade transient data
   *
   * @since 1.0.0
   * @return string
   */
  private function get_upgrade_transient_key(): string
  {
    return base64_encode(sprintf('%s-upgraded-data', $this->plugin->get_plugin_name()));
  }


  /**
   * Returns plugins upgrade transient data or null if no exist
   *
   * @since 1.0.0
   * @return array|null
   */
  private function get_upgrade_transient_data(): ?array
  {
    $data = get_transient($this->get_upgrade_transient_key());
    if (!$data)
      return null;

    return $data;
  }


  /**
   * Sets plugins upgrade transient data
   *
   * Set null for deleting transient data
   *
   * @since 1.0.0
   * @param array|null $data
   * @return void
   */
  private function set_upgrade_transient_data(?array $data)
  {
    if (is_null($data))
    {
      delete_transient($this->get_upgrade_transient_key());
      return;
    }

    set_transient($this->get_upgrade_transient_key(), $data);
  }


	/**
	 * This function runs when WordPress completes its upgrade process
	 * It iterates through each plugin updated to see if ours is included
	 *
	 * @since 1.0.0
	 * @param $upgrader_object Array
	 * @param $options Array
	 */
	public function wp_upgrade_completed($upgrader_object, $options)
	{
		// The path to our plugin's main file
		$our_plugin = plugin_basename( __FILE__ );

		// If an update has taken place and the updated type is plugins and the plugins element exists
		if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) )
		{
			// Iterate through the plugins being updated and check if ours is there
			foreach($options['plugins'] as $plugin)
			{
				if ($plugin == $our_plugin)
          $this->set_upgrade_transient_data(['updated' => time(), 'version' => $this->version]);
			}
		}
	}


  /**
   * Fires once activated plugins have loaded.
   *
   * Updates client registration data
   *
   * @since 1.0.0
   * @return void
   */
  public function plugin_upgraded()
  {
    $upgrade_data = $this->get_upgrade_transient_data();
    if (is_null($upgrade_data))
      return;

    if (!$this->plugin->get_client()->is_registered())
      return;

    $this->plugin->get_client()->update(get_locale());
  }
}
