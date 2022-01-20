<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.shutterstock.com
 * @since      1.0.0
 *
 * @package    Shutterstock
 * @subpackage Shutterstock/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Shutterstock
 * @subpackage Shutterstock/public
 * @author     Shutterstock <api@shutterstock.com>
 */
class Shutterstock_Public {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $shutterstock       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $shutterstock, $version,  $shutterstock_ui ) {

		$this->shutterstock = $shutterstock;
		$this->version = $version;
		$this->shutterstock_ui = $shutterstock_ui;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->shutterstock, plugin_dir_url( __FILE__ ) . 'css/shutterstock-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->shutterstock, plugin_dir_url( __FILE__ ) . 'js/shutterstock-public.js', array( 'jquery' ), $this->version, false );

	}

	public function load_shutterstock_block() {
		$dir = dirname( __FILE__ );

		$script_asset_path = "$dir/shutterstock-block/build/index.asset.php";

		if ( ! file_exists( $script_asset_path ) ) {
			throw new Error(
				'You need to run `npm start` or `npm run build` for the "shutterstock/public/shutterstock-block" block first.'
			);
		}

		// Registering the shutterstock-block script
		$index_js     = 'shutterstock-block/build/index.js';
		$script_asset = require(dirname( __FILE__ ) . '/shutterstock-block/build/index.asset.php' );

		wp_register_script(
			'shutterstock-block-block-editor',
			plugins_url( $index_js, __FILE__ ),
			$script_asset['dependencies'],
			$script_asset['version']
		);

		// Registering Shutterstock UI script
		wp_register_script('shutterstock-block-block-editor-shuttestock-ui-js', $this->shutterstock_ui['js']);

		wp_set_script_translations( 'shutterstock-block-block-editor', 'shutterstock', plugin_dir_path(__DIR__) . 'languages');

		// Registering the shutterstock-block styles
		$editor_css = 'shutterstock-block/build/index.css';

		wp_register_style('shutterstock-block-block-editor', plugins_url( $editor_css, __FILE__ ));

		// Registering Shutterstock UI styles
		wp_register_style('shutterstock-block-block-editor-shutterstock-ui-css', $this->shutterstock_ui['css']);

		// Registerging the shutterstock-block. Pattern is 'namespace/block-name'
		register_block_type( 'shutterstock/shutterstock-block', array(
			'editor_script' => 'shutterstock-block-block-editor',
			'script' => 'shutterstock-block-block-editor-shuttestock-ui-js',
			'editor_style'  => 'shutterstock-block-block-editor',
			'style' => 'shutterstock-block-block-editor-shutterstock-ui-css',
		) );

		$shutterstock_helper = new Shutterstock_Helper($this->shutterstock, $this->version);
		$shutterstock_js_object = $shutterstock_helper->get_shutterstock_js_object();

		wp_localize_script('shutterstock-block-block-editor', 'shutterstock', $shutterstock_js_object );
	}

}
