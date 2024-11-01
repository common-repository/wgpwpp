<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - cache settings page
 *
 * @since 1.1.0
 * @package Wgpwpp
 * @subpackage Wgpwpp/admin
 */
class Wgpwpp_Cache_Setting
{
  /**
   * Plugin instance
   *
   * @since 1.1.0
   * @var Wgpwpp
   */
  private $plugin;


  /**
   * Constructor
   *
   * @since 1.1.0
   * @param Wgpwpp $plugin plugin instance
   * @return void
   */
  public function __construct(Wgpwpp $plugin)
  {
    $this->plugin = $plugin;
    $this->define_hooks();
  }


  /**
   * Renders page
   *
   * @since 1.1.0
   * @return void
   */
  public function render()
  {
    include_once('partials/wgpwpp-cache-setting-display.php');
  }


  /**
   * Defines required hooks
   *
   * @since 1.1.0
   * @return void
   */
  private function define_hooks()
  {
    $this->plugin->get_loader()->add_action('admin_init', $this, 'init_settings', 10);
    
    $this->plugin->get_loader()->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts', 100);

    $this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_purge_wp_cache', $this, 'ajax_purge_wp_cache');

    $this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_purge_cdn_cache', $this, 'ajax_purge_cdn_cache');
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

    if ($hook !== 'wedos-global_page_wgpwpp_cache')
      return;

    wp_enqueue_script(
      $this->plugin->get_plugin_name(),
      plugin_dir_url( __FILE__ ).'js/wgpwpp-admin-cache.js',
      [ 'jquery' ],
      $this->plugin->get_version(), false
    );

    // flush lokální wp cache
    wp_localize_script(
      $this->plugin->get_plugin_name(),
      'wgpwpp_cache_settings',
      [
        'ajax_url'          => admin_url('admin-ajax.php'),
        'nonce_purge_cache' => wp_create_nonce('nonce_purge_cache'),
      ]
    );    
  }


  /**
   * Processes flushing of CDN cache
   *
   * @since 1.2.2
   * @return void
   */
  public function ajax_purge_cdn_cache()
  {
    // check nonce
    if (!check_ajax_referer( 'nonce_purge_cache', false, false))
    {
      $this->plugin->admin_section->notices->error(__('Invalid request', 'wgpwpp'), true);
      wp_die();
    }

    $error_msg = '';
    $result = $this->plugin->get_service()->cache_purge($error_msg);

    if ($result)
      $this->plugin->admin_section->notices->success(__('CDN cache successfully purged!', 'wgpwpp'), true);
    else
      $this->plugin->admin_section->notices->error(__('Failed to purge CDN cache!', 'wgpwpp').' '.$error_msg, true);

    wp_die();
  }

  
  /**
   * Processes flushing of local cache
   *
   * @since 1.2.2
   * @return void
   */
  public function ajax_purge_wp_cache()
  {
    // check nonce
    if (!check_ajax_referer( 'nonce_purge_cache', false, false))
    {
      $this->plugin->admin_section->notices->error(__('Invalid request', 'wgpwpp'), true);
      wp_die();
    }

    $this->plugin->wp_cache->flush_cache();

    $this->plugin->admin_section->notices->success(__('Wordpress cache successfully purged!', 'wgpwpp'), true);

    wp_die();
  }


  /**
   * Init settings
   *
   * @since 1.1.0
   * @return void
   */
  public function init_settings()
  {
    $this->init_settings_wp_cache();
    $this->init_settings_cdn_cache();
  }
  
