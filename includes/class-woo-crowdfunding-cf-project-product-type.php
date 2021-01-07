<?php

/**
 * Custom product type
 *
 * @link       https://ivoslavik.cz
 * @since      1.0.0
 *
 * @package    Woo_Crowdfunding
 * @subpackage Woo_Crowdfunding/includes
 */

/*
 * Wraps class to make sure we extend existing product types.
 *
 * @since      1.0.0
 */
function woo_crowdfunding_create_custom_product_type() {
  /**
   * Adds new product type for CF projects.
   *
   * @since      1.0.0
   * @package    Woo_Crowdfunding
   * @subpackage Woo_Crowdfunding/includes
   * @author     Ivo Slavík <info@ivoslavik.cz>
   */
	class WC_Product_Cf_Project extends WC_Product {
		public function __construct( $product ) {
			$this->product_type = 'cf_project';
			parent::__construct( $product );
		}
	}

}

add_action( 'init', 'woo_crowdfunding_create_custom_product_type' );