<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - dashboard page
 *
 * @since      1.2.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_dashboard_layout
{
  /**
   * Plugin instance
   *
   * @since 1.2.0
   * @var Wgpwpp
   */
  private Wgpwpp $plugin;


  /**
   * Constructor
   *
   * @since 1.2.0
   * @param Wgpwpp $plugin
   */
  public function __construct(Wgpwpp $plugin)
  {
    $this->plugin = $plugin;
    $this->define_hooks();
  }

  /**
   * Defines required hooks
   *
   * @since 1.2.0
   * @return void
   */
  private function define_hooks()
  {
    // CSS and JS
    $this->plugin->get_loader()->add_action('admin_enqueue_scripts', $this, 'enqueue_styles', 100);
    $this->plugin->get_loader()->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts', 100);

    // AJAX
    $this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_dashboard_load_opensearch_data', $this, 'ajax_load_opensearch_data');
    $this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_dashboard_toggle_wp_cache', $this, 'ajax_toggle_wp_cache');
    $this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_dashboard_toggle_cdn_cache', $this, 'ajax_toggle_cdn_cache');
    $this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_dashboard_dismiss_rating', $this, 'ajax_dismiss_rating');
    }


  /**
   * AJAX handler - activate/deactivate WP cache
   *
   * @since 1.2.0
   * @return void
   */
  public function ajax_toggle_wp_cache()
  {
    $status = filter_var($_POST['status'], FILTER_VALIDATE_BOOLEAN);

    // check nonce
    if (!check_ajax_referer( 'wgpwpp_dashboard_toggle_wp_cache', false, false))
    {
      wp_die(json_encode([
        'result' => 'error',
        'msg' => __('Invalid request', 'wgpwpp'),
        'notice' => $this->plugin->admin_section->notices->error(__('Invalid request', 'wgpwpp'))->render(true, true),
      ]));
    }

    if (!isset($_POST['status']))
    {
      wp_die(json_encode([
        'result' => 'error',
        'msg' => __('Invalid input', 'wgpwpp'),
        'notice' => $this->plugin->admin_section->notices->error(__('Invalid input', 'wgpwpp'))->render(true, true),
      ]));
    }

    if ($status)
    {
      if (!$this->plugin->wp_cache->activate())
      {
        wp_die(json_encode([
          'result' => 'error',
          'msg' => __('Failed to activate WP cache', 'wgpwpp'),
          'notice' => $this->plugin->admin_section->notices->error(__('Failed to activate WP cache', 'wgpwpp'))->render(true, true),
        ]));
      }

      $notice = $this->plugin->admin_section->notices->success(__('Local WordPress CACHE was successfully activated!', 'wgpwpp'))->render(true, true);
    }
    else
    {
      $this->plugin->wp_cache->deactivate();
      $notice = $this->plugin->admin_section->notices->warning(__('Local WordPress CACHE was deactivated!', 'wgpwpp'))->render(true, true);
    }

    wp_die(json_encode([
      'result'  => 'success',
      'status'  => $status,
      'notice'  => $notice,
    ]));
  }


  /**
   * AJAX handler - activate/deactivate CDN cache
   *
   * @since 1.2.0
   * @return void
   */
  public function ajax_toggle_cdn_cache()
  {
    // check nonce
    if (!check_ajax_referer( 'wgpwpp_dashboard_toggle_cdn_cache', false, false))
    {
      wp_die(json_encode([
        'result' => 'error',
        'msg' => __('Invalid request', 'wgpwpp'),
        'notice' => $this->plugin->admin_section->notices->error(__('Invalid request', 'wgpwpp'))->render(true, true),
      ]));
    }

    if (!$this->plugin->get_service()->is_active())
    {
      wp_die(json_encode([
        'result' => 'error',
        'msg' => __('Service must be active to activate CDN cache', 'wgpwpp'),
        'notice' => $this->plugin->admin_section->notices->error(__('Service must be active to activate CDN cache', 'wgpwpp'))->render(true, true),
      ]));
    }

    if (!isset($_POST['status']))
    {
      wp_die(json_encode([
        'result' => 'error',
        'msg' => __('Invalid input', 'wgpwpp'),
        'notice' => $this->plugin->admin_section->notices->error(__('Invalid input', 'wgpwpp'))->render(true, true),
      ]));
    }

    $status = filter_var($_POST['status'], FILTER_VALIDATE_BOOLEAN);

    $res = $this->plugin->get_service()->cache_control($status);
    if (!$res)
    {
      if ($status)
      {
        wp_die(json_encode([
          'result' => 'error',
          'msg' => __('Failed to activate CDN cache', 'wgpwpp'),
          'notice' => $this->plugin->admin_section->notices->error(__('Failed to activate CDN cache', 'wgpwpp'))->render(true, true),
        ]));
      }
      else
      {
        wp_die(json_encode([
          'result' => 'error',
          'msg' => __('Failed to deactivate CDN cache', 'wgpwpp'),
          'notice' => $this->plugin->admin_section->notices->error(__('Failed to deactivate CDN cache', 'wgpwpp'))->render(true, true),
        ]));
      }
    }

    if ($status)
      $notice = $this->plugin->admin_section->notices->success(__('Global WordPress CDN Cache was successfully activated!','wgpwpp'))->render(true, true);
    else
      $notice = $this->plugin->admin_section->notices->warning(__('Global WordPress CDN Cache was deactivated!','wgpwpp'))->render(true, true);

    wp_die(json_encode([
      'result' => 'success',
      'status' => $status,
      'notice' => $notice,
    ]));
  }


  /**
   * AJAX handler - returns OpenSearch data
   *
   * @since 1.2.0
   * @return void
   */
  public function ajax_load_opensearch_data()
  {
    // check nonce
    if (!check_ajax_referer( 'wgpwpp_dashboard_load_opensearch_data', false, false))
      wp_die(json_encode(['result' => 'error' ,'msg'  => __('Invalid request', 'wgpwpp')]));

    if (!$this->plugin->get_service()->is_active())
      wp_die(json_encode(['result' => 'error', 'msg' => __('Service must be active for loading statistics data', 'wgpwpp')]));

    if (!isset($_POST['type']))
      wp_die(json_encode(['result' => 'error' ,'msg' => __('Invalid data','wgpwpp')]));

    $type = sanitize_text_field($_POST['type']);

    switch ($type)
    {
      case 'ddos': $type = Wgpwpp_Service::OPENSEARCH_DATA_TYPE_DDOS; break;
      case 'robots': $type = Wgpwpp_Service::OPENSEARCH_DATA_TYPE_ROBOTS; break;
      case 'cache': $type = Wgpwpp_Service::OPENSEARCH_DATA_TYPE_CACHE; break;
      default: wp_die(json_encode(['result' => 'error' ,'msg' => __('Invalid data type', 'wgpwpp')]));
    }

    $res = $this->plugin->get_service()->get_opensearch_data($type);

    if (is_null($res))
      wp_die(json_encode(['result' => 'error' ,'msg' => __('Invalid API response', 'wgpwpp')]));

    wp_die(json_encode($res));
  }


  /**
   * Register the stylesheets for the admin area.
   *
   * @since    1.2.0
   */
  public function enqueue_styles($hook)
  {
    if ($hook !== 'toplevel_page_wgpwpp_dashboard_layout')
      return;

    wp_enqueue_style(
      $this->plugin->get_plugin_name() . '_css_dashboard',
      plugins_url('admin/css/wgpwpp-dashboard-layout-display.css', WGPWPP_PLUGIN_FILE),
      [],
      $this->plugin->get_version(), 'all'
    );
  }


  /**
   * Register the JavaScript for the admin area.
   *
   * @since    1.2.0
   */
  public function enqueue_scripts($hook)
  {
    if ($hook !== 'toplevel_page_wgpwpp_dashboard_layout')
      return;

    wp_enqueue_script(
      $this->plugin->get_plugin_name(),
      plugin_dir_url( __FILE__ ).'js/wgpwpp-admin-dashboard.js',
      [ 'jquery' ],
      $this->plugin->get_version(), false
    );

    wp_localize_script('wgpwpp', 'wgpwpp_admin_dashboard', [
      'ajaxurl'                 => admin_url('admin-ajax.php'),
      'service_active'          => $this->plugin->get_service()->is_active(),
      'nonce_opensearch_data'   => wp_create_nonce('wgpwpp_dashboard_load_opensearch_data'),
      'nonce_toggle_wp_cache'   => wp_create_nonce('wgpwpp_dashboard_toggle_wp_cache'),
      'nonce_toggle_cdn_cache'  => wp_create_nonce('wgpwpp_dashboard_toggle_cdn_cache'),
      'nonce_dismiss_rating'    => wp_create_nonce('wgpwpp_dashboard_dismiss_rating'),
    ]);
  }


  /**
   * Returns service object
   *
   * @since 1.2.0
   * @return Wgpwpp_Service
   */
  public function get_service()
  {
    return $this->plugin->get_service();
  }


  /**
   * Returns service status
   *
   * @since 1.2.0
   * @return bool
   */
  public function get_service_status(): bool
  {
    return $this->plugin->get_service()->is_active();
  }


  /**
   * Returns service variant
   *
   * @since 1.2.0
   * @return string
   */
  public function get_service_variant(): string
  {
    $service_data = $this->get_service()->get_service_data();
    if (!$service_data || !isset($service_data['variant']['name']))
      return '';

    return (string)$service_data['variant']['name'];
  }


  /**
   * Returns Trial days remaining
   *
   * @since 1.2.0
   * @return int|null
   * @throws Exception
   */
  public function get_trial_days(): ?int
  {
    $service_data = $this->get_service()->get_service_data();
    if (!$service_data || !$service_data['is_trial'])
      return null;

    $expiration = new DateTime($service_data['expiration'], new DateTimeZone('Europe/Prague'));
    $diff = $expiration->diff(new DateTime(date('Y-m-d'), new DateTimeZone(('Europe/Prague'))));

    if (!$diff->invert)
      return null;

    return $diff->days;
  }


  /**
   * Returns WP cache status
   *
   * @since 1.2.0
   * @return array|false|mixed|null[]|null
   */
  public function get_wp_cache_status()
  {
    return $this->plugin->wp_cache->get_status();
  }


  /**
   * Returns CDN cache status
   *
   * @since 1.2.0
   * @return bool
   */
  public function get_cdn_cache_status(): bool
  {
    if (!$this->get_service_status())
      return false;

    return $this->plugin->get_service()->get_service_cache_status();
  }


  /**
   * Returns host
   *
   * @since 1.2.0
   * @return string
   */
  public function get_service_name(): string
  {
    $host = $this->plugin->get_host();
    if (is_wp_error($host))
      $host = $_SERVER['HTTP_HOST'];

    return $host;
  }


  /**
   * Checks if OAuth2 client is registered
   *
   * @since 1.2.0
   * @return bool
   */
  public function is_client_registered(): bool
  {
    return $this->plugin->get_client()->is_registered();
  }


  /**
   * Draw chart HTML
   *
   * @since 1.2.0
   * @param string $type type of chart data
   * @param bool $random generate random data
   * @return void
   */
  public function draw_chart(string $type, bool $random=false)
  {
    switch ($type)
    {
      case Wgpwpp_Service::OPENSEARCH_DATA_TYPE_DDOS:
        $title = __('Blocked attacks', 'wgpwpp');
        break;

      default:
        $title = __('Preloaded pages', 'wgpwpp');
        break;
    }

    ?>
    <div class="wrap-chart">

      <?php if($this->get_service()->is_active()): ?>
      <div class="wgpwpp-chart-spinner-wrap" id="wgpwpp_chart_<?= $type; ?>_spinner">
        <div class="wgpwpp-chart-spinner wgpwpp-chart-spinner-<?= $type; ?>"></div>
      </div>
      <?php endif; ?>


      <?php if ($this->get_service()->is_active()): ?>
      <h2 style="margin-bottom:0;">
        <span class="chart-line" id="wgpwpp_total_<?= $type; ?>"></span>
        <br><?= $title; ?><br>
        <span class="chart-note"><?= __('In the last 14 days', 'wgpwpp'); ?></span>
      </h2>
      <?php else: ?>
      <h2><br><?= $title; ?><br></h2>
      <a href="?page=wgpwpp" class="btn_green horni"><?= __('Activate', 'wgpwpp'); ?></a>
      <?php endif; ?>

      <div class="chart">
        <?php
        if (!$random)
        {
          for ($i = 0; $i < 14; $i++)
          {
            $left = $i === 0 ? 0 : 'calc(' . $i . ' * 100% / 14)';
            ?>
            <div class="bar red" id="wgpwpp_chart_bar_<?= $type; ?>_<?= $i; ?>" style="left: <?= $left; ?>; height: 1px;">
              <div class="tooltip">
                <div id="wgpwpp_chart_bar_tooltip_<?= $type; ?>_cnt_<?= $i; ?>"></div>
                <div id="wgpwpp_chart_bar_tooltip_<?= $type; ?>_date_<?= $i; ?>"></div>
              </div>
            </div>
            <?php
          }
        }
        else
        {
          $data = $this->get_charts_random_data();

          $i = 0;

          foreach ($data as $value)
          {
            $left = $i === 0 ? 0 : 'calc(' . $i . ' * 100% / 14)';
            $bgcolor = $value < 75 ? '#e6e6e6' : '#ccc';
            ?>
            <div class="bar blur" style="left: <?= $left; ?>; height: <?= $value ;?>px; background:<?= $bgcolor; ?>"></div>
            <?php
            $i++;
          }
        }
        ?>
      </div>
    </div>
    <?php
  }


  /**
   * Returns chart random data
   *
   * @since 1.2.0
   * @param int $sum total count (reference)
   * @return array
   */
  private function get_charts_random_data(int &$sum = 0): array
  {
    $sum = 0;
    $days_array = [];
    for($count=0; $count<14; $count++) {
      $day_attacks = rand(10, 150);
      $days_array[] = $day_attacks;
      $sum += $day_attacks;
    }

    return $days_array;
  }


  /**
  * AJAX handler to dismiss the rating notice
  *
  * @since 1.2.0
  */
  public function ajax_dismiss_rating()
  {
    // check nonce
    if (!check_ajax_referer( 'wgpwpp_dashboard_dismiss_rating', false, false))
      wp_die(json_encode(['result' => 'error' ,'msg'  => __('Invalid request', 'wgpwpp')]));

    update_option('wgpwpp_rating_dismissed', true);
    wp_die(json_encode(['result' => 'success']));
  }

  /**
   * Renders setting page
   *
   * @since 1.2.0
   * @return void
   */
  public function render()
  {
    include_once('partials/wgpwpp-dashboard-layout-display.php');
  }

}