  /**
   * Init WP page caching settings
   *
   * @since 1.1.0
   * @return void
   */
  private function init_settings_wp_cache()
  {
    register_setting('wgpwpp_wp_cache', 'wgpwpp_wp_cache', [$this, 'process_wp_cache_setting']);

    $value = $this->plugin->option->get(Wgpwpp_Option::OPTION_WP_CACHE_STATUS, null);
    if (!is_null($value))
      $value = 'on';

    $wp_cache_section_callback = function()
    {
      echo '<p>'.esc_html(__('WEDOS Global page caching will cache your WordPress posts and pages as static files. These static files are then served to users, reducing the processing load on the server. This can improve performance several hundred times over for fairly static pages.','wgpwpp')).'</p>';

      if (!$this->plugin->wp_cache->is_active())
      {
        $text = '<strong>'.esc_html(__('We recommend activating the Local WordPress cache!', 'wgpwpp')).'</strong>';
        $text .= '<br>';
        $text .= esc_html(__('Your website will speed up instantly with one click, without the need for complex configuration.', 'wgpwpp'));
        $this->plugin->admin_section->notices->info($text)->render();
      }

      $cache_plugins_detection = $this->plugin->wp_cache->detect_cache_plugins();
      if (count($cache_plugins_detection['known-cache-plugins']))
      {
        $text = __('It seems there is activated another page caching plugin. We strongly recommend to deactivate all other page caching plugins before activating our page caching to avoid incompatibilities.', 'wgpwpp');
        $text .= ' '.__('Detected page caching plugins:', 'wgpwpp');
        $text = '<strong>'.$text.'</strong>';
        $text .= '<ul>';
        foreach ($cache_plugins_detection['known-cache-plugins'] as $name)
          $text .= '<li>'.esc_html($name).'</li>';
        $text .= '</ul>';
        $this->plugin->admin_section->notices->warning($text)->render();
      }
      elseif ($cache_plugins_detection['advanced-caching'])
      {
        $text = __('It seems there is activated another page caching plugin. We strongly recommend to deactivate all other page caching plugins before activating our page caching to avoid incompatibilities.', 'wgpwpp');
        $text = '<strong>'.$text.'</strong>';
        $this->plugin->admin_section->notices->warning($text)->render();
      }

      if ($this->plugin->wp_cache->is_active())
      {
        $health = $this->plugin->wp_cache->health_test();
        if ($health['status'] !== 'good')
        {
          $text = '<strong>' . $health['description'] . '</strong>';
          $this->plugin->admin_section->notices->error($text)->render();
        }
        else
        {
          $text = '<strong>' . __('WEDOS Global page caching is active and working.', 'wgpwpp') . '</strong>';
          $this->plugin->admin_section->notices->success($text)->render();
        }
      }

      echo '<hr>';
    };

    add_settings_section(
      'wgpwpp_wp_cache_section',
      '',
      $wp_cache_section_callback,
      $this->plugin->get_plugin_name().'_wp_cache'
    );

    add_settings_field(
      'wgpwpp_wp_cache_status',
      __('WordPress page caching status','wgpwpp'),
      [$this, 'print_toggle_button'],
      $this->plugin->get_plugin_name().'_wp_cache',
      'wgpwpp_wp_cache_section',
      [
        'section' => 'wgpwpp_wp_cache',
        'name'    => 'wgpwpp_wp_cache_status',
        'value'   => $value,
      ]
    );
  }


  /**
   * Init CDN cache settings
   *
   * @since 1.1.0
   * @return void
   */
  private function init_settings_cdn_cache()
  {
    $this->plugin->get_service()->info();

    register_setting('wgpwpp_cdn_cache', 'wgpwpp_cdn_cache', [$this, 'process_cdn_cache_setting']);

    $cdn_section_callback = function()
    {
      echo '<p>'.esc_html(__('A global CDN from WEDOS will give you significantly better responsiveness and faster loading speeds for website content around the world. This will result in a better user experience, higher search engine rankings, more orders, leads and returning customers. By maximizing your website’s performance, you can improve your conversion rate by several percent!', 'wgpwpp')).'</p>';

      if (!$this->plugin->get_service()->is_active())
      {
        $msg = esc_html(__('To activate CDN Cache, it is necessary to complete the WEDOS Global service activation process.','wgpwpp'));
        $msg .= ' '.sprintf(
          '<p><a href="%s">%s</a></p>',
          esc_url( admin_url( 'admin.php?page=wgpwpp' ) ),
          esc_html(__( 'Go to complete the WEDOS Global service activation process', 'wgpwpp' ))
        );
        $this->plugin->admin_section->notices->warning('<strong>'.$msg.'</strong>')->render();
        die;
      }
      elseif ($this->plugin->get_service()->get_service_cache_status())
      {
        $text = '<strong>'.__('WEDOS Global CDN cache is active and working.', 'wgpwpp').'</strong>';
        $this->plugin->admin_section->notices->success($text)->render();
      }

      echo '<hr>';
    };

    add_settings_section(
      'wgpwpp_cdn_cache_section',
      '',
      $cdn_section_callback,
      $this->plugin->get_plugin_name().'_cdn_cache'
    );

    add_settings_field(
      'wgpwpp_cdn_cache_status',
      esc_html(__('WEDOS Global CDN Cache status','wgpwpp')),
      [$this, 'print_toggle_button'],
      $this->plugin->get_plugin_name().'_cdn_cache',
      'wgpwpp_cdn_cache_section',
      [
        'section'       => 'wgpwpp_cdn_cache',
        'name'          => 'wgpwpp_cdn_cache_status',
        'value'         => $this->plugin->get_service()->get_service_cache_status() ? 'on' : '',
        'checked_value' => $this->plugin->get_service()->get_service_cache_status() ? 'on' : null,
      ]
    );
  }


