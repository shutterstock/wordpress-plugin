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
<div id="message" class="updated notice is-dismissible"><p><?php esc_html_e('Settting saved.') ?></p></div>
<?php endif; ?>
<div class="wrap">
  <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
  <div>
    <p>
      <?php
        $url = 'https://www.shutterstock.com/account/developers/apps';
        $link = sprintf( wp_kses( __( 'wordpress:text_insert_account_information', 'shutterstock' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( $url ) ); // @codingStandardsIgnoreLine
        echo $link; // @codingStandardsIgnoreLine
      ?>
    </p>
  </div>
  <hr />
  <?php settings_errors(); ?>
  <form method="post" action="edit.php?action=shutterstock_network_update_options" id="shutterstock-admin-settings-form">
    <?php
      settings_fields( 'shutterstock_network_option_group' );
      do_settings_sections( 'shutterstock_network_options_page' );
      submit_button();
    ?>
  </form>
</div>
