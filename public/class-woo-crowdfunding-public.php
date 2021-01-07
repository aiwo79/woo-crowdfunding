<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link https://ivoslavik.cz
 * @since 1.0.0
 *
 * @package Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * - Defines the plugin name, version.
 * - Enqueues the public-facing stylesheet and JavaScript.
 * - Adds public-facing functionality.
 *
 * @package Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/public
 * @author Ivo Slavík <info@ivoslavik.cz>
 */
class Woo_Crowdfunding_Public {
	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-crowdfunding-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-crowdfunding-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Makes adjustments to WooCommerce
	 *
	 * @since 1.0.0
	 */
	public function woocommerce_setup() {
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
	}

	/**
	 * Prevents contributions for past projects to be added to cart.
	 *
	 * @since 1.0.0
	 * @param int $cart_item_key
	 * @param int $product_id Contains the id of the product to add to the cart.
	 * @param int $quantity Contains the quantity of the item to add.
	 * @param int $variation_id
	 * @param array $variation Attribute values.
	 * @param array $cart_item_data Extra cart item data we want to pass into the item.
	 * @return bool
	 */

	public function add_cf_project_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		if ( Woo_Crowdfunding::is_cf_project( $product_id ) && ! Woo_Crowdfunding::is_cf_project_active( $product_id ) ) {
			throw new Exception( __( 'It is not possible to contribute to this project, it is over now.', 'woocommerce' ) );
		}

