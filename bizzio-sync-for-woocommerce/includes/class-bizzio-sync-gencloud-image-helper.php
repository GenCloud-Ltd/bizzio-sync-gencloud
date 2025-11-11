<?php
/**
 * The image helper class.
 *
 * Handles all image-related operations for products and categories.
 *
 * @link       https://gencloud.bg
 * @since      1.2.0
 *
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/includes
 */

/**
 * The image helper class.
 *
 * This class defines all image processing functionality.
 *
 * @since      1.2.0
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/includes
 * @author     gencloud <web@gencloud.bg>
 */
class Bizzio_Sync_Gencloud_Image_Helper {

	/**
	 * Find an attachment ID by the Bizzio image ID stored in post meta.
	 *
	 * @since    1.2.0
	 * @param    string $bizzio_id The Bizzio image ID.
	 * @return   int|null The attachment ID if found, otherwise null.
	 */
	public static function find_attachment_by_bizzio_id( $bizzio_id ) {
		global $wpdb;

		// Use direct database query for better performance.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$attachment_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} 
				WHERE meta_key = '_bizzio_image_id' 
				AND meta_value = %s 
				LIMIT 1",
				$bizzio_id
			)
		);

		return $attachment_id ? (int) $attachment_id : null;
	}

	/**
	 * Sideloads an image from Bizzio, attaches it to a product, and sets meta data.
	 *
	 * @since    1.2.0
	 * @param    int   $product_id The product ID.
	 * @param    array $image_data Array containing image 'ID', 'Name', and 'Uri'.
	 * @return   int|WP_Error The new attachment ID or a WP_Error on failure.
	 */
	public static function sideload_bizzio_image( $product_id, $image_data ) {
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$image_url = $image_data['Uri'];

		if ( empty( $image_url ) || ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'invalid_image_url', __( 'Invalid image URL provided.', 'bizzio-sync-for-woocommerce' ) );
		}

		$tmp = download_url( $image_url );
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}

		$file_array = array(
			'name'     => basename( $image_data['Name'] ),
			'tmp_name' => $tmp,
		);

		$attach_id = media_handle_sideload( $file_array, $product_id );

		if ( is_wp_error( $attach_id ) ) {
			wp_delete_file( $file_array['tmp_name'] );
			return $attach_id;
		}

		// Store the Bizzio image ID to prevent duplicates.
		update_post_meta( $attach_id, '_bizzio_image_id', $image_data['ID'] );

		return $attach_id;
	}

	/**
	 * Process and attach images to a product.
	 *
	 * @since    1.2.0
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

			// Check if image already exists.
			$attach_id = self::find_attachment_by_bizzio_id( $file_data['ID'] );

			// If not, sideload it.
			if ( ! $attach_id ) {
				$attach_id = self::sideload_bizzio_image( $product_id, $file_data );
			}

			// If we have a valid attachment ID, add it to our gallery array.
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
	 * @since    1.2.0
	 * @param    int    $term_id    The term ID of the category.
	 * @param    string $image_url  The URL of the image.
	 * @return   void
	 */
	public static function set_category_thumbnail( $term_id, $image_url ) {
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		// Check if the image URL is valid.
		if ( empty( $image_url ) || ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			return;
		}

		// Need to sideload the image.
		$tmp = download_url( $image_url );
		if ( is_wp_error( $tmp ) ) {
			return;
		}

		$file_array = array(
			'name'     => basename( $image_url ),
			'tmp_name' => $tmp,
		);

		// If error storing temporarily, unlink.
		if ( is_wp_error( $tmp ) ) {
			wp_delete_file( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';
		}

		// Do the validation and storage stuff.
		$attach_id = media_handle_sideload( $file_array, 0 );

		if ( ! is_wp_error( $attach_id ) ) {
			update_term_meta( $term_id, 'thumbnail_id', $attach_id );
		}
	}
}
