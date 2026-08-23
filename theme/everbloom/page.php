<?php
/**
 * Generic page template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// WooCommerce shortcode pages (Cart, Checkout, My Account) render their own
// full-width sections — don't constrain them in the narrow article wrapper
// used for plain content pages.
$is_wc_page = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );

if ( $is_wc_page ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
else :
	?>
	<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
		<?php while ( have_posts() ) : the_post(); ?>
			<h1 class="font-display text-3xl sm:text-4xl font-bold text-ink mb-6"><?php the_title(); ?></h1>
			<div class="text-ink-soft leading-relaxed prose max-w-none"><?php the_content(); ?></div>
		<?php endwhile; ?>
	</section>
	<?php
endif;

get_footer();
