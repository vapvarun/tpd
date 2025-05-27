<?php
/**
 * Plugin Name: WooCommerce Box Office
 * Requires Plugins: woocommerce
 * Version: 1.3.3
 * Plugin URI: https://woocommerce.com/products/woocommerce-box-office/
 * Description: The ultimate event ticket management system, built right on top of WooCommerce.
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * License: GPL-2.0+
 * Text Domain: woocommerce-box-office
 * Domain Path: /languages
 * Requires at least: 6.6
 * Tested up to: 6.7
 * WC requires at least: 9.6
 * WC tested up to: 9.8
 * Requires PHP: 7.4
 * PHP tested up to: 8.3
 *
 * Woo: 1628717:e704c9160de318216a8fa657404b9131
 *
 * Copyright: © 2023 WooCommerce
 *
 * @package woocommerce-box-office
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WOOCOMMERCE_BOX_OFFICE_VERSION', '1.3.3' ); // WRCS: DEFINED_VERSION.

// Plugin init hook.
add_action( 'plugins_loaded', 'wc_box_office_init', 5 );

/**
 * Initialize plugin.
 */
function wc_box_office_init() {

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wc_box_office_woocommerce_deactivated' );
		return;
	}

	// Load main plugin class.
	require_once 'includes/class-wc-box-office.php';
	require_once 'includes/wcbo-functions.php';
	WCBO()->init();
}

// Plugin activation.
register_activation_hook( __FILE__, 'wc_box_office_maybe_install' );

/**
 * Plugin update.
 */
function wc_box_office_maybe_install() {
	require_once 'includes/class-wc-box-office.php';
	require_once 'includes/class-wc-box-office-updater.php';
	require_once 'includes/wcbo-functions.php';

	$updater = new WC_Box_Office_Updater();
	$updater->install();
}

/**
 * WooCommerce Deactivated Notice.
 */
function wc_box_office_woocommerce_deactivated() {
	/* translators: %s: WooCommerce link */
	echo '<div class="error"><p>' . sprintf( esc_html__( 'WooCommerce Box Office requires %s to be installed and active.', 'woocommerce-box-office' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</p></div>';
}

