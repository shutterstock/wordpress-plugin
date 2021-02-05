<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.shutterstock.com
 * @since      1.0.0
 *
 * @package    Shutterstock
 * @subpackage Shutterstock/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Shutterstock
 * @subpackage Shutterstock/includes
 * @author     Shutterstock <api@shutterstock.com>
 */
class Shutterstock {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Shutterstock_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $shutterstock    The string used to uniquely identify this plugin.
	 */
	protected $shutterstock;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SHUTTERSTOCK_VERSION' ) ) {
			$this->version = SHUTTERSTOCK_VERSION;
		} else {
			$this->version = '1.3.4';
		}
		$this->shutterstock = 'shutterstock';
		$this->shutterstock_ui = [
			'js' => 'https://api-cdn.shutterstock.com/0.1.33/static/js/sstk-widget.js',
			'css' => 'https://api-cdn.shutterstock.com/0.1.33/static/css/sstk-widget.css',
		];

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_shutterstock_api_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Shutterstock_Loader. Orchestrates the hooks of the plugin.
	 * - Shutterstock_i18n. Defines internationalization functionality.
	 * - Shutterstock_Admin. Defines all hooks for the admin area.
	 * - Shutterstock_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shutterstock-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shutterstock-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shutterstock-helper.php';
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-shutterstock-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-shutterstock-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shutterstock-api.php';

		$this->loader = new Shutterstock_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Shutterstock_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Shutterstock_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		$this->loader->add_filter('load_textdomain_mofile', $plugin_i18n, 'load_textdomain_mofile', 10, 2);
		$this->loader->add_filter('load_script_translation_file', $plugin_i18n, 'load_script_translation_file', 10, 3);

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Shutterstock_Admin( $this->get_shutterstock(), $this->get_version(), $this->shutterstock_ui );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_settings_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_init' );
		$this->loader->add_action( 'network_admin_menu', $plugin_admin, 'add_network_admin_settings_page' );
		$this->loader->add_action( 'network_admin_edit_shutterstock_network_update_options', $plugin_admin, 'shutterstock_network_update_options' );
		$this->loader->add_action( 'network_admin_edit_shutterstock_network_generate_access_token', $plugin_admin, 'shutterstock_generate_access_token' );
		$this->loader->add_action( 'admin_action_shutterstock_generate_access_token', $plugin_admin, 'shutterstock_generate_access_token');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Shutterstock_Public( $this->get_shutterstock(), $this->get_version(), $this->shutterstock_ui );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_public, 'load_shutterstock_block' );

	}

	private function define_shutterstock_api_hooks() {
		$plugin_shutterstock_api = new Shutterstock_API( $this->get_shutterstock(), $this->get_version() );
		$this->loader->add_action( 'rest_api_init', $plugin_shutterstock_api, 'register_routes');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_shutterstock() {
		return $this->shutterstock;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Shutterstock_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
