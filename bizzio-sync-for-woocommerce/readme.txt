=== Bizzio Sync for WooCommerce ===
Contributors: gencloud
Tags: woocommerce, bizzio, erp, sync, import
Requires at least: 6.5
Tested up to: 7.1
Stable tag: 1.1.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bizzio Sync for WooCommerce allows you to easily import products and categories from your Bizzio ERP to your WooCommerce store.

== Description ==

Bizzio Sync for WooCommerce is a powerful integration plugin that connects your WooCommerce store to the Bizzio ERP system, enabling seamless synchronization of your product catalog, categories, and attributes — all with just a few clicks.

*   **Import Products:** Sync your products from Bizzio to WooCommerce, including product name, description, price, and stock quantity.
*   **Import Categories:** Sync your product categories from Bizzio to WooCommerce, maintaining the category hierarchy.
*   **Batch Processing:** The import process is handled in batches to prevent server timeouts and ensure a smooth import, even with a large number of products and categories.
*   **Progress Tracking:** The plugin provides a real-time progress bar to monitor the import process.
*   **Connection Testing:** You can easily test the connection to your Bizzio ERP to ensure that the API credentials are correct.

Note: This plugin requires valid API credentials provided by GenCloud Ltd. and access to a working Bizzio ERP instance.

== External Services ==

This plugin connects to the Bizzio ERP API, a service operated by GenCloud Ltd., to import product and category data into WooCommerce. The plugin cannot work without it.

* **What is sent:** the API credentials entered on the settings page (Database, Username, Password and Site ID) and, for product imports, the store currency code.
* **When:** only when a store administrator clicks "Test Connection", "Import Categories" or "Import Products". Nothing is sent automatically, in the background, or on front-end page views.
* **Where:** `https://bizzio.gencloud.bg/Services/Extensions/RiznShopExtService.svc`. Product and category images returned by the API are downloaded from the image addresses it provides.
* **What is not sent:** no customer, order, visitor or other site data.

The Bizzio ERP service is provided by GenCloud Ltd. — https://gencloud.bg/ (contact: web@gencloud.bg).

== Installation ==

1.  Upload the `bizzio-sync-for-woocommerce` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to the **Bizzio Sync** menu in your WordPress admin area.
4.  Enter your Bizzio API credentials in the **Settings** tab.
5.  Click **Save Changes**.
6.  You can now import products and categories from the **Products** and **Categories** tabs.

== Frequently Asked Questions ==

= What are the requirements for this plugin? =

You need to have a Bizzio ERP API account and WooCommerce installed on your WordPress site.

= Where can I get support? =

For support, please contact us at [web@gencloud.bg](mailto:web@gencloud.bg).

== Screenshots ==

1.  The Bizzio Sync settings page.
2.  The product import page.
3.  The category import page.

== Changelog ==

= 1.1.1 - 2026-10-28 =
* Tested up to WordPress 7.1 and WooCommerce 11.2. Requires WordPress 6.5+ (needed for the "Requires Plugins" header).
* Fix: saving the settings no longer clears the stored API password when the password field is left empty.
* Fix: API credentials and Site ID are XML-escaped, so values containing &, < or > no longer break requests.
* Fix: category images are reused instead of being downloaded again on every import; temporary files are removed when an image import fails.
* Fix: "Manage stock" is set correctly on imported products.
* Security: messages and HTML received from the API are escaped/sanitized before display or storage.
* Product prices are requested in the store currency.
* Large import queues are no longer autoloaded on every page load.
* Uninstall now also removes the created/updated counters.
* Code now passes WordPress Coding Standards, PHPStan and Plugin Check.

= 1.1.0 - 2025-11-11 =
* Major code refactoring for improved architecture and extensibility.
* Added dedicated API layer with separate classes for GetArticles and GetSiteGroups methods.
* Introduced Image Helper class for centralized image processing functionality.
* Improved separation of concerns - admin class now focuses only on AJAX handlers and UI.
* Enhanced code maintainability and WordPress Coding Standards compliance.
* Optimized database queries for better performance.
* Prepared architecture for future features.

= 1.0.4 - 2025-09-01 =
* Updated Author URI and @link tags to gencloud.bg.

= 1.0.3 - 2025-08-25 =
* Minor update to readme.txt – clarified required plugins and docs 3rd Party / external service.
* Еnhance internationalization support

= 1.0.2 - 2025-08-12 =
- Changed plugin name from "Bizzio Sync Gencloud" to "Bizzio Sync for WooCommerce".
- Added direct access check to PHP files in the admin partials.
- Improved code formatting for consistency across files.

= 1.0.1 - 2025-08-01 =
* Improve plugin compatibility


= 1.0.0 - 2025-07-29 =
* Initial release.
* Added product and category import functionality.
* Added batch processing and progress tracking.
* Added connection testing.

