<?php
/**
 * Custom order statuses (Confirmed, Preparing Bouquet, Out for Delivery) and the
 * "EB" + 2 letters + 4 digits order number format — ported from the original
 * Order.STATUS_CHOICES and generate_order_number() (shop/models.py).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function everbloom_register_order_statuses() {
	register_post_status( 'wc-confirmed', array(
		'label'                     => _x( 'Confirmed', 'Order status', 'everbloom-nepal' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s: number of orders */
		'label_count'               => _n_noop( 'Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', 'everbloom-nepal' ),
	) );

	register_post_status( 'wc-preparing', array(
		'label'                     => _x( 'Preparing Bouquet', 'Order status', 'everbloom-nepal' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Preparing Bouquet <span class="count">(%s)</span>', 'Preparing Bouquet <span class="count">(%s)</span>', 'everbloom-nepal' ),
	) );

	register_post_status( 'wc-out-for-delivery', array(
		'label'                     => _x( 'Out for Delivery', 'Order status', 'everbloom-nepal' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Out for Delivery <span class="count">(%s)</span>', 'Out for Delivery <span class="count">(%s)</span>', 'everbloom-nepal' ),
	) );
}
add_action( 'init', 'everbloom_register_order_statuses' );

function everbloom_add_order_statuses( $statuses ) {
	$new_statuses = array();
	foreach ( $statuses as $key => $label ) {
		$new_statuses[ $key ] = $label;
		if ( 'wc-processing' === $key ) {
			$new_statuses['wc-confirmed']         = _x( 'Confirmed', 'Order status', 'everbloom-nepal' );
			$new_statuses['wc-preparing']         = _x( 'Preparing Bouquet', 'Order status', 'everbloom-nepal' );
			$new_statuses['wc-out-for-delivery']  = _x( 'Out for Delivery', 'Order status', 'everbloom-nepal' );
		}
	}
	return $new_statuses;
}
add_filter( 'wc_order_statuses', 'everbloom_add_order_statuses' );

/**
 * Treat the custom statuses as "paid"/valid statuses where appropriate so
 * reports and "My Account > Orders" behave sensibly.
 */
function everbloom_valid_order_statuses( $statuses ) {
	$statuses[] = 'confirmed';
	$statuses[] = 'preparing';
	$statuses[] = 'out-for-delivery';
	return $statuses;
}
add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', 'everbloom_valid_order_statuses' );

/**
 * Custom order number: "EB" + 2 letters + 4 digits, generated once and stored
 * as order meta so it stays stable — mirrors generate_order_number().
 */
function everbloom_generate_order_number() {
	$letters = '';
	for ( $i = 0; $i < 2; $i++ ) {
		$letters .= chr( wp_rand( 65, 90 ) );
	}
	$digits = str_pad( (string) wp_rand( 0, 9999 ), 4, '0', STR_PAD_LEFT );
	return 'EB' . $letters . $digits;
}

function everbloom_assign_order_number( $order_id, $order ) {
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}
	if ( ! $order || $order->get_meta( '_eb_order_number' ) ) {
		return;
	}
	global $wpdb;
	do {
		$number = everbloom_generate_order_number();
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_eb_order_number' AND meta_value = %s",
			$number
		) );
	} while ( $exists );

	$order->update_meta_data( '_eb_order_number', $number );
	$order->save();
}
add_action( 'woocommerce_checkout_order_processed', 'everbloom_assign_order_number', 10, 2 );

function everbloom_filter_order_number( $order_number, $order ) {
	$custom = $order->get_meta( '_eb_order_number' );
	return $custom ? $custom : $order_number;
}
add_filter( 'woocommerce_order_number', 'everbloom_filter_order_number', 10, 2 );
