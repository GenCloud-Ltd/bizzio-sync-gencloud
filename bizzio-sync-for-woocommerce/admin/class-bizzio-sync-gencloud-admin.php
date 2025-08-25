<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://incloud.bg
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
class Bizzio_Sync_Gencloud_Admin
{

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/bizzio-sync-gencloud-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/bizzio-sync-gencloud-admin.js', array('jquery'), $this->version, false);
		wp_localize_script($this->plugin_name, 'bizzio_sync_gencloud_ajax', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'import_products_nonce' => wp_create_nonce('bizzio_import_products_nonce'),
			'import_categories_nonce' => wp_create_nonce('bizzio_import_categories_nonce'),
			'test_connection_nonce' => wp_create_nonce('bizzio_test_connection_nonce')
		));
	}

	/**
	 * Add options page
	 */
	public function add_plugin_admin_menu()
	{

		add_menu_page(
			__('Bizzio Sync Gencloud', 'bizzio-sync-for-woocommerce'),
			__('Bizzio Sync', 'bizzio-sync-for-woocommerce'),
			'manage_options',
			$this->plugin_name,
			array($this, 'display_plugin_admin_page'),
			'dashicons-cloud',
			6
		);

		add_submenu_page(
			$this->plugin_name,
			__('Bizzio Sync Settings', 'bizzio-sync-for-woocommerce'),
			__('Settings', 'bizzio-sync-for-woocommerce'),
			'manage_options',
			$this->plugin_name,
			array($this, 'display_plugin_admin_page')
		);

		add_submenu_page(
			$this->plugin_name,
			__('Bizzio Sync Categories', 'bizzio-sync-for-woocommerce'),
			__('Categories', 'bizzio-sync-for-woocommerce'),
			'manage_options',
			$this->plugin_name . '-categories',
			array($this, 'display_categories_page')
		);

		add_submenu_page(
			$this->plugin_name,
			__('Bizzio Sync Products', 'bizzio-sync-for-woocommerce'),
			__('Products', 'bizzio-sync-for-woocommerce'),
			'manage_options',
			$this->plugin_name . '-products',
			array($this, 'display_products_page')
		);
	}

	/**
	 * Render the plugin settings page
	 */
	public function display_plugin_admin_page()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/bizzio-sync-gencloud-admin-display.php';
	}

	/**
	 * Render the categories page
	 */
	public function display_categories_page()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/bizzio-sync-gencloud-categories-display.php';
	}

	/**
	 * Render the products page
	 */
	public function display_products_page()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/bizzio-sync-gencloud-products-display.php';
	}

	/**
	 * Setup the settings sections
	 */
	public function setup_sections()
	{
		add_settings_section(
			'bizzio_sync_gencloud_section',
			__('API Settings', 'bizzio-sync-for-woocommerce'),
			array($this, 'section_callback'),
			$this->plugin_name
		);
	}

	public function section_callback()
	{
		echo '<p>' . esc_html__('Enter your Bizzio API credentials and settings below.', 'bizzio-sync-for-woocommerce') . '</p>';
	}

	/**
	 * Setup the settings fields
	 */
	public function setup_fields()
	{
		$fields = array(
			array(
				'uid' => 'bizzio_api_database',
				'name' => __('API Database', 'bizzio-sync-for-woocommerce'),
				'type' => 'text',
				'section' => 'bizzio_sync_gencloud_section',
				'placeholder' => __('Enter API Database', 'bizzio-sync-for-woocommerce'),
				'helper' => __('The database name for the Bizzio API.', 'bizzio-sync-for-woocommerce'),
			),
			array(
				'uid' => 'bizzio_api_username',
				'name' => __('API Username', 'bizzio-sync-for-woocommerce'),
				'type' => 'text',
				'section' => 'bizzio_sync_gencloud_section',
				'placeholder' => __('Enter API Username', 'bizzio-sync-for-woocommerce'),
				'helper' => __('The username for the Bizzio API.', 'bizzio-sync-for-woocommerce'),
			),
			array(
				'uid' => 'bizzio_api_password',
				'name' => __('API Password', 'bizzio-sync-for-woocommerce'),
				'type' => 'password',
				'section' => 'bizzio_sync_gencloud_section',
				'placeholder' => __('Enter API Password', 'bizzio-sync-for-woocommerce'),
				'helper' => __('The password for the Bizzio API.', 'bizzio-sync-for-woocommerce'),
			),
			array(
				'uid' => 'bizzio_id_site',
				'name' => __('ID Site', 'bizzio-sync-for-woocommerce'),
				'type' => 'text',
				'section' => 'bizzio_sync_gencloud_section',
				'placeholder' => __('Enter ID Site', 'bizzio-sync-for-woocommerce'),
				'helper' => __('The ID Site for the Bizzio API.', 'bizzio-sync-for-woocommerce'),
			),
		);

		foreach ($fields as $field) {
			add_settings_field(
				$field['uid'],
				$field['name'],
				array($this, 'field_callback'),
				$this->plugin_name,
				$field['section'],
				array(
					'uid' => $field['uid'],
					'type' => $field['type'],
					'placeholder' => $field['placeholder'],
					'helper' => $field['helper'],
				)
			);

			$sanitize_callback = array($this, 'sanitize_setting');
			if (isset($field['type']) && 'password' === $field['type']) {
				$sanitize_callback = array($this, 'sanitize_password');
			}
			register_setting($this->plugin_name, $field['uid'], array('sanitize_callback' => $sanitize_callback));
		}
	}

	public function field_callback($arguments)
	{
		$uid         = esc_attr($arguments['uid']);
		$type        = esc_attr(isset($arguments['type']) ? $arguments['type'] : 'text');
		$placeholder = esc_attr($arguments['placeholder']);

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

		if ('password' === $type) {
			$value = get_option($arguments['uid']);
			$placeholder_text = !empty($value) ? '********' : $placeholder;
			$output = sprintf(
				'<input id="%s" name="%s" type="password" placeholder="%s" value="" autocomplete="new-password" />',
				$uid,
				$uid,
				$placeholder_text
			);
		} else {
			$value = esc_attr(get_option($arguments['uid']));
			$output = sprintf(
				'<input id="%s" name="%s" type="%s" placeholder="%s" value="%s" />',
				$uid,
				$uid,
				$type,
				$placeholder,
				$value
			);
		}

		if (! empty($arguments['helper'])) {
			$output .= sprintf('<p class="description">%s</p>', esc_html($arguments['helper']));
		}

		echo wp_kses($output, $allowed_html);
	}

	/**
	 * Sanitize a string from user input.
	 *
	 * @since 1.0.0
	 * @param string $input The input string.
	 * @return string The sanitized string.
	 */
	public function sanitize_setting($input)
	{
		return sanitize_text_field($input);
	}

	/**
	 * Sanitize a password field from user input.
	 *
	 * @since 1.0.2
	 * @param string $input The input string.
	 * @return string The sanitized string.
	 */
	public function sanitize_password($input)
	{
		return trim($input);
	}

	/**
	 * Handle AJAX request for importing products
	 */
	public function import_products_callback()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce')), 403);
		}
		check_ajax_referer('bizzio_import_products_nonce', 'security');

		$api_database = get_option('bizzio_api_database');
		$api_username = get_option('bizzio_api_username');
		$api_password = get_option('bizzio_api_password');
		$id_site = get_option('bizzio_id_site');

		$soap_action = 'http://tempuri.org/IRiznShopExtService/GetArticles';
		$request_body = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
			<soapenv:Header>
				<tem:Authentication xmlns:tem="http://tempuri.org/">
					<biz:Database xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $api_database . '</biz:Database>
					<biz:Username xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $api_username . '</biz:Username>
					<biz:Password xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $api_password . '</biz:Password>
				</tem:Authentication>
			</soapenv:Header>
			<soapenv:Body>
				<tem:GetArticlesRequest xmlns:tem="http://tempuri.org/">
					<tem:AvailableOnly>false</tem:AvailableOnly>
					<tem:Barcodes xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>
					<tem:Currency xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>
					<tem:ID_Site>' . $id_site . '</tem:ID_Site>
					<tem:IsCars>false</tem:IsCars>
					<tem:IsFiles>true</tem:IsFiles>
					<tem:IsQtyByWarehouses>false</tem:IsQtyByWarehouses>
				</tem:GetArticlesRequest>
			</soapenv:Body>
		</soapenv:Envelope>';

		$response = $this->_make_soap_request($soap_action, $request_body);

		if (is_wp_error($response)) {
			/* translators: %s: error message */
			wp_send_json_error(array('message' => sprintf(esc_html__('Error fetching products: %s', 'bizzio-sync-for-woocommerce'), $response->get_error_message())));
		}

		$body = wp_remote_retrieve_body($response);
		$xml = simplexml_load_string($body);

		if (false === $xml) {
			wp_send_json_error(array('message' => esc_html__('Failed to parse XML response for products.', 'bizzio-sync-for-woocommerce')));
		}

		$responseElement = $this->_process_bizzio_response($xml, 'products');

		if (is_wp_error($responseElement)) {
			wp_send_json_error(array('message' => $responseElement->get_error_message(), 'response' => $responseElement->get_error_data()));
		}

		$namespaces = $xml->getNamespaces(true);
		$articles_to_import = array();

		if (isset($responseElement->Articles) && isset($responseElement->Articles->children($namespaces['a'])->AI)) {
			foreach ($responseElement->Articles->children($namespaces['a'])->AI as $article) {
				$article_data = array(
					'Name' => (string)$article->Name,
					'Barcode' => (string)$article->Barcode,
					'P_Sale' => (float)$article->P_Sale,
					'Qty' => (int)$article->Qty,
					'Description' => '',
					'SiteArticles' => array(),
					'SiteProps' => array()
				);

				if (isset($article->Props)) {
					$props = $article->Props->children($namespaces['b']);
					foreach ($props as $prop) {
						if (strpos((string)$prop, '<p>') !== false) {
							$article_data['Description'] = (string)$prop;
							break;
						}
					}
				}

				if (isset($article->SiteArticles)) {
					$site_articles = $article->SiteArticles->children($namespaces['a']);
					foreach ($site_articles as $site_article) {
						$article_data['SiteArticles'][] = (string)$site_article->ID_SiteGroup;
					}
				}

				if (isset($article->SiteProps)) {
					$site_props = $article->SiteProps->children($namespaces['a']);
					foreach ($site_props as $site_prop) {
						if (isset($site_prop->Val) && strpos((string)$site_prop->Val, 'http') === 0) {
							$article_data['SiteProps'][] = (string)$site_prop->Val;
						}
					}
				}

				$articles_to_import[] = $article_data;
			}
		}

		$total_articles = count($articles_to_import);

		// Store articles and reset progress counters
		update_option('bizzio_sync_gencloud_articles_to_import', $articles_to_import);
		update_option('bizzio_sync_gencloud_total_articles', $total_articles);
		update_option('bizzio_sync_gencloud_import_progress', 0);
		update_option('bizzio_sync_gencloud_imported_count', 0);
		update_option('bizzio_sync_gencloud_failed_count', 0);
		update_option('bizzio_sync_gencloud_import_status', $total_articles > 0 ? 'in_progress' : 'idle');

		if ($total_articles > 0) {
			/* translators: %d: number of products */
			wp_send_json_success(array('message' => sprintf(esc_html__('Fetched %d products. Ready to import.', 'bizzio-sync-for-woocommerce'), $total_articles), 'total_articles' => $total_articles));
		} else {
			wp_send_json_error(array('message' => esc_html__('No products found to import.', 'bizzio-sync-for-woocommerce')));
		}
	}

	/**
	 * Handle AJAX request for processing a batch of products.
	 */
	public function process_product_batch_callback()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce')), 403);
		}

		check_ajax_referer('bizzio_import_products_nonce', 'security');

		$articles_to_import = get_option('bizzio_sync_gencloud_articles_to_import');
		$current_progress = get_option('bizzio_sync_gencloud_import_progress', 0);
		$total_articles = get_option('bizzio_sync_gencloud_total_articles', 0);
		$imported_count = get_option('bizzio_sync_gencloud_imported_count', 0);
		$failed_count = get_option('bizzio_sync_gencloud_failed_count', 0);

		$batch_size = 10; // Process 10 products at a time
		$articles_processed_in_batch = 0;

		if (empty($articles_to_import) || $current_progress >= $total_articles) {
			update_option('bizzio_sync_gencloud_import_status', 'completed');
			wp_send_json_success(array(
				'message' => esc_html__('Product import complete.', 'bizzio-sync-for-woocommerce'),
				'status' => 'completed',
				'imported' => $imported_count,
				'failed' => $failed_count,
			));
		}

		for ($i = 0; $i < $batch_size && $current_progress < $total_articles; $i++) {
			$article_data = $articles_to_import[$current_progress];

			$product_name = $article_data['Name'];
			$product_barcode = $article_data['Barcode'];
			$product_price = $article_data['P_Sale'];
			$product_qty = $article_data['Qty'];
			$product_description = $article_data['Description'];

			// Check if product exists by barcode
			$product_id = 0;
			if (function_exists('wc_get_products')) {
				$products = wc_get_products(array('sku' => $product_barcode, 'limit' => 1));
				if (!empty($products)) {
					$product_id = $products[0]->get_id();
				}
			} elseif (function_exists('wc_get_product_id_by_sku')) {
				$product_id = wc_get_product_id_by_sku($product_barcode);
			}

			$product_post_data = array(
				'post_title'    => $product_name,
				'post_content'  => $product_description,
				'post_status'   => 'publish',
				'post_type'     => 'product',
			);

			if ($product_id) {
				// Update existing product
				$product_post_data['ID'] = $product_id;
				$new_product_id = wp_update_post($product_post_data);
				update_post_meta($new_product_id, '_price', $product_price);
				update_post_meta($new_product_id, '_regular_price', $product_price);
				if (function_exists('wc_update_product_stock')) {
					wc_update_product_stock($new_product_id, $product_qty);
				} else {
					update_post_meta($new_product_id, '_stock', $product_qty);
				}
			} else {
				// Create new product
				$new_product_id = wp_insert_post($product_post_data);
				if (! is_wp_error($new_product_id)) {
					update_post_meta($new_product_id, '_sku', $product_barcode);
					update_post_meta($new_product_id, '_price', $product_price);
					update_post_meta($new_product_id, '_regular_price', $product_price);
					update_post_meta($new_product_id, '_manage_stock', 'yes');
					update_post_meta($new_product_id, '_stock', $product_qty);
					update_post_meta($new_product_id, '_stock_status', ($product_qty > 0) ? 'instock' : 'outofstock');
				}
			}

			if (! is_wp_error($new_product_id)) {
				$imported_count++;

				// Handle product categories
				if (!empty($article_data['SiteArticles'])) {
					foreach ($article_data['SiteArticles'] as $id_site_group) {
						$term = get_term_by('slug', sanitize_title($id_site_group), 'product_cat');
						if ($term) {
							wp_set_object_terms($new_product_id, (int) $term->term_id, 'product_cat', true);
						}
					}
				}

				// Handle product images
				if (!empty($article_data['SiteProps'])) {
					foreach ($article_data['SiteProps'] as $image_url) {
						if (!empty($image_url) && (strpos($image_url, '.jpg') !== false || strpos($image_url, '.png') !== false)) {
							$this->_set_woocommerce_product_image($new_product_id, $image_url);
							break; // Assuming first image is the main one
						}
					}
				}
			} else {
				$failed_count++;
			}
			$current_progress++;
			$articles_processed_in_batch++;
		}

		update_option('bizzio_sync_gencloud_import_progress', $current_progress);
		update_option('bizzio_sync_gencloud_imported_count', $imported_count);
		update_option('bizzio_sync_gencloud_failed_count', $failed_count);

		$status = ($current_progress >= $total_articles) ? 'completed' : 'in_progress';
		update_option('bizzio_sync_gencloud_import_status', $status);

		wp_send_json_success(array(
			/* translators: 1: processed count, 2: imported count, 3: failed count */
			'message' => sprintf(esc_html__('Processed %1$d products. Total imported: %2$d, Total failed: %3$d', 'bizzio-sync-for-woocommerce'), $articles_processed_in_batch, $imported_count, $failed_count),
			'status' => $status,
			'progress' => $current_progress,
			'total_articles' => $total_articles,
			'imported' => $imported_count,
			'failed' => $failed_count,
		));
	}

	/**
	 * Handle AJAX request for getting product import progress.
	 */
	public function get_import_progress_callback()
	{
		if (! current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce')), 403);
		}
		check_ajax_referer('bizzio_import_products_nonce', 'security'); // Using the same nonce for simplicity

		$current_progress = get_option('bizzio_sync_gencloud_import_progress', 0);
		$total_articles = get_option('bizzio_sync_gencloud_total_articles', 0);
		$imported_count = get_option('bizzio_sync_gencloud_imported_count', 0);
		$failed_count = get_option('bizzio_sync_gencloud_failed_count', 0);
		$import_status = get_option('bizzio_sync_gencloud_import_status', 'idle');

		wp_send_json_success(array(
			'progress' => $current_progress,
			'total_articles' => $total_articles,
			'imported' => $imported_count,
			'failed' => $failed_count,
			'status' => $import_status,
		));
	}

	/**
	 * Handle AJAX request for testing API connection
	 */
	public function test_connection_callback()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce')), 403);
		}
		check_ajax_referer('bizzio_test_connection_nonce', 'security');

		$api_database = get_option('bizzio_api_database');
		$api_username = get_option('bizzio_api_username');
		$api_password = get_option('bizzio_api_password');
		$id_site = get_option('bizzio_id_site');

		$soap_action = 'http://tempuri.org/IRiznShopExtService/GetSiteGroups'; // Using GetSiteGroups for connection test
		$request_body = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
			<soapenv:Header>
				<tem:Authentication xmlns:tem="http://tempuri.org/">
					<biz:Database xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $api_database . '</biz:Database>
					<biz:Username xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $api_username . '</biz:Username>
					<biz:Password xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $api_password . '</biz:Password>
				</tem:Authentication>
			</soapenv:Header>
			<soapenv:Body>
				<tem:GetSiteGroupsRequest xmlns:tem="http://tempuri.org/">
					<tem:ID_Site>' . $id_site . '</tem:ID_Site>
                     <tem:IsFiles>false</tem:IsFiles>
				</tem:GetSiteGroupsRequest>
			</soapenv:Body>
		</soapenv:Envelope>';

		$response = $this->_make_soap_request($soap_action, $request_body);

		if (is_wp_error($response)) {
			/* translators: %s: error message */
			wp_send_json_error(array('message' => sprintf(esc_html__('Connection test failed: %s', 'bizzio-sync-for-woocommerce'), $response->get_error_message())));
		}

		$body = wp_remote_retrieve_body($response);
		$xml = simplexml_load_string($body);

		if (false === $xml) {
			wp_send_json_error(array('message' => esc_html__('Connection test failed: Failed to parse XML response.', 'bizzio-sync-for-woocommerce')));
		}

		$responseElement = $this->_process_bizzio_response($xml, 'connection_test');

		if (is_wp_error($responseElement)) {
			wp_send_json_error(array('message' => $responseElement->get_error_message(), 'response' => $responseElement->get_error_data()));
		}


		wp_send_json_success(array('message' => esc_html__('Connection successful!', 'bizzio-sync-for-woocommerce')));
	}

	/**
	 * Process Bizzio API response and handle common errors.
	 *
	 * @param SimpleXMLElement|WP_Error $xml The SimpleXMLElement object or WP_Error on failure.
	 * @param string         $context  Context for error messages (e.g., 'categories', 'products').
	 * @return SimpleXMLElement|WP_Error SimpleXMLElement on success, WP_Error on failure.
	 */
	private function _process_bizzio_response($xml, $context)
	{
		if (is_wp_error($xml)) {
			/* translators: 1: context, 2: error message */
			return new WP_Error('bizzio_api_error', sprintf(esc_html__('Error fetching %1$s: %2$s', 'bizzio-sync-for-woocommerce'), $context, $xml->get_error_message()));
		}

		$namespaces = $xml->getNamespaces(true);
		$body = $xml->children($namespaces['s'])->Body;
		$response_container = $body->children($namespaces['']);

		// Determine the correct response element based on context
		$response_element_name = '';
		if ('categories' === $context || 'connection_test' === $context) {
			$response_element_name = 'GetSiteGroupsResponse';
		} elseif ('products' === $context) {
			$response_element_name = 'GetArticlesResponse';
		}

		$responseElement = $response_container->{$response_element_name};

		if (! $responseElement) {
			/* translators: %s: context */
			return new WP_Error('bizzio_invalid_response', sprintf(esc_html__('Invalid response element for %s.', 'bizzio-sync-for-woocommerce'), $context));
		}

		$error_code = (string) $responseElement->ErrorCode;
		$error_message = (string) $responseElement->ErrorMessage;
		$error_type = (string) $responseElement->ErrorType;

		if ('0' !== $error_code || 'Success' !== $error_type) {
			return new WP_Error(
				'bizzio_api_error',
				sprintf(
					/* translators: 1: context, 2: error message, 3: error code */
					esc_html__('API Error fetching %1$s: %2$s (Code: %3$s). <br> Please contact Bizzio administrator for assistance.', 'bizzio-sync-for-woocommerce'),
					$context,
					$error_message,
					$error_code
				),
				array('response' => $xml->asXML())
			);
		}

		return $responseElement;
	}

	/**
	 * Handle AJAX request for importing categories
	 */
	public function import_categories_callback()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce')), 403);
		}
		check_ajax_referer('bizzio_import_categories_nonce', 'security');

		$api_database = get_option('bizzio_api_database');
		$api_username = get_option('bizzio_api_username');
		$api_password = get_option('bizzio_api_password');
		$id_site = get_option('bizzio_id_site');

		$soap_action = 'http://tempuri.org/IRiznShopExtService/GetSiteGroups';
		$request_body = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
			<soapenv:Header>
				<tem:Authentication xmlns:tem="http://tempuri.org/">
					<biz:Database xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $api_database . '</biz:Database>
					<biz:Username xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $api_username . '</biz:Username>
					<biz:Password xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $api_password . '</biz:Password>
				</tem:Authentication>
			</soapenv:Header>
			<soapenv:Body>
				<tem:GetSiteGroupsRequest xmlns:tem="http://tempuri.org/">
					<tem:ID_Site>' . $id_site . '</tem:ID_Site>
                     <tem:IsFiles>true</tem:IsFiles>
				</tem:GetSiteGroupsRequest>
			</soapenv:Body>
		</soapenv:Envelope>';

		$response = $this->_make_soap_request($soap_action, $request_body);
		$body = wp_remote_retrieve_body($response);
		$xml = simplexml_load_string($body);
		$responseElement = $this->_process_bizzio_response($xml, 'categories');

		if (is_wp_error($responseElement)) {
			wp_send_json_error(array('message' => $responseElement->get_error_message(), 'response' => $responseElement->get_error_data()));
		}

		$namespaces = $xml->getNamespaces(true);
		$categories_to_import = array();

		if (isset($responseElement->SiteGroups) && isset($responseElement->SiteGroups->children($namespaces['a'])->SG)) {
			foreach ($responseElement->SiteGroups->children($namespaces['a'])->SG as $category) {
				$image_url = '';
				if (isset($category->Files) && isset($category->Files->children($namespaces['a'])->FI) && isset($category->Files->children($namespaces['a'])->FI->Uri)) {
					$image_url = (string)$category->Files->children($namespaces['a'])->FI->Uri;
				}

				$categories_to_import[] = array(
					'ID' => (string)$category->ID,
					'ID_Parent' => (string)$category->ID_Parent,
					'Name' => (string)$category->Name,
					'Note' => (string)$category->Note,
					'Image' => $image_url,
				);
			}
		}

		$total_categories = count($categories_to_import);

		// Store categories and reset progress counters
		update_option('bizzio_sync_gencloud_categories_to_import', $categories_to_import);
		update_option('bizzio_sync_gencloud_total_categories', $total_categories);
		update_option('bizzio_sync_gencloud_category_import_progress', 0);
		update_option('bizzio_sync_gencloud_category_imported_count', 0);
		update_option('bizzio_sync_gencloud_category_failed_count', 0);
		update_option('bizzio_sync_gencloud_category_import_status', $total_categories > 0 ? 'in_progress' : 'idle');

		if ($total_categories > 0) {
			/* translators: %d: number of categories */
			wp_send_json_success(array('message' => sprintf(esc_html__('Fetched %d categories. Ready to import.', 'bizzio-sync-for-woocommerce'), $total_categories), 'total_categories' => $total_categories));
		} else {
			wp_send_json_error(array('message' => esc_html__('No categories found to import.', 'bizzio-sync-for-woocommerce')));
		}
	}

	/**
	 * Handle AJAX request for processing a batch of categories.
	 */
	public function process_category_batch_callback()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce')), 403);
		}
		check_ajax_referer('bizzio_import_categories_nonce', 'security');

		$categories_to_import = get_option('bizzio_sync_gencloud_categories_to_import');
		$current_progress = get_option('bizzio_sync_gencloud_category_import_progress', 0);
		$total_categories = get_option('bizzio_sync_gencloud_total_categories', 0);
		$imported_count = get_option('bizzio_sync_gencloud_category_imported_count', 0);
		$failed_count = get_option('bizzio_sync_gencloud_category_failed_count', 0);

		$batch_size = 10; // Process 10 categories at a time
		$categories_processed_in_batch = 0;

		if (empty($categories_to_import) || $current_progress >= $total_categories) {
			update_option('bizzio_sync_gencloud_category_import_status', 'completed');
			wp_send_json_success(array(
				'message' => esc_html__('Category import complete.', 'bizzio-sync-for-woocommerce'),
				'status' => 'completed',
				'imported' => $imported_count,
				'failed' => $failed_count,
			));
		}

		for ($i = 0; $i < $batch_size && $current_progress < $total_categories; $i++) {
			$category_data = $categories_to_import[$current_progress];

			$category_name = $category_data['Name'];
			$category_id = $category_data['ID'];
			$parent_id = $category_data['ID_Parent'];
			$category_description = $category_data['Note'];
			$category_image_url = $category_data['Image'];

			$parent_term_id = 0;
			if (!empty($parent_id)) {
				// Bizzio parent IDs might not exist yet, so we store them to process later.
				// For now, we check if a term with that Bizzio ID as slug exists.
				$parent_term = get_term_by('slug', sanitize_title($parent_id), 'product_cat');
				if ($parent_term) {
					$parent_term_id = $parent_term->term_id;
				}
			}

			$term = get_term_by('slug', sanitize_title($category_id), 'product_cat');

			if ($term) {
				// Update existing category
				$term_id_result = wp_update_term(
					$term->term_id,
					'product_cat',
					array(
						'name' => $category_name,
						'description' => $category_description,
						'parent' => $parent_term_id,
					)
				);
			} else {
				// Create new category
				$term_id_result = wp_insert_term(
					$category_name,
					'product_cat',
					array(
						'description' => $category_description,
						'slug'        => sanitize_title($category_id),
						'parent'      => $parent_term_id,
					)
				);
			}

			if (! is_wp_error($term_id_result)) {
				$imported_count++;
				$new_term_id = is_array($term_id_result) ? $term_id_result['term_id'] : $term_id_result;

				// Download and set category thumbnail
				if (! empty($category_image_url)) {
					$this->_set_woocommerce_category_thumbnail($new_term_id, $category_image_url);
				}
			} else {
				$failed_count++;
			}
			$current_progress++;
			$categories_processed_in_batch++;
		}

		update_option('bizzio_sync_gencloud_category_import_progress', $current_progress);
		update_option('bizzio_sync_gencloud_category_imported_count', $imported_count);
		update_option('bizzio_sync_gencloud_category_failed_count', $failed_count);

		$status = ($current_progress >= $total_categories) ? 'completed' : 'in_progress';
		update_option('bizzio_sync_gencloud_category_import_status', $status);

		wp_send_json_success(array(
			/* translators: 1: processed count, 2: imported count, 3: failed count */
			'message' => sprintf(esc_html__('Processed %1$d categories. Total imported: %2$d, Total failed: %3$d', 'bizzio-sync-for-woocommerce'), $categories_processed_in_batch, $imported_count, $failed_count),
			'status' => $status,
			'progress' => $current_progress,
			'total_categories' => $total_categories,
			'imported' => $imported_count,
			'failed' => $failed_count,
		));
	}

	/**
	 * Handle AJAX request for getting category import progress.
	 */
	public function get_category_import_progress_callback()
	{
		if (! current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'bizzio-sync-for-woocommerce')), 403);
		}
		check_ajax_referer('bizzio_import_categories_nonce', 'security');

		$current_progress = get_option('bizzio_sync_gencloud_category_import_progress', 0);
		$total_categories = get_option('bizzio_sync_gencloud_total_categories', 0);
		$imported_count = get_option('bizzio_sync_gencloud_category_imported_count', 0);
		$failed_count = get_option('bizzio_sync_gencloud_category_failed_count', 0);
		$import_status = get_option('bizzio_sync_gencloud_category_import_status', 'idle');

		wp_send_json_success(array(
			'progress' => $current_progress,
			'total_categories' => $total_categories,
			'imported' => $imported_count,
			'failed' => $failed_count,
			'status' => $import_status,
		));
	}

	/**
	 * Helper function to make SOAP requests
	 *
	 * @param string $soap_action The SOAPAction header.
	 * @param string $request_body The XML request body.
	 * @return array|WP_Error The response array or WP_Error on failure.
	 */
	private function _make_soap_request($soap_action, $request_body)
	{
		$endpoint = 'https://bizzio.gencloud.bg/Services/Extensions/RiznShopExtService.svc';

		if (defined('BIZZIO_SYNC_GENCLOUD_DEBUG_LOG') && BIZZIO_SYNC_GENCLOUD_DEBUG_LOG) {
			$sanitized_request_body = preg_replace('/(<biz:Database>)(.*)(<\/biz:Database>)/i', '$1[REDACTED]$3', $request_body);
			$sanitized_request_body = preg_replace('/(<biz:Username>)(.*)(<\/biz:Username>)/i', '$1[REDACTED]$3', $sanitized_request_body);
			$sanitized_request_body = preg_replace('/(<biz:Password>)(.*)(<\/biz:Password>)/i', '$1[REDACTED]$3', $sanitized_request_body);
		}

		$args = array(
			'body'        => $request_body,
			'headers'     => array(
				'Content-Type' => 'text/xml; charset=utf-8',
				'SOAPAction'   => $soap_action,
			),
			'method'      => 'POST',
			'timeout'     => 60, // seconds
		);

		$response = wp_remote_post($endpoint, $args);

		return $response;
	}

	/**
	 * Helper function to set WooCommerce category thumbnail.
	 *
	 * @param int    $term_id    The term ID of the category.
	 * @param string $image_url  The URL of the image.
	 */
	private function _set_woocommerce_category_thumbnail($term_id, $image_url)
	{
		if (! function_exists('media_handle_sideload')) {
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');
		}

		// Check if the image URL is valid
		if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
			return;
		}

		// Need to sideload the image
		$tmp = download_url($image_url);
		if (is_wp_error($tmp)) {
			return;
		}

		$file_array = array();
		$file_array['name'] = basename($image_url);
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink
		if (is_wp_error($tmp)) {
			wp_delete_file($file_array['tmp_name']);
			$file_array['tmp_name'] = '';
		}

		// do the validation and storage stuff
		$attach_id = media_handle_sideload($file_array, 0);

		if (! is_wp_error($attach_id)) {
			update_term_meta($term_id, 'thumbnail_id', $attach_id);
		}
	}

	/**
	 * Helper function to set WooCommerce product image.
	 *
	 * @param int    $product_id The product ID.
	 * @param string $image_url  The URL of the image.
	 */
	private function _set_woocommerce_product_image($product_id, $image_url)
	{
		if (! function_exists('media_handle_sideload')) {
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');
		}

		// Check if the image URL is valid
		if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
			return;
		}

		// Need to sideload the image
		$tmp = download_url($image_url);
		if (is_wp_error($tmp)) {
			return;
		}

		$file_array = array();
		$file_array['name'] = basename($image_url);
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink
		if (is_wp_error($tmp)) {
			wp_delete_file($file_array['tmp_name']);
			$file_array['tmp_name'] = '';
		}

		// do the validation and storage stuff
		$attach_id = media_handle_sideload($file_array, $product_ . phpid);

		if (! is_wp_error($attach_id)) {
			set_post_thumbnail($product_id, $attach_id);
		}
	}
}
