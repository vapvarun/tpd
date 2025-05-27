<?php
/**
 * Box Office Email Preview Class
 *
 * @package woocommerce-box-office
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Box Office Email Preview Class
 */
class WC_Box_Office_Email_Preview {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'woocommerce_prepare_email_for_preview', array( $this, 'prepare_email_for_preview' ) );
	}

	/**
	 * Prepare email for preview
	 *
	 * @param WC_Email $email Email object.
	 * @return WC_Email
	 */
	public function prepare_email_for_preview( $email ) {
		if ( 'WC_Box_Office_Email' !== get_class( $email ) ) {
			return $email;
		}

		$email->message = $this->get_email_preview_content();

		return $email;
	}

	/**
	 * Get email preview content for the default email
	 *
	 * @return string
	 */
	public function get_email_preview_content() {
		// Get default email content.
		$content  = wpautop( wc_get_template_html( 'ticket/default-email-content.php', array(), 'woocommerce-box-office', WCBO()->dir . 'templates/' ) );
		$page_id  = absint( get_option( 'box_office_my_ticket_page_id' ) );
		$page_url = $page_id ? get_permalink( $page_id ) : get_site_url();

		// Add a dummy token to the URL for previewing.
		$url = add_query_arg( 'token', '25f9e794323b453885f5181f1b624d0b', $page_url );

		/**
		 * Hook to filter the ticket URL.
		 *
		 * @since 1.1.0
		 *
		 * @param string $url URL.
		 */
		$ticket_url = esc_url( apply_filters( 'woocommerce_box_office_my_ticket_url', $url ) );

		// Replace placeholders in content.
		$content = str_replace( '{ticket_link}', $ticket_url, $content );
		$content = str_replace( '{ticket_id}', '12345', $content );

		return $content;
	}
}
