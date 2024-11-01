<?php

if (!defined('ABSPATH'))
  exit;

if (!current_user_can('manage_options'))
  return;

if (isset($_GET['settings-updated'])) {
  add_settings_error('wgpwpp_cache_messages', 'wgpwpp_cache_messages', __('Settings Saved', 'wgpwpp'), 'updated');
}

$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
?>
<div class="wrap">
  <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

  <div id="wgpwpp-cache-notices" style="font-weight: bold;">
  <?php
  $notices = $this->plugin->admin_section->notices->get_notices();
  foreach ($notices as $notice) {
    $notice->render();
  }
  ?>
  </div>

  <nav class="nav-tab-wrapper">
    <a href="?page=wgpwpp_cache" class="nav-tab <?php if ($tab === null) : ?>nav-tab-active<?php endif; ?>"><?= esc_html(__('Wordpress Cache', 'wgpwpp')); ?></a>
    <a href="?page=wgpwpp_cache&tab=cdn-cache" class="nav-tab <?php if ($tab === 'cdn-cache') : ?>nav-tab-active<?php endif; ?>"><?= esc_html(__('WEDOS Global CDN Cache', 'wgpwpp')); ?></a>
  </nav>

  <form method="post" action="options.php" autocomplete="off" id="wgpwpp-admin-form" style="border-top: none;">

    <div class="tab-content">
      <?php switch ($tab) :
        case 'cdn-cache':
          settings_errors('wgpwpp_cdn_cache_status');
          settings_fields($this->plugin->get_plugin_name() . '_cdn_cache');
          do_settings_sections($this->plugin->get_plugin_name() . '_cdn_cache');
          //submit_button(esc_html(__('Save', 'wgpwpp')), 'primary', 'submit', TRUE, ['style' => "text-align:right;padding:5px 40px;font-weight:bold;"]);
          break;

        default:
          settings_errors('wgpwpp_wp_cache_status');
          settings_fields($this->plugin->get_plugin_name() . '_wp_cache');
          do_settings_sections($this->plugin->get_plugin_name() . '_wp_cache');
          //submit_button(esc_html(__('Save', 'wgpwpp')), 'primary', 'submit', TRUE, ['style' => "text-align:right;padding:5px 40px;font-weight:bold;"]);
          break;
      endswitch; ?>
    </div>
  </form>

</div>