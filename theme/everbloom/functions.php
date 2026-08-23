<?php
/**
 * Everbloom Nepal theme setup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EVERBLOOM_VERSION', '1.0.0' );

/**
 * Theme setup.
 */
function everbloom_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	// WooCommerce support.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'everbloom' ),
	) );

	add_image_size( 'everbloom-category', 480, 600, true );
	add_image_size( 'everbloom-product', 800, 800, true );
}
add_action( 'after_setup_theme', 'everbloom_setup' );

/**
 * Enqueue styles and scripts.
 */
function everbloom_scripts() {
	// Google Fonts.
	wp_enqueue_style( 'everbloom-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,600&family=Poppins:wght@300;400;500;600;700&display=swap', array(), null );

	// Font Awesome.
	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );

	// Tailwind CDN (JIT, no build step — matches the original design system).
	wp_enqueue_script( 'tailwindcss', 'https://cdn.tailwindcss.com', array(), null, false );
	wp_add_inline_script( 'tailwindcss', "
		tailwind.config = {
			theme: {
				extend: {
					colors: {
						blush: '#fbeef0',
						rose: { DEFAULT: '#c76b7f', dark: '#a8465c' },
						cream: '#fdf9f4',
						sage: '#8a9a7e',
						gold: '#c8a96a',
						ink: '#3a2e2f',
					},
					fontFamily: {
						display: ['\"Playfair Display\"', 'serif'],
						body: ['Poppins', 'sans-serif'],
					},
				},
			},
		}
	" );

	// Theme stylesheet (includes WooCommerce reskin).
	wp_enqueue_style( 'everbloom-style', get_stylesheet_uri(), array(), EVERBLOOM_VERSION );

	// Theme JS.
	wp_enqueue_script( 'everbloom-main', get_template_directory_uri() . '/assets/js/main.js', array( 'jquery' ), EVERBLOOM_VERSION, true );
	wp_localize_script( 'everbloom-main', 'everbloomData', array(
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'everbloom-nonce' ),
		'loginUrl'   => wc_get_page_permalink( 'myaccount' ),
		'isLoggedIn' => is_user_logged_in(),
	) );
}
add_action( 'wp_enqueue_scripts', 'everbloom_scripts' );

/**
 * Everbloom brand / business constants (ported from the original Django settings).
 */
function everbloom_currency_symbol() {
	return 'रु.';
}

function everbloom_free_delivery_threshold() {
	return 5000;
}

function everbloom_standard_delivery_fee() {
	return 150;
}

/**
 * Format a number as Nepali Rupees, e.g. 2200 -> रु. 2,200
 */
function everbloom_npr( $value ) {
	$value = (float) $value;
	if ( floor( $value ) === $value ) {
		$formatted = number_format( $value, 0 );
	} else {
		$formatted = number_format( $value, 2 );
	}
	return everbloom_currency_symbol() . ' ' . $formatted;
}

/**
 * Get shop/occasion product categories ordered like the original site.
 *
 * @param bool $occasion Whether to return "occasion" categories instead of main ones.
 * @param int  $limit    Max number of terms (0 = all).
 */
function everbloom_get_categories( $occasion = false, $limit = 0 ) {
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'meta_key'   => 'eb_order',
		'orderby'    => 'meta_value_num',
		'order'      => 'ASC',
		'meta_query' => array(
			array(
				'key'     => 'eb_is_occasion',
				'value'   => $occasion ? '1' : '0',
				'compare' => '=',
			),
		),
		'exclude'    => array( get_option( 'default_product_cat' ) ),
	) );

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	if ( $limit > 0 ) {
		$terms = array_slice( $terms, 0, $limit );
	}

	return $terms;
}

function everbloom_category_icon( $term_id ) {
	$icon = get_term_meta( $term_id, 'eb_icon', true );
	return $icon ? $icon : 'fa-solid fa-seedling';
}

function everbloom_category_nepali_name( $term_id ) {
	return get_term_meta( $term_id, 'eb_nepali_name', true );
}

/**
 * Current page's active nav slug, for header highlighting.
 */
function everbloom_active_nav() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_shop() || is_product_category() || is_product() ) {
		return 'shop';
	}
	if ( is_page( 'about' ) ) {
		return 'about';
	}
	if ( is_page( 'contact' ) ) {
		return 'contact';
	}
	return '';
}

/**
 * The original cart page had no cross-sell / "you may be interested in" section.
 */
function everbloom_remove_cart_cross_sells() {
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10 );
}
add_action( 'wp', 'everbloom_remove_cart_cross_sells' );

/**
 * Our custom checkout/thankyou.php already renders the full order breakdown
 * (items, totals, addresses) — WooCommerce core also auto-hooks the default
 * order-details table onto the thank-you page, which would duplicate it.
 * Only remove it there; keep it for My Account > View Order.
 */
function everbloom_remove_duplicate_thankyou_details() {
	remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
}
add_action( 'init', 'everbloom_remove_duplicate_thankyou_details', 20 );

require get_template_directory() . '/inc/template-tags.php';
