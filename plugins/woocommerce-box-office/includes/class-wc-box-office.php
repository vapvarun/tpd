<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Box_Office {

	/**
	 * The single instance of WooCommerce_Box_Office.
	 *
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Plugin's component.
	 *
	 * @var   object
	 * @since 1.0.0
	 */
	public $components;

	/**
	 * Flag to indicate that the plugin has been initiated.
	 *
	 * @var   object
	 * @since 1.0.0
	 */
	private $_initiated = false;

	/**
	 * OrderUtil object.
	 *
	 * @var \Automattic\WooCommerce\Utilities\OrderUtil object.
	 */
	public static $order_util;

	/**
	 * Whether to display the native reports or not.
	 *
	 * @var bool
	 */
	public $display_reports = true;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function __construct( $file, $version ) {
		$this->_version      = $version;
		$this->_token        = 'woocommerce_box_office';
		$this->file          = $file;
		$this->dir           = trailingslashit( plugin_dir_path( $this->file ) );
		$this->assets_dir    = $this->dir . 'build';
		$this->assets_url    = trailingslashit( plugins_url( '/build/', $this->file ) );
		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	/**
	 * Init the plugin. Can be initiated once.
	 *
	 * @return void
	 */
	public function init() {
		if ( $this->_initiated ) {
			return;
		}

		try {
			self::$order_util = wc_get_container()->get( Automattic\WooCommerce\Utilities\OrderUtil::class );
		} catch ( Exception $e ) {
			self::$order_util = false;
		}

		// The Box Office reports are incompatible with stores running HPOS with syncing disabled.
		if ( self::is_cot_enabled() && ! self::is_cot_sync_enabled() ) {
			add_action( 'admin_notices', array( __CLASS__, 'display_hpos_incompatibility_notice' ) );
			$this->display_reports = false;
		}

		// Load includes.
		$this->_load_includes();

		// Set plugin's components.
		$this->_set_components();

		// Handle localisation.
		$this->load_plugin_textdomain();

		// Check updates.
		add_action( 'init', array( $this, 'check_updates' ) );

		// Register custom emails.
		add_filter( 'woocommerce_email_classes', array( $this, 'register_email' ), 10, 1 );

		// Declare compatibility with High-Performance Order Storage.
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

		// Show notice about changes in shortcode functionality in v1.2.3.
		add_action( 'admin_init', array( $this, 'render_shortcode_changes_notice' ) );

		$this->_initiated = true;
	}

	private function _load_includes() {
		// Box Office functions.
		require_once( $this->dir . 'includes/wcbo-deprecated-functions.php' );
		require_once( $this->dir . 'includes/wcbo-update-functions.php' );

		// Ticket model.
		require_once( $this->dir . 'includes/class-wc-box-office-ticket.php' );

		// Ticket form.
		require_once( $this->dir . 'includes/class-wc-box-office-ticket-form.php' );

		// Create ticket admin.
		require_once( $this->dir . 'includes/class-wc-box-office-ticket-create-admin.php' );

		// Settings.
		require_once( $this->dir . 'includes/class-wc-box-office-settings.php' );

		// Updater.
		require_once( $this->dir . 'includes/class-wc-box-office-updater.php' );

		// Component classes.
		require_once( $this->dir . 'includes/class-wc-box-office-logger.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-post-types.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-product-admin.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-cart.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-cron.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-ticket-admin.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-ticket-ajax.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-ticket-barcode.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-ticket-frontend.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-ticket-shortcode.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-assets.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-tools.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-report.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-privacy.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-order.php' );
		require_once( $this->dir . 'includes/class-wc-box-office-blocks.php' );
		require_once $this->dir . 'includes/class-wc-box-office-email-preview.php';
	}

	/**
	 * Set plugin's components.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function _set_components() {
		$this->components                   = new stdClass;
		$this->components->logger           = new WC_Box_Office_Logger();
		$this->components->post_types       = new WC_Box_Office_Post_Types();
		$this->components->ticket_barcode   = new WC_Box_Office_Ticket_Barcode();
		$this->components->ticket_admin     = new WC_Box_Office_Ticket_Admin();
		$this->components->ticket_ajax      = new WC_Box_Office_Ticket_Ajax();
		$this->components->ticket_frontend  = new WC_Box_Office_Ticket_Frontend();
		$this->components->ticket_shortcode = new WC_Box_Office_Ticket_Shortcode();
		$this->components->settings         = new WC_Box_Office_Settings();
		$this->components->product_admin    = new WC_Box_Office_Product_Admin();
		$this->components->cart             = new WC_Box_Office_Cart();
		$this->components->order            = new WC_Box_Office_Order();
		$this->components->assets           = new WC_Box_Office_Assets();
		$this->components->tools            = new WC_Box_Office_Tools();
		$this->components->cron             = new WC_Box_Office_Cron();
		$this->components->updater          = new WC_Box_Office_Updater();
		$this->components->blocks           = new WC_Box_Office_Blocks();
		$this->components->email_preview    = new WC_Box_Office_Email_Preview();

		if ( $this->display_reports ) {
			$this->components->report = new WC_Box_Office_Report();
		}
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		$domain = 'woocommerce-box-office';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

	/**
	 * Main WC_Box_Office Instance.
	 *
	 * Ensures only one instance of WC_Box_Office is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see get_woocommerce_box_office()
	 * @return Main WooCommerce_Box_Office instance
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'woocommerce-box-office' ), esc_html( $this->_version ) );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'woocommerce-box-office' ), esc_html( $this->_version ) );
	}

	/**
	 * Check updates
	 *
	 * @since 1.1.0
	 */
	public function check_updates() {
		$this->components->updater->update_check( $this->_version );
	}

	/**
	 * Register Box Office email class to WooCommerce emails
	 *
	 * @param WC_Email[] $emails WooCommerce registered email classes.
	 * @return WC_Email[]
	 */
	public function register_email( $emails = array() ) {
		require_once $this->dir . 'includes/class-wc-box-office-email.php';

		$emails['WC_Box_Office_Email'] = new WC_Box_Office_Email();

		return $emails;
	}

	/**
	 * Declare compatibility with High-Performance Order Storage.
	 *
	 * @since 1.1.42
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->file, true );
		}
	}

	/**
	 * Helper function to get whether custom order tables are enabled or not.
	 *
	 * @return bool
	 */
	public static function is_cot_enabled() {
		return self::$order_util && self::$order_util::custom_orders_table_usage_is_enabled();
	}

	/**
	 * Helper function to check whether custom order tables are in sync or not.
	 *
	 * @return bool
	 */
	public static function is_cot_sync_enabled() {
		return self::$order_util && self::$order_util::is_custom_order_tables_in_sync();
	}

	/**
	 * Displays an admin notice indicating Box Office reports are disabled on HPOS environments with no syncing.
	 */
	public static function display_hpos_incompatibility_notice() {
		$screen = get_current_screen();

		// Only display the admin notice on report admin screens.
		if ( ! $screen || 'woocommerce_page_wc-reports' !== $screen->id ) {
			return;
		}

		if ( current_user_can( 'activate_plugins' ) ) {
			/* translators: %1$s: Minimum version %2$s: Plugin page link start %3$s Link end */
			printf(
				'<div class="notice notice-error"><p><strong>%s</strong></p><p>%s</p></div>',
				esc_html__( 'WooCommerce Box Office - Reports Not Available', 'woocommerce-box-office' ),
				sprintf(
					// translators: placeholders $1 and $2 are opening <a> tags linking to the WooCommerce documentation on HPOS and data synchronization. Placeholder $3 is a closing link (<a>) tag.
					esc_html__( 'Box Office reports are incompatible with the %1$sWooCommerce data storage features%3$s enabled on your store. Please enable %2$stable synchronization%3$s if you wish to use Box Office reports.', 'woocommerce-box-office' ),
					'<a href="https://woocommerce.com/document/high-performance-order-storage/" target="_blank">',
					'<a href="https://woocommerce.com/document/high-performance-order-storage/#synchronization" target="_blank">',
					'</a>',
				)
			);
		}
	}

	/**
	 * Admin notice for the changes introduced to the shortcodes in v1.2.3s.
	 */
	function render_shortcode_changes_notice() {
		$notice_id    = 'wc_box_office_shortcode_change';
		$is_dismissed = get_user_meta( get_current_user_id(), "dismissed_{$notice_id}_notice", true );

		if ( $is_dismissed ) {
			WC_Admin_Notices::remove_notice( $notice_id );
			return;
		}

		$html = sprintf(
			/* translators: %1$s - Plugin name, %2$s - Link to documentation for shortcodes. */
			__( '<strong>%1$s 1.2.3</strong> has some changes to the functionality of the <code>[tickets]</code>, <code>[user_tickets]</code>, and <code>[order_tickets]</code> shortcodes. Please refer to <a  target="_blank" href="%2$s">this documentation</a> for more information.', 'woocommerce-box-office' ), // phpcs:ignore
			esc_html__( 'WooCommerce Box Office', 'woocommerce-box-office' ),
			esc_url( 'https://woo.com/document/woocommerce-box-office/#shortcodes' ),
		);

		WC_Admin_Notices::add_custom_notice(
			$notice_id,
			$html,
		);
	}
}
