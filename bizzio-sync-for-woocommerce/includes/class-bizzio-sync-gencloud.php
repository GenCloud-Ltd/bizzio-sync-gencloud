<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also, this class defines the main file that will be used to instantiate the plugin.
 *
 * @since      1.0.0
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/includes
 * @author     Gencloud <web@gencloud.bg>
 */
class Bizzio_Sync_Gencloud {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Bizzio_Sync_Gencloud_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'BIZZIO_SYNC_GENCLOUD_VERSION' ) ) {
			$this->version = BIZZIO_SYNC_GENCLOUD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'bizzio-sync-gencloud';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Bizzio_Sync_Gencloud_Loader. Orchestrates the hooks of the plugin.
	 * - Bizzio_Sync_Gencloud_i18n. Defines internationalization functionality.
	 * - Bizzio_Sync_Gencloud_Admin. Defines all hooks for the admin area.
	 * - Bizzio_Sync_Gencloud_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bizzio-sync-gencloud-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bizzio-sync-gencloud-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-bizzio-sync-gencloud-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-bizzio-sync-gencloud-public.php';

		$this->loader = new Bizzio_Sync_Gencloud_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Bizzio_Sync_Gencloud_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Bizzio_Sync_Gencloud_i18n();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Bizzio_Sync_Gencloud_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'setup_sections' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'setup_fields' );
		$this->loader->add_action( 'wp_ajax_bizzio_test_connection', $plugin_admin, 'test_connection_callback' );
		$this->loader->add_action( 'wp_ajax_bizzio_import_categories', $plugin_admin, 'import_categories_callback' );
		$this->loader->add_action( 'wp_ajax_bizzio_import_products', $plugin_admin, 'import_products_callback' );
		$this->loader->add_action( 'wp_ajax_bizzio_process_product_batch', $plugin_admin, 'process_product_batch_callback' );
		$this->loader->add_action( 'wp_ajax_bizzio_get_import_progress', $plugin_admin, 'get_import_progress_callback' );
  $this->loader->add_action( 'wp_ajax_bizzio_process_category_batch', $plugin_admin, 'process_category_batch_callback' );
  $this->loader->add_action( 'wp_ajax_bizzio_get_category_import_progress', $plugin_admin, 'get_category_import_progress_callback' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Bizzio_Sync_Gencloud_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Bizzio_Sync_Gencloud_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
