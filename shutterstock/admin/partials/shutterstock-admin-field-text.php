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
?>
	<input
		class="<?php echo esc_attr( $atts['class'] ); ?>"
		id="<?php echo esc_attr( $atts['id'] ); ?>"
		name="<?php echo esc_attr( $atts['name'] ); ?>"
		placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
		type="<?php echo esc_attr( $atts['type'] ); ?>"
		value="<?php echo esc_attr( $atts['value'] ); ?>"
	/>
	<p class="description"><?php echo esc_html( $atts['description'] ); ?></p>
<?php
