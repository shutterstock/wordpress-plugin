<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.shutterstock.com
 * @since      1.0.0
 *
 * @package    Shutterstock
 * @subpackage Shutterstock/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
  <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
  <div>
    <p>            
      Please insert your Shutterstock account information to use this plugin. You can create an app and access token on your 
      <a href="https://www.shutterstock.com/account/developers/apps" target="_blank">developer apps page</a>.
    </p>
  </div>
  <hr />
  <form method="post" action="options.php">
    <?php
      settings_fields( 'shutterstock_option_group' );
      do_settings_sections( 'shutterstock-admin' );
      submit_button();
    ?>
  </form>
</div>
