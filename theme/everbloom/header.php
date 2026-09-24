<?php
/**
 * The header for the Everbloom Nepal theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eb_active     = everbloom_active_nav();
$nav_categories = everbloom_get_categories( false );
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌸</text></svg>">
<script>document.documentElement.classList.add('js');</script>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-cream font-body antialiased' ); ?>>
<?php wp_body_open(); ?>

<div id="toast-container"></div>

<!-- Announcement bar -->
<div class="bg-ink text-cream text-center text-xs sm:text-sm py-2 px-4">
	<i class="fa-solid fa-truck-fast mr-1.5"></i>
	Free delivery within Kathmandu Valley on orders above <?php echo esc_html( everbloom_npr( everbloom_free_delivery_threshold() ) ); ?> &middot; Same-day delivery available
</div>

<!-- Header -->
<header class="sticky top-0 z-50 bg-cream/95 backdrop-blur border-b border-blush-deep shadow-sm">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="flex items-center justify-between h-20">

			<!-- Mobile menu button -->
			<button id="mobile-menu-btn" class="lg:hidden text-ink text-xl w-10 h-10 flex items-center justify-center">
				<i class="fa-solid fa-bars"></i>
			</button>

			<!-- Logo -->
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2 shrink-0">
				<span class="text-rose-dark text-2xl"><i class="fa-solid fa-spa"></i></span>
				<span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-ink">Everbloom</span>
				<span class="hidden sm:inline text-[0.65rem] uppercase tracking-widest text-eb-sage font-semibold border border-sage/40 rounded-full px-2 py-0.5 -ml-1 mt-2">Nepal</span>
			</a>

			<!-- Desktop nav -->
			<nav class="hidden lg:flex items-center gap-7 mx-auto">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-link <?php echo 'home' === $eb_active ? 'active' : ''; ?>">Home</a>
				<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="nav-link <?php echo 'shop' === $eb_active ? 'active' : ''; ?>">Shop</a>
				<div class="relative group">
					<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="nav-link flex items-center gap-1">Categories <i class="fa-solid fa-chevron-down text-[0.6rem] mt-0.5"></i></a>
					<div class="absolute left-1/2 -translate-x-1/2 top-full pt-3 hidden group-hover:block z-50">
						<div class="bg-white rounded-2xl shadow-2xl border border-blush-deep p-4 grid grid-cols-2 gap-1 w-80">
							<?php foreach ( $nav_categories as $cat ) : ?>
							<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blush text-sm text-ink-soft hover:text-rose-dark transition">
								<i class="<?php echo esc_attr( everbloom_category_icon( $cat->term_id ) ); ?> text-eb-rose text-xs"></i> <?php echo esc_html( $cat->name ); ?>
							</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'about' ) ) ); ?>" class="nav-link <?php echo 'about' === $eb_active ? 'active' : ''; ?>">About Us</a>
				<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>" class="nav-link <?php echo 'contact' === $eb_active ? 'active' : ''; ?>">Contact</a>
			</nav>

			<!-- Right icons -->
			<div class="flex items-center gap-1 sm:gap-2">
				<button id="search-toggle-btn" class="w-10 h-10 flex items-center justify-center text-ink hover:text-rose-dark transition rounded-full hover:bg-blush">
					<i class="fa-solid fa-magnifying-glass"></i>
				</button>
				<?php if ( is_user_logged_in() ) :
					$current_user = wp_get_current_user();
					$wishlist_page = get_page_by_path( 'wishlist' );
				?>
				<a href="<?php echo esc_url( $wishlist_page ? get_permalink( $wishlist_page ) : '#' ); ?>" class="w-10 h-10 flex items-center justify-center text-ink hover:text-rose-dark transition rounded-full hover:bg-blush">
					<i class="fa-regular fa-heart"></i>
				</a>
				<div class="relative group hidden sm:block">
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="w-10 h-10 flex items-center justify-center text-ink hover:text-rose-dark transition rounded-full hover:bg-blush">
						<i class="fa-regular fa-circle-user"></i>
					</a>
					<div class="absolute right-0 top-full pt-3 hidden group-hover:block z-50">
						<div class="bg-white rounded-2xl shadow-2xl border border-blush-deep py-2 w-52 text-sm">
							<div class="px-4 py-2 text-ink-soft border-b border-blush">Hi, <?php echo esc_html( $current_user->first_name ? $current_user->first_name : $current_user->user_login ); ?></div>
							<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="block px-4 py-2 hover:bg-blush text-ink">My Profile</a>
							<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="block px-4 py-2 hover:bg-blush text-ink">My Orders</a>
							<a href="<?php echo esc_url( $wishlist_page ? get_permalink( $wishlist_page ) : '#' ); ?>" class="block px-4 py-2 hover:bg-blush text-ink">Wishlist</a>
							<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="block px-4 py-2 hover:bg-blush text-rose-dark">Logout</a>
						</div>
					</div>
				</div>
				<?php else : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="w-10 h-10 flex items-center justify-center text-ink hover:text-rose-dark transition rounded-full hover:bg-blush">
					<i class="fa-regular fa-circle-user"></i>
				</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="relative w-10 h-10 flex items-center justify-center text-ink hover:text-rose-dark transition rounded-full hover:bg-blush">
					<i class="fa-solid fa-bag-shopping"></i>
					<span class="cart-count-badge absolute -top-0.5 -right-0.5 bg-rose-dark text-white text-[0.65rem] font-bold w-4.5 h-4.5 min-w-[18px] min-h-[18px] flex items-center justify-center rounded-full <?php echo WC()->cart->get_cart_contents_count() === 0 ? 'hidden' : ''; ?>"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
				</a>
				<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="hidden sm:inline-flex btn btn-primary btn-sm ml-2">Shop Flowers</a>
			</div>
		</div>
	</div>

	<!-- Mobile menu -->
	<div id="mobile-menu" class="hidden lg:hidden border-t border-blush-deep bg-cream px-4 py-4 space-y-1">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="block py-2.5 text-ink font-medium">Home</a>
		<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="block py-2.5 text-ink font-medium">Shop All Flowers</a>
		<div class="py-2 text-xs uppercase tracking-widest text-ink-soft font-semibold">Categories</div>
		<?php foreach ( $nav_categories as $cat ) : ?>
		<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="block py-2 pl-3 text-ink-soft text-sm"><?php echo esc_html( $cat->name ); ?></a>
		<?php endforeach; ?>
		<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'about' ) ) ); ?>" class="block py-2.5 text-ink font-medium border-t border-blush-deep mt-2 pt-3">About Us</a>
		<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>" class="block py-2.5 text-ink font-medium">Contact</a>
		<?php if ( is_user_logged_in() ) : ?>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="block py-2.5 text-ink font-medium">My Profile</a>
		<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="block py-2.5 text-ink font-medium">My Orders</a>
		<?php else : ?>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="block py-2.5 text-ink font-medium">Login / Sign Up</a>
		<?php endif; ?>
	</div>
</header>

<!-- Search overlay -->
<div id="search-overlay" class="hidden fixed inset-0 z-[60] bg-ink/60 backdrop-blur-sm">
	<div class="max-w-2xl mx-auto mt-24 sm:mt-32 px-4">
		<form action="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" method="get" class="bg-white rounded-2xl shadow-2xl p-3 flex items-center gap-2">
			<i class="fa-solid fa-magnifying-glass text-ink-soft ml-2"></i>
			<input type="text" name="s" placeholder="Search for roses, bouquets, gifts..." class="flex-1 py-3 px-2 text-ink outline-none bg-transparent">
			<input type="hidden" name="post_type" value="product">
			<button type="button" id="search-close-btn" class="w-9 h-9 rounded-full hover:bg-blush flex items-center justify-center text-ink-soft"><i class="fa-solid fa-xmark"></i></button>
			<button type="submit" class="btn btn-primary btn-sm">Search</button>
		</form>
	</div>
</div>

<?php
$eb_notices = everbloom_get_notices();
if ( ! empty( $eb_notices ) ) :
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 space-y-2">
	<?php foreach ( $eb_notices as $notice ) : ?>
	<div class="eb-message flex items-center gap-2 rounded-xl px-4 py-3 text-sm <?php echo 'error' === $notice['type'] ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-sage-light text-eb-sage border border-sage/30'; ?>">
		<i class="fa-solid <?php echo 'error' === $notice['type'] ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
		<span><?php echo esc_html( $notice['message'] ); ?></span>
	</div>
	<?php endforeach; ?>
</div>
<?php endif; ?>

<main>
