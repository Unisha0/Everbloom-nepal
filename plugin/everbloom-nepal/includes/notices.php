<?php
/**
 * A lightweight flash-message system for non-WooCommerce forms (contact, newsletter),
 * mirroring the original Django messages framework via a redirect + transient token.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function everbloom_notice_redirect_url( $target_url, $message, $type = 'success' ) {
	$token = wp_generate_password( 12, false );
	set_transient( 'eb_notice_' . $token, array( 'message' => $message, 'type' => $type ), 60 );
	return add_query_arg( 'eb_notice', $token, $target_url );
}

function everbloom_get_notices() {
	if ( empty( $_GET['eb_notice'] ) ) {
		return array();
	}
	$token = sanitize_text_field( wp_unslash( $_GET['eb_notice'] ) );
	$notice = get_transient( 'eb_notice_' . $token );
	if ( ! $notice ) {
		return array();
	}
	delete_transient( 'eb_notice_' . $token );
	return array( $notice );
}
