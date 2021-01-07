<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://ivoslavik.cz
 * @since      1.0.0
 *
 * @package    Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/includes
 * @author     Ivo Slavík <info@ivoslavik.cz>
 */
class Woo_Crowdfunding_i18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'woo-crowdfunding',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}