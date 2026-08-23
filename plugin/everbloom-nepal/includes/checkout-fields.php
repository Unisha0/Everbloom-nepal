<?php
/**
 * Nepal-specific checkout: provinces (as WooCommerce states), districts, delivery
 * address fields and order-level delivery date/notes — ported from the original
 * Order model (province/district/city/street_address/landmark/delivery_date/delivery_notes).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function everbloom_districts() {
	return array(
		'Kathmandu', 'Lalitpur', 'Bhaktapur', 'Kavrepalanchok', 'Chitwan',
		'Pokhara / Kaski', 'Makwanpur', 'Morang', 'Sunsari', 'Jhapa',
		'Rupandehi', 'Kailali', 'Banke', 'Dang', 'Dhading', 'Nuwakot',
		'Kaski', 'Syangja', 'Palpa', 'Parsa', 'Bara', 'Rautahat', 'Other',
	);
}

/**
 * Register Nepal's 7 provinces as WooCommerce "states" for NP, so they render
 * as a native select for billing_state / shipping_state.
 */
function everbloom_register_np_states( $states ) {
	$states['NP'] = array(
		'Koshi'         => 'Koshi Province',
		'Madhesh'       => 'Madhesh Province',
		'Bagmati'       => 'Bagmati Province',
		'Gandaki'       => 'Gandaki Province',
		'Lumbini'       => 'Lumbini Province',
		'Karnali'       => 'Karnali Province',
		'Sudurpashchim' => 'Sudurpashchim Province',
	);
	return $states;
}
add_filter( 'woocommerce_states', 'everbloom_register_np_states' );

/**
 * Restrict the store to Nepal only (this is a Nepal-only florist).
 */
function everbloom_restrict_to_nepal() {
	update_option( 'woocommerce_allowed_countries', 'specific' );
	update_option( 'woocommerce_specific_allowed_countries', array( 'NP' ) );
	update_option( 'woocommerce_default_country', 'NP:Bagmati' );
	update_option( 'woocommerce_ship_to_countries', '' );
}

/**
 * Customise the billing fields: relabel address_1/city, drop postcode/company,
 * and add a "District" select — mirrors CheckoutForm in the original Django app.
 */
function everbloom_customize_billing_fields( $fields ) {
	unset( $fields['billing_company'] );
	unset( $fields['billing_postcode'] );

	$fields['billing_address_1']['label']       = __( 'Street Address / Tole', 'everbloom-nepal' );
	$fields['billing_address_1']['placeholder'] = __( 'House no., street, tole', 'everbloom-nepal' );
	$fields['billing_address_2']['label']       = __( 'Landmark (optional)', 'everbloom-nepal' );
	$fields['billing_address_2']['placeholder'] = __( 'Nearby landmark (optional)', 'everbloom-nepal' );
	$fields['billing_address_2']['required']    = false;
	$fields['billing_city']['label']            = __( 'City / Area', 'everbloom-nepal' );
	$fields['billing_city']['placeholder']      = __( 'e.g. Baneshwor, New Road', 'everbloom-nepal' );
	$fields['billing_state']['label']           = __( 'Province', 'everbloom-nepal' );
	$fields['billing_phone']['placeholder']     = '98XXXXXXXX';

	$district_choices = array( '' => __( 'Select a district&hellip;', 'everbloom-nepal' ) );
	foreach ( everbloom_districts() as $district ) {
		$district_choices[ $district ] = $district;
	}

	$fields['billing_district'] = array(
		'label'    => __( 'District', 'everbloom-nepal' ),
		'type'     => 'select',
		'options'  => $district_choices,
		'required' => true,
		'class'    => array( 'form-row-wide' ),
		'priority' => 65,
	);

	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'everbloom_customize_billing_fields' );

/**
 * Add order-level "Preferred Delivery Date" and "Delivery Notes / Card Message" fields.
 */
add_filter( 'woocommerce_checkout_fields', 'everbloom_add_order_field_to_checkout' );
function everbloom_add_order_field_to_checkout( $fields ) {
	$fields['order']['delivery_date'] = array(
		'type'     => 'date',
		'label'    => __( 'Preferred Delivery Date', 'everbloom-nepal' ),
		'required' => false,
		'class'    => array( 'form-row-wide', 'notes' ),
		'priority' => 5,
	);
	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = __( 'Delivery Notes / Card Message (optional)', 'everbloom-nepal' );
		$fields['order']['order_comments']['placeholder'] = __( 'Any special instructions for delivery or the message card...', 'everbloom-nepal' );
	}
	return $fields;
}

/**
 * Persist the delivery date onto the order.
 */
function everbloom_save_delivery_date( $order, $data ) {
	if ( ! empty( $_POST['delivery_date'] ) ) {
		$order->update_meta_data( '_delivery_date', sanitize_text_field( wp_unslash( $_POST['delivery_date'] ) ) );
	}
}
add_action( 'woocommerce_checkout_create_order', 'everbloom_save_delivery_date', 10, 2 );

/**
 * Show the delivery date on the order-received page, order details, emails and admin.
 */
function everbloom_display_delivery_date( $order ) {
	$date = $order->get_meta( '_delivery_date' );
	if ( ! $date ) {
		return;
	}
	echo '<p><strong>' . esc_html__( 'Requested Delivery Date:', 'everbloom-nepal' ) . '</strong> ' .
		esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ) . '</p>';
}
add_action( 'woocommerce_order_details_after_order_table', 'everbloom_display_delivery_date' );
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'everbloom_display_delivery_date' );

function everbloom_display_delivery_date_email( $order, $sent_to_admin, $plain_text, $email ) {
	everbloom_display_delivery_date( $order );
}
add_action( 'woocommerce_email_after_order_table', 'everbloom_display_delivery_date_email', 10, 4 );
