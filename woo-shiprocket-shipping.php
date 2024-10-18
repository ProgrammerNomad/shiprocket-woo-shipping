<?php
/**
 * Plugin Name: Woo Shiprocket Shipping
 * Plugin URI:  https://github.com/ProgrammerNomad/woo-shiprocket-shipping
 * Description: Integrate Shiprocket shipping rates into your WooCommerce store.
 * Version:     1.0.0
 * Author:      Shiv Singh
 * Author URI:  https://github.com/ProgrammerNomad/
 * Text Domain: woo-shiprocket-shipping
 * Domain Path: /languages
 *
 * @package woo-shiprocket-shipping
 */

// Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-shiprocket-shipping.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/woo-shiprocket-shipping-pincode.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/woo-shiprocket-shipping-rates.php'; // Include the new file
}