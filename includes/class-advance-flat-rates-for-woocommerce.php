<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/digitalhydra
 * @since      1.0.0
 *
 * @package    Advance_Flat_Rates_For_Woocommerce
 * @subpackage Advance_Flat_Rates_For_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Advance_Flat_Rates_For_Woocommerce
 * @subpackage Advance_Flat_Rates_For_Woocommerce/includes
 * @author     Jairo Rondon <jimnoname@gmail.com>
 */
class Advance_Flat_Rates_For_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Advance_Flat_Rates_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'ADVANCE_FLAT_RATES_FOR_WOOCOMMERCE_VERSION' ) ) {
			$this->version = ADVANCE_FLAT_RATES_FOR_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'advance-flat-rates-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Advance_Flat_Rates_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Advance_Flat_Rates_For_Woocommerce_i18n. Defines internationalization functionality.
	 * - Advance_Flat_Rates_For_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Advance_Flat_Rates_For_Woocommerce_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advance-flat-rates-for-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advance-flat-rates-for-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-advance-flat-rates-for-woocommerce-admin.php';

    /*
    * Check if WooCommerce is active
    */
    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
      add_action( 'woocommerce_shipping_init', array($this, 'create_shipping_method') );
		}
		
		$this->loader = new Advance_Flat_Rates_For_Woocommerce_Loader();

	}

  public function create_shipping_method()
  {
    /**
     * The class responsible for defining the shipping method for woocommerce
     * side of the site.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-advance-flat-rates-for-woocommerce-advance-flat-rate.php';

    add_filter('woocommerce_shipping_methods', array($this, 'add_advance_flat_rate_shipping_method'));
  }

  /**
   * Register the shipping method
   *
   * @since    1.0.0
   */

  public function add_advance_flat_rate_shipping_method($methods)
  {
    $methods['wc-adv-flat-rate'] = 'WC_Advance_Flat_Rate';
    return $methods;
	}
	
	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Advance_Flat_Rates_For_Woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Advance_Flat_Rates_For_Woocommerce_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Advance_Flat_Rates_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

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
	 * @return    Advance_Flat_Rates_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
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
