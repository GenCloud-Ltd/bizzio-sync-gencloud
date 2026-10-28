<?php
/**
 * The image helper class.
 *
 * Handles all image-related operations for products and categories.
 *
 * @link       https://gencloud.bg
 * @since      1.1.0
 *
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The image helper class.
 *
 * This class defines all image processing functionality.
 *
 * @since      1.1.0
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/includes
 * @author     gencloud <web@gencloud.bg>
 */
class Bizzio_Sync_Gencloud_Image_Helper {

	/**
	 * Find an attachment ID by a meta value stored on the attachment.
	 *
	 * @since    1.1.1
	 * @param    string $meta_key   The attachment meta key.
	 * @param    string $meta_value The meta value to look for.
	 * @return   int|null The attachment ID if found, otherwise null.
	 */
	private static function find_attachment_by_meta( $meta_key, $meta_value ) {
		global $wpdb;

		// Direct query: a meta_query through WP_Query is far slower for this exact-match lookup on large media libraries.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$attachment_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
				$meta_key,
				$meta_value
			)
		);

		return $attachment_id ? (int) $attachment_id : null;
	}

	/**
	 * Find an attachment ID by the Bizzio image ID stored in post meta.
	 *
	 * @since    1.1.0
	 * @param    string $bizzio_id The Bizzio image ID.
	 * @return   int|null The attachment ID if found, otherwise null.
	 */
	public static function find_attachment_by_bizzio_id( $bizzio_id ) {
		return self::find_attachment_by_meta( '_bizzio_image_id', (string) $bizzio_id );
	}

	/**
	 * Load the WordPress media functions needed for sideloading outside of wp-admin requests.
	 *
	 * @since    1.1.1
	 * @return   void
	 */
	private static function load_media_functions() {
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}
	}

	/**
	 * Download a remote image into the media library.
	 *
	 * @since    1.1.1
	 * @param    string $image_url The remote image URL.
	 * @param    string $file_name The file name to store the image under.
	 * @param    int    $parent_id The post the attachment belongs to (0 for none).
	 * @return   int|WP_Error The new attachment ID or a WP_Error on failure.
	 */
	private static function sideload( $image_url, $file_name, $parent_id ) {
		self::load_media_functions();

		if ( empty( $image_url ) || ! wp_http_validate_url( $image_url ) ) {
			return new WP_Error( 'invalid_image_url', __( 'Invalid image URL provided.', 'bizzio-sync-for-woocommerce' ) );
		}

		$tmp = download_url( $image_url );
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}

		$file_array = array(
			'name'     => sanitize_file_name( basename( $file_name ) ),
			'tmp_name' => $tmp,
		);

		$attach_id = media_handle_sideload( $file_array, $parent_id );

		if ( is_wp_error( $attach_id ) ) {
			wp_delete_file( $tmp );
		}

		return $attach_id;
	}

	/**
	 * Sideloads an image from Bizzio, attaches it to a product, and sets meta data.
	 *
	 * @since    1.1.0
	 * @param    int   $product_id The product ID.
	 * @param    array $image_data Array containing image 'ID', 'Name', and 'Uri'.
	 * @return   int|WP_Error The new attachment ID or a WP_Error on failure.
	 */
	public static function sideload_bizzio_image( $product_id, $image_data ) {
		$file_name = ! empty( $image_data['Name'] ) ? $image_data['Name'] : wp_parse_url( $image_data['Uri'], PHP_URL_PATH );
		$attach_id = self::sideload( $image_data['Uri'], (string) $file_name, (int) $product_id );

		if ( ! is_wp_error( $attach_id ) ) {
			// Store the Bizzio image ID to prevent duplicates.
			update_post_meta( $attach_id, '_bizzio_image_id', $image_data['ID'] );
		}

		return $attach_id;
	}

	/**
	 * Process and attach images to a product.
	 *
	 * @since    1.1.0
	 * @param    int   $product_id The product ID.
	 * @param    array $files      Array of file data from the API.
	 * @return   void
	 */
	public static function process_product_images( $product_id, $files ) {
		if ( empty( $files ) ) {
			return;
		}

		$gallery_ids        = array();
		$allowed_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );

		foreach ( $files as $file_data ) {
			$file_name = ! empty( $file_data['Name'] ) ? $file_data['Name'] : $file_data['Uri'];
			$extension = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );

			if ( ! in_array( $extension, $allowed_extensions, true ) ) {
				continue;
			}

			// Reuse the attachment if this Bizzio image was imported before.
			$attach_id = self::find_attachment_by_bizzio_id( $file_data['ID'] );

			if ( ! $attach_id ) {
				$attach_id = self::sideload_bizzio_image( $product_id, $file_data );
			}

			if ( ! is_wp_error( $attach_id ) ) {
				$gallery_ids[] = $attach_id;
			}
		}

		if ( ! empty( $gallery_ids ) ) {
			// Set the first image as the featured image.
			set_post_thumbnail( $product_id, $gallery_ids[0] );

			// If there are more images, set them as the gallery.
			if ( count( $gallery_ids ) > 1 ) {
				array_shift( $gallery_ids ); // Remove the featured image from the gallery array.
				update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery_ids ) );
			} else {
				// Ensure the gallery is empty if there's only one image.
				delete_post_meta( $product_id, '_product_image_gallery' );
			}
		}
	}

	/**
	 * Set WooCommerce category thumbnail from a URL.
	 *
	 * The source URL is stored on the attachment, so re-running the import reuses the
	 * existing attachment instead of downloading the same image again.
	 *
	 * @since    1.1.0
	 * @param    int    $term_id    The term ID of the category.
	 * @param    string $image_url  The URL of the image.
	 * @return   void
	 */
	public static function set_category_thumbnail( $term_id, $image_url ) {
		if ( empty( $image_url ) || ! wp_http_validate_url( $image_url ) ) {
			return;
		}

		$attach_id = self::find_attachment_by_meta( '_bizzio_image_url', $image_url );

		if ( ! $attach_id ) {
			$attach_id = self::sideload( $image_url, (string) wp_parse_url( $image_url, PHP_URL_PATH ), 0 );

			if ( is_wp_error( $attach_id ) ) {
				return;
			}

			update_post_meta( $attach_id, '_bizzio_image_url', $image_url );
		}

		update_term_meta( $term_id, 'thumbnail_id', $attach_id );
	}
}
