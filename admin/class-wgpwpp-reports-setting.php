<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - reports settings page
 *
 * @since      1.1.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Reports_Setting
{
  /**
   * Plugin instance
   *
   * @since 1.1.0
   * @var Wgpwpp
   */
  private Wgpwpp $plugin;

  /**
   * Options array
   *
   * @since 1.1.0
   * @var array
   */
  private array $options;

  /**
   * Reports categories
   *
   * @since 1.1.0
   * @var array|string[]
   */
  public static array $categories = [
    'regular_reports_daily',
    'regular_reports_weekly',
    'regular_reports_monthly',
    'critical_reports',
    'info_reports_cyber',
    'info_reports_performance',
    'info_reports_news',
    'info_reports_study',
  ];


  /**
   * Constructor
   *
   * @since 1.1.0
   * @param Wgpwpp $plugin
   */
  public function __construct(Wgpwpp $plugin)
  {
    $this->plugin = $plugin;
    $this->define_hooks();
  }


  /**
   * Returns reports setting
   *
   * @since 1.1.0
   * @param int|null $user_id WP user ID
   * @return array|null
   */
  private function get_option(?int $user_id = NULL): ?array
  {
    if ($user_id === 0)
      return NULL;

    $option = get_option('wgpwpp_reports', null);
    if (is_null($option))
    {
      add_option('wgpwpp_reports', []);
      $option = [];
    }

    if ($user_id)
      return $option[$user_id] ?? NULL;

    return $option;
  }


  /**
   * Defines required hooks
   *
   * @since 1.1.0
   * @return void
   */
  private function define_hooks()
  {
    $this->plugin->get_loader()->add_action('admin_init', $this, 'init_settings');

    // JS
    $this->plugin->get_loader()->add_action('admin_enqueue_scripts', $this, 'enqueue_styles', 100);
    $this->plugin->get_loader()->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts', 100);
  }


  public function enqueue_styles($hook)
  {
    if ($hook !== 'wedos-global_page_wgpwpp_reports')
      return;

    wp_enqueue_style($this->plugin->get_plugin_name() . '_css_flags', plugins_url('admin/css/flag-icons.min.css', WGPWPP_PLUGIN_FILE), [], $this->plugin->get_version(), 'all');
  }


  /**
   * Register the JavaScript for the admin area.
   *
   * @since    1.2.0
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

    if ($hook !== 'wedos-global_page_wgpwpp_reports')
      return;

    wp_enqueue_script(
      $this->plugin->get_plugin_name(),
      plugin_dir_url( __FILE__ ).'js/wgpwpp-admin-reports.js',
      [ 'jquery' ],
      $this->plugin->get_version(), false
    );
  }


  /**
   * Init settings
   *
   * @since 1.1.0
   * @return void
   */
  public function init_settings()
  {
    register_setting('wgpwpp_reports', 'wgpwpp_reports', [$this, 'validate']);

    $options = $this->get_option(get_current_user_id());
    if (!$options)
      $options = [];

    $this->options = $options;

    // LANGUAGES SETTING

    add_settings_section(
      'wgpwpp_reports_lang_section',
      '',
      function() {},
      $this->plugin->get_plugin_name().'_reports'
    );

    add_settings_field(
      'wgpwpp_reports_lang_field',
      __('Choose preferred reports language (at least one)', 'wgpwpp'),
      [$this, 'field_reports_langs'],
      $this->plugin->get_plugin_name().'_reports',
      'wgpwpp_reports_lang_section'
    );


    // REGULAR REPORTS SETTING

    add_settings_section(
      'wgpwpp_regular_reports_section',
      __('Regular security reports', 'wgpwpp'),
      [$this, 'section_description'],
      $this->plugin->get_plugin_name().'_reports',
      [
        'desc' => esc_html(__('These reports contain information about attacks, content caching, site availability and potential problems.', 'wgpwpp')),
      ]
    );

    add_settings_field(
      'wgpwpp_regular_reports_daily',
      __('I want to receive daily report','wgpwpp'),
      [$this, 'print_toggle_button'],
      $this->plugin->get_plugin_name().'_reports',
      'wgpwpp_regular_reports_section',
      [
        'name'  => 'regular_reports_daily',
      ]
    );

    add_settings_field(
      'wgpwpp_regular_reports_weekly',
      __('I want to receive weekly report','wgpwpp'),
      [$this, 'print_toggle_button'],
      $this->plugin->get_plugin_name().'_reports',
      'wgpwpp_regular_reports_section',
      [
        'name'  => 'regular_reports_weekly',
      ]
    );

    add_settings_field(
      'wgpwpp_regular_reports_monthly',
      __('I want to receive monthly report','wgpwpp'),
      [$this, 'print_toggle_button'],
      $this->plugin->get_plugin_name().'_reports',
      'wgpwpp_regular_reports_section',
      [
        'name'  => 'regular_reports_monthly',
      ]
    );


    // CRITICAL REPORTS SETTING

    add_settings_section(
      'wgpwpp_critical_reports_section',
      __('Critical information regarding your WordPress installation', 'wgpwpp'),
      [$this, 'section_description'],
      $this->plugin->get_plugin_name().'_reports',
      [
        'desc' => esc_html(__('If we detect a problem with your WordPress installation, domain, web hosting or IP address, we will send you a warning as soon as possible.', 'wgpwpp')),
      ]
    );

    add_settings_field(
      'wgpwpp_critical_reports',
      __('I want to receive critical information as soon as possible','wgpwpp'),
      [$this, 'print_toggle_button'],
      $this->plugin->get_plugin_name().'_reports',
      'wgpwpp_critical_reports_section',
      [
        'name'  => 'critical_reports',
      ]
    );


    // INFORMATION REPORTS SETTING

    add_settings_section(
      'wgpwpp_info_reports_section',
      __('Points of interest, recommendations and other information', 'wgpwpp'),
      [$this, 'section_description'],
      $this->plugin->get_plugin_name().'_reports',
      [
        'desc' => esc_html(__('WEDOS has been dealing with cyber security and website performance optimization since 2010. We protect hundreds of thousands of websites, so we know a lot about current cyber threats and are happy to share important information with you. As well as how your WordPress can be faster and more efficient.', 'wgpwpp')),
      ]
    );

    add_settings_field(
      'wgpwpp_info_reports_cyber',
      __('I want to receive information about current cyber threats','wgpwpp'),
      [$this, 'print_toggle_button'],
      $this->plugin->get_plugin_name().'_reports',
      'wgpwpp_info_reports_section',
      [
        'name'  => 'info_reports_cyber',
      ]
    );

    add_settings_field(
      'wgpwpp_info_reports_performance',
      __('I want to receive information about WEDOS news and offers','wgpwpp'),
      [$this, 'print_toggle_button'],
      $this->plugin->get_plugin_name().'_reports',
      'wgpwpp_info_reports_section',
      [
        'name'  => 'info_reports_performance',
      ]
    );

    add_settings_field(
      'wgpwpp_info_reports_news',
      __('I want to receive information on how to improve the performance of my WordPress','wgpwpp'),
      [$this, 'print_toggle_button'],
      $this->plugin->get_plugin_name().'_reports',
      'wgpwpp_info_reports_section',
      [
        'name'  => 'info_reports_news',
      ]
    );

    add_settings_field(
      'wgpwpp_info_reports_study',
      __('I want to receive information about trainings, tutorials, podcasts and other study materials from WEDOS','wgpwpp'),
      [$this, 'print_toggle_button'],
      $this->plugin->get_plugin_name().'_reports',
      'wgpwpp_info_reports_section',
      [
        'name'  => 'info_reports_study',
      ]
    );
  }


  /**
   * Print`s language select buttons
   *
   * @since 1.1.0
   * @return void
   */
  public function field_reports_langs()
  {
    ?>
    <div class="wgpwpp-cache-button-wrapper" style="display: flex; align-items: center;">

      <div>
        <div style="white-space: nowrap;">
          <?php
          $this->print_flag('en', 'gb');
          $this->print_flag('cs', 'cz');
          $this->print_flag('sk', 'sk');
          $this->print_flag('pl', 'pl');
          $this->print_flag('fr', 'fr');
          ?>
        </div>
      </div>

      <div>
        <span class="wgpwpp-button-spinner" id="wgpwpp-reports-flags-spinner" style=""></span>
      </div>
    </div>
    <?php
  }


  /**
   * Returns input name parameter value
   *
   * @since 1.1.0
   * @param string $name input name
   * @return string
   */
  private function get_input_name(string $name): string
  {
    return sprintf('wgpwpp_reports[%d][%s]', get_current_user_id(), $name);
  }


  /**
   * Print language flag
   *
   * @since 1.1.0
   * @param string $lang language
   * @param string $flag flag name
   * @return void
   */
  private function print_flag(string $lang, string $flag)
  {
    $name = 'lang_'.$lang;
    ?>
    <label class="wgpwpp-reports-lang">
      <input class="wgpwpp-flag-checkbox" type="checkbox" name="<?= $this->get_input_name($name); ?>" <?= isset($this->options[$name]) ? checked($this->options[$name], 'on', false) : ''; ?>>
      <span class="wgpwpp-lang-flag fi fi-<?= $flag; ?>"></span>
      <?php if (isset($this->options[$name]) && $this->options[$name] === 'on'): ?>
      <span class='wgpwpp-lang-flag-yes dashicons dashicons-yes-alt'></span>
      <?php else: ?>
      <span class='wgpwpp-lang-flag-no dashicons dashicons-dismiss'></span>
      <?php endif; ?>
    </label>
    <?php
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
    ?>
    <div class="wgpwpp-cache-button-wrapper" style="display: flex; align-items: center;">

      <div>
        <label class="wgpwpp-reports-button">
          <input class="wgpwpp-btn-checkbox" id="wgpwpp-reports-toggle-button" type="checkbox" name="<?= $this->get_input_name($args['name']); ?>" <?= isset($this->options[$args['name']]) ? checked($this->options[$args['name']], 'on', false) : ''; ?>>
          <?php if (isset($this->options[$args['name']]) && $this->options[$args['name']] === 'on'): ?>
          <span class="wgpwpp-button-on" title="<?= __('deactivate', 'wgpwpp'); ?>"><?= __('ON','wgpwpp'); ?></span>
          <?php else: ?>
          <span class="wgpwpp-button-off" title="<?= __('activate', 'wgpwpp'); ?>"><?= __('OFF','wgpwpp'); ?></span>
          <?php endif; ?>
        </label>
        <input type="hidden" name="<?= $this->get_input_name($args['name'].'_consent_ip'); ?>" value="<?= $this->options[$args['name'].'_consent_ip'] ?? NULL; ?>">
        <input type="hidden" name="<?= $this->get_input_name($args['name'].'_consent_time'); ?>" value="<?= $this->options[$args['name'].'_consent_time'] ?? NULL; ?>">
        <input type="hidden" name="<?= $this->get_input_name($args['name'].'_consent_rem_ip'); ?>" value="<?= $this->options[$args['name'].'_consent_rem_ip'] ?? NULL; ?>">
        <input type="hidden" name="<?= $this->get_input_name($args['name'].'_consent_rem_time'); ?>" value="<?= $this->options[$args['name'].'_consent_rem_time'] ?? NULL; ?>">
      </div>

      <div>
        <span class="wgpwpp-button-spinner" id="<?= $this->get_input_name($args['name']).'-spinner'; ?>" style=""></span>
      </div>

    </div>
    <?php
  }


  /**
   * Prints section description
   *
   * @since 1.1.0
   * @param array $args parameters
   * @return void
   */
  public function section_description(array $args)
  {
    echo '<p>'.$args['desc'].'</p>';
  }


  /**
   * Validates reports setting
   *
   * @since 1.1.0
   * @param array $inputs inputs array
   * @return array
   */
  public function validate(array $inputs)
  {
    if (!isset($inputs[get_current_user_id()]))
      return [];

    $inputs = $inputs[get_current_user_id()];

    $valid = [];
    $langs = [];
    $active = [];

    foreach ($inputs as $key => $value)
    {
      if (!preg_match('/^lang_+/', $key))
        continue;

      $langs[] = $value;
      $valid[$key] = $value;
    }

    $validate = function(string $name) use ($inputs, &$valid, &$active)
    {
      $current_value = $this->options[$name] ?? NULL;

      if (isset($inputs[$name]))
      {
        $active[] = $name;
        $valid[$name] = $inputs[$name];
        $valid[$name.'_consent_rem_ip'] = NULL;
        $valid[$name.'_consent_rem_time'] = NULL;

        if ($current_value === 'on')
        {
          $valid[$name.'_consent_ip'] = $this->options[$name.'_consent_ip'] ?? NULL;
          $valid[$name.'_consent_time'] = $this->options[$name.'_consent_time'] ?? NULL;
        }
        elseif ($inputs[$name] === 'on')
        {
          $valid[$name.'_consent_ip'] = $_SERVER['REMOTE_ADDR'];
          $valid[$name.'_consent_time'] = time();
        }
      }
      else
      {
        $valid[$name] = 'off';
        $valid[$name.'_consent_ip'] = $this->options[$name.'_consent_ip'] ?? NULL;
        $valid[$name.'_consent_time'] = $this->options[$name.'_consent_time'] ?? NULL;

        if ($current_value === 'on')
        {
          $valid[$name . '_consent_rem_ip'] = $_SERVER['REMOTE_ADDR'];
          $valid[$name . '_consent_rem_time'] = time();
        }
        else
        {
          $valid[$name . '_consent_rem_ip'] = NULL;
          $valid[$name . '_consent_rem_time'] = NULL;
        }
      }
    };

    $validate('regular_reports_daily');
    $validate('regular_reports_weekly');
    $validate('regular_reports_monthly');
    $validate('critical_reports');
    $validate('info_reports_cyber');
    $validate('info_reports_performance');
    $validate('info_reports_news');
    $validate('info_reports_study');

    if (count($active) && !count($langs))
      $valid['lang_en'] = 'on';

    $option = $this->get_option(get_current_user_id());
    if (!is_array($option))
      $option = [];

    $option[get_current_user_id()] = $valid;

    return $option;
  }


  /**
   * Checks if the user opted in to receive required type of report
   *
   * @since 1.1.0
   * @param int $user_id WP user ID
   * @param string $category category
   * @param string $lang lang
   * @return bool
   */
  public function is_user_allowed(int $user_id, string $category, string $lang = '')
  {
    if ($user_id === 0)
      return false;

    $options = $this->get_option($user_id);
    if (!$options)
      return false;

    if ($lang && (!isset($options['lang_'.$lang]) || $options['lang_'.$lang] !== 'on'))
      return false;

    return isset($options[$category]) && $options[$category] === 'on';
  }


  /**
   * Returns array with IDs of users who opted in to receive required type of report
   *
   * @since 1.1.0
   * @param string $category category
   * @param string $lang lang
   * @return array
   */
  public function get_allowed_users(string $category, string $lang = ''): array
  {
    $options = $this->get_option();
    if (!$options)
      return [];

    $allowed_users = [];

    foreach (array_keys($options) as $user_id)
    {
      if (!is_numeric($user_id))
        continue;

      if ($this->is_user_allowed($user_id, $category, $lang))
        $allowed_users[] = $user_id;
    }

    return $allowed_users;
  }


  /**
   * Renders setting page
   *
   * @since 1.1.0
   * @return void
   */
  public function render()
  {
    include_once('partials/wgpwpp-reports-setting-display.php');
  }

}