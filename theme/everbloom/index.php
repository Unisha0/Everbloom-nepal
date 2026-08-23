<?php
/**
 * Fallback template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'eb-card p-8 mb-6' ); ?>>
			<h1 class="font-display text-2xl font-bold text-ink mb-4"><?php the_title(); ?></h1>
			<div class="text-ink-soft leading-relaxed"><?php the_content(); ?></div>
		</article>
	<?php endwhile; else : ?>
		<p class="text-ink-soft">Nothing found.</p>
	<?php endif; ?>
</section>
<?php
get_footer();
