<?php
/** If this file is called directly, abort.
 *
 * @since    1.0.0
 * @package  Bizzio_Sync_Gencloud
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://incloud.bg
 * @since      1.0.0
 *
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/admin/partials
 */
?>

<div class="wrap">

	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<h2><?php esc_html_e( 'Import Products from Bizzio ERP', 'bizzio-sync-for-woocommerce' ); ?></h2>
	<p><?php esc_html_e( 'Click the button below to fetch and import products from the Bizzio ERP / gencloud.bg API.', 'bizzio-sync-for-woocommerce' ); ?></p>
	<button id="bizzio-import-products" class="button button-primary"><?php esc_html_e( 'Import Products', 'bizzio-sync-for-woocommerce' ); ?></button>
	<div id="bizzio-product-import-status" style="margin-top: 10px;"></div>

	<div class="bizzio-progress-container" style="width: 100%; background-color: #f3f3f3; border-radius: 5px; margin-top: 20px;">
		<div id="bizzio-product-import-progress-bar" class="bizzio-progress-bar" style="width: 0%; height: 30px; background-color: #4CAF50; text-align: center; line-height: 30px; color: white; border-radius: 5px;">0%</div>
	</div>

</div>