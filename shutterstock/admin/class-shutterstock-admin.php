<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.shutterstock.com
 * @since      1.0.0
 *
 * @package    Shutterstock
 * @subpackage Shutterstock/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Shutterstock
 * @subpackage Shutterstock/admin
 * @author     Shutterstock <api@shutterstock.com>
 */
class Shutterstock_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $shutterstock    The ID of this plugin.
	 */
	private $shutterstock;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $shutterstock_options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $shutterstock       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $shutterstock, $version ) {

		$this->shutterstock = $shutterstock;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Shutterstock_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Shutterstock_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->shutterstock, plugin_dir_url( __FILE__ ) . 'css/shutterstock-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Shutterstock_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Shutterstock_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->shutterstock, plugin_dir_url( __FILE__ ) . 'js/shutterstock-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_admin_settings_page() {
		add_options_page(
			'Shutterstock', // page_title
			'Shutterstock', // menu_title
			'manage_options', // capability
			"{$this->shutterstock}_options_page", // menu_slug
			array( $this, 'create_admin_settings_page') // function
		);
	}

	public function add_network_admin_settings_page() {
		add_submenu_page(
			'settings.php', // Parent element
			'Shutterstock', // Text in browser title bar
			'Shutterstock', // Text to be displayed in the menu.
			'manage_network_options', // Capability
			"{$this->shutterstock}_network_options_page", // Page slug, will be displayed in URL
			array( $this, 'create_network_admin_settings_page' ) // Callback function which displays the page
		);

		$this->network_settings_init();
	}

	public function create_network_admin_settings_page() {
		$this->shutterstock_options = $this->get_site_option();

		include( plugin_dir_path( __FILE__ ) . "partials/{$this->shutterstock}-admin-network-settings-page.php" );
	}

	public function create_admin_settings_page() {
		$this->shutterstock_options = $this->get_option();
		include( plugin_dir_path( __FILE__ ) . "partials/{$this->shutterstock}-admin-settings-page.php");
	}

	/**
	 * Register settings for network admins for section of shutterstock settings page
	 *
	 * @since    1.0.0
	 */
	public function network_settings_init() {
		register_setting(
			'shutterstock_network_option_group',
			'shutterstock_option_name',
			array($this, 'sanitize_fields')
		);

		add_settings_section(
			'shutterstock_setting_section', // id
			'', // title
			array( $this, 'shutterstock_section_info' ), // callback
			'shutterstock_network_options_page' // page
		);

		$this->register_fields("shutterstock_network_options_page");
	}


	/**
	 * Register settings for admin_init action
	 *
	 * @since    1.0.0
	 */
	public function settings_init() {
		register_setting(
			'shutterstock_option_group', // option_group
			'shutterstock_option_name', // option_name
			array( $this, 'sanitize_fields' ) // sanitize_callback
		);

		add_settings_section(
			'shutterstock_setting_section', // id
			'', // title
			array( $this, 'shutterstock_section_info' ), // callback
			'shutterstock-admin' // page
		);

		$this->register_fields('shutterstock-admin');
	}

	/**
	 * Register fields for section of shutterstock settings page
	 *
	 * @since    1.0.0
	 */
	public function register_fields($page) {
		add_settings_field(
			'api_key', // id
			__('API Key *', 'shutterstock'), // title
			array( $this, 'field_text' ), // callback
			$page, // page
			'shutterstock_setting_section', // section
			array(
				'id' => 'api_key',
				'description' => 'Required. Use the API key from an app you have created.',
			)
		);

		add_settings_field(
			'app_token', // id
			__('App Token *', 'shutterstock'),
			array( $this, 'field_textarea' ), // callback
			$page, // page
			'shutterstock_setting_section', // section
			array(
				'id' => 'app_token',
				'description' => 'Required. You can generate a token on your developer apps page.',
			)
		);

		add_settings_field(
			'user_settings', // id
			__('User Settings', 'shutterstock'), // title
			array( $this, 'field_user_settings' ), // callback
			$page, // page
			'shutterstock_setting_section', // section
			array(
				'id' => 'user_settings',
				'description' => 'Define which user roles are allowed to search and/or license assets.',				
			)
		);

	}

	public function sanitize_fields($input) {
		$sanitary_values = array();
		if ( isset( $input['api_key'] ) ) {
			$sanitary_values['api_key'] = sanitize_text_field( $input['api_key'] );
		}

		if ( isset( $input['app_token'] ) ) {
			$sanitary_values['app_token'] = esc_textarea( $input['app_token'] );
		}

		if (isset( $input['user_settings'])) {
			$sanitary_values['user_settings'] = $input['user_settings'];
			$sanitary_values['user_settings'] = array();

			if ( is_array( $input['user_settings'] ) ) {
				foreach($input['user_settings'] as $user_type => $user_capabilities) {
					$sanitary_values['user_settings'][$user_type] = array_map( 'sanitize_text_field', wp_unslash( $user_capabilities) );
				}
			}
		}

		return $sanitary_values;
	}

	public function shutterstock_section_info() {

	}

	/**
	 * Creates a text field
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_text( $args ) {
		$defaults['class'] 			= 'regular-text';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= "{$this->shutterstock}_option_name[{$args['id']}]";
		$defaults['placeholder'] 	= '';
		$defaults['type'] 			= 'text';
		$defaults['value'] 			= '';

		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->shutterstock_options[$atts['id']] ) ) {

			$atts['value'] = $this->shutterstock_options[$atts['id']];

		}

		include( plugin_dir_path( __FILE__ ) . "partials/{$this->shutterstock}-admin-field-text.php" );

	}

	/**
	 * Creates a textarea field
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_textarea( $args ) {

		$defaults['class'] 			= '';
		$defaults['cols'] 			= 100;
		$defaults['context'] 		= '';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= "{$this->shutterstock}_option_name[{$args['id']}]";
		$defaults['rows'] 			= 6;
		$defaults['value'] 			= '';

		$atts = wp_parse_args( $args, $defaults );

		if ( ! empty( $this->shutterstock_options[$atts['id']] ) ) {

			$atts['value'] = $this->shutterstock_options[$atts['id']];

		}

		include( plugin_dir_path( __FILE__ ) . "partials/{$this->shutterstock}-admin-field-textarea.php" );

	}

	public function field_user_settings($args) {
		$wp_roles = wp_roles();
		$role_names = $wp_roles->role_names;
		$atts['role_names'] = $role_names;
		$atts['description'] = $args['description'];

		if (isset($this->shutterstock_options[$args['id']])) {
			$atts['user_settings'] = $this->shutterstock_options[$args['id']];
		}

		include( plugin_dir_path( __FILE__ ) . "partials/{$this->shutterstock}-admin-field-user-settings.php" );

	}

	/**
	 * This function here is hooked up to a special action and necessary to process
	 * the saving of the options. This is the big difference with a normal options
	 * page.
	 */
	public function shutterstock_network_update_options() {
		// Make sure we are posting from our options page. There's a little surprise
		// here, on the options page we used the 'shutterstock_network_option_group'
		// slug when calling 'settings_fields' but we must add the '-options' postfix
		// when we check the referer.
		check_admin_referer( 'shutterstock_network_option_group-options' ); // Nonce security check

		// This is the list of registered options.
		global $new_whitelist_options;

		$options = $new_whitelist_options['shutterstock_network_option_group'];

		// Go through the posted data and save only our options.
		foreach ($options as $option) {			
			if (isset($_POST[$option])) {
				$api_key = isset( $_POST[$option]['api_key'] ) ? sanitize_text_field($_POST[$option]['api_key']) : '';			
				$app_token = isset( $_POST[$option]['app_token'] ) ? sanitize_textarea_field($_POST[$option]['app_token']) : '';
				$user_settings = [];

				if (isset($_POST[$option]['user_settings'])) {
					$wp_roles = wp_roles();
					$role_names = $wp_roles->role_names;
					
					foreach($role_names as $role_slug => $role_display_name) {
						if (isset($_POST[$option]['user_settings'][$role_slug])) {
							$user_settings[$role_slug] = array_map( 'sanitize_text_field', wp_unslash( $_POST[$option]['user_settings'][$role_slug] ) );
						}
					}			
				}			

				update_site_option($option, array(
					'api_key' => $api_key,
					'app_token' => $app_token,
					'user_settings' => $user_settings,
				));
			}
		}

		$wp_nonce = wp_create_nonce('shutterstock-network-settings-updated');
		// At last we redirect back to our options page.
		wp_redirect(add_query_arg(array('page' => 'shutterstock_network_options_page',
		'updated' => $wp_nonce, ), network_admin_url('settings.php')));
		exit;
	}

	private function get_option() {
		return get_option("{$this->shutterstock}_option_name");
	}

	private function get_site_option() {
		return get_site_option("{$this->shutterstock}_option_name");
	}
}
