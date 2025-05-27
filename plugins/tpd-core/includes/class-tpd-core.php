<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Tpd_Core
 * @subpackage Tpd_Core/includes
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
 * @package    Tpd_Core
 * @subpackage Tpd_Core/includes
 * @author     WBCOM Team <admin@wbcomdesigns.com>
 */
class Tpd_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tpd_Core_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

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
		if ( defined( 'TPD_CORE_VERSION' ) ) {
			$this->version = TPD_CORE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tpd-core';

		$this->define_constants();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}


	/**
	 * Define plugin constants that are use entire plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function define_constants() {
		$this->define( 'TPDCORE_FILE', __FILE__ );
		$this->define( 'TPDCORE_URL', plugin_dir_url( dirname( __FILE__ ) ) );
		$this->define( 'TPDCORE_PATH', plugin_dir_path( dirname( __FILE__ ) ) );
		$this->define( 'TPDCORE_TEMPLATE_PATH', plugin_dir_path( dirname( __FILE__ ) ) . '/templates/' );
	}

	/**
	 * Define constant if not already defined
	 *
	 * @since 1.0.0
	 *
	 * @param string      $name
	 * @param string|bool $value
	 *
	 * @return void
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tpd_Core_Loader. Orchestrates the hooks of the plugin.
	 * - Tpd_Core_i18n. Defines internationalization functionality.
	 * - Tpd_Core_Admin. Defines all hooks for the admin area.
	 * - Tpd_Core_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tpd-core-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tpd-core-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tpd-core-admin.php';

		/**
		 * This function is responsible for general functions of plugin
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/tpd-core-functions.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tpd-core-public.php';

		$this->loader = new Tpd_Core_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tpd_Core_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tpd_Core_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tpd_Core_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tpd_Core_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'wbcom_remove_single_page_hooks', 5 );
		$this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'wbcom_display_product_attributes', 40 );
		$this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'wbcom_single_product_sold_by', 7 );

		/*
		* Manage WC Vendor template to pick file from addon.
		*/
		$this->loader->add_action( 'woocommerce_locate_template', $plugin_public, 'wbcom_wcvendors_plugin_template', 10, 3 );
		/**
		* Set product header at top.
		*/
		// $this->loader->add_action( 'buddyboss_theme_begin_content', $plugin_public, 'wbcom_wcvendors_render_store_header_on_top', 9 );
		// $this->loader->add_action( 'buddyboss_theme_begin_content', $plugin_public, 'wbcom_wcvendors_render_single_product_header', 9 );
		/**
		 * Add breadcrump on pages
		 */
		$this->loader->add_action( 'buddyboss_theme_begin_content', $plugin_public, 'wbcom_render_woocommerce_breadcrumb', 10 );
		$this->loader->add_action( 'woocommerce_after_main_content', $plugin_public, 'wbcom_render_archive_description', 10 );
		/**
		* Display category image on category archive
		*/
		$this->loader->add_action( 'woocommerce_archive_description', $plugin_public, 'wbcom_render_woocommerce_category_image', 50 );
		$this->loader->add_action( 'woocommerce_register_form_start', $plugin_public, 'wbcom_render_extra_register_fields' );
		// $this->loader->add_filter( 'wcvendors_sold_by_link', $plugin_public, 'wbcom_render_sold_by_link', 10, 2 );
		$this->loader->add_filter( 'woocommerce_product_get_rating_html', $plugin_public, 'wbcom_shop_product_rating', 10, 3 );
		$this->loader->add_action( 'woocommerce_after_quantity_input_field', $plugin_public, 'wbcom_display_quantity_plus' );
		$this->loader->add_action( 'woocommerce_before_quantity_input_field', $plugin_public, 'wbcom_display_quantity_minus' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'wbcom_add_cart_quantity_plus_minus' );
		$this->loader->add_action( 'wp', $plugin_public, 'wbcom_remove_actions' );
		$this->loader->add_action( 'woocommerce_after_shop_loop_item', $plugin_public, 'wbcom_display_seller_name_shop' );
		$this->loader->add_action( 'edit_term', $plugin_public, 'wbcom_save_product_cat_description', 999, 3 );
		$this->loader->add_action( 'created_term', $plugin_public, 'wbcom_save_product_cat_description', 999, 3 );
		// $this->loader->add_action( 'init', $plugin_public, 'wbcom_vendor_product_bulk_add_category' );
		// $this->loader->add_action( 'dokan_new_product_added', $plugin_public, 'wbcom_vendor_product_add_category', 11, 2 );
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
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tpd_Core_Loader    Orchestrates the hooks of the plugin.
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
