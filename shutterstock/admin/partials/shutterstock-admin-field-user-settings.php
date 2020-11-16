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
  'can_user_license_shutterstock_photos' => esc_html__('wordpress:text_license_images', 'shutterstock'),
  'can_user_search_editorial_images' => esc_html__('wordpress:text_search_editorial_images', 'shutterstock'),
  'can_user_license_shutterstock_editorial_image' => esc_html__('wordpress:text_license_editorial_images', 'shutterstock'),
  'can_user_license_all_shutterstock_images' => esc_html__('wordpress:text_license_all_assets', 'shutterstock'),
];

?>
<table class="user-settings-table">
    <thead>
      <th><?php echo esc_html_e('wordpress:text_user_type', 'shutterstock') ?></th>
      <?php
      foreach($capabilites_with_texts as $capability) { ?>
        <th><?php echo esc_attr($capability); ?></th>
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
