<?php
/**
 * Register the custom gateways and order them like the original PAYMENT_CHOICES:
 * COD, eSewa, Khalti, IME Pay, Bank Transfer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function everbloom_register_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_Esewa';
	$gateways[] = 'WC_Gateway_Khalti';
	$gateways[] = 'WC_Gateway_Imepay';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'everbloom_register_gateways' );

function everbloom_order_gateways( $order ) {
	return array( 'cod', 'esewa', 'khalti', 'imepay', 'bacs' );
}
add_filter( 'woocommerce_gateway_order', 'everbloom_order_gateways' );

/**
 * Sensible defaults on plugin activation: enable COD + BACS with brand-matching copy.
 */
function everbloom_enable_default_gateways() {
	if ( get_option( 'eb_gateways_configured' ) ) {
		return;
	}

	$cod = get_option( 'woocommerce_cod_settings', array() );
	$cod['enabled'] = 'yes';
	$cod['title']   = 'Cash on Delivery';
	update_option( 'woocommerce_cod_settings', $cod );

	$bacs = get_option( 'woocommerce_bacs_settings', array() );
	$bacs['enabled'] = 'yes';
	$bacs['title']   = 'Bank Transfer';
	update_option( 'woocommerce_bacs_settings', $bacs );

	update_option( 'eb_gateways_configured', 1 );
}
add_action( 'admin_init', 'everbloom_enable_default_gateways' );
