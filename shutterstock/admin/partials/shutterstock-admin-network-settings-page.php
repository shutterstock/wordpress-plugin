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
<?php

if (isset($_GET['updated']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['updated'] ) ), 'shutterstock-network-settings-updated' ) ): ?>
<div id="message" class="updated notice is-dismissible"><p><?php _e('Settting saved.') ?></p></div>
<?php endif; ?>
<div class="wrap">
  <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
  <div>
    <p>            
      Please insert your Shutterstock account information to use this plugin. You can create an app and access token on your 
      <a href="https://www.shutterstock.com/account/developers/apps" target="_blank">developer apps page</a>.
    </p>
  </div>
  <hr />
  <?php settings_errors(); ?>
  <form method="post" action="edit.php?action=shutterstock_network_update_options">
    <?php
      settings_fields( 'shutterstock_network_option_group' );
      do_settings_sections( 'shutterstock_network_options_page' );
      submit_button();
    ?>
  </form>
</div>
