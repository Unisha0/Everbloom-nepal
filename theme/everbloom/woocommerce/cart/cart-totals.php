<?php
/**
 * Cart totals — restyled as the "Order Summary" card from the original cart.html.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h3 class="font-display text-lg font-semibold text-ink mb-5">Order Summary</h3>

	<div class="space-y-3 text-sm">
		<div class="flex justify-between text-ink-soft">
			<span>Subtotal</span>
			<span class="text-ink font-medium"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="flex justify-between text-ink-soft coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<?php wc_cart_totals_coupon_label( $coupon ); ?>
				<span class="text-ink font-medium"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
			<div class="flex justify-between text-ink-soft">
				<span>Delivery Fee</span>
				<span class="text-ink font-medium"><?php wc_cart_totals_shipping_html(); ?></span>
			</div>
			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="flex justify-between text-ink-soft">
				<span><?php echo esc_html( $fee->name ); ?></span>
				<span class="text-ink font-medium"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<div class="flex justify-between text-ink-soft">
						<span><?php echo esc_html( $tax->label ); ?></span>
						<span class="text-ink font-medium"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="flex justify-between text-ink-soft">
					<span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
					<span class="text-ink font-medium"><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

	<div class="border-t border-blush-deep mt-4 pt-4 flex justify-between items-baseline">
		<span class="font-display font-semibold text-ink">Total</span>
		<span class="font-display text-2xl font-bold text-ink"><?php wc_cart_totals_order_total_html(); ?></span>
	</div>

	<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>
