<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/digitalhydra
 * @since             1.0.0
 * @package           Advance_Flat_Rates_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Advance Flat Rates for WooCommerce
 * Plugin URI:        https://github.com/digitalhydra/advance-flat-rates-for-woocommerce
 * Description:       Flat rate shipping method that let you filter by user role an minimum order value.
 * Version:           1.0.0
 * Author:            Jairo Rondon
 * Author URI:        https://github.com/digitalhydra
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       advance-flat-rates-for-woocommerce
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ADVANCE_FLAT_RATES_FOR_WOOCOMMERCE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-advance-flat-rates-for-woocommerce-activator.php
 */
function activate_advance_flat_rates_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-advance-flat-rates-for-woocommerce-activator.php';
	Advance_Flat_Rates_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-advance-flat-rates-for-woocommerce-deactivator.php
 */
function deactivate_advance_flat_rates_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-advance-flat-rates-for-woocommerce-deactivator.php';
	Advance_Flat_Rates_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_advance_flat_rates_for_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_advance_flat_rates_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-advance-flat-rates-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_advance_flat_rates_for_woocommerce() {

	$plugin = new Advance_Flat_Rates_For_Woocommerce();
	$plugin->run();

}
run_advance_flat_rates_for_woocommerce();
