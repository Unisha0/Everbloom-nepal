<?php
/**
 * One-off migration: legacy Django/SQLite catalog (categories, products, reviews)
 * -> WooCommerce. Run once via: wp eval-file migration/import.php --path=wordpress
 */

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$json_path = dirname( __DIR__ ) . '/migration/export.json';
$data      = json_decode( file_get_contents( $json_path ), true );

WP_CLI::log( sprintf(
	'Importing %d categories, %d products, %d reviews...',
	count( $data['categories'] ), count( $data['products'] ), count( $data['reviews'] )
) );

/**
 * Sideload a remote image URL as an attachment, returning the attachment ID (or 0).
 */
function eb_import_sideload_image( $url, $desc = '' ) {
	static $cache = array();
	if ( isset( $cache[ $url ] ) ) {
		return $cache[ $url ];
	}
	if ( ! $url ) {
		return 0;
	}
	$id = media_sideload_image( $url, 0, $desc, 'id' );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( 'Image sideload failed for ' . $url . ': ' . $id->get_error_message() );
		$cache[ $url ] = 0;
		return 0;
	}
	$cache[ $url ] = $id;
	return $id;
}

// ---------------------------------------------------------------------------
// Categories
// ---------------------------------------------------------------------------
$category_term_map = array(); // sqlite category id => term_id

foreach ( $data['categories'] as $cat ) {
	$existing = term_exists( $cat['slug'], 'product_cat' );
	if ( $existing ) {
		$term_id = (int) $existing['term_id'];
		wp_update_term( $term_id, 'product_cat', array(
			'name'        => $cat['name'],
			'description' => $cat['description'],
		) );
	} else {
		$result = wp_insert_term( $cat['name'], 'product_cat', array(
			'slug'        => $cat['slug'],
			'description' => $cat['description'],
		) );
		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( 'Category ' . $cat['name'] . ': ' . $result->get_error_message() );
			continue;
		}
		$term_id = (int) $result['term_id'];
	}

	update_term_meta( $term_id, 'eb_nepali_name', $cat['nepali_name'] );
	update_term_meta( $term_id, 'eb_icon', $cat['icon'] );
	update_term_meta( $term_id, 'eb_is_occasion', $cat['is_occasion'] ? '1' : '0' );
	update_term_meta( $term_id, 'eb_order', (int) $cat['order'] );

	$image_id = eb_import_sideload_image( $cat['image_url'], $cat['name'] );
	if ( $image_id ) {
		update_term_meta( $term_id, 'thumbnail_id', $image_id );
	}

	$category_term_map[ $cat['id'] ] = $term_id;
	WP_CLI::log( 'Category: ' . $cat['name'] . ' -> term #' . $term_id );
}

// ---------------------------------------------------------------------------
// Products
// ---------------------------------------------------------------------------
$product_id_map = array(); // sqlite product id => WC product id

foreach ( $data['products'] as $p ) {
	$existing = get_page_by_path( $p['slug'], OBJECT, 'product' );

	$product = $existing ? wc_get_product( $existing->ID ) : new WC_Product_Simple();

	$product->set_name( $p['name'] );
	$product->set_slug( $p['slug'] );
	$product->set_description( $p['description'] );
	$product->set_short_description( $p['short_description'] );
	$product->set_status( $p['is_active'] ? 'publish' : 'draft' );
	$product->set_catalog_visibility( 'visible' );

	if ( $p['compare_at_price'] ) {
		$product->set_regular_price( (string) $p['compare_at_price'] );
		$product->set_sale_price( (string) $p['price'] );
	} else {
		$product->set_regular_price( (string) $p['price'] );
		$product->set_sale_price( '' );
	}

	$product->set_manage_stock( true );
	$product->set_stock_quantity( (int) $p['stock'] );
	$product->set_stock_status( $p['stock'] > 0 ? 'instock' : 'outofstock' );
	$product->set_featured( (bool) $p['is_featured'] );

	if ( isset( $category_term_map[ $p['category_id'] ] ) ) {
		$product->set_category_ids( array( $category_term_map[ $p['category_id'] ] ) );
	}

	$product_id = $product->save();

	update_post_meta( $product_id, '_eb_stem_count', $p['stem_count'] );
	update_post_meta( $product_id, '_eb_vase_included', $p['vase_included'] ? '1' : '0' );
	update_post_meta( $product_id, '_eb_is_bestseller', $p['is_bestseller'] ? '1' : '0' );
	update_post_meta( $product_id, '_eb_is_new', $p['is_new'] ? '1' : '0' );

	$image_id = eb_import_sideload_image( $p['image_url'], $p['name'] );
	if ( $image_id ) {
		set_post_thumbnail( $product_id, $image_id );
	}
	if ( $p['image_url_secondary'] ) {
		$gallery_id = eb_import_sideload_image( $p['image_url_secondary'], $p['name'] . ' (alt)' );
		if ( $gallery_id ) {
			update_post_meta( $product_id, '_product_image_gallery', (string) $gallery_id );
		}
	}

	$product_id_map[ $p['id'] ] = $product_id;
	WP_CLI::log( 'Product: ' . $p['name'] . ' -> #' . $product_id );
}

// ---------------------------------------------------------------------------
// Reviews (native WooCommerce product reviews / comment rating)
// ---------------------------------------------------------------------------
foreach ( $data['reviews'] as $r ) {
	if ( ! isset( $product_id_map[ $r['product_id'] ] ) ) {
		continue;
	}
	$post_id = $product_id_map[ $r['product_id'] ];

	$existing_reviews = get_comments( array(
		'post_id'    => $post_id,
		'meta_key'   => '_eb_review_source_id',
		'meta_value' => $r['id'],
	) );
	if ( ! empty( $existing_reviews ) ) {
		continue;
	}

	$comment_id = wp_insert_comment( array(
		'comment_post_ID'      => $post_id,
		'comment_author'       => $r['name'],
		'comment_content'      => $r['comment'],
		'comment_date'         => $r['created_at'],
		'comment_approved'     => 1,
		'comment_type'         => 'review',
	) );

	if ( $comment_id ) {
		update_comment_meta( $comment_id, 'rating', (int) $r['rating'] );
		update_comment_meta( $comment_id, 'verified', 1 );
		update_comment_meta( $comment_id, '_eb_review_source_id', $r['id'] );
		// Recalculate the product's cached average rating / review count.
		WC_Comments::add_comment_rating( $comment_id );
	}
}
WP_CLI::log( 'Reviews imported.' );

wc_delete_product_transients();
WP_CLI::success( 'Migration complete: ' . count( $category_term_map ) . ' categories, ' . count( $product_id_map ) . ' products.' );
