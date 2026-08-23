<?php
/**
 * One-off fix: the first import.php run had a comment-query bug that dropped most
 * reviews as false-positive duplicates. Wipe the partial review import and redo it
 * cleanly. Run via: wp eval-file migration/fix_reviews.php --path=wordpress
 */

$json_path = dirname( __DIR__ ) . '/migration/export.json';
$data      = json_decode( file_get_contents( $json_path ), true );

global $wpdb;
$existing_ids = $wpdb->get_col( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_type = 'review'" );
foreach ( $existing_ids as $comment_id ) {
	wp_delete_comment( $comment_id, true );
}
WP_CLI::log( 'Deleted ' . count( $existing_ids ) . ' existing review comments.' );

// Build sqlite product id => WP product id map by slug.
$product_id_map = array();
foreach ( $data['products'] as $p ) {
	$post = get_page_by_path( $p['slug'], OBJECT, 'product' );
	if ( $post ) {
		$product_id_map[ $p['id'] ] = $post->ID;
	}
}

$inserted = 0;
foreach ( $data['reviews'] as $r ) {
	if ( ! isset( $product_id_map[ $r['product_id'] ] ) ) {
		continue;
	}
	$post_id = $product_id_map[ $r['product_id'] ];

	$comment_id = wp_insert_comment( array(
		'comment_post_ID'  => $post_id,
		'comment_author'   => $r['name'],
		'comment_content'  => $r['comment'],
		'comment_date'     => $r['created_at'],
		'comment_approved' => 1,
		'comment_type'     => 'review',
	) );

	if ( $comment_id ) {
		update_comment_meta( $comment_id, 'rating', (int) $r['rating'] );
		update_comment_meta( $comment_id, 'verified', 1 );
		update_comment_meta( $comment_id, '_eb_review_source_id', $r['id'] );
		WC_Comments::add_comment_rating( $comment_id );
		$inserted++;
	}
}

wc_delete_product_transients();
WP_CLI::success( "Re-imported {$inserted} reviews." );
