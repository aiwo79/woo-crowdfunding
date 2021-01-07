<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link https://ivoslavik.cz
 * @since 1.0.0
 *
 * @package Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * - Defines the plugin name, version.
 * - Enqueues the admin-specific stylesheet and JavaScript.
 * - Adds crowdfunding functionality to WooCommerce product.
 * - Creates plugin widget sidebar and contribution levels widget.
 * - Adds new product type.
 *
 * @package Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/admin
 * @author Ivo Slavík <info@ivoslavik.cz>
 */
class Woo_Crowdfunding_Admin {
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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		$current_screen = get_current_screen();
		if ( 'product' == $current_screen->post_type ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-crowdfunding-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$current_screen = get_current_screen();
		if ( 'product' == $current_screen->post_type ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-crowdfunding-admin.js', array( 'jquery' ), $this->version, false );
		}
	}

	/**
	 * Adds a new panel to the product interface.
	 *
	 * @since 1.0.0
	 */
	public function add_cf_project_panel() {
		global $post;

		$data = get_post_meta( $post->ID, '_cf_project_data', true );
		$end_date = get_post_meta( $post->ID, '_cf_project_end_date', true );
		$is_new = false;

		if ( ! $data ) {
			$data = array();
			$is_new = true;
			$data['goal'] = 0;
		}

		$progress = $progress_percent = 0;
		if ( $data && isset( $data['progress'] ) ) {
			$progress = $data['progress'];

			if ( $data['progress'] > $data['goal'] ) {
				$progress_percent = 100;
			} elseif ( $data['goal'] > 0 ) {
				$progress_percent = round( $data['progress'] / $data['goal'] * 100 );
			}
		}

		if ( ! empty( $end_date ) ) {
			$end_date = date( get_option( 'date_format' ), strtotime( $end_date ) );
		}
		?>

		<div id="cf_data" class="panel woocommerce_options_panel">
			<div class="options_group">
				<p class="form-field cf_goal_field">
					<label for="_cf_project_goal"><?php _e( 'Goal', 'woo-crowdfunding' ); ?> (<?php echo get_woocommerce_currency_symbol(); ?>)</label>
					<input type="text" class="short" name="_cf_project_data[goal]" id="_cf_project_goal" value="<?php echo esc_attr( $data['goal'] ); ?>"<?php echo ( ! $is_new && $data['goal'] > 0 ? ' disabled="disabled"' : '' ); ?> />
					<span class="woocommerce-help-tip" data-tip="<?php _e( 'The amount needed to complete your project.', 'woo-crowdfunding' ); ?>"></span>
				</p>

				<p class="form-field cf_goal_progress_field" style="<?php echo $is_new ? 'display: none;' : ''; ?>">
					<label for="_cf_project_contributed"><?php _e( 'Contributed', 'woo-crowdfunding' ); ?> (<?php echo get_woocommerce_currency_symbol(); ?>)</label>
					<span class="cf-progress-desc">
						<?php echo $progress; ?>
					</span>
					<span class="cf-progress">
						<span class="cf-progress-percent" style="width: <?php echo $progress_percent; ?>%;"></span>
					</span>
				</p>
			</div>

			<div class="options_group">
				<p class="form-field cf_duration_field">
					<label for="_cf_project_end_date"><?php _e( 'End Date', 'woo-crowdfunding' ); ?></label>
					<input type="text" class="short" name="_cf_project_end_date" id="_cf_project_end_date" value="<?php echo esc_attr( $end_date ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"<?php echo ( ! $is_new && ! empty( $end_date ) ) ? ' disabled="disabled"' : ''; ?> />
					<span class="woocommerce-help-tip" data-tip="<?php _e( 'When the project should end.', 'woo-crowdfunding' ); ?>"></span>
				</p>
			</div>

			<div class="cf_levels_options_group">
				<p class="form-field cf_levels_field wc-cf-fix">
					<?php
					$args = array(
						'post_type' => 'product_variation',
						'post_status' => array( 'private', 'publish' ),
						'numberposts' => -1,
						'orderby' => 'menu_order',
						'order' => 'ASC',
						'post_parent' => $post->ID
					);
					$levels = get_posts( $args );
					?>
					<label><?php _e( 'Contribution Levels', 'woo-crowdfunding' ); ?></label>
					<input type="hidden" id="_cf_project_levels_count" value="<?php echo count( $levels ); ?>" />
				</p>

				<ul id="cf-levels" class="levels">
					<?php
					$loop = 0;
					if ( $levels ) {
						foreach ( $levels as $level ) :
							$level_data = get_post_custom( $level->ID );
							?>
							<li id="level-<?php echo $loop; ?>" class="level woocommerce_variable_attributes" rel="<?php echo $loop; ?>">
								<input type="hidden" id="_level_<?php echo $loop; ?>_deleted" name="_cf_project[levels][<?php echo $loop; ?>][_deleted]" value="0" />
								<input type="hidden" id="_level_<?php echo $loop; ?>_id" name="_cf_project[levels][<?php echo $loop; ?>][id]" value="<?php echo $level->ID; ?>" />
								<input type="hidden" class="level-menu-order" name="_cf_project[levels][<?php echo $loop; ?>][menu_order]" value="<?php echo $loop; ?>" />

								<div class="level-header">
									<span class="level-title"><?php echo get_the_title( $level->ID ); ?></span>
									<span class="toggle-reward-details" aria-label="<?php _e( 'Toggle reward details', 'woo-crowdfunding' ); ?>"><?php _e( 'Toggle reward details', 'woo-crowdfunding' ); ?></span>
									<span class="tips sort" data-tip="<?php _e( 'Drag and drop to set rewards order', 'woo-crowdfunding' ); ?>"></span>
								</div>

								<div class="level-fields">
									<div class="level-row">
										<p class="form-row form-row-first">
											<label for="_level_<?php echo $loop; ?>_title"><?php _e( 'Title', 'woo-crowdfunding' ); ?></label>
											<input type="text" id="_level_<?php echo $loop; ?>_title" name="_cf_project[levels][<?php echo $loop; ?>][title]" value="<?php echo get_the_title( $level->ID ); ?>" />
										</p>

										<p class="form-row form-row-last">
											<label for="_level_<?php echo $loop; ?>_amount"><?php _e( 'Amount', 'woo-crowdfunding' ); ?> (<?php echo get_woocommerce_currency_symbol(); ?>)</label>
											<input type="text" id="_level_<?php echo $loop; ?>_amount" name="_cf_project[levels][<?php echo $loop; ?>][amount]" value="<?php if ( isset( $level_data['_price'][0] ) ) echo $level_data['_price'][0]; ?>" />
										</p>

										<p class="form-row form-row-full options">
											<label>
												<?php _e( 'No reward', 'woo-crowdfunding' ); ?>
												<input type="checkbox" class="checkbox" name="_cf_project[levels][<?php echo $loop; ?>][no_reward]"<?php echo 'yes' == $level_data['_virtual'][0] ? ' checked="checked"' : ''; ?> />
											</label>

											<label>
												<?php _e( 'Manage stock?', 'woo-crowdfunding' ); ?>
												<input type="checkbox" class="checkbox manage-stock" name="_cf_project[levels][<?php echo $loop; ?>][manage_stock]"<?php echo 'yes' == $level_data['_manage_stock'][0] ? ' checked="checked"' : ''; ?> />
											</label>
										</p>
									</div>

									<div class="level-row">
										<p class="form-row form-row-full">
											<label for="_level_<?php echo $loop; ?>_desc"><?php _e( 'Description', 'woo-crowdfunding' ); ?></label>
											<textarea id="_level_<?php echo $loop; ?>_desc" name="_cf_project[levels][<?php echo $loop; ?>][desc]"><?php echo $level->post_content; ?></textarea>
										</p>
									</div>

									<div class="level-row">
										<p class="form-row form-row-first stock-status">
											<label for="_level_<?php echo $loop; ?>_stock"><?php _e( 'Stock', 'woo-crowdfunding' ); ?></label>
											<select id="_level_<?php echo $loop; ?>_stock" name="_cf_project[levels][<?php echo $loop; ?>][stock_status]">
												<option value=""><?php _e( 'In stock', 'woo-crowdfunding' ); ?></option>
												<option value="outofstock"<?php echo 'outofstock' == $level_data['_stock_status'][0] ? ' selected="selected"' : ''; ?>><?php _e( 'Out of stock', 'woo-crowdfunding' ); ?></option>
											</select>
										</p>

										<p class="form-row form-row-first stock-quantity">
											<label for="_level_<?php echo $loop; ?>_qty"><?php _e( 'Quantity', 'woo-crowdfunding' ); ?></label>
											<input type="number" id="_level_<?php echo $loop; ?>_qty" name="_cf_project[levels][<?php echo $loop; ?>][stock]" value="<?php echo $level_data['_stock'][0]; ?>" size="5" step="any" />
										</p>
									</div>

									<p class="toolbar">
										<button type="button" id="level_<?php echo $loop; ?>_delete" class="button level-delete-button"><?php _e( 'Delete', 'woo-crowdfunding' ); ?></button>
									</p>
								</div>
							</li>
							<?php
							++$loop;
						endforeach;
					}
					?>

					<li id="new-level" class="level woocommerce_variable_attributes" rel="new">
						<input type="hidden" id="_level_new_deleted" name="_cf_project[levels][new][_deleted]" value="0" />
						<input type="hidden" id="_level_new_id" name="_cf_project[levels][new][id]" />
						<input type="hidden" class="level-menu-order" name="_cf_project[levels][new][menu_order]" />

						<div class="level-header">
							<span class="level-title"></span>
							<span class="toggle-reward-details" aria-label="<?php _e( 'Toggle reward details', 'woo-crowdfunding' ); ?>"><?php _e( 'Toggle reward details', 'woo-crowdfunding' ); ?></span>
							<span class="tips sort" data-tip="<?php _e( 'Drag and drop to set rewards order', 'woo-crowdfunding' ); ?>"></span>
						</div>

						<div class="level-fields">
							<div class="level-row">
								<p class="form-row form-row-first">
									<label for="_level_new_title"><?php _e( 'Title', 'woo-crowdfunding' ); ?></label>
									<input type="text" id="_level_new_title" name="_cf_project[levels][new][title]" />
								</p>

								<p class="form-row form-row-last">
									<label for="_level_new_amount"><?php _e( 'Amount', 'woo-crowdfunding' ); ?> (<?php echo get_woocommerce_currency_symbol(); ?>)</label>
									<input type="text" id="_level_new_amount" name="_cf_project[levels][new][amount]" />
								</p>

								<p class="form-row form-row-full options">
									<label>
										<?php _e( 'No reward', 'woo-crowdfunding' ); ?>
										<input type="checkbox" class="checkbox" name="_cf_project[levels][new][no_reward]" />
									</label>

									<label>
										<?php _e( 'Manage stock?', 'woo-crowdfunding' ); ?>
										<input type="checkbox" class="checkbox manage-stock" name="_cf_project[levels][new][manage_stock]" />
									</label>
								</p>
							</div>

							<div class="level-row">
								<p class="form-row form-row-full">
									<label for="_level_new_desc"><?php _e( 'Description', 'woo-crowdfunding' ); ?></label>
									<textarea id="_level_new_desc" name="_cf_project[levels][new][desc]"></textarea>
								</p>
							</div>

							<div class="level-row">
								<p class="form-row form-row-first stock-status">
									<label for="_level_new_stock"><?php _e( 'Stock', 'woo-crowdfunding' ); ?></label>
									<select id="_level_new_stock" name="_cf_project[levels][new][stock_status]">
										<option value=""><?php _e( 'In stock', 'woo-crowdfunding' ); ?></option>
										<option value="outofstock"><?php _e( 'Out of stock', 'woo-crowdfunding' ); ?></option>
									</select>
								</p>

								<p class="form-row form-row-first stock-quantity">
									<label for="_level_new_qty"><?php _e( 'Quantity', 'woo-crowdfunding' ); ?></label>
									<input type="number" id="_level_new_qty" name="_cf_project[levels][new][stock]" size="5" step="any" />
								</p>
							</div>

							<p class="toolbar">
								<button type="button" class="button level-delete-button"><?php _e( 'Delete', 'woo-crowdfunding' ); ?></button>
							</p>
						</div>
					</li>

					<li class="add-level">
						<a href="#" id="cf_level_add" class="add-level-link"><?php _e( 'Add contribution level', 'woo-crowdfunding' ); ?></a>
					</li>
				</ul>
			</div><!-- /.cf_levels_options_group -->
		</div><!-- /#cf_data -->
		<?php
	}

