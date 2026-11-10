<?php
/**
 * Admin view: Bizzio Sync settings page.
 *
 * Included from Bizzio_Sync_Gencloud_Admin, so $this is the admin instance.
 *
 * @link       https://gencloud.bg
 * @since      1.0.0
 *
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">

	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content">
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
				<div id="bizzio-test-connection-result" style="margin-top: 10px;" aria-live="polite"></div>
			</div>

			<div id="postbox-container-1" class="postbox-container">
				<?php if ( ! $this->is_pro_active() ) : ?>
					<div class="postbox bizzio-sync-pro-box">
						<div class="postbox-header">
							<h2><?php esc_html_e( 'Bizzio ERP Sync PRO', 'bizzio-sync-for-woocommerce' ); ?></h2>
						</div>
						<div class="inside">
							<p><?php esc_html_e( 'Send WooCommerce orders to Bizzio ERP automatically:', 'bizzio-sync-for-woocommerce' ); ?></p>
							<ul>
								<li><?php esc_html_e( 'Automatic order sync to Bizzio ERP', 'bizzio-sync-for-woocommerce' ); ?></li>
								<li><?php esc_html_e( 'Send orders as offers', 'bizzio-sync-for-woocommerce' ); ?></li>
								<li><?php esc_html_e( 'Payment and shipping method mapping', 'bizzio-sync-for-woocommerce' ); ?></li>
								<li><?php esc_html_e( 'Company invoice data (VAT number, MOL)', 'bizzio-sync-for-woocommerce' ); ?></li>
								<li><?php esc_html_e( 'One-click resend of failed orders', 'bizzio-sync-for-woocommerce' ); ?></li>
							</ul>
							<p>
								<a class="button button-primary" href="<?php echo esc_url( $this->get_pro_url( 'settings-box' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get PRO', 'bizzio-sync-for-woocommerce' ); ?></a>
							</p>
						</div>
					</div>
				<?php endif; ?>

				<?php
				/**
				 * Fires in the sidebar of the Bizzio Sync settings page.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bizzio_sync_settings_sidebar' );
				?>
			</div>

		</div>
		<br class="clear">
	</div>

</div>
