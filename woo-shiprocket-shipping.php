<?php
/**
 * Plugin Name: WooCommerce Shiprocket Shipping
 * Plugin URI:  (Your Plugin URI)
 * Description: Integrate Shiprocket shipping rates into your WooCommerce store.
 * Version:     1.0.0
 * Author:      Shiv Singh
 * Author URI:  (Your Website)
 * Text Domain: woo-shiprocket-shipping
 * Domain Path: /languages
 *
 * @package woo-shiprocket-shipping
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include the main class.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-shiprocket-shipping-method.php';

/**
 * Initialize the plugin.
 *
 * @return void
 */
function woo_shiprocket_shipping_init() {
	if ( ! class_exists( 'WC_Shipping_Method' ) ) {
		return;
	}

	new Woo_Shiprocket_Shipping_Method();
}
add_action( 'woocommerce_shipping_init', 'woo_shiprocket_shipping_init' );

/**
 * Add the shipping method to WooCommerce.
 *
 * @param array $methods Existing shipping methods.
 *
 * @return array Modified shipping methods.
 */
function woo_shiprocket_shipping_add_method( $methods ) {
	$methods['woo_shiprocket_shipping'] = 'Woo_Shiprocket_Shipping_Method';
	return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'woo_shiprocket_shipping_add_method' );