	/**
	 * Updates CF project progress.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order number.
	 */
	public function update_project_progress( $order_id ) {
		$order = wc_get_order( $order_id );
		$total = 0;

		foreach ( $order->get_items() as $order_item ) {
			if (  Woo_Crowdfunding::is_cf_project( $order_item['product_id'] ) ) {
				if ( empty( $project_id ) ) {
					$project_id = $order_item['product_id'];
				}

				$total += $order_item['total'];
			}
		}

		if ( isset( $project_id ) ) {
			$data = get_post_meta( $project_id, '_cf_project_data', true );

			if ( ! isset( $data['progress'] ) ) {
				$data['progress'] = $total;
			} else {
				$data['progress'] += $total;

				if ( $data['progress'] >= $data['goal'] ) {
					update_post_meta( $project_id, '_cf_project_complete', true );
				}
			}

			if ( ! isset( $data['contributors'] ) ) {
				$data['contributors'] = 1;
			} else {
				$data['contributors'] += 1;
			}

			update_post_meta( $project_id, '_cf_project_data', $data );
		}
	}

	/**
	 * Show crowdfunding project custom fields in admin
	 *
	 * @since 1.0.0
	 * @param object $order Order data object.
	 */
	public function cf_checkout_fields_display_admin_order_meta( $order ) {
		if ( Woo_Crowdfunding::is_cf_project_contribution_in_order( $order ) ) {
			echo '<p><strong>' . __( 'Publish donator', 'woo-crowdfunding' ) . ':</strong> ' . ( get_post_meta( $order->get_id(), '_publish_donator', true ) ? __( 'Yes', 'woo-crowdfunding' ) : __( 'No', 'woo-crowdfunding' ) ) . '</p>';
			echo '<p><strong>' . __( 'Publish amount', 'woo-crowdfunding' ) . ':</strong> ' . ( get_post_meta( $order->get_id(), '_publish_amount', true ) ? __( 'Yes', 'woo-crowdfunding' ) : __( 'No', 'woo-crowdfunding' ) ) . '</p>';
		}
	}

