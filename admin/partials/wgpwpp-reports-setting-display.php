<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Provide admin area view for the reports setting page
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.wedos.cz
 * @since      1.1.0
 *
 * @package    wgpwpp
 * @subpackage wgpwpp/admin/partials
 */

if (!current_user_can('manage_options'))
  return;

?>

<div class="wrap">

  <form method="post" action="options.php" id="wgpwpp-admin-form">

    <h1 style="display: flex; justify-content: space-between; align-items: center;">
      <span><?php echo esc_html(__('WEDOS Global - Security Reports Setting', 'wgpwpp')); ?></span>
    </h1>
    <hr>

    <p>
      <?php echo sprintf(__('Choose how often you want to receive security reports to your email %s.', 'wgpwpp'), sprintf('<strong>%s</strong>', $this->plugin->get_admin_email())); ?>
    </p>

    <?php
    settings_fields($this->plugin->get_plugin_name().'_reports');
    do_settings_sections($this->plugin->get_plugin_name().'_reports');
    ?>

  </form>

</div>
