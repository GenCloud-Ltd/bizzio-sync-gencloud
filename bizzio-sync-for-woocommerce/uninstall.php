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
if (! defined('WP_UNINSTALL_PLUGIN')) {
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

foreach ($options as $option_name) {
    delete_option($option_name);
}

// Delete the Bizzio logs/xml directory.
$upload_dir = wp_upload_dir();
$bizzio_dir = $upload_dir['basedir'] . '/bizzio';

if (is_dir($bizzio_dir)) {
    /**
     * Recursively delete a directory and its contents.
     *
     * @param string $dir The directory path to delete.
     */
    function bizzio_sync_gencloud_recursive_delete($dir)
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? bizzio_sync_gencloud_recursive_delete("$dir/$file") : unlink("$dir/$file");
        }

        rmdir($dir);
    }

    bizzio_sync_gencloud_recursive_delete($bizzio_dir);
}
