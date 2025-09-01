<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://gencloud.bg
 * @since      1.0.0
 *
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/admin/partials
 */
?>

<div class="wrap">

	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form action="options.php" method="post">

		<?php
		settings_fields( $this->plugin_name );
		do_settings_sections( $this->plugin_name );
		submit_button( __( 'Save Settings', 'bizzio-sync-for-woocommerce' ) );
		?>

	</form>

	<h2><?php esc_html_e( 'Test Connection', 'bizzio-sync-for-woocommerce' ); ?></h2>
	<p><?php esc_html_e( 'Click the button below to test the connection to the Bizzio ERP / gencloud.bg API.', 'bizzio-sync-for-woocommerce' ); ?></p>
	<button id="bizzio-test-connection" class="button button-primary"><?php esc_html_e( 'Test Connection', 'bizzio-sync-for-woocommerce' ); ?></button>
	<div id="bizzio-test-connection-result" style="margin-top: 10px;"></div>

</div>