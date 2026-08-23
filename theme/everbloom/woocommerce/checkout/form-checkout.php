<?php
/**
 * Checkout form — restyled as the two-column "Delivery Address" / "Order Summary"
 * layout from the original checkout.html, while keeping WooCommerce's default
 * hooks (billing fields, order review, payment methods, AJAX totals) untouched
 * so address changes, shipping recalculation and payment switching keep working.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
	<h1 class="font-display text-3xl sm:text-4xl font-bold text-ink mb-2">Checkout</h1>
	<p class="text-ink-soft mb-10">Complete your order in just a few steps.</p>

	<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

		<div class="grid lg:grid-cols-3 gap-10">
			<div class="lg:col-span-2 space-y-8">

				<?php if ( $checkout->get_checkout_fields() ) : ?>
					<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

					<div class="eb-card p-6 sm:p-8">
						<h2 class="font-display text-lg font-semibold text-ink mb-5 flex items-center gap-2">
							<span class="w-7 h-7 rounded-full bg-eb-rose text-white text-xs flex items-center justify-center">1</span>
							Delivery Address
						</h2>
						<div id="customer_details">
							<div class="col-1">
								<?php do_action( 'woocommerce_checkout_billing' ); ?>
							</div>
							<div class="col-2">
								<?php do_action( 'woocommerce_checkout_shipping' ); ?>
							</div>
						</div>
					</div>

					<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
				<?php endif; ?>

			</div>

			<!-- Order Summary (items, totals, payment method, place order) -->
			<div class="lg:col-span-1">
				<div class="eb-card p-6 lg:sticky lg:top-28">
					<h3 class="font-display text-lg font-semibold text-ink mb-5">Order Summary</h3>

					<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
					<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

					<div id="order_review" class="woocommerce-checkout-review-order">
						<?php do_action( 'woocommerce_checkout_order_review' ); ?>
					</div>

					<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

					<p class="text-xs text-ink-soft mt-4 flex items-start gap-1.5"><i class="fa-solid fa-shield-halved mt-0.5"></i> This is a demo store — no real payment will be charged. For eSewa / Khalti / IME Pay you'll receive payment instructions with your order confirmation.</p>
				</div>
			</div>
		</div>
	</form>
</section>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
