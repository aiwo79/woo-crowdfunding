<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link https://ivoslavik.cz
 * @since 1.0.0
 * @package Woo_Crowdfunding
 *
 * @wordpress-plugin
 * Plugin Name:       Woo Crowdfunding
 * Plugin URI:        https://ivoslavik.cz
 * Description:       This plugin enables creation of crowdfunding projects in WooCommerce.
 * Version:           1.0.0
 * Author:            Ivo Slavík
 * Author URI:        https://ivoslavik.cz
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-crowdfunding
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Used for referring to the plugin directory path.
if ( ! defined( 'PLUGIN_PATH' ) ) {
	define( 'PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-crowdfunding-activator.php
 */
function activate_woo_crowdfunding() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-crowdfunding-activator.php';
	Woo_Crowdfunding_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-crowdfunding-deactivator.php
 */
function deactivate_woo_crowdfunding() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-crowdfunding-deactivator.php';
	Woo_Crowdfunding_Deactivator::deactivate();
}

//register_activation_hook( __FILE__, 'activate_woo_crowdfunding' );
//register_deactivation_hook( __FILE__, 'deactivate_woo_crowdfunding' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-crowdfunding.php';

/**
 * The class that is used to check WooCommerce is active.
 */
if ( ! class_exists( 'WC_Dependencies' ) ) {
	require plugin_dir_path( __FILE__ ) . 'includes/class-woo-crowdfunding-dependencies.php';
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_woo_crowdfunding() {
	$plugin = new Woo_Crowdfunding();
	$plugin->run();
}

/**
 * Detects if WooCommerce plugin is active.
 *
 * @since 1.0.0
 **/
function woo_crowdfunding_is_woocommerce_active() {
	return WC_Dependencies::woocommerce_active_check();
}

/**
 * Shows admin notice if WooCommerce isn't active.
 *
 * @since 1.0.0
 */
function woo_crowdfunding_error_notice() {
	?>
	<div class="error notice">
		<p><?php _e( 'Please install WooCommerce, it is required for Woo Crowdfunding plugin to work properly!', 'woo-crowdfunding' ); ?></p>
	</div>
	<?php
}

if ( woo_crowdfunding_is_woocommerce_active() ) {
	run_woo_crowdfunding();
} else {
	add_action( 'admin_notices', 'woo_crowdfunding_error_notice' );
}