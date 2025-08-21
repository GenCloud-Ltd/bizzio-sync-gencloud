<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://incloud.bg
 * @since      1.0.0
 *
 * @package    Bizzio_Sync_Gencloud
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options from the options table.
$options = array(
    'bizzio_api_database',
    'bizzio_api_username',
    'bizzio_api_password',
    'bizzio_id_site',
    'bizzio_sync_gencloud_articles_to_import',
    'bizzio_sync_gencloud_total_articles',
    'bizzio_sync_gencloud_import_progress',
    'bizzio_sync_gencloud_imported_count',
    'bizzio_sync_gencloud_failed_count',
    'bizzio_sync_gencloud_import_status',
    'bizzio_sync_gencloud_categories_to_import',
    'bizzio_sync_gencloud_total_categories',
    'bizzio_sync_gencloud_category_import_progress',
    'bizzio_sync_gencloud_category_imported_count',
    'bizzio_sync_gencloud_category_failed_count',
    'bizzio_sync_gencloud_category_import_status',
);

foreach ( $options as $option_name ) {
    delete_option( $option_name );
}

// Delete the Bizzio logs/xml directory using WP_Filesystem.
global $wp_filesystem;

if ( is_null( $wp_filesystem ) ) {
    require_once ABSPATH . '/wp-admin/includes/file.php';
    WP_Filesystem();
}

$upload_dir = wp_upload_dir();
$bizzio_dir = $upload_dir['basedir'] . '/bizzio';

if ( $wp_filesystem->is_dir( $bizzio_dir ) ) {
    $wp_filesystem->delete( $bizzio_dir, true );
}
