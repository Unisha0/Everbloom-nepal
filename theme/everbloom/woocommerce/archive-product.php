<?php
/**
 * Shop / product category archive — mirrors the original shop_list.html.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$current_term   = is_product_category() ? get_queried_object() : null;
$all_categories = everbloom_get_categories( false );
$query          = get_search_query();
$price_min      = isset( $_GET['min_price'] ) ? sanitize_text_field( wp_unslash( $_GET['min_price'] ) ) : '';
$price_max      = isset( $_GET['max_price'] ) ? sanitize_text_field( wp_unslash( $_GET['max_price'] ) ) : '';
$shop_url       = get_permalink( wc_get_page_id( 'shop' ) );
?>

<section class="relative bg-eb-blush/50 py-14 sm:py-16">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<nav class="text-xs text-ink-soft mb-3">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-eb-rose-dark">Home</a> /
			<a href="<?php echo esc_url( $shop_url ); ?>" class="hover:text-eb-rose-dark">Shop</a>
			<?php if ( $current_term ) : ?> / <span class="text-ink"><?php echo esc_html( $current_term->name ); ?></span><?php endif; ?>
		</nav>
		<h1 class="font-display text-3xl sm:text-4xl font-bold text-ink">
			<?php echo $current_term ? esc_html( $current_term->name ) : 'All Flowers'; ?>
		</h1>
		<?php if ( $current_term && $current_term->description ) : ?>
		<p class="text-ink-soft mt-2 max-w-2xl"><?php echo esc_html( $current_term->description ); ?></p>
		<?php else : ?>
		<p class="text-ink-soft mt-2 max-w-2xl">Browse our full collection of fresh flowers, bouquets and gifts — handcrafted and delivered across Nepal.</p>
		<?php endif; ?>
	</div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
	<div class="grid lg:grid-cols-4 gap-10">

		<!-- Sidebar filters -->
		<aside class="lg:col-span-1">
			<div class="lg:sticky lg:top-28 space-y-8">
				<form method="get" id="filter-form" action="<?php echo esc_url( $shop_url ); ?>">
					<?php if ( $query ) : ?><input type="hidden" name="s" value="<?php echo esc_attr( $query ); ?>"><input type="hidden" name="post_type" value="product"><?php endif; ?>

					<div>
						<h3 class="font-display font-semibold text-ink mb-4 text-lg">Categories</h3>
						<ul class="space-y-2.5 text-sm">
							<li>
								<a href="<?php echo esc_url( $shop_url ); ?>" class="flex items-center justify-between <?php echo ! $current_term ? 'text-eb-rose-dark font-semibold' : 'text-ink-soft hover:text-eb-rose-dark'; ?> transition">
									All Flowers
								</a>
							</li>
							<?php foreach ( $all_categories as $cat ) : ?>
							<li>
								<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="flex items-center justify-between <?php echo ( $current_term && $current_term->slug === $cat->slug ) ? 'text-eb-rose-dark font-semibold' : 'text-ink-soft hover:text-eb-rose-dark'; ?> transition">
									<span><i class="<?php echo esc_attr( everbloom_category_icon( $cat->term_id ) ); ?> text-xs w-4"></i> <?php echo esc_html( $cat->name ); ?></span>
									<span class="text-xs text-ink-soft/70"><?php echo esc_html( $cat->count ); ?></span>
								</a>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="border-t border-blush-deep pt-6 mt-6">
						<h3 class="font-display font-semibold text-ink mb-4 text-lg">Price Range (NPR)</h3>
						<div class="flex items-center gap-2">
							<input type="number" name="min_price" value="<?php echo esc_attr( $price_min ); ?>" placeholder="Min" class="form-input text-sm py-2">
							<span class="text-ink-soft">–</span>
							<input type="number" name="max_price" value="<?php echo esc_attr( $price_max ); ?>" placeholder="Max" class="form-input text-sm py-2">
						</div>
					</div>

					<button type="submit" class="btn btn-outline btn-sm btn-block mt-6">Apply Filters</button>
					<?php if ( $price_min || $price_max || $query ) : ?>
					<a href="<?php echo esc_url( $current_term ? get_term_link( $current_term ) : $shop_url ); ?>" class="block text-center text-xs text-ink-soft hover:text-eb-rose-dark mt-3">Clear all filters</a>
					<?php endif; ?>
				</form>

				<div class="border-t border-blush-deep pt-6 rounded-2xl bg-eb-blush/40 p-5 -mx-1">
					<i class="fa-solid fa-headset text-eb-rose-dark text-xl mb-2"></i>
					<h4 class="font-semibold text-ink text-sm mb-1">Need Help Choosing?</h4>
					<p class="text-xs text-ink-soft mb-3">Our florists are happy to help you pick the perfect bouquet.</p>
					<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>" class="text-xs font-semibold text-eb-rose-dark">Contact Us <i class="fa-solid fa-arrow-right ml-1"></i></a>
				</div>
			</div>
		</aside>

		<!-- Product grid -->
		<div class="lg:col-span-3">
			<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
				<p class="text-sm text-ink-soft">
					<?php if ( $query ) : ?>Showing results for "<span class="text-ink font-medium"><?php echo esc_html( $query ); ?></span>" — <?php endif; ?>
					<span class="font-medium text-ink"><?php echo esc_html( wc_get_loop_prop( 'total', 0 ) ); ?></span> product<?php echo 1 === (int) wc_get_loop_prop( 'total', 0 ) ? '' : 's'; ?>
				</p>
				<div class="woocommerce-ordering-wrap">
					<?php woocommerce_catalog_ordering(); ?>
				</div>
			</div>

			<?php if ( woocommerce_product_loop() ) : ?>
			<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-6">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php everbloom_product_card( get_the_ID() ); ?>
				<?php endwhile; ?>
			</div>

			<?php woocommerce_pagination(); ?>

			<?php else : ?>
			<div class="text-center py-20">
				<i class="fa-solid fa-seedling text-4xl text-eb-blush-deep mb-4"></i>
				<h3 class="font-display text-xl font-semibold text-ink mb-2">No flowers found</h3>
				<p class="text-ink-soft mb-6">Try adjusting your filters or search terms.</p>
				<a href="<?php echo esc_url( $shop_url ); ?>" class="btn btn-primary btn-sm">View All Flowers</a>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
get_footer();
