<?php
if (!defined('ABSPATH'))
  exit;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.wedos.cz
 * @since      1.0.0
 *
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_i18n {
  /**
   * Plugin instance
   * @var Wgpwpp
   */
  public $plugin;

  /**
   * Paths to Latte templates
   * @var array
   */
	private static array $templates_paths = [
		Wgpwpp_Service_Layout::TEMPLATES_BASE_DIR,
	];


  /**
   * Constructor
   *
   * @param Wgpwpp $plugin plugin object
   * @return void
   * @since 1.0.0
   */
  public function __construct(Wgpwpp $plugin)
  {
    $this->plugin = $plugin;
  }


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			$this->plugin->get_plugin_name(),
			false,
			dirname( plugin_basename( __FILE__ ), 2) . '/languages/'
		);

	}


  /**
   * Explicit compilation of latte templates into PHP files to grab language texts
   *
   * Only for developing purposes. This method is never called in production.
   *
   * @param Wgpwpp $plugin plugin object
   * @return void
   * @throws \Latte\CompileException
   * @since 1.0.0
   */
	public static function compile_templates(Wgpwpp $plugin)
	{
    $templates_dir = $plugin->get_plugin_dir().'admin/partials/wp-wgp/src/templates_c';
		if (!is_dir($templates_dir))
			mkdir($templates_dir, 0755, true);

		$latte = new Latte\Engine;
		$latte->setTempDirectory($templates_dir);

		$temp_files = glob($templates_dir.'/*');
		foreach ($temp_files as $file)
			@unlink($file);

		foreach (self::$templates_paths as $path)
		{
			$path = $plugin->get_plugin_dir().$path;
			if (!is_dir($path))
				continue;

			$files = glob($path.'*.latte');
			if (!$files)
				continue;

			foreach ($files as $file)
			{
				$fileinfo = new SplFileInfo($file);
				$code = $latte->compile($fileinfo->getPathname());
				file_put_contents($latte->getCacheFile($fileinfo->getPathname()), $code);
			}
		}
	}
}
