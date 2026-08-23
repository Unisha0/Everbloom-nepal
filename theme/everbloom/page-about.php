<?php
/**
 * Template Name: About Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$shop_url = get_permalink( wc_get_page_id( 'shop' ) );
$contact_page = get_page_by_path( 'contact' );
?>

<!-- Hero -->
<section class="relative h-[50vh] min-h-[380px] flex items-center">
	<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c9/Blooming_garden_center.jpg/1920px-Blooming_garden_center.jpg" alt="Everbloom florist studio" class="hero-bg-img">
	<div class="hero-overlay"></div>
	<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-white">
		<p class="section-eyebrow text-gold mb-3">Our Story</p>
		<h1 class="font-display text-4xl sm:text-5xl font-bold">About Everbloom Nepal</h1>
	</div>
</section>

<!-- Story -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
	<div class="grid lg:grid-cols-2 gap-14 items-center">
		<div class="reveal">
			<p class="section-eyebrow text-eb-rose mb-3">Who We Are</p>
			<h2 class="font-display text-3xl sm:text-4xl font-bold text-ink mb-6">Rooted in Kathmandu, Blooming Across Nepal</h2>
			<p class="text-ink-soft leading-relaxed mb-4">Everbloom Nepal began in 2019 with a simple idea: everyone deserves access to beautifully arranged, genuinely fresh flowers — not just for grand occasions, but for everyday moments too.</p>
			<p class="text-ink-soft leading-relaxed mb-4">What started as a small flower cart in Jhamsikhel has grown into one of Nepal's most loved online florists, working with local growers from Kavre and Dhading to bring the freshest blooms to your doorstep — from Kathmandu and Lalitpur to Pokhara, Chitwan and beyond.</p>
			<p class="text-ink-soft leading-relaxed">Every bouquet is hand-tied by our in-house florists, who bring years of craft and genuine care to every arrangement, whether it's a birthday surprise, a wedding mandap, or a quiet "thinking of you."</p>
		</div>
		<div class="relative reveal">
			<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f7/24h_unmanned_flower_shop.jpg/1920px-24h_unmanned_flower_shop.jpg" alt="Everbloom flower shop" class="rounded-3xl w-full h-[460px] object-cover">
		</div>
	</div>
</section>

<!-- Values -->
<section class="bg-eb-blush/40 py-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="text-center max-w-xl mx-auto mb-14 reveal">
			<p class="section-eyebrow text-eb-rose mb-2">What We Stand For</p>
			<h2 class="font-display text-3xl sm:text-4xl font-bold text-ink">Our Values</h2>
		</div>
		<div class="grid sm:grid-cols-3 gap-8">
			<div class="eb-card p-8 text-center reveal">
				<i class="fa-solid fa-seedling text-eb-rose text-2xl mb-4"></i>
				<h3 class="font-display text-lg font-semibold text-ink mb-2">Freshness First</h3>
				<p class="text-sm text-ink-soft leading-relaxed">We source directly from trusted local growers and import partners, so every stem reaches you at its best.</p>
			</div>
			<div class="eb-card p-8 text-center reveal">
				<i class="fa-solid fa-hands-holding-circle text-eb-rose text-2xl mb-4"></i>
				<h3 class="font-display text-lg font-semibold text-ink mb-2">Crafted With Care</h3>
				<p class="text-sm text-ink-soft leading-relaxed">Our florists treat every order — big or small — with the same attention to detail and artistry.</p>
			</div>
			<div class="eb-card p-8 text-center reveal">
				<i class="fa-solid fa-earth-asia text-eb-rose text-2xl mb-4"></i>
				<h3 class="font-display text-lg font-semibold text-ink mb-2">Proudly Nepali</h3>
				<p class="text-sm text-ink-soft leading-relaxed">We support local farmers and communities, and celebrate Nepali festivals and traditions in every collection.</p>
			</div>
		</div>
	</div>
</section>

<!-- Team / studio -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
	<div class="grid lg:grid-cols-3 gap-6">
		<div class="lg:col-span-2 rounded-3xl overflow-hidden reveal">
			<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Flowers_for_sale_at_Bedford_Fruit_Market%2C_Williamsburg%2C_Brooklyn%2C_New_York_-_20221123.jpg/1920px-Flowers_for_sale_at_Bedford_Fruit_Market%2C_Williamsburg%2C_Brooklyn%2C_New_York_-_20221123.jpg" alt="Fresh flowers on display" class="w-full h-[420px] object-cover">
		</div>
		<div class="rounded-3xl overflow-hidden reveal">
			<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Retail_flower_display.JPG/1920px-Retail_flower_display.JPG" alt="Everbloom flower shop interior" class="w-full h-[420px] object-cover">
		</div>
	</div>
</section>

<!-- Stats -->
<section class="bg-ink py-16">
	<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
		<div class="reveal"><p class="font-display text-3xl sm:text-4xl font-bold text-white">6+</p><p class="text-white/60 text-xs uppercase tracking-wide mt-2">Years of Craft</p></div>
		<div class="reveal"><p class="font-display text-3xl sm:text-4xl font-bold text-white">15,000+</p><p class="text-white/60 text-xs uppercase tracking-wide mt-2">Bouquets Delivered</p></div>
		<div class="reveal"><p class="font-display text-3xl sm:text-4xl font-bold text-white">30+</p><p class="text-white/60 text-xs uppercase tracking-wide mt-2">Cities Served</p></div>
		<div class="reveal"><p class="font-display text-3xl sm:text-4xl font-bold text-white">4.8/5</p><p class="text-white/60 text-xs uppercase tracking-wide mt-2">Customer Rating</p></div>
	</div>
</section>

<!-- CTA -->
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center reveal">
	<h2 class="font-display text-3xl sm:text-4xl font-bold text-ink mb-4">Let's Make Your Moment Bloom</h2>
	<p class="text-ink-soft mb-8 max-w-md mx-auto">Browse our collections or reach out — our florists are always happy to help you find the perfect arrangement.</p>
	<div class="flex flex-wrap justify-center gap-4">
		<a href="<?php echo esc_url( $shop_url ); ?>" class="btn btn-primary">Shop Flowers</a>
		<a href="<?php echo esc_url( $contact_page ? get_permalink( $contact_page ) : '#' ); ?>" class="btn btn-outline">Contact Us</a>
	</div>
</section>

<?php
get_footer();
