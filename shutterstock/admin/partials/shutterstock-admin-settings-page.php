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
      <?php
        $url = 'https://www.shutterstock.com/account/developers/apps';
        $link = sprintf( wp_kses( __( 'wordpress:text_insert_account_information', 'shutterstock' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( $url ) ); // @codingStandardsIgnoreLine
        echo $link; // @codingStandardsIgnoreLine
      ?>
    </p>
  </div>
  <hr />
  <form method="post" action="options.php" id="shutterstock-admin-settings-form">
    <?php
      settings_fields( 'shutterstock_option_group' );
      do_settings_sections( 'shutterstock-admin' );
      submit_button();
    ?>
  </form>
</div>