	/**
	 * Adds CF project filter in admin orders list
	 *
	 * @since 1.0.0
	 */
	public function filter_orders_by_product() {
		global $typenow;

		if ( 'shop_order' != $typenow ) {
			return false;
		}

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'orderby'        => 'title',
			'no_found_rows'  => false,
			'meta_query'     => array(
				array(
					'key'     => '_cf_project_data',
					'compare' => 'EXISTS'
				)
			)
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) : ?>
			<select name="cf_project">
				<option value=""><?php esc_html_e( 'All projects', 'woo-crowdfunding' ); ?></option>
				<?php while ( $query->have_posts() ) :
					$query->the_post(); ?>
					<option value="<?php the_ID(); ?>" <?php echo esc_attr( isset( $_GET['cf_project'] ) ? selected( get_the_id(), $_GET['cf_project'], false ) : '' ); ?>>
						<?php echo esc_html( get_the_title() ); ?>
					</option>
				<?php endwhile; ?>
			</select>
			<?php wp_reset_postdata(); ?>
		<?php endif;
	}

	/**
	 * Saves the data inputed into the product boxes into a serialized array.
	 *
	 * @since 1.0.0
	 */
	public function product_save_data() {
		global $post;

		$cf_project_data = $_POST['_cf_project_data'];
		$cf_project = $_POST['_cf_project'];
		$cf_project_end_date = $_POST['_cf_project_end_date'];

		$levels = $cf_project['levels'];
		if ( $levels['new'] ) {
			unset( $levels['new'] );
		}

		if ( $cf_project_data && is_array( $cf_project_data ) ) {
			foreach ( $_cf_project_data as $key => $value ) {
				$_cf_project_data[ $key ] = sanitize_text_field( $value );
			}
			update_post_meta( $post->ID, '_cf_project_data', $cf_project_data );
		}

		if ( $cf_project_end_date ) {
			update_post_meta( $post->ID, '_cf_project_end_date', sanitize_text_field( $cf_project_end_date ) );
		}

		if ( $levels ) {
			foreach ( $levels as $key => $level ) {
				if ( isset( $level['_deleted'] ) && "1" == $level['_deleted'] ) {
					if ( $level['id'] ) {
						$level_id = ( int ) $level['id'];
						wp_delete_post( $level_id );
					}
				} elseif ( $level['id'] ) {
					$level_id = ( int ) $level['id'];

					$level_post = array(
						'ID'           => $level_id,
						'post_content' => sanitize_text_field( $level['desc'] ),
						'post_title'   => sanitize_text_field( $level['title'] ),
						'post_status'  => $post->post_status,
						'menu_order'   => sanitize_text_field( $level['menu_order'] )
					);

					wp_update_post( $level_post );
				} else {
					$level_post = array(
						'post_title'   => sanitize_text_field( $level['title'] ),
						'post_content' => sanitize_text_field( $level['desc'] ),
						'post_status'  => 'publish',
						'post_author'  => get_current_user_id(),
						'post_parent'  => $post->ID,
						'post_type'    => 'product_variation',
						'menu_order'   => sanitize_text_field( $level['menu_order'] )
					);

					$level_id = wp_insert_post( $level_post );
				}

				update_post_meta( $level_id, '_price', sanitize_text_field( $level['amount'] ) );
				update_post_meta( $level_id, '_regular_price', sanitize_text_field( $level['amount'] ) );
				update_post_meta( $level_id, '_stock', empty( $level['stock'] ) ? 'NULL' : sanitize_text_field( $level['stock'] ) );
				update_post_meta( $level_id, '_virtual', empty( $level['no_reward'] ) ? 'no' : 'yes' );
				update_post_meta( $level_id, '_downloadable', 'no' );
				update_post_meta( $level_id, '_stock_status', ( ! empty( $level['stock'] ) || empty( $level['stock_status'] ) ) ? 'instock' : 'outofstock' );
				update_post_meta( $level_id, '_backorders', 'no' );
				update_post_meta( $level_id, '_manage_stock', empty( $level['manage_stock'] ) ? 'no' : 'yes' );
			}

			$post_parent = $post->ID;

			$children = get_posts( array(
				'post_parent'    => $post_parent,
				'posts_per_page' => -1,
				'post_type'      => 'product_variation',
				'fields'         => 'ids',
				'post_status'    => 'publish'
			) );

			$lowest_price = '';
			$highest_price = '';

			if ( $children ) {
				foreach ( $children as $child ) {
					$child_price = get_post_meta( $child, '_price', true );

					if ( ! $lowest_price || $child_price < $lowest_price ) {
						$lowest_price = $child_price;
					}

					if ( ! $highest_price || $child_price > $highest_price ) {
						$highest_price = $child_price;
					}
				}
			}

			update_post_meta( $post_parent, '_price', $lowest_price );
			update_post_meta( $post_parent, '_min_variation_price', $lowest_price );
			update_post_meta( $post_parent, '_max_variation_price', $highest_price );
		}
	}

	/**
	 * Adds new product type.
	 *
	 * @since 1.0.0
	 * @param array $types Product types.
	 * @return array
	 */
	public function add_product_type( $types ) {
		$types['cf_project'] = __( 'Crowdfunding Project', 'woo-crowdfunding' );
		return $types;
	}

	/**
	 * Prevents WooCommerce from changing contribution title on insert.
	 *
	 * @since 1.0.0
	 * @param string $title Product title.
	 * @param WC_Product_Variation $product Product data object.
	 * @param string $title_base Product title.
	 * @param string $title_suffix Product attributes.
	 * @return
	 */
	public function modify_contribution_title( $title, $product, $title_base, $title_suffix ) {
		$parent_id = $product->get_parent_id();
		$parent = wc_get_product( $parent_id );

		if ( 'cf_project' == $parent->get_type() ) {
			return $product->get_name();
		}

		return $title;
	}

	/**
	 * Changes item name, if it is CF project contribution (order edit page).
	 *
	 * @since 1.0.0
	 * @param string $item_name Order item name.
	 * @param object $item Order item object.
	 * @return string
	 */
	public function rename_order_item( $item_name, $item ) {
		if ( ! is_admin() && Woo_Crowdfunding::is_cf_project( $item->get_product_id() ) ) {
			$product = wc_get_product( $item->get_product_id() );
			$item_name = $product->get_title() . ' - ' . $item_name;
		}

		return $item_name;
	}

	/**
	 * Adds a new tab to the product interface.
	 *
	 * @since 1.0.0
	 * @param array $product_data_tabs Product tabs parameters.
	 * @return array
	 */
	public function add_cf_project_tab( $product_data_tabs ) {
		$product_data_tabs['cf-tab'] = array(
			'label'    => __( 'CF Project', 'woo-crowdfunding' ),
			'target'   => 'cf_data',
			'class'    => array( 'cf_tab', 'show_if_cf_project' ),
			'priority' => 5
		);

		return $product_data_tabs;
	}

	/**
	 * Modifies WP query to filter orders for chosen CF project
	 *
	 * @since 1.0.0
	 * @param object $query Main WP query.
	 * @return object
	 */
	public function query_filter_orders_by_product( $query ) {
		global $pagenow, $typenow;

		if ( $query->is_main_query() && is_admin() && 'edit.php' == $pagenow && 'shop_order' == $typenow && isset( $_GET['cf_project'] ) ) {
			global $wpdb;

			$product_id = wc_clean( $_GET['cf_project'] );

			$order_items = $wpdb->get_col( $wpdb->prepare( "SELECT order_item_id FROM wp_woocommerce_order_itemmeta WHERE meta_key = '_product_id' AND meta_value = %d", $product_id ) );

			if ( empty( $order_items ) ) {
				return $query;
			}

			$items_count = count( $order_items );
			$placeholders = array_fill( 0, $items_count, '%d' );
			$format = implode( ', ', $placeholders );

			$orders = $wpdb->get_col( $wpdb->prepare( "SELECT order_id FROM wp_woocommerce_order_items WHERE order_item_id IN ($format)", $order_items ) );

			if ( empty( $orders ) ) {
				return $query;
			}

			$query->set( 'post__in', $orders );
		}

		return $query;
	}
}