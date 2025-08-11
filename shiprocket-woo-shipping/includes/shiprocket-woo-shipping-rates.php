<?php
/**
 * Shiprocket Shipping Rates
 *
 * @package shiprocket-woo-shipping
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get Shiprocket authentication token.
 *
 * @return string|false Bearer token or false on failure.
 */
function woo_shiprocket_get_auth_token() {
    // Check for cached token first
    $token = get_transient('shiprocket_auth_token');
    if ($token) {
        return $token;
    }

    // Get the Shiprocket settings
    $settings = get_option('woocommerce_woo_shiprocket_shipping_settings');
    $api_user_email = isset($settings['api_user_email']) ? $settings['api_user_email'] : '';
    $api_user_password = isset($settings['api_user_password']) ? $settings['api_user_password'] : '';

    if (!$api_user_email || !$api_user_password) {
        error_log('Shiprocket: Missing API user credentials');
        return false;
    }

    // Login endpoint URL as per official documentation
    $login_url = 'https://apiv2.shiprocket.in/v1/external/auth/login';

    // Request data
    $body = json_encode(array(
        'email' => $api_user_email,
        'password' => $api_user_password,
    ));

    // Build the request arguments
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => $body,
        'method' => 'POST',
        'timeout' => 30,
    );

    // Make the authentication request
    $response = wp_remote_post($login_url, $args);

    if (is_wp_error($response)) {
        error_log('Shiprocket Auth Error: ' . $response->get_error_message());
        return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        error_log('Shiprocket Auth Error: HTTP ' . $response_code . ' - ' . wp_remote_retrieve_body($response));
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['token'])) {
        // Cache token for 23 hours (tokens typically last 24 hours)
        set_transient('shiprocket_auth_token', $body['token'], 23 * HOUR_IN_SECONDS);
        return $body['token'];
    }

    error_log('Shiprocket Auth Error: No token in response');
    return false;
}

/**
 * Get shipping rates from Shiprocket API.
 *
 * @param string $pincode Destination pincode.
 * @param float  $weight  Package weight.
 * @param array  $dimensions Package dimensions.
 * @param float  $total_amount Cart total amount.
 *
 * @return array Shipping rates.
 */
function woo_shiprocket_get_rates($pincode, $weight, $dimensions, $total_amount)
{
    // Get authentication token
    $auth_token = woo_shiprocket_get_auth_token();
    if (!$auth_token) {
        error_log('Shiprocket: Failed to get authentication token');
        return array();
    }

    // Get the Shiprocket settings
    $settings = get_option('woocommerce_woo_shiprocket_shipping_settings');
    $pickup_postcode = isset($settings['pickup_postcode']) ? $settings['pickup_postcode'] : '';

    // Fallback to WooCommerce store postcode if not set in settings
    if (empty($pickup_postcode)) {
        $pickup_postcode = get_option('woocommerce_store_postcode');
        
        // Additional fallback to base location
        if (empty($pickup_postcode)) {
            $pickup_postcode = WC()->countries->get_base_postcode();
        }
    }

    if (!$pickup_postcode) {
        error_log('Shiprocket: Missing pickup postcode');
        return array();
    }

    // Check cache first
    $cache_duration = isset($settings['cache_duration']) ? intval($settings['cache_duration']) : 10;
    $cache_key = 'shiprocket_rates_' . md5($pickup_postcode . $pincode . $weight . $total_amount);
    $cached_rates = wp_cache_get($cache_key);
    
    if (false !== $cached_rates) {
        return $cached_rates;
    }

    // Shiprocket API endpoint URL
    $endpoint_url = 'https://apiv2.shiprocket.in/v1/courier/ratingserviceability';

    // Use dimensions from package or set defaults
    $length = isset($dimensions['length']) && $dimensions['length'] > 0 ? $dimensions['length'] : 12;
    $breadth = isset($dimensions['breadth']) && $dimensions['breadth'] > 0 ? $dimensions['breadth'] : 15;
    $height = isset($dimensions['height']) && $dimensions['height'] > 0 ? $dimensions['height'] : 10;

    // Shipping data 
    $cod = '0';
    $declared_value = $total_amount;
    $rate_calculator = '1';
    $blocked = '1';
    $is_return = '0';
    $is_web = '1';
    $is_dg = '0';
    $only_qc_couriers = '0';

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

    // Build the request arguments with Bearer token authentication
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $auth_token,
            'Content-Type' => 'application/json',
        ),
        'method' => 'GET',
        'timeout' => 30,
    );

    // Make the API request
    $response = wp_remote_get($full_url, $args);

    if (is_wp_error($response)) {
        error_log('Shiprocket API Error: ' . $response->get_error_message());
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        error_log('Shiprocket API Error: HTTP ' . $response_code . ' - ' . wp_remote_retrieve_body($response));
        return array();
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

        // Get show_top_courier setting
        $show_top_courier = isset($settings['show_top_courier']) ? $settings['show_top_courier'] : 'yes';

        if ($show_top_courier !== 'yes') {
            // Show all companies
            $filtered_companies = $companies;
        } else {
            // Show only top 5 rated quick delivery and 5 normal delivery companies
            $QuicklyDeliveries = [];
            $NormalDeliveries = [];
            
            foreach ($companies as $company) {
                if ($company['estimated_delivery_days'] <= 1) {
                    $QuicklyDeliveries[] = $company;
                } else {
                    $NormalDeliveries[] = $company;
                }
            }

            // Sort QuicklyDeliveries by delivery_performance and get top 5
            usort($QuicklyDeliveries, function ($a, $b) {
                return $b['delivery_performance'] <=> $a['delivery_performance']; // Descending order (higher is better)
            });
            $QuicklyDeliveries = array_slice($QuicklyDeliveries, 0, 5);

            // Sort NormalDeliveries by delivery_performance and get top 5
            usort($NormalDeliveries, function ($a, $b) {
                return $b['delivery_performance'] <=> $a['delivery_performance']; // Descending order (higher is better)
            });
            $NormalDeliveries = array_slice($NormalDeliveries, 0, 5);

            $filtered_companies = array_merge($QuicklyDeliveries, $NormalDeliveries);
        }

        foreach ($filtered_companies as $company) {
            if ($company['estimated_delivery_days'] <= 1) {
                $PostFixText = 'Same-Day Delivery';
            } else {
                $PostFixText = $company['estimated_delivery_days'] . ' days delivery';
            }

            $rates[] = array(
                'id' => sanitize_title($company['courier_name']),
                'name' => $company['courier_name'] . ' - ' . $PostFixText,
                'cost' => $company['rate'],
            );
        }
    }

    // Cache the results
    if (!empty($rates)) {
        wp_cache_set($cache_key, $rates, '', $cache_duration * 60); // Convert minutes to seconds
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