  /**
   * Processes CDN cache setting
   *
   * @since 1.1.0
   * @param $inputs
   * @return mixed
   */
  public function process_cdn_cache_setting($inputs)
  {
    if (!$this->plugin->get_service()->is_active())
    {
      add_settings_error('wgpwpp_cdn_cache_status', 1, esc_html(__('To activate CDN Cache, it is necessary to complete the WEDOS Global service activation process.', 'wgpwpp')), 'error');
      $inputs['wgpwpp_cdn_cache_status'] = 0;
      return $inputs;
    }

    $inputs['wgpwpp_cdn_cache_status'] = isset($inputs['wgpwpp_cdn_cache_status']) && $inputs['wgpwpp_cdn_cache_status'] === 'on';

    if ($inputs['wgpwpp_cdn_cache_status'] === $this->plugin->get_service()->get_service_cache_status())
      return $inputs;

    if (!$this->plugin->get_service()->cache_control($inputs['wgpwpp_cdn_cache_status']))
    {
      add_settings_error(
        'wgpwpp_cdn_cache_status',
        1,
        esc_html(__('Something went wrong. Try it later please.')),
        'error'
      );
      $inputs['wgpwpp_cdn_cache_status'] = !$inputs['wgpwpp_cdn_cache_status'];
    }

    return $inputs;
  }


  /**
   * Processes WP cache setting
   *
   * @since 1.1.0
   * @param $inputs
   * @return mixed
   */
  public function process_wp_cache_setting($inputs)
  {
    $status = isset($inputs['wgpwpp_wp_cache_status']) && $inputs['wgpwpp_wp_cache_status'] === 'on';

    if ($status && !$this->plugin->wp_cache->activate())
      add_settings_error('wgpwpp_wp_cache_status', 1, __('Something went wrong. Try it again please. Or check cache status on Wordpress Health page.', 'wgpwpp'));

    if (!$status)
      $this->plugin->wp_cache->deactivate();

    return $inputs;
  }


  /**
   * Prints toggle button HTML
   *
   * @since 1.1.0
   * @param array $args parameters
   * @return void
   */
  public function print_toggle_button(array $args)
  {
    $checked_value = 'on';
    if (isset($args['checked_value']))
      $checked_value = $args['checked_value'];

    switch ($args['section'])
    {
      case 'wgpwpp_wp_cache':
        $action = 'wgpwpp_purge_wp_cache';
        $cache_status = $this->plugin->wp_cache->is_active();
        break;

      case 'wgpwpp_cdn_cache':
        $action = 'wgpwpp_purge_cdn_cache';
        $cache_status = $this->plugin->get_service()->get_service_cache_status();
        break;

      default:
        $action = '';
        $cache_status = true;
    }
    ?>

    <div class="wgpwpp-cache-button-wrapper" style="display: flex; align-items: center;">

      <div>
        <label class="wgpwpp-button">
          <input class="wgpwpp-btn-checkbox" id="wgpwpp-cache-toggle-button" type="checkbox" name="<?= $this->get_input_name($args['section'], $args['name']); ?>" <?= isset($args['value']) ? checked($args['value'], $checked_value, false) : ''; ?>>
          <?php if ($args['value'] === 'on'): ?>
            <span class="wgpwpp-button-on" title="<?= __('deactivate', 'wgpwpp'); ?>"><?= __('enabled','wgpwpp'); ?></span>
          <?php else: ?>
            <span class="wgpwpp-button-off" title="<?= __('activate', 'wgpwpp'); ?>"><?= __('disabled','wgpwpp'); ?></span>
          <?php endif; ?>
        </label>
      </div>

      <?php if ($action): ?>
      <div>
        <label class="wgpwpp-button">
          <input class="wgpwpp-btn-button" id="wgpwpp-purge-cache" type="button" data-action="<?= $action; ?>" <?php disabled($cache_status, false); ?>>
          <span class="wgpwpp-button-off" title="<?= __('purge cache', 'wgpwpp'); ?>"><?= __('purge cache','wgpwpp'); ?></span>
        </label>
      </div>
      <?php endif; ?>

      <div>
        <span class="wgpwpp-button-spinner" id="wgpwpp-button-spinner" style=""></span>
      </div>

    </div>


    <?php
  }

  /**
   * Returns input name parameter value
   *
   * @param string $section
   * @param string $name input name
   * @return string
   * @since 1.1.0
   */
  private function get_input_name(string $section, string $name): string
  {
    return sprintf('%s[%s]', $section, $name);
  }
}
