<?php
/**
 * The footer for the Everbloom Nepal theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footer_categories = everbloom_get_categories( false, 5 );
$about_page   = get_page_by_path( 'about' );
$contact_page = get_page_by_path( 'contact' );
?>
</main>

<!-- Newsletter -->
<section class="bg-ink text-cream">
	<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center reveal">
		<i class="fa-solid fa-envelope-open-text text-gold text-2xl mb-4"></i>
		<h2 class="font-display text-2xl sm:text-3xl font-semibold mb-3">Stay in Bloom</h2>
		<p class="text-cream/70 max-w-lg mx-auto mb-7 text-sm sm:text-base">Subscribe for seasonal collections, festive offers, and floral inspiration — delivered straight to your inbox.</p>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="flex max-w-md mx-auto">
			<input type="hidden" name="action" value="everbloom_newsletter_subscribe">
			<?php wp_nonce_field( 'everbloom_newsletter', 'everbloom_newsletter_nonce' ); ?>
			<input type="email" name="email" required placeholder="Enter your email address" class="newsletter-input">
			<button type="submit" class="btn btn-primary rounded-l-none">Subscribe</button>
		</form>
	</div>
</section>

<!-- Footer -->
<footer class="bg-[#2c2223] text-cream/80">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
		<div>
			<div class="flex items-center gap-2 mb-4">
				<span class="text-rose text-xl"><i class="fa-solid fa-spa"></i></span>
				<span class="font-display text-xl font-bold text-white">Everbloom</span>
			</div>
			<p class="text-sm text-cream/60 leading-relaxed mb-4">Flowers that make every moment bloom. Handcrafted bouquets and gifts delivered with love, anywhere in Nepal.<br><span class="text-xs">फूलले हरेक क्षणलाई फुलाउँछ।</span></p>
			<div class="flex gap-3">
				<a href="#" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-rose-dark transition"><i class="fa-brands fa-facebook-f text-sm"></i></a>
				<a href="#" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-rose-dark transition"><i class="fa-brands fa-instagram text-sm"></i></a>
				<a href="#" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-rose-dark transition"><i class="fa-brands fa-tiktok text-sm"></i></a>
				<a href="#" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-rose-dark transition"><i class="fa-brands fa-viber text-sm"></i></a>
			</div>
		</div>
		<div>
			<h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Shop</h4>
			<ul class="space-y-2.5 text-sm text-cream/60">
				<li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="hover:text-rose transition">All Flowers</a></li>
				<?php foreach ( $footer_categories as $cat ) : ?>
				<li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="hover:text-rose transition"><?php echo esc_html( $cat->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div>
			<h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Company</h4>
			<ul class="space-y-2.5 text-sm text-cream/60">
				<li><a href="<?php echo esc_url( $about_page ? get_permalink( $about_page ) : '#' ); ?>" class="hover:text-rose transition">About Us</a></li>
				<li><a href="<?php echo esc_url( $contact_page ? get_permalink( $contact_page ) : '#' ); ?>" class="hover:text-rose transition">Contact Us</a></li>
				<li><a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="hover:text-rose transition">Track Order</a></li>
				<li><a href="<?php echo esc_url( home_url( '/#delivery-info' ) ); ?>" class="hover:text-rose transition">Delivery Information</a></li>
				<li><a href="<?php echo esc_url( $contact_page ? get_permalink( $contact_page ) : '#' ); ?>" class="hover:text-rose transition">FAQs</a></li>
			</ul>
		</div>
		<div>
			<h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Get in Touch</h4>
			<ul class="space-y-3 text-sm text-cream/60">
				<li class="flex items-start gap-2.5"><i class="fa-solid fa-location-dot mt-1 text-rose"></i> Jhamsikhel, Lalitpur, Kathmandu Valley, Nepal</li>
				<li class="flex items-center gap-2.5"><i class="fa-solid fa-phone text-rose"></i> +977 1-5010000</li>
				<li class="flex items-center gap-2.5"><i class="fa-brands fa-whatsapp text-rose"></i> +977 980-1234567</li>
				<li class="flex items-center gap-2.5"><i class="fa-solid fa-envelope text-rose"></i> hello@everbloomnepal.com</li>
				<li class="flex items-center gap-2.5"><i class="fa-solid fa-clock text-rose"></i> Sun–Fri: 8am – 8pm</li>
			</ul>
		</div>
	</div>
	<div class="border-t border-white/10 py-5">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-cream/50">
			<p>&copy; <?php echo esc_html( date( 'Y' ) ); ?> Everbloom Nepal. All rights reserved.</p>
			<div class="flex items-center gap-4">
				<span>We accept:</span>
				<span class="px-2 py-1 rounded bg-white/10 font-semibold text-[0.65rem]">eSewa</span>
				<span class="px-2 py-1 rounded bg-white/10 font-semibold text-[0.65rem]">Khalti</span>
				<span class="px-2 py-1 rounded bg-white/10 font-semibold text-[0.65rem]">IME Pay</span>
				<span class="px-2 py-1 rounded bg-white/10 font-semibold text-[0.65rem]">COD</span>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
