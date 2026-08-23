<?php
/**
 * IME Pay — manual/offline payment gateway (see class-wc-gateway-esewa.php for notes).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Gateway_Imepay extends WC_Payment_Gateway {

	public function __construct() {
		$this->id                 = 'imepay';
		$this->icon               = '';
		$this->has_fields         = false;
		$this->method_title       = __( 'IME Pay', 'everbloom-nepal' );
		$this->method_description = __( 'Accept manual IME Pay payments. This is a demo store — no real payment is charged; customers receive payment instructions after placing the order.', 'everbloom-nepal' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'      => array(
				'title'   => __( 'Enable/Disable', 'everbloom-nepal' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable IME Pay payments', 'everbloom-nepal' ),
				'default' => 'yes',
			),
			'title'        => array(
				'title'       => __( 'Title', 'everbloom-nepal' ),
				'type'        => 'text',
				'default'     => __( 'IME Pay', 'everbloom-nepal' ),
			),
			'description'  => array(
				'title'       => __( 'Description', 'everbloom-nepal' ),
				'type'        => 'textarea',
				'default'     => __( 'Pay via IME Pay. This is a demo store — no real payment will be charged. You will receive payment instructions with your order confirmation.', 'everbloom-nepal' ),
			),
			'instructions' => array(
				'title'       => __( 'Instructions', 'everbloom-nepal' ),
				'type'        => 'textarea',
				'default'     => __( 'Please complete your payment via IME Pay using your order number as the reference. Our team will confirm your payment shortly.', 'everbloom-nepal' ),
			),
		);
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->update_status( 'on-hold', __( 'Awaiting IME Pay payment confirmation.', 'everbloom-nepal' ) );
		wc_reduce_stock_levels( $order_id );
		WC()->cart->empty_cart();

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( $this->instructions ) );
		}
	}
}
