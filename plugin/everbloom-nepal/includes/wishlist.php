<?php
/**
 * Lightweight wishlist stored as user meta (array of product IDs) — ported from
 * the original Wishlist model + shop:wishlist_toggle view.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const EVERBLOOM_WISHLIST_META_KEY = 'eb_wishlist';

function everbloom_get_wishlist_ids( $user_id ) {
	if ( ! $user_id ) {
		return array();
	}
	$ids = get_user_meta( $user_id, EVERBLOOM_WISHLIST_META_KEY, true );
	if ( ! is_array( $ids ) ) {
		return array();
	}
	return array_map( 'absint', $ids );
}

function everbloom_is_in_wishlist( $product_id, $user_id = null ) {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return false;
	}
	return in_array( (int) $product_id, everbloom_get_wishlist_ids( $user_id ), true );
}

function everbloom_ajax_wishlist_toggle() {
	check_ajax_referer( 'everbloom-nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json( array(
			'success'    => false,
			'login_required' => true,
			'login_url'  => wc_get_page_permalink( 'myaccount' ),
		), 401 );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id || ! wc_get_product( $product_id ) ) {
		wp_send_json( array( 'success' => false ), 404 );
	}

	$user_id = get_current_user_id();
	$ids     = everbloom_get_wishlist_ids( $user_id );

	if ( in_array( $product_id, $ids, true ) ) {
		$ids   = array_values( array_diff( $ids, array( $product_id ) ) );
		$added = false;
	} else {
		$ids[]  = $product_id;
		$added  = true;
	}

	update_user_meta( $user_id, EVERBLOOM_WISHLIST_META_KEY, $ids );

	wp_send_json( array( 'success' => true, 'added' => $added ) );
}
add_action( 'wp_ajax_everbloom_wishlist_toggle', 'everbloom_ajax_wishlist_toggle' );
add_action( 'wp_ajax_nopriv_everbloom_wishlist_toggle', 'everbloom_ajax_wishlist_toggle' );

/**
 * Create the /wishlist/ page (assigned to the "Wishlist Page" template) on activation.
 */
function everbloom_create_wishlist_page() {
	$existing = get_page_by_path( 'wishlist' );
	if ( $existing ) {
		return;
	}
	$page_id = wp_insert_post( array(
		'post_title'   => 'Wishlist',
		'post_name'    => 'wishlist',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '',
	) );
	if ( $page_id ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-wishlist.php' );
	}
}
