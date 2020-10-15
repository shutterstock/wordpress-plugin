<?php

/**
 * Provides the markup for any text field
 *
 * @link       https://www.shutterstock.com
 * @since      1.0.0
 *
 * @package    Shutterstock
 * @subpackage Shutterstock/admin/partials
 */
$capabilites_with_texts = [
  'can_user_license_shutterstock_photos' => 'License Images',
  'can_user_search_editorial_images' => 'Search Editorial Images',
  'can_user_license_shutterstock_editorial_image' => 'License Editorial Images	',
  'can_user_license_all_shutterstock_images' => 'License All Assets',
];

?>
<table class="user-settings-table">
    <thead>
      <th><?php echo esc_html_e('User Type', 'shutterstock') ?></th>
      <?php
      foreach($capabilites_with_texts as $capability) { ?>
        <th><?php echo esc_html_e(esc_attr($capability), 'shutterstock'); ?></th>
      <?php }?>
    <tbody>
    <?php
      foreach($atts['role_names'] as $key => $value) {?>
          <tr valign="top">
            <td><?php echo esc_attr($value); ?></td>
            <?php foreach($capabilites_with_texts as $cap_key => $cap_value) { ?>
                <td>
                  <input aria-role="checkbox"
                    <?php
                      if (isset($atts['user_settings'][$key]) && in_array($cap_key, $atts['user_settings'][$key], true)) {
                        checked( 1, true, true );
                      }
                    ?>
                		id="<?php echo esc_attr($key); ?>"
                		name="shutterstock_option_name[user_settings][<?php echo esc_attr($key);?>][]"
                		type="checkbox"
                		value="<?php echo esc_attr($cap_key); ?>" />
                </td>
            <?php } ?>
          </tr>
      <?php }?>
    </tbody>
  </table>
  <p class="description"><?php echo esc_attr( $atts['description'] ); ?></p>
<?php
