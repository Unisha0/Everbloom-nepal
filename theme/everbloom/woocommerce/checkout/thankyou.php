<?php
/**
 * Thank-you page — mirrors the original order_confirmation.html.
 *
 * @var WC_Order $order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 text-center">
	<?php if ( $order && ! $order->has_status( 'failed' ) ) :
		do_action( 'woocommerce_before_thankyou', $order->get_id() );
		$first_name = $order->get_billing_first_name();
	?>
	<div class="w-20 h-20 rounded-full bg-sage-light flex items-center justify-center mx-auto mb-6">
		<i class="fa-solid fa-check text-eb-sage text-3xl"></i>
	</div>
	<h1 class="font-display text-3xl sm:text-4xl font-bold text-ink mb-3">Thank You<?php echo $first_name ? ', ' . esc_html( $first_name ) : ''; ?>!</h1>
	<p class="text-ink-soft mb-1">Your order has been placed successfully.</p>
	<p class="text-ink-soft mb-8">Order Number: <span class="font-semibold text-ink"><?php echo esc_html( $order->get_order_number() ); ?></span></p>

	<div class="eb-card p-6 sm:p-8 text-left">
		<div class="flex flex-wrap justify-between gap-4 pb-5 mb-5 border-b border-blush-deep">
			<div>
				<p class="text-xs text-ink-soft uppercase tracking-wide mb-1">Delivery Address</p>
				<p class="text-sm text-ink font-medium"><?php echo esc_html( $order->get_billing_address_1() . ', ' . $order->get_billing_city() ); ?></p>
				<p class="text-sm text-ink-soft"><?php echo esc_html( WC()->countries->get_states( 'NP' )[ $order->get_billing_state() ] ?? $order->get_billing_state() ); ?></p>
				<?php if ( $order->get_billing_address_2() ) : ?><p class="text-sm text-ink-soft">Landmark: <?php echo esc_html( $order->get_billing_address_2() ); ?></p><?php endif; ?>
				<p class="text-sm text-ink-soft mt-1"><i class="fa-solid fa-phone text-xs mr-1"></i><?php echo esc_html( $order->get_billing_phone() ); ?></p>
			</div>
			<div class="sm:text-right">
				<p class="text-xs text-ink-soft uppercase tracking-wide mb-1">Payment Method</p>
				<p class="text-sm text-ink font-medium"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></p>
				<p class="text-xs mt-1 <?php echo $order->is_paid() ? 'text-eb-sage' : 'text-eb-gold'; ?> font-semibold uppercase"><?php echo $order->is_paid() ? 'Paid' : 'Pending'; ?></p>
				<?php $delivery_date = $order->get_meta( '_delivery_date' ); ?>
				<?php if ( $delivery_date ) : ?><p class="text-xs text-ink-soft mt-2">Requested: <?php echo esc_html( date_i18n( 'M d, Y', strtotime( $delivery_date ) ) ); ?></p><?php endif; ?>
			</div>
		</div>

		<div class="space-y-4 mb-5">
			<?php foreach ( $order->get_items() as $item ) :
				$product = $item->get_product();
			?>
			<div class="flex gap-3 items-center">
				<div class="relative w-14 h-14 rounded-lg overflow-hidden bg-eb-blush flex-shrink-0">
					<?php echo $product ? wp_kses_post( $product->get_image( 'thumbnail' ) ) : ''; ?>
					<span class="absolute -top-1.5 -right-1.5 bg-eb-rose text-white text-[0.65rem] w-5 h-5 rounded-full flex items-center justify-center font-semibold"><?php echo esc_html( $item->get_quantity() ); ?></span>
				</div>
				<div class="flex-1 min-w-0">
					<p class="text-sm text-ink font-medium truncate"><?php echo esc_html( $item->get_name() ); ?></p>
				</div>
				<p class="text-sm font-semibold text-ink"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></p>
			</div>
			<?php endforeach; ?>
		</div>

		<div class="border-t border-blush-deep pt-4 space-y-2 text-sm">
			<div class="flex justify-between text-ink-soft"><span>Subtotal</span><span class="text-ink font-medium"><?php echo wp_kses_post( wc_price( $order->get_subtotal() ) ); ?></span></div>
			<div class="flex justify-between text-ink-soft"><span>Delivery Fee</span><span class="text-ink font-medium"><?php echo 0 == $order->get_shipping_total() ? 'FREE' : wp_kses_post( wc_price( $order->get_shipping_total() ) ); ?></span></div>
			<div class="flex justify-between items-baseline pt-2">
				<span class="font-display font-semibold text-ink">Total</span>
				<span class="font-display text-xl font-bold text-ink"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
			</div>
		</div>
	</div>

	<?php if ( 'cod' !== $order->get_payment_method() ) : ?>
	<div class="eb-card p-5 mt-6 text-left flex items-start gap-3 bg-eb-blush/40">
		<i class="fa-solid fa-circle-info text-eb-rose-dark mt-0.5"></i>
		<p class="text-sm text-ink-soft">Please complete your payment via <strong class="text-ink"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong> using order number <strong class="text-ink"><?php echo esc_html( $order->get_order_number() ); ?></strong> as your reference. Our team will confirm your payment shortly.</p>
	</div>
	<?php endif; ?>

	<div class="flex flex-wrap justify-center gap-4 mt-10">
		<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="btn btn-outline">View Order Details</a>
		<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn btn-primary">Continue Shopping</a>
	</div>

	<?php
		do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
		do_action( 'woocommerce_thankyou', $order->get_id() );
	elseif ( $order && $order->has_status( 'failed' ) ) :
	?>
	<div class="eb-card p-8 text-left">
		<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.</p>
		<p class="mt-4">
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="btn btn-primary">Pay</a>
			<?php if ( is_user_logged_in() ) : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn btn-outline">My account</a>
			<?php endif; ?>
		</p>
	</div>
	<?php else : ?>
	<p class="text-ink-soft">Order not found.</p>
	<?php endif; ?>
</section>
