<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://gencloud.bg
 * @since      1.0.0
 *
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for actions and filters.
 *
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/admin
 * @author     gencloud <web@gencloud.bg>
 */
class Bizzio_Sync_Gencloud_Admin {


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bizzio-sync-gencloud-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bizzio-sync-gencloud-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'bizzio_sync_gencloud_ajax',
			array(
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
				'import_products_nonce'   => wp_create_nonce( 'bizzio_import_products_nonce' ),
				'import_categories_nonce' => wp_create_nonce( 'bizzio_import_categories_nonce' ),
				'test_connection_nonce'   => wp_create_nonce( 'bizzio_test_connection_nonce' ),
			)
		);
	}

	/**
	 * Add options page
	 */
	public function add_plugin_admin_menu() {

		add_menu_page(
			__( 'Bizzio Sync Gencloud', 'bizzio-sync-for-woocommerce' ),
			__( 'Bizzio Sync', 'bizzio-sync-for-woocommerce' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' ),
			'dashicons-cloud',
			6
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Bizzio Sync Settings', 'bizzio-sync-for-woocommerce' ),
			__( 'Settings', 'bizzio-sync-for-woocommerce' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' )
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Bizzio Sync Categories', 'bizzio-sync-for-woocommerce' ),
			__( 'Categories', 'bizzio-sync-for-woocommerce' ),
			'manage_options',
			$this->plugin_name . '-categories',
			array( $this, 'display_categories_page' )
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Bizzio Sync Products', 'bizzio-sync-for-woocommerce' ),
			__( 'Products', 'bizzio-sync-for-woocommerce' ),
			'manage_options',
			$this->plugin_name . '-products',
			array( $this, 'display_products_page' )
		);
	}

	/**
	 * Render the plugin settings page
	 */
	public function display_plugin_admin_page() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/partials/bizzio-sync-gencloud-admin-display.php';
	}

	/**
	 * Render the categories page
	 */
	public function display_categories_page() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/partials/bizzio-sync-gencloud-categories-display.php';
	}

	/**
	 * Render the products page
	 */
	public function display_products_page() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/partials/bizzio-sync-gencloud-products-display.php';
	}

	/**
	 * Setup the settings sections
	 */
	public function setup_sections() {
		add_settings_section(
			'bizzio_sync_gencloud_section',
			__( 'API Settings', 'bizzio-sync-for-woocommerce' ),
			array( $this, 'section_callback' ),
			$this->plugin_name
		);
	}

	public function section_callback() {
		echo '<p>' . esc_html__( 'Enter your Bizzio API credentials and settings below.', 'bizzio-sync-for-woocommerce' ) . '</p>';
	}

	/**
	 * Setup the settings fields
	 */
	public function setup_fields() {
		$fields = array(
			array(
				'uid'         => 'bizzio_api_database',
				'name'        => __( 'API Database', 'bizzio-sync-for-woocommerce' ),
				'type'        => 'text',
				'section'     => 'bizzio_sync_gencloud_section',
				'placeholder' => __( 'Enter API Database', 'bizzio-sync-for-woocommerce' ),
				'helper'      => __( 'The database name for the Bizzio API.', 'bizzio-sync-for-woocommerce' ),
			),
			array(
				'uid'         => 'bizzio_api_username',
				'name'        => __( 'API Username', 'bizzio-sync-for-woocommerce' ),
				'type'        => 'text',
				'section'     => 'bizzio_sync_gencloud_section',
				'placeholder' => __( 'Enter API Username', 'bizzio-sync-for-woocommerce' ),
				'helper'      => __( 'The username for the Bizzio API.', 'bizzio-sync-for-woocommerce' ),
			),
			array(
				'uid'         => 'bizzio_api_password',
				'name'        => __( 'API Password', 'bizzio-sync-for-woocommerce' ),
				'type'        => 'password',
				'section'     => 'bizzio_sync_gencloud_section',
				'placeholder' => __( 'Enter API Password', 'bizzio-sync-for-woocommerce' ),
				'helper'      => __( 'The password for the Bizzio API.', 'bizzio-sync-for-woocommerce' ),
			),
			array(
				'uid'         => 'bizzio_id_site',
				'name'        => __( 'ID Site', 'bizzio-sync-for-woocommerce' ),
				'type'        => 'text',
				'section'     => 'bizzio_sync_gencloud_section',
				'placeholder' => __( 'Enter ID Site', 'bizzio-sync-for-woocommerce' ),
				'helper'      => __( 'The ID Site for the Bizzio API.', 'bizzio-sync-for-woocommerce' ),
			),
		);

		foreach ( $fields as $field ) {
			add_settings_field(
				$field['uid'],
				$field['name'],
				array( $this, 'field_callback' ),
				$this->plugin_name,
				$field['section'],
				array(
					'uid'         => $field['uid'],
					'type'        => $field['type'],
					'placeholder' => $field['placeholder'],
					'helper'      => $field['helper'],
				)
			);

			$sanitize_callback = array( $this, 'sanitize_setting' );
			if ( isset( $field['type'] ) && 'password' === $field['type'] ) {
				$sanitize_callback = array( $this, 'sanitize_password' );
			}
			register_setting( $this->plugin_name, $field['uid'], array( 'sanitize_callback' => $sanitize_callback ) );
		}
	}

	public function field_callback( $arguments ) {
		$uid         = esc_attr( $arguments['uid'] );
		$type        = esc_attr( isset( $arguments['type'] ) ? $arguments['type'] : 'text' );
		$placeholder = esc_attr( $arguments['placeholder'] );

		$allowed_html = array(
			'input' => array(
				'id'           => array(),
				'name'         => array(),
				'type'         => array(),
				'placeholder'  => array(),
				'value'        => array(),
				'autocomplete' => array(),
			),
			'p'     => array(
				'class' => array(),
			),
		);

		$output = '';

		if ( 'password' === $type ) {
			$value            = get_option( $arguments['uid'] );
			$placeholder_text = ! empty( $value ) ? '********' : $placeholder;
			$output           = sprintf(
				'<input id="%s" name="%s" type="password" placeholder="%s" value="" autocomplete="new-password" />',
				$uid,
				$uid,
				$placeholder_text
			);
		} else {
			$value  = esc_attr( get_option( $arguments['uid'] ) );
			$output = sprintf(
				'<input id="%s" name="%s" type="%s" placeholder="%s" value="%s" />',
				$uid,
				$uid,
				$type,
				$placeholder,
				$value
			);
		}

		if ( ! empty( $arguments['helper'] ) ) {
			$output .= sprintf( '<p class="description">%s</p>', esc_html( $arguments['helper'] ) );
		}

		echo wp_kses( $output, $allowed_html );
	}

	/**
	 * Sanitize a string from user input.
	 *
	 * @since 1.0.0
	 * @param string $input The input string.
	 * @return string The sanitized string.
	 */
	public function sanitize_setting( $input ) {
		return sanitize_text_field( $input );
	}

	/**
	 * Sanitize a password field from user input.
	 *
	 * @since 1.0.2
	 * @param string $input The input string.
	 * @return string The sanitized string.
	 */
	public function sanitize_password( $input ) {
		return trim( $input );
	}

	/**
	 * Handle AJAX request for importing products
	 */
	public function import_products_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce' ) ), 403 );
		}
		check_ajax_referer( 'bizzio_import_products_nonce', 'security' );

		$api_client         = new Bizzio_Sync_Gencloud_Api_Get_Articles();
		$articles_to_import = $api_client->fetch();

		if ( is_wp_error( $articles_to_import ) ) {
			wp_send_json_error(
				array(
					'message'  => $articles_to_import->get_error_message(),
					'response' => $articles_to_import->get_error_data(),
				)
			);
		}

		$total_articles = count( $articles_to_import );

		// Store articles and reset progress counters.
		update_option( 'bizzio_sync_gencloud_articles_to_import', $articles_to_import );
		update_option( 'bizzio_sync_gencloud_total_articles', $total_articles );
		update_option( 'bizzio_sync_gencloud_import_progress', 0 );
		update_option( 'bizzio_sync_gencloud_imported_count', 0 );
		update_option( 'bizzio_sync_gencloud_created_count', 0 );
		update_option( 'bizzio_sync_gencloud_updated_count', 0 );
		update_option( 'bizzio_sync_gencloud_failed_count', 0 );
		update_option( 'bizzio_sync_gencloud_import_status', $total_articles > 0 ? 'in_progress' : 'idle' );

		if ( $total_articles > 0 ) {
			wp_send_json_success(
				array(
					/* translators: %d: number of products */
					'message'        => sprintf( esc_html__( 'Fetched %d products. Ready to import.', 'bizzio-sync-for-woocommerce' ), $total_articles ),
					'total_articles' => $total_articles,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => esc_html__( 'No products found to import.', 'bizzio-sync-for-woocommerce' ) ) );
		}
	}

	/**
	 * Handle AJAX request for processing a batch of products.
	 */
	public function process_product_batch_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce' ) ), 403 );
		}

		check_ajax_referer( 'bizzio_import_products_nonce', 'security' );

		$articles_to_import = get_option( 'bizzio_sync_gencloud_articles_to_import' );
		$current_progress   = get_option( 'bizzio_sync_gencloud_import_progress', 0 );
		$total_articles     = get_option( 'bizzio_sync_gencloud_total_articles', 0 );
		$imported_count     = get_option( 'bizzio_sync_gencloud_imported_count', 0 );
		$created_count      = get_option( 'bizzio_sync_gencloud_created_count', 0 );
		$updated_count      = get_option( 'bizzio_sync_gencloud_updated_count', 0 );
		$failed_count       = get_option( 'bizzio_sync_gencloud_failed_count', 0 );

		$batch_size                  = 10;
		$articles_processed_in_batch = 0;

		if ( empty( $articles_to_import ) || $current_progress >= $total_articles ) {
			update_option( 'bizzio_sync_gencloud_import_status', 'completed' );
			wp_send_json_success(
				array(
					'message'  => esc_html__( 'Product import complete.', 'bizzio-sync-for-woocommerce' ),
					'status'   => 'completed',
					'imported' => $imported_count,
					'created'  => $created_count,
					'updated'  => $updated_count,
					'failed'   => $failed_count,
				)
			);
		}

		for ( $i = 0; $i < $batch_size && $current_progress < $total_articles; $i++ ) {
			$article_data = $articles_to_import[ $current_progress ];

			$product_name        = $article_data['Name'];
			$product_barcode     = $article_data['Barcode'];
			$product_price       = $article_data['P_Sale'];
			$product_qty         = $article_data['Qty'];
			$product_description = $article_data['Description'];

			// Check for existing product by SKU, which is more efficient and HPOS-compatible.
			$product_id = wc_get_product_id_by_sku( $product_barcode );

			$product_post_data = array(
				'post_title'   => $product_name,
				'post_content' => $product_description,
				'post_status'  => 'publish',
				'post_type'    => 'product',
			);

			$product = $product_id ? wc_get_product( $product_id ) : new WC_Product_Simple();

			if ( ! $product ) {
				$new_product_id = new WP_Error( 'product_not_found', 'Product not found' );
			} else {
				$product->set_name( $product_name );
				$product->set_description( $product_description );
				$product->set_status( 'publish' );
				$product->set_sku( $product_barcode );
				$product->set_price( $product_price );
				$product->set_regular_price( $product_price );
				$product->set_manage_stock( 'yes' );
				$product->set_stock_quantity( $product_qty );
				$product->set_stock_status( $product_qty > 0 ? 'instock' : 'outofstock' );
				$product->update_meta_data( '_bizzio_barcode', $product_barcode );

				$new_product_id = $product->save();

				if ( $new_product_id ) {
					if ( $product_id ) {
						++$updated_count;
					} else {
						++$created_count;
					}
				}
			}

			if ( ! is_wp_error( $new_product_id ) ) {
				++$imported_count;

				// Handle product categories.
				if ( ! empty( $article_data['SiteArticles'] ) ) {
					foreach ( $article_data['SiteArticles'] as $id_site_group ) {
						$term = get_term_by( 'slug', sanitize_title( $id_site_group ), 'product_cat' );
						if ( $term ) {
							wp_set_object_terms( $new_product_id, (int) $term->term_id, 'product_cat', true );
						}
					}
				}

				// Handle product images using Image Helper.
				if ( ! empty( $article_data['Files'] ) ) {
					Bizzio_Sync_Gencloud_Image_Helper::process_product_images( $new_product_id, $article_data['Files'] );
				}
			} else {
				++$failed_count;
			}
			++$current_progress;
			++$articles_processed_in_batch;
		}

		update_option( 'bizzio_sync_gencloud_import_progress', $current_progress );
		update_option( 'bizzio_sync_gencloud_imported_count', $imported_count );
		update_option( 'bizzio_sync_gencloud_created_count', $created_count );
		update_option( 'bizzio_sync_gencloud_updated_count', $updated_count );
		update_option( 'bizzio_sync_gencloud_failed_count', $failed_count );

		$status = ( $current_progress >= $total_articles ) ? 'completed' : 'in_progress';
		update_option( 'bizzio_sync_gencloud_import_status', $status );

		wp_send_json_success(
			array(
				/* translators: 1: processed count, 2: imported count, 3: failed count */
				'message'        => sprintf( esc_html__( 'Processed %1$d products. Total imported: %2$d, Total failed: %3$d', 'bizzio-sync-for-woocommerce' ), $articles_processed_in_batch, $imported_count, $failed_count ),
				'status'         => $status,
				'progress'       => $current_progress,
				'total_articles' => $total_articles,
				'imported'       => $imported_count,
				'created'        => $created_count,
				'updated'        => $updated_count,
				'failed'         => $failed_count,
			)
		);
	}

	/**
	 * Handle AJAX request for getting product import progress.
	 */
	public function get_import_progress_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce' ) ), 403 );
		}
		check_ajax_referer( 'bizzio_import_products_nonce', 'security' ); // Using the same nonce for simplicity

		$current_progress = get_option( 'bizzio_sync_gencloud_import_progress', 0 );
		$total_articles   = get_option( 'bizzio_sync_gencloud_total_articles', 0 );
		$imported_count   = get_option( 'bizzio_sync_gencloud_imported_count', 0 );
		$created_count    = get_option( 'bizzio_sync_gencloud_created_count', 0 );
		$updated_count    = get_option( 'bizzio_sync_gencloud_updated_count', 0 );
		$failed_count     = get_option( 'bizzio_sync_gencloud_failed_count', 0 );
		$import_status    = get_option( 'bizzio_sync_gencloud_import_status', 'idle' );

		wp_send_json_success(
			array(
				'progress'       => $current_progress,
				'total_articles' => $total_articles,
				'imported'       => $imported_count,
				'created'        => $created_count,
				'updated'        => $updated_count,
				'failed'         => $failed_count,
				'status'         => $import_status,
			)
		);
	}

	/**
	 * Handle AJAX request for testing API connection
	 */
	public function test_connection_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce' ) ), 403 );
		}
		check_ajax_referer( 'bizzio_test_connection_nonce', 'security' );

		$api_client = new Bizzio_Sync_Gencloud_Api_Get_Site_Groups();
		$response   = $api_client->test_connection();

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'message'  => $response->get_error_message(),
					'response' => is_callable( array( $response, 'get_error_data' ) ) ? $response->get_error_data() : '',
				)
			);
		}

		wp_send_json_success( array( 'message' => esc_html__( 'Connection successful!', 'bizzio-sync-for-woocommerce' ) ) );
	}

	/**
	 * Handle AJAX request for importing categories
	 */
	public function import_categories_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce' ) ), 403 );
		}
		check_ajax_referer( 'bizzio_import_categories_nonce', 'security' );

		$api_client           = new Bizzio_Sync_Gencloud_Api_Get_Site_Groups();
		$categories_to_import = $api_client->fetch( true );

		if ( is_wp_error( $categories_to_import ) ) {
			wp_send_json_error(
				array(
					'message'  => $categories_to_import->get_error_message(),
					'response' => $categories_to_import->get_error_data(),
				)
			);
		}

		$total_categories = count( $categories_to_import );

		// Store categories and reset progress counters.
		update_option( 'bizzio_sync_gencloud_categories_to_import', $categories_to_import );
		update_option( 'bizzio_sync_gencloud_total_categories', $total_categories );
		update_option( 'bizzio_sync_gencloud_category_import_progress', 0 );
		update_option( 'bizzio_sync_gencloud_category_imported_count', 0 );
		update_option( 'bizzio_sync_gencloud_category_failed_count', 0 );
		update_option( 'bizzio_sync_gencloud_category_import_status', $total_categories > 0 ? 'in_progress' : 'idle' );

		if ( $total_categories > 0 ) {
			wp_send_json_success(
				array(
					/* translators: %d: number of categories */
					'message'          => sprintf( esc_html__( 'Fetched %d categories. Ready to import.', 'bizzio-sync-for-woocommerce' ), $total_categories ),
					'total_categories' => $total_categories,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => esc_html__( 'No categories found to import.', 'bizzio-sync-for-woocommerce' ) ) );
		}
	}

	/**
	 * Handle AJAX request for processing a batch of categories.
	 */
	public function process_category_batch_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce' ) ), 403 );
		}
		check_ajax_referer( 'bizzio_import_categories_nonce', 'security' );

		$categories_to_import = get_option( 'bizzio_sync_gencloud_categories_to_import' );
		$current_progress     = get_option( 'bizzio_sync_gencloud_category_import_progress', 0 );
		$total_categories     = get_option( 'bizzio_sync_gencloud_total_categories', 0 );
		$imported_count       = get_option( 'bizzio_sync_gencloud_category_imported_count', 0 );
		$failed_count         = get_option( 'bizzio_sync_gencloud_category_failed_count', 0 );

		$batch_size                    = 10;
		$categories_processed_in_batch = 0;

		if ( empty( $categories_to_import ) || $current_progress >= $total_categories ) {
			update_option( 'bizzio_sync_gencloud_category_import_status', 'completed' );
			wp_send_json_success(
				array(
					'message'  => esc_html__( 'Category import complete.', 'bizzio-sync-for-woocommerce' ),
					'status'   => 'completed',
					'imported' => $imported_count,
					'failed'   => $failed_count,
				)
			);
		}

		for ( $i = 0; $i < $batch_size && $current_progress < $total_categories; $i++ ) {
			$category_data = $categories_to_import[ $current_progress ];

			$category_name        = $category_data['Name'];
			$category_id          = $category_data['ID'];
			$parent_id            = $category_data['ID_Parent'];
			$category_description = $category_data['Note'];
			$category_image_url   = $category_data['Image'];

			$parent_term_id = 0;
			if ( ! empty( $parent_id ) ) {
				// Bizzio parent IDs might not exist yet, so we store them to process later.
				// For now, we check if a term with that Bizzio ID as slug exists.
				$parent_term = get_term_by( 'slug', sanitize_title( $parent_id ), 'product_cat' );
				if ( $parent_term ) {
					$parent_term_id = $parent_term->term_id;
				}
			}

			$term = get_term_by( 'slug', sanitize_title( $category_id ), 'product_cat' );

			if ( $term ) {
				// Update existing category.
				$term_id_result = wp_update_term(
					$term->term_id,
					'product_cat',
					array(
						'name'        => $category_name,
						'description' => $category_description,
						'parent'      => $parent_term_id,
					)
				);
			} else {
				// Create new category.
				$term_id_result = wp_insert_term(
					$category_name,
					'product_cat',
					array(
						'description' => $category_description,
						'slug'        => sanitize_title( $category_id ),
						'parent'      => $parent_term_id,
					)
				);
			}

			if ( ! is_wp_error( $term_id_result ) ) {
				++$imported_count;
				$new_term_id = is_array( $term_id_result ) ? $term_id_result['term_id'] : $term_id_result;

				// Download and set category thumbnail using Image Helper.
				if ( ! empty( $category_image_url ) ) {
					Bizzio_Sync_Gencloud_Image_Helper::set_category_thumbnail( $new_term_id, $category_image_url );
				}
			} else {
				++$failed_count;
			}
			++$current_progress;
			++$categories_processed_in_batch;
		}

		update_option( 'bizzio_sync_gencloud_category_import_progress', $current_progress );
		update_option( 'bizzio_sync_gencloud_category_imported_count', $imported_count );
		update_option( 'bizzio_sync_gencloud_category_failed_count', $failed_count );

		$status = ( $current_progress >= $total_categories ) ? 'completed' : 'in_progress';
		update_option( 'bizzio_sync_gencloud_category_import_status', $status );

		wp_send_json_success(
			array(
				/* translators: 1: processed count, 2: imported count, 3: failed count */
				'message'          => sprintf( esc_html__( 'Processed %1$d categories. Total imported: %2$d, Total failed: %3$d', 'bizzio-sync-for-woocommerce' ), $categories_processed_in_batch, $imported_count, $failed_count ),
				'status'           => $status,
				'progress'         => $current_progress,
				'total_categories' => $total_categories,
				'imported'         => $imported_count,
				'failed'           => $failed_count,
			)
		);
	}

	/**
	 * Handle AJAX request for getting category import progress.
	 */
	public function get_category_import_progress_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce' ) ), 403 );
		}
		check_ajax_referer( 'bizzio_import_categories_nonce', 'security' );

		$current_progress = get_option( 'bizzio_sync_gencloud_category_import_progress', 0 );
		$total_categories = get_option( 'bizzio_sync_gencloud_total_categories', 0 );
		$imported_count   = get_option( 'bizzio_sync_gencloud_category_imported_count', 0 );
		$failed_count     = get_option( 'bizzio_sync_gencloud_category_failed_count', 0 );
		$import_status    = get_option( 'bizzio_sync_gencloud_category_import_status', 'idle' );

		wp_send_json_success(
			array(
				'progress'         => $current_progress,
				'total_categories' => $total_categories,
				'imported'         => $imported_count,
				'failed'           => $failed_count,
				'status'           => $import_status,
			)
		);
	}
}