		return true;
	}

	/**
	 * Add CF project custom fields to the checkout.
	 *
	 * @since 1.0.0
	 * @param WC_Checkout $checkout Checkout data object.
	 */
	public function cf_project_checkout_fields( $checkout ) {
		if ( Woo_Crowdfunding::is_cf_project_contribution_in_cart() ) {
			echo '<div class="cf_project_checkout_field">';
			woocommerce_form_field( 'publish_donator', array(
				'type'  => 'checkbox',
				'class' => array( 'form-row-wide' ),
				'label' => __( 'I want to be publicly known as a donator', 'woo-crowdfunding' ),
			), $checkout->get_value( 'publish_donator' ) );
			echo '</div>';

			echo '<div class="cf_project_checkout_field">';
			woocommerce_form_field( 'publish_amount', array(
				'type'  => 'checkbox',
				'class' => array( 'form-row-wide' ),
				'label' => __( 'I want to publish donated amount', 'woo-crowdfunding' ),
			), $checkout->get_value( 'publish_amount' ) );
			echo '</div>';
		}
	}

	/**
	 * Save CF project custom fields.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order number.
	 */
	public function cf_project_checkout_order_update( $order_id ) {
		if ( ! empty( $_POST['publish_donator'] ) ) {
			update_post_meta( $order_id, '_publish_donator', $_POST['publish_donator'] );
		} else {
			update_post_meta( $order_id, '_publish_donator', 0 );
		}

		if ( ! empty( $_POST['publish_amount'] ) ) {
			update_post_meta( $order_id, '_publish_amount', $_POST['publish_amount'] );
		} else {
			update_post_meta( $order_id, '_publish_amount', 0 );
		}

		$order = wc_get_order( $order_id );
		$contribution_amount_total = 0;

		foreach ( $order->get_items() as $order_item ) {
			if ( Woo_Crowdfunding::is_cf_project( $order_item['product_id'] ) ) {
				if ( empty( $product_id ) ) {
					$product_id = $order_item['product_id'];
				}

				$contribution_amount_total += $order_item['line_total'];
			}
		}

		if ( ! empty( $product_id ) ) {
			update_post_meta( $order_id, '_cf_project', $product_id );
		}

		if ( ! empty( $contribution_amount_total ) ) {
			update_post_meta( $order_id, '_order_contribution_total', $contribution_amount_total );
		}
	}

	/**
	 * Sets post meta when target amount is complete.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order number.
	 */
	public function contribution_thank_you( $order_id ) {
		$product_id = get_post_meta( $order_id, '_cf_project', true );

		if ( $product_id ) {
			$data = get_post_meta( $product_id, '_cf_project_data', true );
		}

    $order = wc_get_order( $order_id );

    if ( 'processing' == $order->get_status() ) {
    	$order->update_status( 'completed' );
    }
	}

	/**
	 * Modifies cart item name to contain CF project name plus contribution name.
	 *
	 * @since 1.0.0
	 * @param string $product_permalink Link to product page.
	 * @param array $cart_item Cart item data.
	 * @param string $cart_item_key Cart item key.
	 * @return string
	 */
	public function modify_cart_item_name( $product_permalink, $cart_item, $cart_item_key ) {
		$product = wc_get_product( $cart_item['product_id'] );
		$product_variation = wc_get_product( $cart_item['variation_id'] );

		if ( Woo_Crowdfunding::is_cf_project( $product ) ) {
			$product_permalink = sprintf( '<a href="%s">%s</a>', esc_url( $product->get_permalink() ), $product->get_name() . ' - ' . $product_variation->get_name() );
		}

		return $product_permalink;
	}

	/**
	 * Adds CF specific tabs on project detail page
	 *
	 * @since 1.0.0
	 * @param array $tabs Array with available tabs.
	 * @return array
	 */
	public function modify_tabs( $tabs ) {
		global $post;

		if ( Woo_Crowdfunding::is_cf_project( $post->ID ) ) {
			$tabs['contributions_tab'] = array(
				'title' 	=> __( 'Contributions', 'woo-crowdfunding' ),
				'priority' 	=> 15,
				'callback' 	=> array( &$this, 'contributions_tab_content' )
			);

			$tabs['contributors_tab'] = array(
				'title' 	 => __( 'Contributors', 'woo-crowdfunding' ),
				'priority' => 15,
				'callback' => array( $this, 'contributors_tab_content' )
			);
		}

		return $tabs;
	}

	/**
	 * Adds content to the Contributions tab
	 *
	 * @since 1.0.0
	 */
	public function contributions_tab_content() {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/template-contribution-levels.php' );
	}

	/**
	 * Adds content to the Contributors tab
	 *
	 * @since 1.0.0
	 */
	public function contributors_tab_content() {
		global $post, $wpdb;

		$anonymous_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) AS count
				FROM $wpdb->posts AS p
				JOIN wp_postmeta AS pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_publish_donator'
				JOIN wp_postmeta AS pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_cf_project'
				WHERE p.post_status = 'wc-completed'
				AND pm1.meta_value = 0
				AND pm2.meta_value = %d",
				$post->ID
			)
		);

		$contributors = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm1.post_id AS id, pm2.meta_value AS order_contribution_total, pm3.meta_value AS billing_first_name, pm4.meta_value AS billing_last_name, pm5.meta_value AS show_amount
				FROM $wpdb->posts AS p
				JOIN $wpdb->postmeta AS pm1 ON p.ID = pm1.post_id
				JOIN $wpdb->postmeta AS pm2 ON pm1.post_id = pm2.post_id AND pm2.meta_key = '_order_contribution_total'
				JOIN $wpdb->postmeta AS pm3 ON pm1.post_id = pm3.post_id AND pm3.meta_key = '_billing_first_name'
				JOIN $wpdb->postmeta AS pm4 ON pm1.post_id = pm4.post_id AND pm4.meta_key = '_billing_last_name'
				JOIN $wpdb->postmeta AS pm5 ON pm1.post_id = pm5.post_id AND pm5.meta_key = '_publish_amount'
				JOIN $wpdb->postmeta AS pm6 ON pm1.post_id = pm6.post_id AND pm6.meta_key = '_publish_donator' AND pm6.meta_value = 1
				WHERE p.post_status = 'wc-completed' AND pm1.meta_key = '_cf_project' AND pm1.meta_value = %d
				ORDER BY id DESC",
				$post->ID
			)
		);

		if ( $anonymous_count > 0 ) {
			echo '<p>' . __( 'Number of anonymous contributors:', 'woo-crowdfunding' ) . ' ' . $anonymous_count . '</p>';
		}

		if ( ! empty( $contributors ) ) {
			echo '<table class="contributors-list">';
			foreach ( $contributors as $contributor ) {
				echo '<tr>';
				echo '<td>' . $contributor->billing_first_name . ' ' . $contributor->billing_last_name . '</td>';
				echo '<td>';
				if ( $contributor->show_amount ) {
					echo wc_price( $contributor->order_contribution_total );
				} else {
					_e( 'not published', 'woo-crowdfunding' );
				}
				echo '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
	}

	/**
	 * Modifies notice after adding contribution to cart.
	 *
	 * @since 1.0.0
	 * @param string $message Default add to cart message.
	 * @param int $product_id The id of the product.
	 * @return string
	 */
	public function modify_add_to_cart_message( $message, $products, $show_qty ) {
		$product_id = key( $products );

		if ( Woo_Crowdfunding::is_cf_project_contribution( $product_id ) ) {
			$project_name_in_quotes = sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce' ), strip_tags( get_the_title( $product_id ) ) );

			if ( 'yes' == get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				$cart_link = '';
			} else {
				$cart_link = sprintf( '<a href="%s" tabindex="1" class="button wc-forward">%s</a> ', esc_url( wc_get_cart_url() ), esc_html__( 'View cart', 'woocommerce' ) );
			}

			return $cart_link . sprintf( __( 'Contribution %s has been added to your cart.', 'woo-crowdfunding' ), $project_name_in_quotes );
		}

		return $message;
	}

	/**
	 * Prevents mixing of multiple projects contributions in cart.
	 * Prevents mixing of standard products with contributions in cart.
	 *
	 * @since 1.0.0
	 * @param bool $valid Product validation.
	 * @param int $product_id The id of added product.
	 * @param int $quantity Quantity of added product.
	 * @return bool
	 */
	public function add_product_validation( $valid, $product_id, $quantity ) {
		if ( WC()->cart->get_cart_contents_count() == 0 ) {
			return $valid;
		}

		if ( Woo_Crowdfunding::is_cf_project_contribution( $product_id ) ) {
			$product = wc_get_product( $product_id );
			$parent_id = $product->get_parent_id();

			foreach ( WC()->cart->get_cart() as $item ) {
				if ( Woo_Crowdfunding::is_cf_project_contribution( $item['product_id'] ) ) {
					if ( $parent_id && $parent_id == $item['product_id'] ) {
						return $valid;
					} else {
						WC()->cart->empty_cart();
						wc_add_notice( __( 'It is not possible to mix contributions from multiple projects. Previous cart content has been removed.', 'woo-crowdfunding' ), 'notice' );
						break;
					}
				} else {
					WC()->cart->empty_cart();
					wc_add_notice( __( 'It is not possible to mix standard products with contributions. Previous cart content has been removed.', 'woo-crowdfunding' ), 'notice' );
					break;
				}
			}
		} elseif ( Woo_Crowdfunding::is_cf_project_contribution_in_cart() ) {
			WC()->cart->empty_cart();
			wc_add_notice( __( 'It is not possible to mix standard products with contributions. Previous cart content has been removed.', 'woo-crowdfunding' ), 'notice' );
		}

		return $valid;
	}

	/**
	 * Removes subtotal from order overview.
	 *
	 * @since 1.0.0
	 * @param string $subtotal Subtotal amount with currency symbol.
	 * @param bool $compound Compound tax option.
	 * @param object $order Order data object.
	 */
	public function remove_order_subtotal( $subtotal, $compound, $order ) {
		return false;
	}

	/**
	 * Modifies checkout order button text
	 *
	 * @since 1.0.0
	 * @param string $button_text Order button text.
	 * @return string
	 */
	public function custom_order_button_text( $button_text ) {
		return __( 'Support', 'woo-crowdfunding' );
	}

	/**
	 * Filters products to show only active CF projects
	 *
	 * @since 1.0.0
	 * @param bool $visible Product visibility.
	 * @param int $product_id Product id.
	 * @return bool
	 */
	public function filter_product_visibility( $visible, $product_id ) {
		if ( ! Woo_Crowdfunding::is_cf_project( $product_id ) ) {
			return $visible;
		}

		$cancel = get_post_meta( $product_id, '_cf_project_cancel', true );
		$complete = get_post_meta( $product_id, '_cf_project_complete', true );

		if ( $cancel ) {
			return apply_filters( 'woo-cf-cancelled-project-visibility', false );
		}

		if ( ! Woo_Crowdfunding::is_cf_project_set( $product_id ) || Woo_Crowdfunding::is_cf_project_active( $product_id ) ) {
			return $visible;
		} elseif ( ! $complete ) {
			Woo_Crowdfunding::cancel_project_orders( $product_id );
			return apply_filters( 'woo-cf-cancelled-project-visibility', false );
		}
	}

	/**
	 * Changes order refund text.
	 *
	 * @since 1.0.0
	 * @param string $value Order refund text.
	 * @param object $order_refund Order refund data object.
	 */
	public function modify_order_refund_text( $value, $order_refund ) {
		return __( 'Contribution fully refunded', 'woo-crowdfunding' );
	}
}