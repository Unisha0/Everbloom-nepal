<?php
/**
 * Single product page — mirrors the original product_detail.html.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	global $product;
	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) {
		continue;
	}

	$id           = $product->get_id();
	$in_wishlist  = everbloom_is_in_wishlist( $id );
	$terms        = get_the_terms( $id, 'product_cat' );
	$category     = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	$stem_count   = get_post_meta( $id, '_eb_stem_count', true );
	$vase_included = get_post_meta( $id, '_eb_vase_included', true );

	$image_ids = array_merge(
		array( $product->get_image_id() ),
		$product->get_gallery_image_ids()
	);
	$image_ids = array_filter( $image_ids );
	?>
	<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8">
		<?php woocommerce_output_all_notices(); ?>
		<nav class="text-xs text-ink-soft mb-6">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-eb-rose-dark">Home</a> /
			<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="hover:text-eb-rose-dark">Shop</a> /
			<?php if ( $category ) : ?>
			<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="hover:text-eb-rose-dark"><?php echo esc_html( $category->name ); ?></a> /
			<?php endif; ?>
			<span class="text-ink"><?php echo esc_html( $product->get_name() ); ?></span>
		</nav>

		<div class="grid lg:grid-cols-2 gap-10 lg:gap-16 pb-16">
			<!-- Gallery -->
			<div>
				<div class="rounded-3xl overflow-hidden aspect-square bg-eb-blush mb-4">
					<img id="product-main-image" src="<?php echo esc_url( wp_get_attachment_image_url( reset( $image_ids ), 'everbloom-product' ) ?: wc_placeholder_img_src() ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" class="w-full h-full object-cover">
				</div>
				<?php if ( count( $image_ids ) > 1 ) : ?>
				<div class="flex gap-3">
					<?php foreach ( $image_ids as $i => $img_id ) :
						$full = wp_get_attachment_image_url( $img_id, 'everbloom-product' );
						$thumb = wp_get_attachment_image_url( $img_id, 'thumbnail' );
					?>
					<button type="button" class="js-gallery-thumb w-20 h-20 rounded-xl overflow-hidden border-2 <?php echo 0 === $i ? 'border-eb-rose' : 'border-transparent'; ?>" data-full-image="<?php echo esc_url( $full ); ?>">
						<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $product->get_name() . ' view ' . ( $i + 1 ) ); ?>" class="w-full h-full object-cover">
					</button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>

			<!-- Info -->
			<div>
				<?php if ( $category ) : ?>
				<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="text-xs font-semibold text-eb-sage uppercase tracking-wider"><?php echo esc_html( $category->name ); ?></a>
				<?php endif; ?>
				<h1 class="font-display text-3xl sm:text-4xl font-bold text-ink mt-2 mb-3"><?php echo esc_html( $product->get_name() ); ?></h1>

				<div class="flex items-center gap-3 mb-5">
					<?php everbloom_star_rating( $product->get_average_rating(), 'text-base' ); ?>
					<span class="text-sm text-ink-soft"><?php echo esc_html( $product->get_average_rating() ); ?> (<?php echo esc_html( $product->get_review_count() ); ?> review<?php echo 1 === (int) $product->get_review_count() ? '' : 's'; ?>)</span>
				</div>

				<div class="flex items-baseline gap-3 mb-6">
					<span class="font-display text-3xl font-bold text-ink"><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
					<?php if ( $product->is_on_sale() ) :
						$regular = (float) $product->get_regular_price();
						$sale    = (float) $product->get_sale_price();
						$discount = $regular > 0 ? round( ( 1 - ( $sale / $regular ) ) * 100 ) : 0;
					?>
					<span class="text-lg text-ink-soft line-through"><?php echo wp_kses_post( wc_price( $regular ) ); ?></span>
					<span class="badge badge-sale">Save <?php echo esc_html( $discount ); ?>%</span>
					<?php endif; ?>
				</div>

				<p class="text-ink-soft leading-relaxed mb-6"><?php echo wp_kses_post( wpautop( $product->get_description() ) ); ?></p>

				<div class="grid grid-cols-2 gap-4 mb-8 text-sm">
					<?php if ( $stem_count ) : ?>
					<div class="flex items-center gap-2 text-ink-soft"><i class="fa-solid fa-leaf text-eb-rose w-4"></i> <?php echo esc_html( $stem_count ); ?></div>
					<?php endif; ?>
					<div class="flex items-center gap-2 text-ink-soft"><i class="fa-solid fa-vase w-4 text-eb-rose"></i> <?php echo $vase_included ? 'Vase included' : 'Vase not included'; ?></div>
					<div class="flex items-center gap-2 text-ink-soft"><i class="fa-solid fa-truck w-4 text-eb-rose"></i> Same-day delivery available</div>
					<div class="flex items-center gap-2 text-ink-soft">
						<?php if ( $product->is_in_stock() ) : ?>
						<i class="fa-solid fa-circle-check w-4 text-eb-sage"></i> In stock
						<?php else : ?>
						<i class="fa-solid fa-circle-xmark w-4 text-red-400"></i> Out of stock
						<?php endif; ?>
					</div>
				</div>

				<div class="flex flex-wrap items-center gap-4 mb-6">
					<div class="flex-1 sm:flex-none">
						<?php woocommerce_template_single_add_to_cart(); ?>
					</div>
					<button type="button" class="js-wishlist-toggle wishlist-btn <?php echo $in_wishlist ? 'is-active' : ''; ?>" data-product-id="<?php echo esc_attr( $id ); ?>" aria-label="Add to wishlist">
						<i class="<?php echo $in_wishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
					</button>
				</div>

				<div class="rounded-2xl bg-eb-blush/40 p-4 flex items-start gap-3 text-sm text-ink-soft">
					<i class="fa-solid fa-truck-fast text-eb-rose-dark mt-0.5"></i>
					<span>Order early for same-day delivery in Kathmandu Valley. Delivering nationwide within 2–3 days.</span>
				</div>
			</div>
		</div>
	</section>

	<!-- Reviews -->
	<section class="bg-eb-blush/40 py-16">
		<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid md:grid-cols-3 gap-8 mb-4">
				<div class="md:col-span-1 text-center md:text-left">
					<p class="font-display text-5xl font-bold text-ink"><?php echo esc_html( number_format( (float) $product->get_average_rating(), 1 ) ); ?></p>
					<div class="my-2 inline-block"><?php everbloom_star_rating( $product->get_average_rating(), 'text-lg' ); ?></div>
					<p class="text-sm text-ink-soft">Based on <?php echo esc_html( $product->get_review_count() ); ?> review<?php echo 1 === (int) $product->get_review_count() ? '' : 's'; ?></p>
				</div>
				<div class="md:col-span-2">
					<?php comments_template(); ?>
				</div>
			</div>
		</div>
	</section>

	<!-- Related products -->
	<?php
	$related_ids = wc_get_related_products( $id, 4 );
	if ( ! empty( $related_ids ) ) :
	?>
	<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
		<h2 class="font-display text-2xl sm:text-3xl font-bold text-ink mb-10">You May Also Like</h2>
		<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
			<?php foreach ( $related_ids as $related_id ) : everbloom_product_card( $related_id ); endforeach; ?>
		</div>
	</section>
	<?php endif; ?>
<?php
endwhile;

get_footer( 'shop' );
