# Bizzio Sync Gencloud

**Plugin Name:** Bizzio Sync Gencloud

**Description:** This WordPress plugin facilitates the synchronization of product and category data from Bizzio ERP (gencloud.bg) to WooCommerce.

## Features

*   **API Integration:** Connects to the Bizzio ERP API to fetch article groups (categories) and articles (products).
*   **Background Processing:** Imports products in batches using AJAX to prevent timeouts and provide real-time progress updates.
*   **WooCommerce Compatibility:** Maps Bizzio data to WooCommerce product and category structures, including names, descriptions, prices, stock, and images.
*   **Admin UI:** Provides an administrative interface for configuring API credentials and managing import processes.
*   **XML Response Logging:** Saves XML responses from the Bizzio ERP to a protected directory for review and analysis.

## Installation

1.  **Download the plugin:** Obtain the plugin files (e.g., as a ZIP archive).
2.  **Upload to WordPress:**
    *   Navigate to `Plugins > Add New` in your WordPress admin dashboard.
    *   Click on the `Upload Plugin` button.
    *   Choose the downloaded ZIP file and click `Install Now`.
3.  **Activate the plugin:** After installation, click `Activate Plugin`.

## Configuration

After activating the plugin, you will need to configure your Bizzio API credentials:

1.  Navigate to `Bizzio Sync > Settings` in your WordPress admin menu.
2.  Enter the following API credentials provided by Bizzio ERP / gencloud.bg:
    *   **API Database:** Your Bizzio API database name.
    *   **API Username:** Your Bizzio API username.
    *   **API Password:** Your Bizzio API password.
    *   **ID Site:** The specific ID for your site within the Bizzio ERP system.
3.  Click `Save Changes` to store your settings.

## Usage

### Testing API Connection

On the `Bizzio Sync > Settings` page, you will find a `Test Connection` button. Click this button to verify that your API credentials are correct and that the plugin can successfully communicate with the Bizzio ERP.

### Importing Categories

1.  Navigate to `Bizzio Sync > Categories`.
2.  Click the `Import Categories` button to fetch and import article groups from Bizzio ERP into WooCommerce categories.

### Importing Products

1.  Navigate to `Bizzio Sync > Products`.
2.  Click the `Import Products` button to fetch and import articles from Bizzio ERP into WooCommerce products. This process runs in the background, and you will see progress updates on the page.

## Debugging

To enable detailed debug logging for the plugin, add the following line to your `wp-config.php` file (preferably above the `/* That's all, stop editing! Happy publishing. */` line):

```php
define( 'BIZZIO_SYNC_GENCLOUD_DEBUG_LOG', true );
```

Debug information will be logged to your WordPress debug log file (usually `wp-content/debug.log`).

## Support

For any issues or questions, please refer to the plugin documentation or contact the developer.
