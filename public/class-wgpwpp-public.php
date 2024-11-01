<?php
if (!defined('ABSPATH'))
  exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.wedos.cz
 * @since      1.0.0
 *
 * @package    Wgpwpp
 * @subpackage Wgpwpp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wgpwpp
 * @subpackage Wgpwpp/public
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Public {

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
	 * Objekt pluginu
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var Wgpwpp
	 */
	public Wgpwpp $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param Wgpwpp $plugin objekt pluginu
	 * @return void
	 */
	public function __construct(Wgpwpp $plugin)
	{
		$this->plugin = $plugin;
		$this->plugin_name = $plugin->get_plugin_name();
		$this->version = $plugin->get_version();

		$this->define_hooks();
	}


	/**
	 * Register all of the hooks related to the public area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks()
	{
//		$this->plugin->get_loader()->add_action( 'wp_enqueue_scripts', $this, 'enqueue_styles' );
//		$this->plugin->get_loader()->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts' );
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wgpwpp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wgpwpp-public.js', array( 'jquery' ), $this->version, false );

	}

}
