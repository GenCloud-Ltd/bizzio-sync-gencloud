<?php

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

	<form action="options.php" method="post">

		<?php
		settings_fields( $this->plugin_name );
		do_settings_sections( $this->plugin_name );
		submit_button( 'Save Settings' );
		?>

	</form>

	<h2>Test Connection</h2>
	<p>Click the button below to test the connection to the Bizzio ERP / gencloud.bg API.</p>
	<button id="bizzio-test-connection" class="button button-primary">Test Connection</button>
	<div id="bizzio-test-connection-result" style="margin-top: 10px;"></div>

</div>