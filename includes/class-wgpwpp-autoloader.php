<?php
if (!defined('ABSPATH'))
  exit;

/**
 * Class - class responsible for plugin`s class autoloading
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Autoloader
{
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct()
	{
		spl_autoload_register([$this, 'autoload']);
	}


	/**
	 * Autoload
	 *
	 * @since 1.0.0
	 * @param string $class_name
	 * @return bool
	 */
	private function autoload(string $class_name): bool
	{
		$class_file_name = 'class-'.str_replace('_', '-', strtolower($class_name)).'.php';

		$classes_dirs = [
			realpath(plugin_dir_path(__FILE__)),
			realpath(plugin_dir_path(__FILE__).'/../admin'),
			realpath(plugin_dir_path(__FILE__).'/../public'),
		];

		foreach ($classes_dirs as $class_dir)
		{
			$class_file_path = $class_dir . DIRECTORY_SEPARATOR . $class_file_name;

			if (file_exists($class_file_path))
			{
				require_once $class_file_path;
				return true;
			}
		}

		return false;
	}
}

new Wgpwpp_Autoloader();