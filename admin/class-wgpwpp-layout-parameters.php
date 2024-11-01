<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - base admin layout parameters class
 *
 * @since      1.1.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
abstract class Wgpwpp_Layout_Parameters
{
  public Wgpwpp $plugin;

  public string $img_url;

  public string $ajax_url;

  public array $notices;


  public function __construct(Wgpwpp $plugin)
  {
    $this->plugin = $plugin;
    $this->img_url = $plugin->get_plugin_admin_images_url();
    $this->ajax_url = admin_url('admin-ajax.php');
    $this->notices = $plugin->admin_section->notices->get_notices();
  }
}