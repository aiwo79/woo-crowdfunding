<?php

/**
 * Provides the markup for CF project contribution levels.
 *
 * @link https://ivoslavik.cz
 * @since 1.0.0
 *
 * @package Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/includes/partials
 */

global $post, $product;

$args = array(
	'post_type'      => 'product_variation',
	'post_status'    => array( 'private', 'publish' ),
	'post_parent'    => $post->ID,
	'posts_per_page' => -1,
	'order'          => 'ASC',
	'orderby'        => 'menu_order',
	'no_found_rows'  => false
);

$levels = get_posts( $args );

do_action( 'woocommerce_before_add_to_cart_button' );

if ( empty( $levels ) ) : ?>
	<p><?php _e( 'There are no contribution levels.', 'woo-crowdfunding' ); ?></p>
<?php elseif ( ! Woo_Crowdfunding::is_cf_project_set( $post->ID ) ) : ?>
	<p><?php _e( 'It is not possible to contribute to this project, it is not yet set.', 'woo-crowdfunding' ); ?></p>
<?php elseif ( Woo_Crowdfunding::is_cf_project_active( $post->ID ) ) : ?>
	<div class="contribution-levels">
		<?php
		foreach ( $levels as $level ) :
			$level_data = get_post_custom( $level->ID );
			?>
			<form action="<?php echo get_permalink() . '?add-to-cart=' . $level->ID; ?>" method="post" class="level">
				<div class="level-header">
					<h3 class="level-title"><?php echo get_the_title( $level->ID ); ?></h3>
					<span class="level-amount"><?php echo isset( $level_data['_price'][0] ) ? wc_price( $level_data['_price'][0] ) : wc_price( 0 ); ?></span>
				</div>

				<div class="level-description">
					<p><?php echo $level->post_content; ?></p>
				</div>

				<div class="level-button">
					<?php
					$manage_stock = get_post_meta( $level->ID, '_manage_stock', true );
					$product = wc_get_product( $level->ID );
					$stock_quntity = $product->get_stock_quantity();

					if ( ( 'yes' == $manage_stock && $stock_quntity > 0 ) || 'no' == $manage_stock ) : ?>
						<button type="submit" class="level-submit-button"><?php _e( 'Support project', 'woo-crowdfunding' ); ?></button>
					<?php else : ?>
						<p><?php _e( 'This contribution is not available anymore.', 'woo-crowdfunding' ); ?></p>
					<?php endif; ?>
				</div>
			</form>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<p><?php _e( 'It is not possible to contribute to this project, it is over now.', 'woo-crowdfunding' ); ?></p>
<?php endif; ?>