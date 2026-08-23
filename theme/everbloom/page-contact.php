<?php
/**
 * Template Name: Contact Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="bg-eb-blush/50 py-14 sm:py-16">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
		<p class="section-eyebrow text-eb-rose mb-2">We'd Love to Hear From You</p>
		<h1 class="font-display text-3xl sm:text-4xl font-bold text-ink">Get in Touch</h1>
		<p class="text-ink-soft mt-3 max-w-xl mx-auto">Questions about an order, custom arrangements, or bulk/wedding orders? Our team is here to help.</p>
	</div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
	<div class="grid lg:grid-cols-5 gap-10">
		<!-- Contact info -->
		<div class="lg:col-span-2 space-y-5">
			<div class="eb-card p-6 flex items-start gap-4">
				<div class="w-11 h-11 rounded-full bg-eb-blush flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-location-dot text-eb-rose-dark"></i></div>
				<div>
					<h3 class="font-semibold text-ink mb-1">Visit Our Studio</h3>
					<p class="text-sm text-ink-soft">Jhamsikhel, Lalitpur, Kathmandu Valley, Nepal</p>
				</div>
			</div>
			<div class="eb-card p-6 flex items-start gap-4">
				<div class="w-11 h-11 rounded-full bg-eb-blush flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-phone text-eb-rose-dark"></i></div>
				<div>
					<h3 class="font-semibold text-ink mb-1">Call Us</h3>
					<p class="text-sm text-ink-soft">+977 1-5010000 &middot; +977 980-1234567 (WhatsApp/Viber)</p>
				</div>
			</div>
			<div class="eb-card p-6 flex items-start gap-4">
				<div class="w-11 h-11 rounded-full bg-eb-blush flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-envelope text-eb-rose-dark"></i></div>
				<div>
					<h3 class="font-semibold text-ink mb-1">Email Us</h3>
					<p class="text-sm text-ink-soft">hello@everbloomnepal.com</p>
				</div>
			</div>
			<div class="eb-card p-6 flex items-start gap-4">
				<div class="w-11 h-11 rounded-full bg-eb-blush flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-clock text-eb-rose-dark"></i></div>
				<div>
					<h3 class="font-semibold text-ink mb-1">Working Hours</h3>
					<p class="text-sm text-ink-soft">Sunday – Friday: 8:00 AM – 8:00 PM<br>Saturday: 9:00 AM – 5:00 PM</p>
				</div>
			</div>
			<div class="rounded-2xl overflow-hidden h-48">
				<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Hydrangea_bouquet_%289580026536%29.jpg/1920px-Hydrangea_bouquet_%289580026536%29.jpg" alt="Everbloom studio" class="w-full h-full object-cover">
			</div>
		</div>

		<!-- Contact form -->
		<div class="lg:col-span-3">
			<div class="eb-card p-6 sm:p-10">
				<h2 class="font-display text-2xl font-semibold text-ink mb-6">Send Us a Message</h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="everbloom_contact_submit">
					<?php wp_nonce_field( 'everbloom_contact', 'everbloom_contact_nonce' ); ?>
					<div class="grid sm:grid-cols-2 gap-4 mb-4">
						<div>
							<label class="form-label">Your Name</label>
							<input type="text" name="name" class="form-input" placeholder="Your name" required>
						</div>
						<div>
							<label class="form-label">Email Address</label>
							<input type="email" name="email" class="form-input" placeholder="you@example.com" required>
						</div>
					</div>
					<div class="mb-4">
						<label class="form-label">Subject</label>
						<input type="text" name="subject" class="form-input" placeholder="Subject" required>
					</div>
					<div class="mb-6">
						<label class="form-label">Message</label>
						<textarea name="message" rows="5" class="form-input" placeholder="Your message..." required></textarea>
					</div>
					<button type="submit" class="btn btn-primary">Send Message <i class="fa-solid fa-paper-plane text-sm"></i></button>
				</form>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
