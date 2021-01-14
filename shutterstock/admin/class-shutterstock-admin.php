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
	public function __construct( $shutterstock, $version, $shutterstock_ui ) {

		$this->shutterstock = $shutterstock;
		$this->version = $version;
		$this->shutterstock_ui = $shutterstock_ui;
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
	public function enqueue_scripts($hook) {

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

		if ('media_page_shutterstock_media_page' === $hook) {
			$this->load_scripts_for_media_page();
		}
	}

	public function load_scripts_for_media_page() {
		wp_register_style('shutterstock-media-page-shutterstock-ui-css', $this->shutterstock_ui['css']);
		wp_enqueue_style( 'shutterstock-media-page-shutterstock-ui-css');
		wp_register_script('shutterstock-media-page-shuttestock-ui-js', $this->shutterstock_ui['js']);
		wp_enqueue_script( 'shutterstock-media-page-shuttestock-ui-js');

		$dir = dirname( __FILE__ );

		$script_asset_path = "$dir/shutterstock-media-page/index.asset.php";

		if ( ! file_exists( $script_asset_path ) ) {
			throw new Error(
				'You need to run `npm start:shutterstock:mediapage` or `npm run build:shutterstock:mediapage` for the "shutterstock/public/shutterstock-block" block first.'
			);
		}


		// Registering the shutterstock-media-page script
		$index_js     = 'shutterstock-media-page/index.js';
		$script_asset = require(dirname( __FILE__ ) . '/shutterstock-media-page/index.asset.php' );

		// Registering the shutterstock-media-page styles
		$index_css = 'shutterstock-media-page/index.css';

		wp_register_style('shutterstock-media-page-styles', plugins_url( $index_css, __FILE__ ));

		wp_register_script(
			'shutterstock-media-page',
			plugins_url( $index_js, __FILE__ ),
			$script_asset['dependencies'],
			$script_asset['version']
		);

		$loaded = wp_set_script_translations( 'shutterstock-media-page', 'shutterstock', plugin_dir_path(__DIR__) . 'languages/');

		wp_enqueue_script('shutterstock-media-page');
		wp_enqueue_style( 'shutterstock-media-page-styles');

		$shutterstock_helper = new Shutterstock_Helper($this->shutterstock, $this->version);
		$shutterstock_js_object = $shutterstock_helper->get_shutterstock_js_object();

		wp_localize_script('shutterstock-media-page-shuttestock-ui-js', 'shutterstock', $shutterstock_js_object );
	}

	public function add_admin_settings_page() {
		add_options_page(
			'Shutterstock', // page_title
			'Shutterstock', // menu_title
			'manage_options', // capability
			"{$this->shutterstock}_options_page", // menu_slug
			array( $this, 'create_admin_settings_page') // function
		);

		add_submenu_page(
			'upload.php',
			'Shutterstock',
			'Shutterstock',
			'upload_files',
			'shutterstock_media_page',
			array($this, 'create_media_page')
		);
	}

	public function create_media_page() {
		include( plugin_dir_path( __FILE__ ) . "partials/{$this->shutterstock}-media-shutterstock.php");
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
			__('wordpress:text_api_key', 'shutterstock'), // title
			array( $this, 'field_text' ), // callback
			$page, // page
			'shutterstock_setting_section', // section
			array(
				'id' => 'api_key',
				'description' => __('wordpress:text_api_key_description', 'shutterstock'),
			)
		);

        add_settings_field(
            'api_secret', // id
            __('wordpress:text_api_secret', 'shutterstock'), // title
            array( $this, 'field_text' ), // callback
            $page, // page
            'shutterstock_setting_section', // section
            array(
                'id' => 'api_secret',
                'description' => __('wordpress:text_api_secret_description', 'shutterstock'),
            )
        );

	$nonce = wp_create_nonce('generate-token');

	$network_admin = is_network_admin();

	$redirect_uri = $network_admin ?
            network_admin_url('edit.php?action=shutterstock_network_generate_access_token&_wpnonce='.$nonce) :
	    admin_url('admin.php?action=shutterstock_generate_access_token&_wpnonce='.$nonce);

	$post_location = $network_admin ?
		'edit.php?action=shutterstock_network_update_options' :
		'options.php';


        $redirect_query_params = http_build_query(array(
            'state' => gmdate(DATE_ATOM),
	    'redirect_uri' => $redirect_uri,
            'client_id' => 'CLIENT_ID',
            'scope' => 'licenses.create licenses.view purchases.view user.view'
        ));

        $query_params = http_build_query(array(
            'next' => '/oauth/authorize?' . $redirect_query_params,
        ));

        $onclick_location = 'https://accounts.shutterstock.com/login?' . $query_params;


        add_settings_field(
            'app_token', // id
            __('wordpress:text_app_token', 'shutterstock'),
            array($this, 'field_button'), // callback
            $page, // page
            'shutterstock_setting_section', // section
            array(
                'id' => 'app_token',
                'context' => [
                    'connected_class' => 'shutterstock-token',
                    'onclickLocation' => $onclick_location,
                    'postLocation' => $post_location,
                    'has_value_button_text' => __('wordpress:text_logout', 'shutterstock'),
		    'no_value_button_text' => __('wordpress:text_log_in_with_shutterstock', 'shutterstock'),
		    'description' => __('wordpress:text_app_token_description', 'shutterstock'),
                ],
            )
        );

		add_settings_field(
			'editorial_country', // id
			__('wordpress:text_editorial_country', 'shutterstock'), // title
			array( $this, 'field_text' ), // callback
			$page, // page
			'shutterstock_setting_section', // section
			array(
				'id' => 'editorial_country',
				'description' => __('wordpress:text_editorial_country_description', 'shutterstock'),
			)
		);

		add_settings_field(
			'user_settings', // id
			__('wordpress:text_user_settings', 'shutterstock'), // title
			array( $this, 'field_user_settings' ), // callback
			$page, // page
			'shutterstock_setting_section', // section
			array(
				'id' => 'user_settings',
				'description' => __('wordpress:text_user_settings_description', 'shutterstock'),
			)
		);

	}

	public function sanitize_fields($input) {
		$sanitary_values = array();
		if ( isset( $input['api_key'] ) ) {
			$sanitary_values['api_key'] = sanitize_text_field( $input['api_key'] );
		}

        if ( isset( $input['api_secret'] ) ) {
            $sanitary_values['api_secret'] = sanitize_text_field( $input['api_secret'] );
        }

		if ( isset( $input['editorial_country'] ) ) {
			$sanitary_values['editorial_country'] = sanitize_text_field( $input['editorial_country'] );
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
		$defaults			= [];
		$defaults['class'] 		= 'regular-text';
		$defaults['description'] 	= '';
		$defaults['label'] 		= '';
		$defaults['name'] 		= "{$this->shutterstock}_option_name[{$args['id']}]";
		$defaults['placeholder'] 	= '';
		$defaults['type'] 		= 'text';
		$defaults['value'] 		= '';

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
		$defaults			= [];
		$defaults['class'] 		= '';
		$defaults['cols'] 		= 100;
		$defaults['context'] 		= '';
		$defaults['description'] 	= '';
		$defaults['label'] 		= '';
		$defaults['name'] 		= "{$this->shutterstock}_option_name[{$args['id']}]";
		$defaults['rows'] 		= 6;
		$defaults['value'] 		= '';

		$atts = wp_parse_args( $args, $defaults );

		if ( ! empty( $this->shutterstock_options[$atts['id']] ) ) {

			$atts['value'] = $this->shutterstock_options[$atts['id']];

		}

		include( plugin_dir_path( __FILE__ ) . "partials/{$this->shutterstock}-admin-field-textarea.php" );

	}

    /**
     * Creates a button field
     *
     * @param 	array 		$args 			The arguments for the field
     * @return 	string 						The HTML field
     */
    public function field_button( $args ) {

        $defaults = [];
        $defaults['context'] 		= '';
        $defaults['value'] 			= '';
        $defaults['name'] 		= "{$this->shutterstock}_option_name[{$args['id']}]";

        $atts = wp_parse_args( $args, $defaults );

        if ( ! empty( $this->shutterstock_options[$atts['id']] ) ) {

            $atts['value'] = $this->shutterstock_options[$atts['id']];

        }

        include( plugin_dir_path( __FILE__ ) . "partials/{$this->shutterstock}-admin-field-shutterstock-login-button.php" );

    }

	public function field_user_settings($args) {
		$wp_roles = wp_roles();
		$role_names = $wp_roles->role_names;
		$atts = [];
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
				$api_secret = isset( $_POST[$option]['api_secret'] ) ? sanitize_text_field($_POST[$option]['api_secret']) : '';
				$app_token = isset( $_POST[$option]['app_token'] ) ? sanitize_textarea_field($_POST[$option]['app_token']) : '';
				$editorial_country = isset( $_POST[$option]['editorial_country'] ) ? sanitize_text_field($_POST[$option]['editorial_country']) : '';
				$user_settings = [];

				if (isset($_POST[$option]['user_settings'])) {
					$wp_roles = wp_roles();
					$role_names = $wp_roles->role_names;
					$roles_slugs = array_keys($role_names);

					foreach($roles_slugs as $role_slug) {
						if (isset($_POST[$option]['user_settings'][$role_slug])) {
							$user_settings[$role_slug] = array_map( 'sanitize_text_field', wp_unslash( $_POST[$option]['user_settings'][$role_slug] ) );
						}
					}
				}

				update_site_option($option, array(
					'api_key' => $api_key,
					'api_secret' => $api_secret,
					'app_token' => $app_token,
					'user_settings' => $user_settings,
					'editorial_country' => $editorial_country,
				));
			}
		}

		$wp_nonce = wp_create_nonce('shutterstock-network-settings-updated');
		// At last we redirect back to our options page.
		wp_redirect(add_query_arg(array('page' => 'shutterstock_network_options_page',
		'updated' => $wp_nonce, ), network_admin_url('settings.php')));
		exit;
	}

	public function shutterstock_generate_access_token() {
		$nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']): '';

		if (!wp_verify_nonce( $nonce, 'generate-token')) {
			die( esc_html_e('Security error.') );
		} else {
			$code = isset($_REQUEST['code']) ? sanitize_text_field($_REQUEST['code']) : '';

			if ($code) {
				$option = is_network_admin() ? $this->get_site_option() : $this->get_option();
				$token = $this->post_token($code, $option['api_key'], $option['api_secret']);

				$option['app_token'] = $token;

				if (is_network_admin()) {
					update_site_option("{$this->shutterstock}_option_name", $option);
					$wp_nonce = wp_create_nonce('shutterstock-network-settings-updated');
					// At last we redirect back to our options page.
					wp_redirect(add_query_arg(array('page' => 'shutterstock_network_options_page',
					'updated' => $wp_nonce, ), network_admin_url('settings.php')));
					exit;
				} else {
					update_option("{$this->shutterstock}_option_name", $option);
					$wp_nonce = wp_create_nonce('shutterstock-setting-updated');
					// At last we redirect back to our options page.
					wp_redirect(add_query_arg(array('page' => 'shutterstock_options_page',
					'updated' => $wp_nonce, ), admin_url('options-general.php')));
					exit;
				}
			}
		}
	}

	private function get_option() {
		return get_option("{$this->shutterstock}_option_name");
	}

	private function get_site_option() {
		return get_site_option("{$this->shutterstock}_option_name");
	}

	private function post_token($code, $key, $secret) {
        $token_url = 'https://api.shutterstock.com/v2/oauth/access_token';

        $body = [
            "client_id" => $key,
            "client_secret" => $secret,
            "code" => $code,
            "grant_type" => 'authorization_code',
        ];

        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-shutterstock-application' => 'Wordpress/'. $this->version,
            ],
            'body' => wp_json_encode($body),
            'data_format' => 'body',
        ];

        $response = wp_remote_post($token_url, $args);
        $response_body = wp_remote_retrieve_body($response);

        $decoded_body = json_decode($response_body, true);

        return $decoded_body['access_token'];
	}
}
