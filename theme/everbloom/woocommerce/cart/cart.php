<?php
/**
 * Cart page — mirrors the original cart.html card layout while preserving all
 * WooCommerce cart hooks (coupons, shipping calc, cross-sells, AJAX quantity/remove).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_cart' );

$cart_count = WC()->cart->get_cart_contents_count();
$shop_url   = get_permalink( wc_get_page_id( 'shop' ) );
?>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
	<h1 class="font-display text-3xl sm:text-4xl font-bold text-ink mb-2">Your Shopping Cart</h1>
	<p class="text-ink-soft mb-10"><?php echo esc_html( $cart_count ); ?> item<?php echo 1 === $cart_count ? '' : 's'; ?> in your cart</p>

	<?php if ( $cart_count > 0 ) : ?>
	<div class="grid lg:grid-cols-3 gap-10">
		<div class="lg:col-span-2">
			<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
				<?php do_action( 'woocommerce_before_cart_table' ); ?>

				<div class="space-y-4">
					<?php do_action( 'woocommerce_before_cart_contents' ); ?>

					<?php
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
						$visible    = apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key );

						if ( ! ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible ) ) {
							continue;
						}

						$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
						$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
						$terms             = get_the_terms( $product_id, 'product_cat' );
						$stem_count        = get_post_meta( $product_id, '_eb_stem_count', true );
						?>
						<div class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?> eb-card flex flex-col sm:flex-row gap-4 p-4 sm:items-center">
							<a href="<?php echo esc_url( $product_permalink ); ?>" class="w-full sm:w-28 h-40 sm:h-28 rounded-xl overflow-hidden flex-shrink-0 block bg-eb-blush">
								<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'everbloom-product' ), $cart_item, $cart_item_key ) ); ?>
							</a>
							<div class="flex-1">
								<a href="<?php echo esc_url( $product_permalink ); ?>"><h3 class="font-display font-semibold text-ink hover:text-eb-rose-dark transition"><?php echo wp_kses_post( $product_name ); ?></h3></a>
								<p class="text-xs text-ink-soft mt-1">
									<?php echo ( $terms && ! is_wp_error( $terms ) ) ? esc_html( $terms[0]->name ) : ''; ?>
									<?php if ( $stem_count ) : ?> &middot; <?php echo esc_html( $stem_count ); ?><?php endif; ?>
								</p>
								<p class="font-semibold text-ink mt-2"><?php echo wp_kses_post( WC()->cart->get_product_price( $_product ) ); ?></p>
							</div>
							<div class="flex items-center gap-6 sm:gap-8">
								<?php
								echo apply_filters( 'woocommerce_cart_item_quantity', woocommerce_quantity_input( // phpcs:ignore
									array(
										'input_name'   => "cart[{$cart_item_key}][qty]",
										'input_value'  => $cart_item['quantity'],
										'max_value'    => $_product->get_max_purchase_quantity(),
										'min_value'    => 0,
										'product_name' => $product_name,
									),
									$_product,
									false
								), $cart_item_key, $cart_item );
								?>
								<p class="font-display font-bold text-ink w-24 text-right"><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); ?></p>
								<?php
								echo apply_filters( // phpcs:ignore
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a role="button" href="%s" class="remove text-ink-soft hover:text-red-500 transition" aria-label="%s" data-product_id="%s" data-product_sku="%s"><i class="fa-solid fa-trash-can"></i></a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										esc_attr( sprintf( __( 'Remove %s from cart', 'everbloom' ), wp_strip_all_tags( $product_name ) ) ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() )
									),
									$cart_item_key
								);
								?>
							</div>
						</div>
						<?php
					}
					?>

					<?php do_action( 'woocommerce_cart_contents' ); ?>
				</div>

				<div class="flex flex-wrap items-center justify-between gap-4 mt-6">
					<a href="<?php echo esc_url( $shop_url ); ?>" class="inline-flex items-center gap-2 text-sm font-medium text-eb-rose-dark">
						<i class="fa-solid fa-arrow-left"></i> Continue Shopping
					</a>
					<div class="flex items-center gap-3">
						<?php if ( wc_coupons_enabled() ) : ?>
						<input type="text" name="coupon_code" class="form-input text-sm py-2 w-auto" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
						<button type="submit" class="btn btn-outline btn-sm" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
						<?php endif; ?>
						<button type="submit" class="btn btn-outline btn-sm" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>
					</div>
				</div>

				<?php do_action( 'woocommerce_cart_actions' ); ?>
				<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
				<?php do_action( 'woocommerce_after_cart_table' ); ?>
			</form>
		</div>

		<!-- Order summary -->
		<div class="lg:col-span-1">
			<div class="eb-card p-6 lg:sticky lg:top-28">
				<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
				<?php do_action( 'woocommerce_cart_collaterals' ); ?>
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn btn-primary btn-block mt-2">
					Proceed to Checkout <i class="fa-solid fa-arrow-right text-sm"></i>
				</a>
				<div class="flex items-center justify-center gap-3 mt-5 text-ink-soft text-xs">
					<i class="fa-solid fa-lock"></i> Secure checkout &middot; eSewa, Khalti, COD accepted
				</div>
			</div>
		</div>
	</div>

	<?php else : ?>
	<div class="text-center py-24">
		<i class="fa-solid fa-bag-shopping text-5xl text-eb-blush-deep mb-6"></i>
		<h3 class="font-display text-2xl font-semibold text-ink mb-2">Your cart is empty</h3>
		<p class="text-ink-soft mb-8">Looks like you haven't added any blooms yet.</p>
		<a href="<?php echo esc_url( $shop_url ); ?>" class="btn btn-primary">Shop Flowers</a>
	</div>
	<?php endif; ?>
</section>

<?php do_action( 'woocommerce_after_cart' ); ?>
