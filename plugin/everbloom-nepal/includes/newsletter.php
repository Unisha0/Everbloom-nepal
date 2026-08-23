<?php
/**
 * Newsletter subscription — ported from NewsletterSubscriber model + core:newsletter_subscribe view.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function everbloom_newsletter_subscribe() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	if ( ! isset( $_POST['everbloom_newsletter_nonce'] ) || ! wp_verify_nonce( $_POST['everbloom_newsletter_nonce'], 'everbloom_newsletter' ) ) {
		wp_safe_redirect( everbloom_notice_redirect_url( $redirect, 'Something went wrong. Please try again.', 'error' ) );
		exit;
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! $email || ! is_email( $email ) ) {
		wp_safe_redirect( everbloom_notice_redirect_url( $redirect, 'Please enter a valid email address.', 'error' ) );
		exit;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'eb_newsletter_subscribers';
	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $email ) );
	if ( ! $exists ) {
		$wpdb->insert( $table, array(
			'email'         => $email,
			'subscribed_at' => current_time( 'mysql' ),
		) );
	}

	wp_safe_redirect( everbloom_notice_redirect_url( $redirect, "You're subscribed! Watch your inbox for fresh floral inspiration.", 'success' ) );
	exit;
}
add_action( 'admin_post_everbloom_newsletter_subscribe', 'everbloom_newsletter_subscribe' );
add_action( 'admin_post_nopriv_everbloom_newsletter_subscribe', 'everbloom_newsletter_subscribe' );

/**
 * Admin list page under WooCommerce for newsletter subscribers.
 */
function everbloom_newsletter_admin_menu() {
	add_submenu_page(
		'woocommerce',
		__( 'Newsletter Subscribers', 'everbloom-nepal' ),
		__( 'Newsletter', 'everbloom-nepal' ),
		'manage_woocommerce',
		'everbloom-newsletter',
		'everbloom_newsletter_admin_page'
	);
}
add_action( 'admin_menu', 'everbloom_newsletter_admin_menu' );

function everbloom_newsletter_admin_page() {
	global $wpdb;
	$table = $wpdb->prefix . 'eb_newsletter_subscribers';
	$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY subscribed_at DESC" );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Newsletter Subscribers', 'everbloom-nepal' ); ?></h1>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Email', 'everbloom-nepal' ); ?></th><th><?php esc_html_e( 'Subscribed', 'everbloom-nepal' ); ?></th></tr></thead>
			<tbody>
			<?php if ( $rows ) : foreach ( $rows as $row ) : ?>
				<tr><td><?php echo esc_html( $row->email ); ?></td><td><?php echo esc_html( $row->subscribed_at ); ?></td></tr>
			<?php endforeach; else : ?>
				<tr><td colspan="2"><?php esc_html_e( 'No subscribers yet.', 'everbloom-nepal' ); ?></td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
