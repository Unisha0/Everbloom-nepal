<?php
/**
 * Everbloom Delivery — flat delivery fee, free above a threshold.
 * Ported from the original Cart.delivery_fee property (shop/cart.py).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Shipping_Everbloom extends WC_Shipping_Method {

	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'everbloom_delivery';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Everbloom Delivery', 'everbloom-nepal' );
		$this->method_description = __( 'Flat delivery fee that becomes free above the free-delivery threshold.', 'everbloom-nepal' );
		$this->supports           = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal' );

		$this->init();
	}

	public function init() {
		$this->init_form_fields();
		$this->init_settings();

		$this->title             = $this->get_option( 'title', __( 'Standard Delivery', 'everbloom-nepal' ) );
		$this->fee                = $this->get_option( 'fee', '150' );
		$this->free_delivery_over = $this->get_option( 'free_delivery_over', '5000' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function init_form_fields() {
		$this->instance_form_fields = array(
			'title'               => array(
				'title'       => __( 'Title', 'everbloom-nepal' ),
				'type'        => 'text',
				'default'     => __( 'Standard Delivery', 'everbloom-nepal' ),
			),
			'fee'                 => array(
				'title'       => __( 'Delivery Fee (NPR)', 'everbloom-nepal' ),
				'type'        => 'number',
				'default'     => '150',
			),
			'free_delivery_over'  => array(
				'title'       => __( 'Free Delivery Threshold (NPR)', 'everbloom-nepal' ),
				'type'        => 'number',
				'default'     => '5000',
				'description' => __( 'Orders at or above this subtotal get free delivery. Set to 0 to disable.', 'everbloom-nepal' ),
			),
		);
	}

	public function calculate_shipping( $package = array() ) {
		$subtotal = 0;
		foreach ( $package['contents'] as $item ) {
			$subtotal += (float) $item['line_total'];
		}

		$free_over = (float) $this->free_delivery_over;
		$fee       = ( $free_over > 0 && $subtotal >= $free_over ) ? 0 : (float) $this->fee;

		$this->add_rate( array(
			'id'    => $this->get_rate_id(),
			'label' => $this->title,
			'cost'  => $fee,
		) );
	}
}

function everbloom_register_shipping_method( $methods ) {
	$methods['everbloom_delivery'] = 'WC_Shipping_Everbloom';
	return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'everbloom_register_shipping_method' );
