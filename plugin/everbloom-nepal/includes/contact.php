<?php
/**
 * Contact form handler — ported from ContactMessage model + core:contact view.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function everbloom_contact_submit() {
	$contact_page = get_page_by_path( 'contact' );
	$redirect     = $contact_page ? get_permalink( $contact_page ) : home_url( '/' );

	if ( ! isset( $_POST['everbloom_contact_nonce'] ) || ! wp_verify_nonce( $_POST['everbloom_contact_nonce'], 'everbloom_contact' ) ) {
		wp_safe_redirect( everbloom_notice_redirect_url( $redirect, 'Something went wrong. Please try again.', 'error' ) );
		exit;
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( ! $name || ! $email || ! is_email( $email ) || ! $subject || ! $message ) {
		wp_safe_redirect( everbloom_notice_redirect_url( $redirect, 'Please fill in all fields with a valid email address.', 'error' ) );
		exit;
	}

	global $wpdb;
	$wpdb->insert( $wpdb->prefix . 'eb_contact_messages', array(
		'name'       => $name,
		'email'      => $email,
		'subject'    => $subject,
		'message'    => $message,
		'is_read'    => 0,
		'created_at' => current_time( 'mysql' ),
	) );

	wp_safe_redirect( everbloom_notice_redirect_url( $redirect, 'Thank you for reaching out! Our team will get back to you shortly.', 'success' ) );
	exit;
}
add_action( 'admin_post_everbloom_contact_submit', 'everbloom_contact_submit' );
add_action( 'admin_post_nopriv_everbloom_contact_submit', 'everbloom_contact_submit' );

/**
 * Admin list page under WooCommerce for contact messages.
 */
function everbloom_contact_admin_menu() {
	add_submenu_page(
		'woocommerce',
		__( 'Contact Messages', 'everbloom-nepal' ),
		__( 'Contact Messages', 'everbloom-nepal' ),
		'manage_woocommerce',
		'everbloom-contact-messages',
		'everbloom_contact_admin_page'
	);
}
add_action( 'admin_menu', 'everbloom_contact_admin_menu' );

function everbloom_contact_admin_page() {
	global $wpdb;
	$table = $wpdb->prefix . 'eb_contact_messages';

	if ( isset( $_GET['mark_read'] ) && check_admin_referer( 'everbloom_mark_read' ) ) {
		$wpdb->update( $table, array( 'is_read' => 1 ), array( 'id' => absint( $_GET['mark_read'] ) ) );
	}

	$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC" );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Contact Messages', 'everbloom-nepal' ); ?></h1>
		<table class="widefat striped">
			<thead><tr>
				<th><?php esc_html_e( 'From', 'everbloom-nepal' ); ?></th>
				<th><?php esc_html_e( 'Subject', 'everbloom-nepal' ); ?></th>
				<th><?php esc_html_e( 'Message', 'everbloom-nepal' ); ?></th>
				<th><?php esc_html_e( 'Received', 'everbloom-nepal' ); ?></th>
				<th><?php esc_html_e( 'Status', 'everbloom-nepal' ); ?></th>
			</tr></thead>
			<tbody>
			<?php if ( $rows ) : foreach ( $rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row->name ); ?><br><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
					<td><?php echo esc_html( $row->subject ); ?></td>
					<td><?php echo esc_html( wp_trim_words( $row->message, 20 ) ); ?></td>
					<td><?php echo esc_html( $row->created_at ); ?></td>
					<td>
						<?php if ( $row->is_read ) : ?>
							<?php esc_html_e( 'Read', 'everbloom-nepal' ); ?>
						<?php else : ?>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'mark_read', $row->id ), 'everbloom_mark_read' ) ); ?>"><?php esc_html_e( 'Mark as read', 'everbloom-nepal' ); ?></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; else : ?>
				<tr><td colspan="5"><?php esc_html_e( 'No messages yet.', 'everbloom-nepal' ); ?></td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
