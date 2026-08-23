<?php
/**
 * Plugin Name: Everbloom Nepal
 * Plugin URI: https://everbloomnepal.com
 * Description: Nepal-specific storefront logic for Everbloom Nepal — checkout fields, delivery pricing, local payment methods, order statuses, wishlist, newsletter and contact form. Requires WooCommerce.
 * Version: 1.0.0
 * Author: Everbloom Nepal
 * Text Domain: everbloom-nepal
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EVERBLOOM_NEPAL_PATH', plugin_dir_path( __FILE__ ) );
define( 'EVERBLOOM_NEPAL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Bail with an admin notice if WooCommerce isn't active.
 */
function everbloom_nepal_missing_woocommerce_notice() {
	echo '<div class="notice notice-error"><p>' .
		esc_html__( 'Everbloom Nepal requires WooCommerce to be installed and active.', 'everbloom-nepal' ) .
		'</p></div>';
}

function everbloom_nepal_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'everbloom_nepal_missing_woocommerce_notice' );
		return;
	}

	require_once EVERBLOOM_NEPAL_PATH . 'includes/notices.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/checkout-fields.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/order-statuses.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/wishlist.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/newsletter.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/contact.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/class-wc-shipping-everbloom.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/shipping.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/class-wc-gateway-esewa.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/class-wc-gateway-khalti.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/class-wc-gateway-imepay.php';
	require_once EVERBLOOM_NEPAL_PATH . 'includes/gateways.php';
}
add_action( 'plugins_loaded', 'everbloom_nepal_init' );

/**
 * Create custom tables (contact messages, newsletter subscribers) on activation.
 */
function everbloom_nepal_activate() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset_collate = $wpdb->get_charset_collate();

	$sql1 = "CREATE TABLE {$wpdb->prefix}eb_contact_messages (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		name VARCHAR(150) NOT NULL,
		email VARCHAR(254) NOT NULL,
		subject VARCHAR(200) NOT NULL,
		message TEXT NOT NULL,
		is_read TINYINT(1) NOT NULL DEFAULT 0,
		created_at DATETIME NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
	dbDelta( $sql1 );

	$sql2 = "CREATE TABLE {$wpdb->prefix}eb_newsletter_subscribers (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		email VARCHAR(254) NOT NULL,
		subscribed_at DATETIME NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email)
	) $charset_collate;";
	dbDelta( $sql2 );

	if ( function_exists( 'everbloom_create_wishlist_page' ) ) {
		everbloom_create_wishlist_page();
	}
}
register_activation_hook( __FILE__, 'everbloom_nepal_activate' );
