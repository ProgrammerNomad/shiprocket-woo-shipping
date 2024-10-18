<?php
/**
 * Shiprocket Pincode Check
 *
 * @package shiprocket-woo-shipping
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_single_product_summary', 'show_shiprocket_pincode_check', 20);

/**
 * Show an option to check serviceability to a pincode on the product page.
 *
 * @return void
 */
function show_shiprocket_pincode_check()
{
    global $product;

    $settings = get_option('woocommerce_woo_shiprocket_shipping_settings');

    // Check if the "Show Pincode Check" option is enabled in the settings
    if (!isset($settings['show_pincode_check']) || $settings['show_pincode_check'] !== 'yes') {
        return; // Exit if the option is not enabled
    }
    ?>
    <div id="pincode_check_form">
        <input type="text" id="shiprocket_pincode_check" name="shiprocket_pincode_check" value=""
            placeholder="Enter Pincode">

        <button id="check_pincode" onClick="checkPincode_Shiprocket_Manual()"> Check Pincode </button>
    </div>
    <div id="pincode_response"></div>
    <script>

        function checkPincode_Shiprocket_Manual() {
            var pincode = document.getElementById("shiprocket_pincode_check").value;
            if (pincode == '') {
                jQuery('#pincode_response').text("This pincode field is required!")
            } else {
                // Set the pincode in localStorage
                localStorage.setItem('shiprocket_pincode', pincode);

                ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>'; // Get AJAX URL

                var data = {
                    'action': 'frontend_action_without_file',
                    'delivery_postcode': pincode,
                    'product_id': <?php echo esc_url($product->get_id()); ?>,
                };

                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        jQuery('#pincode_response').html(response); // Display the response
                        // Set the response message in localStorage
                        localStorage.setItem('shiprocket_pincode_response', response);
                    }
                });
            }
        }

        // Check if a pincode is saved in localStorage and pre-fill the input field
        var savedPincode = localStorage.getItem('shiprocket_pincode');
        if (savedPincode) {
            document.getElementById("shiprocket_pincode_check").value = savedPincode;
        }

        // Check if a pincode response is saved in localStorage and display the message
        var savedResponse = localStorage.getItem('shiprocket_pincode_response');
        if (savedResponse) {
            jQuery('#pincode_response').html(savedResponse);
        }
    </script>
    <?php
}

add_action('wp_ajax_frontend_action_without_file', 'shiprocket_pincode_check_ajax_handler');
add_action('wp_ajax_nopriv_frontend_action_without_file', 'shiprocket_pincode_check_ajax_handler');

/**
 * AJAX handler to check pincode serviceability using Shiprocket API.
 *
 * @return void
 */
function shiprocket_pincode_check_ajax_handler()
{

    $delivery_postcode = sanitize_text_field($_POST['delivery_postcode']);

    // Get the product ID from the AJAX request
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if (!$product_id) {
        wp_send_json_error(array('message' => __('Invalid product ID.', 'shiprocket-woo-shipping')));
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array('message' => __('Product not found.', 'shiprocket-woo-shipping')));
    }

    $weight = floatval($product->get_weight()); // Get the product weight

    // Get the Shiprocket token from settings
    $settings = get_option('woocommerce_woo_shiprocket_shipping_settings');
    $token = isset($settings['token']) ? $settings['token'] : '';

    if (!$token) {
        wp_send_json_error(array('message' => __('Shiprocket token not found.', 'shiprocket-woo-shipping')));
    }

    // Shiprocket API endpoint URL
    $endpoint_url = 'https://apiv2.shiprocket.in/v1/courier/ratingserviceability';

    // Get the store's postcode from WooCommerce settings
    $pickup_postcode = get_option('woocommerce_store_postcode');

    // Shipping data 
    $cod = '0';
    $declared_value = '200';
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
        'delivery_postcode' => $delivery_postcode,
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

    // Check for errors
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => $response->get_error_message()));
    } else {
        // Decode the JSON response
        $Response = json_decode($response['body'], true);

        $available_courier_companies = $Response['data']['available_courier_companies'];

        $ShowOutput = '';

        if (empty($available_courier_companies)) {
            $ShowOutput = '<p style="font-weight: bold; padding: 10px 0px 0px 0px;" >' . esc_html__('No courier companies available for your pincode.', 'shiprocket-woo-shipping') . '</p>';
        } else {
            $QuickCouriers = array('Quick-Ola', 'Quick-Borzo', 'Quick-Flash', 'Quick-Qwqer', 'Quick-Mover', 'Quick-Porter', 'Loadshare Hyperlocal');
            foreach ($available_courier_companies as $company) {

                if (in_array($company['courier_name'], $QuickCouriers)) {
                    $QuickDelivery = true;
                    break;
                } else {
                    $QuickDelivery = false;
                }

            }
            if ($QuickDelivery) {
                $ShowOutput = '<p style="font-weight: bold; padding: 10px 0px 0px 0px;">' . esc_html__('Don\'t wait! Order now and get it delivered to your doorstep within the next 2 hours.', 'shiprocket-woo-shipping') . '</p>';
            } else {
                // Remove items with empty or zero estimated_delivery_days
                $filteredItems = array_filter($available_courier_companies, function ($item) {
                    return !empty($item['estimated_delivery_days']) && $item['estimated_delivery_days'] !== 0;
                });

                // Reset array keys after filtering
                $filteredItems = array_values($filteredItems);

                /* translators: %1$s: City name, %2$d: Number of days for delivery. */
                $ShowOutput = '<p style="font-weight: bold; padding: 10px 0px 0px 0px;">' . sprintf(esc_html__('Fast delivery to %1$s! Your order arrives in just %2$d days with our expedited shipping.', 'woo-shiprocket-shipping'), esc_html($filteredItems[0]['city']), esc_html($filteredItems[0]['estimated_delivery_days'])) . '</p>';
            }
        }


        echo $ShowOutput;
    }



    wp_die();
}