<?php
/**
 * Plugin Name: Bizzio QA – block self-update
 * Description: QA site only. The plugin is a junction to the git repo; a WP.org update would overwrite the working copy.
 *
 * @package Bizzio_Sync_Gencloud
 */

add_filter(
	'site_transient_update_plugins',
	static function ( $transient ) {
		if ( is_object( $transient ) && isset( $transient->response['bizzio-sync-for-woocommerce/bizzio-sync-for-woocommerce.php'] ) ) {
			unset( $transient->response['bizzio-sync-for-woocommerce/bizzio-sync-for-woocommerce.php'] );
		}
		return $transient;
	}
);
