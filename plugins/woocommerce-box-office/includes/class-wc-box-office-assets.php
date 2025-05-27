<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Box_Office_Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Load frontend JS & CSS.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );

		// Load admin JS & CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
	}

	/**
	 * Load frontend CSS.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles() {
		wp_register_style( 'woocommerce-box-office-frontend', esc_url( WCBO()->assets_url ) . '/frontend.css', array(), WCBO()->_version );
		wp_enqueue_style( 'woocommerce-box-office-frontend' );

		wp_register_style( 'woocommerce-box-office-multiple-tickets', WCBO()->assets_url . '/multiple-tickets.css', array( 'dashicons' ), WCBO()->_version );

		if ( is_product() ) {
			wp_enqueue_style( 'woocommerce-box-office-multiple-tickets' );
		}
	}

	/**
	 * Load frontend Javascript.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts( $force = false ) {
		if ( wcbo_is_my_ticket_page() || $force ) {

			// Load JS for ticket edit page.
			wcbo_register_script( 'woocommerce-box-office-frontend', 'frontend' );
			wp_enqueue_script( 'woocommerce-box-office-frontend' );

			// Pass data to frontend JS
			wp_localize_script( 'woocommerce-box-office-frontend', 'wc_box_office', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'scan_nonce' => wp_create_nonce( 'scan-barcode' ) ) );
		}
	}

	/**
	 * Load admin Javascript.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts( $hook = '' ) {
		$exported_js = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		);

		$content_type = get_post_type();

		// Support COT order edit page
		if ( isset( $_GET['page'], $_GET['id'], $_GET['action'] ) && $_GET['page'] === 'wc-orders' && $_GET['action'] === 'edit' && (int) $_GET['id'] > 0 ) {
			$content_type = 'cot_order_edit';
		}

		switch ( $content_type ) {
			case 'cot_order_edit':
			case 'shop_order':
				$exported_js['editPostUrl'] = admin_url( 'post.php?action=edit' );
				wcbo_register_script( 'woocommerce-box-office-admin-order', 'admin-order' );
				wp_enqueue_script( 'woocommerce-box-office-admin-order' );
				wp_localize_script( 'woocommerce-box-office-admin-order', 'wcBoxOfficeParams', $exported_js );
				break;
			case 'product':
				// Export default contents for print and email to JS var.
				$exported_js['defaultPrintContent'] = wc_get_template_html( 'ticket/default-print-content.php', array(), 'woocommerce-box-office', WCBO()->dir . 'templates/' );
				$exported_js['defaultEmailContent'] = wc_get_template_html( 'ticket/default-email-content.php', array(), 'woocommerce-box-office', WCBO()->dir . 'templates/' );

				wcbo_register_script( 'woocommerce-box-office-admin-product', 'admin-product' );
				wp_enqueue_script( 'woocommerce-box-office-admin-product' );
				wp_localize_script( 'woocommerce-box-office-admin-product', 'wcBoxOfficeParams', $exported_js );
				break;
			case 'event_ticket':
				break;
		}

		$screen = get_current_screen();
		if ( 'event_ticket_page_ticket_tools' === $screen->id ) {
			$exported_js['previewEmailAction']    = 'show_test_email';
			$exported_js['previewEmailNonce']     = wp_create_nonce( 'test-email' );

			$exported_js['i18n_previewEmptyProductOrBody']    = __( 'Product is not selected or email body is empty. Please fill it.', 'woocommerce-box-office' );

			wcbo_register_script( 'woocommerce-box-office-admin-tools', 'admin-tools' );
			wp_enqueue_script( 'woocommerce-box-office-admin-tools' );
			wp_localize_script( 'woocommerce-box-office-admin-tools', 'wcBoxOfficeParams', $exported_js );
		}
	}

	/**
	 * Load admin CSS.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles( $hook = '' ) {
		wp_register_style( 'woocommerce-box-office-admin-post-type-product', WCBO()->assets_url . 'admin-post-type-product.css', array(), WCBO()->_version );
		wp_register_style( 'woocommerce-box-office-admin-post-type-event-ticket', WCBO()->assets_url . 'admin-post-type-event-ticket.css', array(), WCBO()->_version );
		wp_register_style( 'woocommerce-box-office-admin-post-type-event-ticket-email', WCBO()->assets_url . 'admin-post-type-event-ticket-email.css', array(), WCBO()->_version );
		wp_register_style( 'woocommerce-box-office-admin-tools', WCBO()->assets_url . 'admin-tools.css', array(), WCBO()->_version );
		wp_register_style( 'woocommerce-box-office-multiple-tickets', WCBO()->assets_url . 'multiple-tickets.css', array(), WCBO()->_version );

		$post_type = get_post_type();
		switch ( $post_type ) {
			case 'product':
				wp_enqueue_style( 'woocommerce-box-office-admin-post-type-product' );
				break;
			case 'event_ticket':
				wp_enqueue_style( 'woocommerce-box-office-admin-post-type-event-ticket' );
				break;
			case 'event_ticket_email':
				wp_enqueue_style( 'woocommerce-box-office-admin-post-type-event-ticket-email' );
				break;
		}

		$screen = get_current_screen();
		switch ( $screen->id ) {
			case 'event_ticket_page_ticket_tools':
				wp_enqueue_style( 'woocommerce-box-office-admin-tools' );
				break;
			case 'event_ticket_page_create_ticket':
				wp_enqueue_style( 'woocommerce-box-office-multiple-tickets' );
				break;
		}
	}
}
