<?php
if (!defined('ABSPATH'))
  exit;

use Latte\Engine;

/**
 * Class - base admin layout class
 *
 * @since      1.1.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
abstract class Wgpwpp_Layout
{
  /**
   * Relative path to templates
   *
   * @since 1.0.0
   */
  public const TEMPLATES_BASE_DIR = '/admin/partials/wp-wgp/src/html/';

  /**
   * Plugin instance
   *
   * @since 1.0.0
   * @var Wgpwpp
   */
  protected Wgpwpp $plugin;

  /**
   * Latte
   *
   * @since 1.0.0
   * @var Engine
   */
  public Latte\Engine $latte;


  /**
   * Constructor
   *
   * @since 1.0.0
   * @param Wgpwpp $plugin plugin instance
   */
  public function __construct(Wgpwpp $plugin)
  {
    $this->plugin = $plugin;

    $this->define_hooks();
    $this->init_latte();
  }


  /**
   * Latte engine initialization
   *
   * @since 1.0.0
   * @return void
   */
  private function init_latte()
  {
    $this->latte = new Latte\Engine;
    $this->latte->setTempDirectory(false);
    $this->latte->setAutoRefresh();
    $loader = new Latte\Loaders\FileLoader($this->plugin->get_plugin_dir().self::TEMPLATES_BASE_DIR);
    $this->latte->setLoader($loader);
  }


  /**
   * Renders layout
   *
   * @since 1.0.0
   * @return void
   */
  abstract public function render();


  /**
   * Register all of the hooks related to the render layout.
   *
   * @since    1.0.0
   * @access   private
   */
  abstract protected function define_hooks();
}