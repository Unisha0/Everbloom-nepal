<?php
/**
 * Reusable render helpers (category cards, product cards, star ratings).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a category/occasion card — mirrors the original category_card.html include.
 */
function everbloom_category_card( $term ) {
	if ( ! $term || is_wp_error( $term ) ) {
		return;
	}
	$image_id  = get_term_meta( $term->term_id, 'thumbnail_id', true );
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'everbloom-category' ) : wc_placeholder_img_src();
	$icon      = everbloom_category_icon( $term->term_id );
	$nepali    = everbloom_category_nepali_name( $term->term_id );
	?>
	<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="eb-card group block reveal">
		<div class="relative aspect-[4/5] overflow-hidden">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
			<div class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/10 to-transparent"></div>
			<div class="absolute bottom-0 left-0 right-0 p-5 text-white">
				<i class="<?php echo esc_attr( $icon ); ?> text-gold mb-1.5 block text-lg"></i>
				<h3 class="font-display text-lg sm:text-xl font-semibold"><?php echo esc_html( $term->name ); ?></h3>
				<?php if ( $nepali ) : ?>
					<p class="text-xs text-white/70 mt-0.5"><?php echo esc_html( $nepali ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</a>
	<?php
}

/**
 * Render a star-rating widget (matches .star-rating / .stars-filled CSS).
 */
function everbloom_star_rating( $rating, $size_class = '' ) {
	$rating = max( 0, min( 5, (float) $rating ) );
	$percent = round( ( $rating / 5 ) * 100 );
	?>
	<div class="star-rating <?php echo esc_attr( $size_class ); ?>">
		<i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
		<div class="stars-filled" style="width: <?php echo esc_attr( $percent ); ?>%">
			<i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
		</div>
	</div>
	<?php
}

/**
 * Render a product card — mirrors the original product_card.html include.
 */
function everbloom_product_card( $product ) {
	$product = wc_get_product( $product );
	if ( ! $product ) {
		return;
	}
	$id             = $product->get_id();
	$is_bestseller  = get_post_meta( $id, '_eb_is_bestseller', true );
	$is_new         = get_post_meta( $id, '_eb_is_new', true );
	$is_on_sale     = $product->is_on_sale();
	$discount       = 0;
	if ( $is_on_sale && (float) $product->get_regular_price() > 0 ) {
		$discount = round( ( 1 - ( (float) $product->get_sale_price() / (float) $product->get_regular_price() ) ) * 100 );
	}
	$in_wishlist = everbloom_is_in_wishlist( $id );
	$image_url   = wp_get_attachment_image_url( $product->get_image_id(), 'everbloom-product' );
	if ( ! $image_url ) {
		$image_url = wc_placeholder_img_src();
	}
	?>
	<div class="eb-card group relative">
		<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="block product-card-img-wrap">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy">
			<div class="absolute top-3 left-3 flex flex-col gap-1.5">
				<?php if ( $is_bestseller ) : ?><span class="badge badge-bestseller"><i class="fa-solid fa-fire text-[0.6rem]"></i> Bestseller</span><?php endif; ?>
				<?php if ( $is_new ) : ?><span class="badge badge-new">New</span><?php endif; ?>
				<?php if ( $discount > 0 ) : ?><span class="badge badge-sale">-<?php echo esc_html( $discount ); ?>%</span><?php endif; ?>
			</div>
		</a>
		<button
			class="js-wishlist-toggle wishlist-btn absolute top-3 right-3 <?php echo $in_wishlist ? 'is-active' : ''; ?>"
			data-product-id="<?php echo esc_attr( $id ); ?>" aria-label="Add to wishlist">
			<i class="<?php echo $in_wishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
		</button>

		<div class="p-4 sm:p-5">
			<?php $terms = get_the_terms( $id, 'product_cat' ); ?>
			<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
				<p class="text-xs text-eb-sage font-medium mb-1"><?php echo esc_html( $terms[0]->name ); ?></p>
			<?php endif; ?>
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<h3 class="font-display text-base sm:text-lg font-semibold text-ink leading-snug mb-1.5 hover:text-eb-rose-dark transition line-clamp-2"><?php echo esc_html( $product->get_name() ); ?></h3>
			</a>
			<div class="flex items-center gap-1.5 mb-2.5">
				<?php everbloom_star_rating( $product->get_average_rating() ); ?>
				<span class="text-xs text-eb-ink-soft">(<?php echo esc_html( $product->get_review_count() ); ?>)</span>
			</div>
			<div class="flex items-center justify-between">
				<div>
					<span class="font-display text-lg font-bold text-ink"><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
					<?php if ( $is_on_sale ) : ?>
						<span class="text-xs text-ink-soft line-through ml-1"><?php echo wp_kses_post( wc_price( $product->get_regular_price() ) ); ?></span>
					<?php endif; ?>
				</div>
				<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
					data-product_id="<?php echo esc_attr( $id ); ?>"
					data-quantity="1"
					class="js-quick-add ajax_add_to_cart add_to_cart_button w-10 h-10 rounded-full bg-blush text-eb-rose-dark hover:bg-eb-rose hover:text-white transition flex items-center justify-center"
					aria-label="Add to cart">
					<i class="fa-solid fa-plus"></i>
				</a>
			</div>
		</div>
	</div>
	<?php
}
