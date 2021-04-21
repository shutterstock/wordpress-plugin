<?php

/**
 * Provides the markup for a button field
 *
 * @link       https://www.shutterstock.com
 * @since      1.0.0
 *
 * @package    Shutterstock
 * @subpackage Shutterstock/admin/partials
 */
?>
    <?php
        if ($atts['value']) {
            echo '
            <script type="text/javascript">
                function logout() {
                  var pre = \'<p class="' . esc_attr($atts['context']['connected_class']) . '">' . esc_html__('wordpress:logging_out', 'shutterstock') . '</p>\';
                  jQuery(".shutterstock-token").replaceWith(pre);
                  jQuery(".shutterstock-connected").addClass("shutterstock-hidden");
                }
            </script>
            ';
            echo '<textarea
                readonly
                id="' . esc_attr($atts['id']) . '"
                name="' . esc_attr($atts['name']) . '"
                value="' . esc_textarea($atts['value']) . '"
                class="' . esc_attr($atts['context']['connected_class']) . ' shutterstock-hidden">' .
                    esc_textarea($atts['value']) .
                '</textarea>';

            echo '<div class="shutterstock-connected">'. esc_html__('wordpress:connected', 'shutterstock') .'</div>';
            echo '<button class="shutterstock-logout" onclick="logout()">' . esc_attr($atts['context']['has_value_button_text']) . '</button>';
        } else {
            echo '
            <script type="text/javascript">
                function loginWithShutterstock() {
                    var body = jQuery("#shutterstock-admin-settings-form").serializeArray();
                    jQuery.post("'. esc_attr($atts['context']['postLocation']) .'", body).error(
                        function() {

                        }).success(function() {
                            var clientId = jQuery("#api_key").val().trim();
                            var redirectURI ="'. esc_attr($atts['context']['onclickLocation']) .'";
                            window.location = redirectURI.replace("CLIENT_ID", clientId);
                        });
                }
            </script>
            <input
                class="shutterstock-login"
                type="button"
                value="' . esc_attr($atts['context']['no_value_button_text']) . '"
                onclick="loginWithShutterstock()"
            />
            <p class="description">' . esc_attr($atts['context']['description']). '</p>
            ';
        }
    ?>
<?php
