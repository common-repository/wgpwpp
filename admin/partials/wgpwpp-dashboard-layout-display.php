<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Provide admin area view for the dashboard page
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.wedos.cz
 * @since      1.2.0
 *
 * @package    wgpwpp
 * @subpackage wgpwpp/admin/partials
 */

/**
 * @return Wgpwpp_dashboard_layout
 */
$controller = function()
{
  return $this;
};
$controller = $controller();
?>

<style>

</style>

<div class="wrap">

  <div id="wrapper-dashboard">
    <h1 class="dashboard"><?= esc_html(get_admin_page_title()).' - '.esc_html($controller->get_service_name()); ?></h1>
    <p><i class="fa fa-home fa-1x"></i> <b><?= __('Home','wgpwpp'); ?></b> <?= __('Dashboard','wgpwpp'); ?></p>

    <?php
    if ($controller->get_service()->is_active())
    {
      ?>
      <h2 class="status"><span style="font-weight:normal"><?= __('Variant', 'wgpwpp'); ?></span> <span style=""><b><?= $controller->get_service_variant(); ?></b></span>
      <?php
      $trial_days = $controller->get_trial_days();
      if (!is_null($trial_days))
      {
        ?>
        <span class="advanced-trial"><?= sprintf(_n('%s day TRIAL', '%s days TRIAL', $trial_days, 'wgpwpp'), number_format_i18n($trial_days)); ?></span>
        <?php
      }
      ?>
      </h2>
      <?php
    }
    else
    {
      ?>
      <h2 class="status"><span style="color:#0c1098"></span><span class="advanced-trial"><?= __('Local WordPress CACHE for FREE', 'wgpwpp'); ?></span></h2>
      <?php
    }
    ?>

    <div style="clear:both">

      <div class="wgpwpp_dashboard_notices" id="wgpwpp_dashboard_notices"></div>

      <div class="element_rating warnings info" id="wgpwpp_wp_cache_recommendation" style="display:<?= $controller->get_wp_cache_status() !== 1 ? 'block' : 'none'; ?>;">
        <div style="float:right">
          <a href="#" class="btn_krizek topstripe blue" id="wgpwpp_cache_recommendation_dismiss">×</a>
        </div>

        <div class="stars-box" style="margin-top:0;border:0">
          <a href="?page=wgpwpp_cache" class="blue" style="text-decoration:none;" target="_blank">
            <span class="fa fa-4x fa-lightbulb-o"></span>
          </a>
        </div>

        <a href="?page=wgpwpp_cache" style="text-decoration:none;">
          <h2 class="warning blue">
            <?= __('We recommend activating the Local WordPress cache!', 'wgpwpp'); ?></h2>
          <p class="help-us blue"><?= __('Your website will speed up instantly with one click, without the need for complex configuration.', 'wgpwpp'); ?></p></a>
      </div>

      <div class="wrapper-parent">
        <?php $controller->draw_chart(Wgpwpp_Service::OPENSEARCH_DATA_TYPE_DDOS, !$controller->get_service()->is_active()); ?>
        <?php $controller->draw_chart(Wgpwpp_Service::OPENSEARCH_DATA_TYPE_CACHE, !$controller->get_service()->is_active()); ?>
        <div class="wrap-chart mapicka" style="background: white top center no-repeat url('<?= $this->plugin->get_plugin_admin_images_url(); ?>304-250-max.png');"></div>
      </div>


      <div style="clear:both"></div>


      <?php if (get_option('wgpwpp_activation_time') && strtotime('+1 week', strtotime(get_option('wgpwpp_activation_time'))) <= current_time('timestamp') && !get_option('wgpwpp_rating_dismissed')) {?>
        <div class="element_rating" id="wgpwpp_rating_section">
          <div style="float:right">
            <a href="#" class="btn_krizek" id="wgpwpp_dismiss_rating_button">×</a>
          </div>

          <div class="stars-box">
            <a href="https://cs.wordpress.org/plugins/wgpwpp/#reviews" style="text-decoration:none;" target="_blank">
              <span class="fa fa-2x fa-star"></span>
              <span class="fa fa-2x fa-star"></span>
              <span class="fa fa-2x fa-star"></span>
              <span class="fa fa-2x fa-star"></span>
              <span class="fa fa-2x fa-star"></span>
            </a>
          </div>
          <a href="https://cs.wordpress.org/plugins/wgpwpp/#reviews" style="text-decoration:none;" target="_blank">
            <h2 class="rating">
              <?= __('Thank you for using the WEDOS Global Plugin!', 'wgpwpp'); ?></h2>
            <p class="help-us"><?= __('Help us make the web faster for others by rating our plugin.', 'wgpwpp'); ?></p></a>
        </div>
        
      <?php } ?>
     


      <div class="element">
        <div style="float:right">
          <label class="switch">
            <input type="checkbox" id="wgpwpp_wp_cache_checkbox" <?= checked($controller->get_wp_cache_status(), 1, false); ?>>
            <span class="slider"></span>
          </label>
        </div>

        <h2><?= __('Local', 'wgpwpp'); ?> <span style="font-weight:normal"><?= __('WordPress CACHE', 'wgpwpp'); ?></span></h2>
        <p><?= __('A feature that helps your website load faster by remembering, and reusing previously viewed pages, and content.', 'wgpwpp'); ?></p>
      </div>


      <div class="element <?= $controller->get_service()->is_active() ? '' : 'grey'; ?>">

        <?php if ($controller->get_service()->is_active()): ?>
        <div style="float:right">
          <label class="switch">
            <input type="checkbox" id="wgpwpp_cdn_cache_checkbox" <?= checked($controller->get_cdn_cache_status(), true, false); ?>>
            <span class="slider"></span>
          </label>
        </div>
        <?php else: ?>
        <div class="btn_align">
          <a href="?page=wgpwpp" class="btn_green"><?= __('Activate', 'wgpwpp'); ?></a>
        </div>
        <?php endif; ?>

        <h2><?= __('Global' ,'wgpwpp'); ?> <span style="font-weight:normal"><?= __('WordPress CACHE', 'wgpwpp'); ?></span></h2>
        <p><?= __('A feature that speeds up the loading of websites by storing their content on our servers around the world.', 'wgpwpp'); ?><p>
      </div>


      <div class="element <?= $controller->get_service()->is_active() ? '' : 'grey' ?>">

        <?php if ($controller->get_service()->is_active()): ?>
        <div style="float:right">
          <label class="switch">
            <input type="checkbox" checked disabled>
            <span class="slider" title="<?= __('The function is always available', 'wgpwpp'); ?>"><i style="margin-left:5px; margin-top:6px; color: white;" class="fa fa-lg fa-lock"></i></span>
          </label>
        </div>
        <?php else: ?>
        <div class="btn_align">
          <a href="?page=wgpwpp" class="btn_green"><?= __('Activate', 'wgpwpp'); ?></a><br /><br /><br />
        </div>
        <?php endif; ?>

        <h2><?= __('Cloud WAF', 'wgpwpp'); ?></h2>
        <p><?= __('It provides website protection by filtering, and blocking malicious internet attacks on cloud servers without burdening your local resources.', 'wgpwpp'); ?><p>
      </div>


      <div class="element <?= $controller->is_client_registered() ? '' : 'grey' ?>">

        <?php if ($controller->is_client_registered()): ?>
        <div class="btn_align">
          <a href="?page=wgpwpp_reports" class="btn_green"><?= __('Settings', 'wgpwpp'); ?></a>
          <br /><br /><br />
        </div>
        <?php else: ?>
        <div class="btn_align">
          <a href="?page=wgpwpp" class="btn_green"><?= __('Activate', 'wgpwpp'); ?></a>
        </div>
        <?php endif; ?>
 
        <h2><?= __('Security reports', 'wgpwpp'); ?></h2>
        <p><?= __('Get regular security reports that inform you about attacks, and threats, and offer tips for improving your WordPress site.', 'wgpwpp'); ?></p>
      </div>


      <div class="element grey">
        <div style="float:right">
          <label class="switch">
            <span class="slider" title="<?= __('Comming soon','wgpwpp'); ?>"></span>
          </label>
        </div>

        <h2 class="grey"><?= __('Instant Indexing', 'wgpwpp'); ?> <span class="blue">| <?= __('Comming soon', 'wgpwpp'); ?></span></h2>
        <p><?= __('Get better visibility in Google search results with our simple WordPress tool that instantly submits your site for indexing.', 'wgpwpp'); ?></p>
      </div>


      <div class="element grey">
        <div style="float:right">
          <label class="switch">

            <span class="slider" title="<?= __('Comming soon','wgpwpp'); ?>"></span>
          </label>
        </div>
        <h2 class="grey"><?= __('One-Click Backup', 'wgpwpp'); ?> <span class="blue">| <?= __('Comming soon', 'wgpwpp'); ?></span></h2>
        <p><?= __('Back up your entire WordPress installation with a single click. Quick, easy, and secure.','wgpwpp'); ?></p>
      </div>


    </div>
  </div>
</div>
