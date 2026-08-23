<?php
/**
 * Ensure a "Nepal" shipping zone exists with the Everbloom Delivery method attached.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function everbloom_ensure_shipping_zone() {
	if ( ! class_exists( 'WC_Shipping_Zone' ) ) {
		return;
	}
	if ( get_option( 'eb_shipping_zone_created' ) ) {
		return;
	}

	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'Nepal' );
	$zone->set_zone_order( 0 );
	$zone->add_location( 'NP', 'country' );
	$zone_id = $zone->save();

	$instance_id = $zone->add_shipping_method( 'everbloom_delivery' );

	if ( $instance_id ) {
		$settings = get_option( "woocommerce_everbloom_delivery_{$instance_id}_settings", array() );
		$settings['title']              = 'Standard Delivery';
		$settings['fee']                = (string) everbloom_standard_delivery_fee_default();
		$settings['free_delivery_over'] = (string) everbloom_free_delivery_threshold_default();
		update_option( "woocommerce_everbloom_delivery_{$instance_id}_settings", $settings );
	}

	update_option( 'eb_shipping_zone_created', 1 );
}
add_action( 'admin_init', 'everbloom_ensure_shipping_zone' );

function everbloom_standard_delivery_fee_default() {
	return 150;
}

function everbloom_free_delivery_threshold_default() {
	return 5000;
}
