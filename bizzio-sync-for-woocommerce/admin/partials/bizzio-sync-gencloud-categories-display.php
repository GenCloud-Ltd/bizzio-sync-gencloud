<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
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

	<h2>Import Categories from Bizzio ERP</h2>
	<p>Click the button below to fetch and import categories from the Bizzio ERP / gencloud.bg API.</p>
	<button id="bizzio-import-categories" class="button button-primary">Import Categories</button>

	<div id="bizzio-category-import-status" style="margin-top: 10px;"></div>

	<div id="bizzio-category-import-progress" style="margin-top: 10px; display: block;">
		<div id="bizzio-category-import-progress-bar" class="bizzio-progress-bar" style="width: 0%;">0%</div>
	</div>

</div>