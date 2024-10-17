<?php
/**
 * Shiprocket Shipping Rates
 *
 * @package woo-shiprocket-shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get shipping rates from Shiprocket API.
 *
 * @param string $pincode Destination pincode.
 * @param float  $weight  Package weight.
 *
 * @return array Shipping rates.
 */
function woo_shiprocket_get_rates( $pincode, $weight, $Dimensions, $total_amount ) {
    // Get the Shiprocket token from settings
    $settings = get_option( 'woocommerce_woo_shiprocket_shipping_settings' );
    $token = isset( $settings['token'] ) ? $settings['token'] : '';

    if ( ! $token ) {
        return array(); // Or handle the error appropriately
    }

    // Shiprocket API endpoint URL
    $endpoint_url = 'https://apiv2.shiprocket.in/v1/courier/ratingserviceability';

    // Get the store's postcode from WooCommerce settings
    $pickup_postcode = get_option( 'woocommerce_store_postcode' );

    // Shipping data 
    $cod = '0';
    $declared_value = $total_amount;
    $rate_calculator = '1';
    $blocked = '1';
    $is_return = '0';
    $is_web = '1';
    $is_dg = '0';
    $only_qc_couriers = '0';
    $length = '12';
    $breadth = '15';
    $height = '10';

    // Build the query string
    $query_string = http_build_query(array(
        'pickup_postcode' => $pickup_postcode,
        'delivery_postcode' => $pincode,
        'weight' => $weight,
        'cod' => $cod,
        'declared_value' => $declared_value,
        'rate_calculator' => $rate_calculator,
        'blocked' => $blocked,
        'is_return' => $is_return,
        'is_web' => $is_web,
        'is_dg' => $is_dg,
        'only_qc_couriers' => $only_qc_couriers,
        'length' => $length,
        'breadth' => $breadth,
        'height' => $height,
    ));

    // Construct the full URL with the query string
    $full_url = $endpoint_url . '?' . $query_string;

    // Build the request arguments
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
        ),
        'method' => 'GET',
    );

    // Make the API request
    $response = wp_remote_get( $full_url, $args );

    if ( is_wp_error( $response ) ) {
        return array(); // Or handle the error appropriately
    }

    $body = json_decode( wp_remote_retrieve_body( $response ) );

    // Process the API response and extract rates
    $rates = array();
    if ( isset( $body->status ) && $body->status == 200 && isset( $body->data->available_courier_companies ) ) {
        foreach ( $body->data->available_courier_companies as $company ) {
            // Assuming the API response includes rate information (e.g., `company->rate`)
            $rates[] = array(
                'id'   => sanitize_title( $company->courier_name ),
                'name' => $company->courier_name,
                'cost' => $company->rate, // Replace with the actual rate field from the API response
            );
        }
    }

    return $rates;
}