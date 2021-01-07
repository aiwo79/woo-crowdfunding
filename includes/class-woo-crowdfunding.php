<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link https://ivoslavik.cz
 * @since 1.0.0
 *
 * @package Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, public-facing
 * and shared site hooks. There are some helper functions too.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since 1.0.0
 * @package Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/includes
 * @author Ivo Slavík <info@ivoslavik.cz>
 */
class Woo_Crowdfunding {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Woo_Crowdfunding_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'woo-crowdfunding';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_shared_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Crowdfunding_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Crowdfunding_i18n. Defines internationalization functionality.
	 * - Woo_Crowdfunding_Admin. Defines all hooks for the admin area.
	 * - Woo_Crowdfunding_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-crowdfunding-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-crowdfunding-i18n.php';

		/**
		 * The class responsible for adding a new product type.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-crowdfunding-cf-project-product-type.php';

		/**
		 * The class responsible for defining all actions that occur in the admin and public-facing area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-crowdfunding-shared.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-crowdfunding-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-crowdfunding-public.php';

		$this->loader = new Woo_Crowdfunding_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_Crowdfunding_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new Woo_Crowdfunding_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Woo_Crowdfunding_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 20 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_product_data_panels', $plugin_admin, 'add_cf_project_panel' );
		$this->loader->add_action( 'woocommerce_order_status_completed', $plugin_admin, 'update_project_progress' );
		$this->loader->add_action( 'woocommerce_admin_order_data_after_billing_address', $plugin_admin, 'cf_checkout_fields_display_admin_order_meta' );
		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'filter_orders_by_product' );

		$this->loader->add_filter( 'woocommerce_process_product_meta_cf_project', $plugin_admin, 'product_save_data' );
		$this->loader->add_filter( 'product_type_selector', $plugin_admin, 'add_product_type' );
		$this->loader->add_filter( 'woocommerce_product_variation_title', $plugin_admin, 'modify_contribution_title', 10, 4 );
		$this->loader->add_filter( 'woocommerce_order_item_get_name', $plugin_admin, 'rename_order_item', 10, 2 );
		$this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'add_cf_project_tab' );
		$this->loader->add_filter( 'pre_get_posts', $plugin_admin, 'query_filter_orders_by_product' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		$plugin_public = new Woo_Crowdfunding_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'after_setup_theme', $plugin_public, 'woocommerce_setup' );
		$this->loader->add_action( 'woocommerce_add_to_cart', $plugin_public, 'add_cf_project_to_cart', 10, 6 );
		$this->loader->add_action( 'woocommerce_after_order_notes', $plugin_public, 'cf_project_checkout_fields' );
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'cf_project_checkout_order_update' );
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'contribution_thank_you' );

		$this->loader->add_filter( 'woocommerce_cart_item_name', $plugin_public, 'modify_cart_item_name', 10, 3 );
		$this->loader->add_filter( 'woocommerce_order_item_name', $plugin_public, 'modify_cart_item_name', 10, 3 );
		$this->loader->add_filter( 'woocommerce_product_tabs', $plugin_public, 'modify_tabs' );
		$this->loader->add_filter( 'wc_add_to_cart_message_html', $plugin_public, 'modify_add_to_cart_message', 10, 3 );
		$this->loader->add_filter( 'woocommerce_add_to_cart_validation', $plugin_public, 'add_product_validation', 10, 3 );
		$this->loader->add_filter( 'woocommerce_order_subtotal_to_display', $plugin_public, 'remove_order_subtotal', 10, 3 );
		$this->loader->add_filter( 'woocommerce_order_button_text', $plugin_public, 'custom_order_button_text' );
		$this->loader->add_filter( 'woocommerce_product_is_visible', $plugin_public, 'filter_product_visibility', 10, 2 );
		$this->loader->add_filter( 'woocommerce_order_refund_get_reason', $plugin_public, 'modify_order_refund_text', 10, 2 );
	}

	/**
	 * Register all of the hooks related to the admin and public-facing functionality
	 * of the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_shared_hooks() {
		$plugin_shared = new Woo_Crowdfunding_Shared();
		$this->loader->add_filter( 'woocommerce_get_price_html', $plugin_shared, 'product_price_html', 10, 2 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since 1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since 1.0.0
	 * @return Woo_Crowdfunding_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since 1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Checks whether we are dealing with CF project.
	 *
	 * @since 1.0.0
	 * @param int|WC_Product $product Product id or Product data object.
	 * @return bool
	 */
	static function is_cf_project( $product ) {
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( $product );
		}

		return ( $product->is_type( 'cf_project' ) ) ? true : false;
	}

	/**
	 * Checks whether we are dealing with CF project contribution.
	 *
	 * @since 1.0.0
	 * @param int|WC_Product $product Product id or Product data object.
	 * @return bool
	 */
	static function is_cf_project_contribution( $product ) {
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( $product );
		}

		$parent_id = $product->get_parent_id();
		if ( $parent_id ) {
			$product = wc_get_product( $parent_id );
		}

		return self::is_cf_project( $product );
	}

	/**
	 * Checks whether the CF project has goal and end date set.
	 *
	 * @since 1.0.0
	 * @param int $product_id Product id.
	 * @return bool
	 */
	static function is_cf_project_set( $product_id ) {
		if ( self::is_cf_project( $product_id ) ) {
			$data = get_post_meta( $product_id, '_cf_project_data', true );
			$end_date = get_post_meta( $product_id, '_cf_project_end_date', true );

			if ( ! empty( $data ) && 0 != $data['goal'] && ! empty( $end_date ) ) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Checks whether the CF project is still active (before end date).
	 *
	 * @since 1.0.0
	 * @param int $product_id Product id.
	 * @return bool
	 */
	static function is_cf_project_active( $product_id ) {
		if ( self::is_cf_project( $product_id ) ) {
			$data = get_post_meta( $product_id, '_cf_project_data', true );
			$end_date = get_post_meta( $product_id, '_cf_project_end_date', true );

			if ( ! empty( $data ) && $end_date >= date( 'Y-m-d' ) ) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Checks whether there is a CF project contribution in cart.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	static function is_cf_project_contribution_in_cart() {
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( $item['variation_id'] > 0 ) {
				$product_id = $item['variation_id'];
			} else {
				$product_id = $item['product_id'];
			}

			if ( self::is_cf_project_contribution( $product_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether the order contains a CF project contribution.
	 *
	 * @since 1.0.0
	 * @param object $order Order data object.
	 * @return bool
	 */
	static function is_cf_project_contribution_in_order( $order ) {
		foreach ( $order->get_items() as $order_item ) {
			if ( $order_item['variation_id'] > 0 ) {
				$product_id = $order_item['variation_id'];
			} else {
				$product_id = $order_item['product_id'];
			}
			if ( self::is_cf_project_contribution( $product_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Changes status of all orders supporting unsuccessful CF project to Refunded.
	 *
	 * @since 1.0.0
	 * @param int $product_id Product id.
	 */
	static function cancel_project_orders( $product_id ) {
		global $wpdb;

		if ( ! $product_id ) {
			return;
		}

		update_post_meta( $product_id, '_cf_project_cancel', true );

		$orders = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id AS order_id
				FROM $wpdb->postmeta AS pm
				WHERE pm.meta_key = '_cf_project' AND pm.meta_value = %d",
				$product_id
			)
		);

		foreach ( $orders as $order ) {
			$order = new WC_Order( $order->order_id );
			$order->update_status( 'refunded', 'CF project goal not met, charge will be refunded.' );
		}
	}
}