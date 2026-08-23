<?php
/**
 * Template Name: Wishlist Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
	exit;
}

get_header();

$product_ids = everbloom_get_wishlist_ids( get_current_user_id() );
$shop_url    = get_permalink( wc_get_page_id( 'shop' ) );
$count       = count( $product_ids );
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
	<h1 class="font-display text-3xl sm:text-4xl font-bold text-ink mb-2">My Wishlist</h1>
	<p class="text-ink-soft mb-10"><?php echo esc_html( $count ); ?> saved item<?php echo 1 === $count ? '' : 's'; ?></p>

	<?php if ( $count > 0 ) : ?>
	<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5 sm:gap-6">
		<?php foreach ( $product_ids as $product_id ) :
			$product = wc_get_product( $product_id );
			if ( ! $product ) { continue; }
			$image_url = wp_get_attachment_image_url( $product->get_image_id(), 'everbloom-product' );
			if ( ! $image_url ) { $image_url = wc_placeholder_img_src(); }
		?>
		<div class="eb-card group relative">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="block product-card-img-wrap">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy">
			</a>
			<button class="js-wishlist-toggle wishlist-btn is-active absolute top-3 right-3" data-product-id="<?php echo esc_attr( $product_id ); ?>" aria-label="Remove from wishlist">
				<i class="fa-solid fa-heart"></i>
			</button>
			<div class="p-4 sm:p-5">
				<?php $terms = get_the_terms( $product_id, 'product_cat' ); ?>
				<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
				<p class="text-xs text-eb-sage font-medium mb-1"><?php echo esc_html( $terms[0]->name ); ?></p>
				<?php endif; ?>
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><h3 class="font-display text-base font-semibold text-ink leading-snug mb-2.5 hover:text-eb-rose-dark transition line-clamp-2"><?php echo esc_html( $product->get_name() ); ?></h3></a>
				<div class="flex items-center justify-between">
					<span class="font-display text-lg font-bold text-ink"><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
					<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
						data-product_id="<?php echo esc_attr( $product_id ); ?>"
						data-quantity="1"
						class="js-quick-add ajax_add_to_cart add_to_cart_button w-10 h-10 rounded-full bg-blush text-eb-rose-dark hover:bg-eb-rose hover:text-white transition flex items-center justify-center"
						aria-label="Add to cart">
						<i class="fa-solid fa-plus"></i>
					</a>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php else : ?>
	<div class="text-center py-20">
		<i class="fa-regular fa-heart text-4xl text-eb-blush-deep mb-4"></i>
		<h3 class="font-display text-xl font-semibold text-ink mb-2">Your wishlist is empty</h3>
		<p class="text-ink-soft mb-6">Save your favourite blooms here for later.</p>
		<a href="<?php echo esc_url( $shop_url ); ?>" class="btn btn-primary btn-sm">Discover Flowers</a>
	</div>
	<?php endif; ?>
</section>
<?php
get_footer();
