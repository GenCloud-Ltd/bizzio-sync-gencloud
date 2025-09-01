<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation hooks, and defines a function that
 * starts the plugin.
 *
 * @link  https://gencloud.bg
 * @since 1.0.0
 * @package           Bizzio_Sync_Gencloud
 *
 * @wordpress-plugin
 * Plugin Name:       Bizzio Sync for WooCommerce
 * Description:       Sync products and categories from Bizzio ERP to WooCommerce store.
 * Version:           1.0.4
 * Author:gencloud
 * Author URI:        https://gencloud.bg/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bizzio-sync-for-woocommerce
 * Requires Plugins: woocommerce
 *
 * WC requires at least: 8.3.0
 * WC tested up to: 10.1.2
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HPOS compatibility declaration.
 *
 * @since 1.0.4
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Currently plugin version.
 * Start at 1.0.0 and use SemVer - https://semver.org
 *
 * @since 1.0.0
 */
define( 'BIZZIO_SYNC_GENCLOUD_VERSION', '1.0.4' );

/**
 * Define BIZZIO_SYNC_GENCLOUD_DEBUG_LOG to true in wp-config.php to enable full debug logging.
 * For example: define( 'BIZZIO_SYNC_GENCLOUD_DEBUG_LOG', true );
 */
if ( ! defined( 'BIZZIO_SYNC_GENCLOUD_DEBUG_LOG' ) ) {
	define( 'BIZZIO_SYNC_GENCLOUD_DEBUG_LOG', false );
}



/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bizzio-sync-gencloud.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bizzio_sync_gencloud() {

	$plugin = new Bizzio_Sync_Gencloud();
	$plugin->run();
}
run_bizzio_sync_gencloud();

/**
 * Load the pro version if it is active.
 */
if ( in_array( 'bizzio-sync-for-woocommerce-pro/bizzio-sync-for-woocommerce-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once( WP_PLUGIN_DIR . '/bizzio-sync-for-woocommerce-pro/bizzio-sync-for-woocommerce-pro.php' );
}
