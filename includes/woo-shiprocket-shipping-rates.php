<?php
/**
 * Shiprocket Shipping Rates
 *
 * @package woo-shiprocket-shipping
 */

if (!defined('ABSPATH')) {
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
function woo_shiprocket_get_rates($pincode, $weight, $Dimensions, $total_amount)
{
    // Get the Shiprocket token from settings
    $settings = get_option('woocommerce_woo_shiprocket_shipping_settings');
    $token = isset($settings['token']) ? $settings['token'] : '';

    if (!$token) {
        return array(); // Or handle the error appropriately
    }

    // Shiprocket API endpoint URL
    $endpoint_url = 'https://apiv2.shiprocket.in/v1/courier/ratingserviceability';

    // Get the store's postcode from WooCommerce settings
    $pickup_postcode = get_option('woocommerce_store_postcode');

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
    $response = wp_remote_get($full_url, $args);

    if (is_wp_error($response)) {
        return array(); // Or handle the error appropriately
    }

    $body = json_decode(wp_remote_retrieve_body($response));

    // Process the API response and extract rates
    $rates = array();
    if (isset($body->status) && $body->status == 200 && isset($body->data->available_courier_companies)) {

        $companies = $body->data->available_courier_companies;
        $companies = objectToArray($companies);

        uasort($companies, function ($a, $b) {
            return $a['estimated_delivery_days'] <=> $b['estimated_delivery_days'];
        });

        // echo '<pre>';
        // print_r($companies);

        // die();

        if (!isset($settings['show_top_courier']) || $settings['show_top_courier'] !== 'yes') {

            // Show all companies
            $companies = $companies;

        } else {

            // Show only top 5 rated quick delivery and 5 nomal delivery companies

            $QuicklyDeliveries = [];
            $NormalDeliveries = [];
            foreach ($companies as $company) {

                if ($company['estimated_delivery_days'] <= 1) {
                    $QuicklyDeliveries[] = $company;
                } else {
                    $NormalDeliveries[] = $company;
                }

            }

            // now sort QuicklyDeliveries top 5 quickly delivery companies accoring to delivery_performance and get top 5

            usort($QuicklyDeliveries, function ($a, $b) {
                return $a['delivery_performance'] <=> $b['delivery_performance'];
            });

            $QuicklyDeliveries = array_slice($QuicklyDeliveries, 0, 5);

            // now sort NormalDeliveries top 5 normal delivery companies accoring to delivery_performance and get top 5

            usort($NormalDeliveries, function ($a, $b) {
                return $a['delivery_performance'] <=> $b['delivery_performance'];
            });

            $NormalDeliveries = array_slice($NormalDeliveries, 0, 5);

            $companies = array_merge($QuicklyDeliveries, $NormalDeliveries);

        }

        foreach ($companies as $company) {

            if ($company['estimated_delivery_days'] <= 1) {

                $PostFixText = 'Same-Day Delivery';

            } else {
                $PostFixText = $company['estimated_delivery_days'] . ' days delivery';
            }

            $rates[] = array(
                'id' => sanitize_title($company['courier_name']),
                'name' => $company['courier_name'].' - '.$PostFixText,
                'cost' => $company['rate'], // Replace with the actual rate field from the API response
            );
        }
    }

    return $rates;
}

function objectToArray($obj)
{
    if (is_object($obj)) {
        $obj = (array) $obj;
    }

    if (is_array($obj)) {
        $new = array();
        foreach ($obj as $key => $val) {
            $new[$key] = objectToArray($val);
        }
    } else {
        $new = $obj;
    }

    return $new;
}