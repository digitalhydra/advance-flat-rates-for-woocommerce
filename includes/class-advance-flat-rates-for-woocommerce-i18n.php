<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/digitalhydra
 * @since      1.0.0
 *
 * @package    Advance_Flat_Rates_For_Woocommerce
 * @subpackage Advance_Flat_Rates_For_Woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Advance_Flat_Rates_For_Woocommerce
 * @subpackage Advance_Flat_Rates_For_Woocommerce/includes
 * @author     Jairo Rondon <jimnoname@gmail.com>
 */
class Advance_Flat_Rates_For_Woocommerce_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'advance-flat-rates-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
