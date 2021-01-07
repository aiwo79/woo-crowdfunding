<?php

/**
 * The admin and public-facing functionality of the plugin.
 *
 * @link https://ivoslavik.cz
 * @since 1.0.0
 *
 * @package Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/includes
 */

/**
 * The admin and public-facing functionality of the plugin.
 *
 * @package Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/includes
 * @author Ivo Slavík <info@ivoslavik.cz>
 */
class Woo_Crowdfunding_Shared {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() { }

	/**
	 * Formats price in a special way for CF projects.
	 *
	 * @since 1.0.0
	 */
	public function product_price_html( $price, $product ) {
		if ( Woo_Crowdfunding::is_cf_project( $product ) ) {
			if ( is_admin() ) {
				$price = $this->product_admin_price_string( $product );
			} else {
				$price = $this->product_frontend_price_string( $product );
			}
		}

		return $price;
	}

	/**
	 * Shows CF project data instead of standard product price in admin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function product_admin_price_string( $product ) {
		global $post;

		if ( ! Woo_Crowdfunding::is_cf_project( $product ) ) {
			return;
		}

		$product_id = $product->get_id();

		$cancel = get_post_meta( $product_id, '_cf_project_cancel', true );
		$complete = get_post_meta( $product_id, '_cf_project_complete', true );
		$data = get_post_meta( $post->ID, '_cf_project_data', true );

		$progress = 0;
		if ( ! empty( $data['progress'] ) ) {
			$progress = $data['progress'];
		}

		$cf_string = '';
		if ( $complete ) {
			$cf_string .= __( 'Raised', 'woo-crowdfunding' ) . ' ' . wc_price( $progress ) . ' / ' . wc_price( $data['goal'] );
		} elseif ( $cancel ) {
			$cf_string = __( 'Cancelled', 'woo-crowdfunding' );
		} else {
			$cf_string .= wc_price( $progress ) . ' / ' . wc_price( $data['goal'] );
		}

		return apply_filters( 'woocommerce_cf_price_string', $cf_string, $product );
	}

	/**
	 * Shows CF project data instead of standard product price on the front end.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function product_frontend_price_string( $product ) {
		global $post, $wpdb;

		if ( ! Woo_Crowdfunding::is_cf_project( $product ) ) {
			return;
		}

		$data = get_post_meta( $post->ID, '_cf_project_data', true );
		$end_date = get_post_meta( $post->ID, '_cf_project_end_date', true );

		$days_left = 0;

		if ( ! empty( $end_date ) ) {
			$now = strtotime( date( 'Y-m-d' ) );
			$then = strtotime( $end_date );
			$datediff = $then - $now;

			$days_left = floor( $datediff / ( 60 * 60 * 24 ) ) + 1;

			if ( $days_left < 0 ) {
				$days_left = 0;
			}
		}

		$progress = $percent = 0;

		if ( ! empty( $data['progress'] ) ) {
			$progress = $data['progress'];
			if ( ! empty( $data['goal'] ) ) {
				$percent = round( $progress / $data['goal'] * 100 );
			}
		}

		if ( $percent > 100 ) {
			$progress_bar_width = 100;
		} else {
			$progress_bar_width = $percent;
		}

		if ( ! empty( $data['contributors'] ) ) {
			$contributors = $data['contributors'];
		} else {
			$contributors = 0;
		}

		$args = array(
			'post_type'   => 'product_variation',
			'post_status' => array( 'private', 'publish' ),
			'numberposts' => -1,
			'orderby'     => 'id',
			'order'       => 'ASC',
			'post_parent' => $post->ID
		);
		$levels = get_posts( $args );

		if ( $levels && count( $levels ) > 0 && ! empty( $data['goal'] ) && ! empty( $end_date ) ) {
			ob_start();
			?>
			<div class="progress">
				<div class="info-top">
					<span><?php _e( 'Raised', 'woo-crowdfunding' ); ?></span>
					<?php printf( __( '%1$s from %2$s', 'woo-crowdfunding' ), '<strong>' . wc_price( $progress ) . '</strong>', '<strong>' . wc_price( $data['goal'] ) . '</strong>' ); ?></div>
				<div class="progress-bar">
					<div class="progress-bar-percent" style="width: <?php echo $progress_bar_width; ?>%;"></div>
				</div>
				<div class="info-bottom">
					<div class="percentage">
						<strong><?php echo $percent . ' %'; ?></strong>
					</div>
					<div class="days-left">
						<?php printf( _n( '<strong>1 day</strong> to go', '<strong>%1$d days</strong> to go', $days_left, 'woo-crowdfunding' ), $days_left ); ?>
					</div>
					<?php if ( is_product() ) : ?>
						<div class="contributors-count">
							<?php printf( _n( '<strong>1</strong> contributor', '<strong>%1$d</strong> contributors', $contributors, 'woo-crowdfunding' ), $contributors ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
			$price_html = ob_get_clean();
		} else {
			$price_html = '<em>' . __( 'Project not set up.', 'woo-crowdfunding' ) . '</em>';
		}

		return $price_html;
	}
}