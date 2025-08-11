<?php
/**
 * Shiprocket Pincode Check
 *
 * @package shiprocket-woo-shipping
 */

if (!defined('ABSPATH')) {
    exit;
}

// Hook into multiple locations to ensure pincode check appears in all themes
add_action('woocommerce_single_product_summary', 'show_shiprocket_pincode_check', 35); // After add to cart
add_action('woocommerce_after_single_product_summary', 'show_shiprocket_pincode_check_fallback', 5); // Fallback position

/**
 * Show an option to check serviceability to a pincode on the product page.
 *
 * @return void
 */
function show_shiprocket_pincode_check()
{
    global $product;

    // Ensure we're on a product page and have a product
    if (!is_product() || !$product || !is_a($product, 'WC_Product')) {
        return;
    }

    $settings = get_option('woocommerce_woo_shiprocket_shipping_settings');

    // Check if the "Show Pincode Check" option is enabled in the settings
    if (!isset($settings['show_pincode_check']) || $settings['show_pincode_check'] !== 'yes') {
        return; // Exit if the option is not enabled
    }

    // Mark that pincode check has been displayed to prevent duplicates
    static $displayed = false;
    if ($displayed) {
        return;
    }
    $displayed = true;
    
    // Enqueue the pincode check CSS
    wp_enqueue_style('shiprocket-pincode-check', plugin_dir_url(dirname(__FILE__)) . 'css/pincode-check.css', array(), '1.0.12');
    ?>
    <div class="tostishop-pincode-check">
        <div class="pincode-check-container">
            <div class="pincode-delivery-header">
                <h4 style="margin: 0; font-size: 16px; font-weight: 600; color: #343a40;">
                    <?php _e('Delivery Options', 'shiprocket-woo-shipping'); ?>
                </h4>
                <svg class="pincode-delivery-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                </svg>
            </div>
            <form class="pincode-form" autocomplete="off" onsubmit="return false;">
                <input type="text" 
                       id="shiprocket_pincode_check" 
                       name="shiprocket_pincode_check" 
                       value=""
                       placeholder="<?php esc_attr_e('Enter pincode', 'shiprocket-woo-shipping'); ?>"
                       class="pincode-input"
                       maxlength="6"
                       pattern="[0-9]{6}">
                <button type="button" 
                        id="check_pincode" 
                        class="pincode-check-btn" 
                        onclick="checkPincode_Shiprocket_Manual()">
                    <span><?php _e('Check', 'shiprocket-woo-shipping'); ?></span>
                </button>
            </form>
            <p class="pincode-help-text">
                <?php _e('Please enter PIN code to check delivery time & Pay on Delivery Availability', 'shiprocket-woo-shipping'); ?>
            </p>
            <div id="pincode_response" class="pincode-response" style="display: none;"></div>
        </div>
    </div>
    <script>
        function checkPincode_Shiprocket_Manual() {
            var pincode = document.getElementById("shiprocket_pincode_check").value.trim();
            var checkBtn = document.getElementById("check_pincode");
            var responseDiv = document.getElementById("pincode_response");
            
            // Validate pincode
            if (pincode === '') {
                showPincodeResponse("This pincode field is required!", "error");
                return;
            }
            
            if (!/^[0-9]{6}$/.test(pincode)) {
                showPincodeResponse("Please enter a valid 6-digit pincode!", "error");
                return;
            }
            
            // Set loading state
            checkBtn.classList.add('loading');
            checkBtn.disabled = true;
            responseDiv.style.display = 'none';
            
            // Set the pincode in localStorage
            localStorage.setItem('shiprocket_pincode', pincode);

            ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>'; // Get AJAX URL

            var data = {
                'action': 'frontend_action_without_file',
                'delivery_postcode': pincode,
                'product_id': <?php echo esc_html($product->get_id()); ?>,
            };

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                timeout: 15000, // 15 seconds timeout
                success: function (response) {
                    var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = response;
                    response = tempDiv.textContent || tempDiv.innerText;

                    showPincodeResponse(response, "success");
                    // Set the response message in localStorage
                    localStorage.setItem('shiprocket_pincode_response', response);
                },
                error: function(xhr, status, error) {
                    var errorMsg = "Unable to check pincode. Please try again.";
                    if (status === 'timeout') {
                        errorMsg = "Request timed out. Please try again.";
                    }
                    showPincodeResponse(errorMsg, "error");
                },
                complete: function() {
                    // Remove loading state
                    checkBtn.classList.remove('loading');
                    checkBtn.disabled = false;
                }
            });
        }
        
        function showPincodeResponse(message, type) {
            var responseDiv = document.getElementById("pincode_response");
            responseDiv.innerHTML = message;
            responseDiv.className = "pincode-response " + type;
            responseDiv.style.display = 'block';
        }
        
        // Initialize pincode check on page load
        jQuery(document).ready(function($) {
            // Check if a pincode is saved in localStorage and pre-fill the input field
            var savedPincode = localStorage.getItem('shiprocket_pincode');
            if (savedPincode) {
                document.getElementById("shiprocket_pincode_check").value = savedPincode;
            }

            // Check if a pincode response is saved in localStorage and display the message
            var savedResponse = localStorage.getItem('shiprocket_pincode_response');
            if (savedResponse) {
                showPincodeResponse(savedResponse, "info");
            }
            
            // Add Enter key support
            $('#shiprocket_pincode_check').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    checkPincode_Shiprocket_Manual();
                }
            });
            
            // Format pincode input (only numbers, max 6 digits)
            $('#shiprocket_pincode_check').on('input', function(e) {
                var value = e.target.value.replace(/[^0-9]/g, '');
                if (value.length > 6) {
                    value = value.substring(0, 6);
                }
                e.target.value = value;
            });
        });
    </script>
    <?php
}

/**
 * Fallback function to show pincode check if not already displayed
 * This ensures compatibility with all themes
 *
 * @return void
 */
function show_shiprocket_pincode_check_fallback()
{
    // Only show if we're on a single product page
    if (is_product()) {
        show_shiprocket_pincode_check();
    }
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

    // Get the Shiprocket settings
    $settings = get_option('woocommerce_woo_shiprocket_shipping_settings');
    
    // Check if we have API user credentials
    $api_user_email = isset($settings['api_user_email']) ? $settings['api_user_email'] : '';
    $api_user_password = isset($settings['api_user_password']) ? $settings['api_user_password'] : '';
    $pickup_postcode = isset($settings['pickup_postcode']) ? $settings['pickup_postcode'] : '';

    // Fallback to WooCommerce store postcode if not set in settings
    if (empty($pickup_postcode)) {
        $pickup_postcode = get_option('woocommerce_store_postcode');
        
        // Additional fallback to base location
        if (empty($pickup_postcode)) {
            $pickup_postcode = WC()->countries->get_base_postcode();
        }
    }

    if (!$api_user_email || !$api_user_password || !$pickup_postcode) {
        wp_send_json_error(array('message' => __('Shiprocket API credentials or pickup postcode not found.', 'shiprocket-woo-shipping')));
    }

    // Get authentication token
    $auth_token = woo_shiprocket_get_auth_token();
    if (!$auth_token) {
        wp_send_json_error(array('message' => __('Failed to authenticate with Shiprocket API.', 'shiprocket-woo-shipping')));
    }

    // Shiprocket API endpoint URL
    $endpoint_url = 'https://apiv2.shiprocket.in/v1/courier/ratingserviceability';

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
                $ShowOutput = '<p style="font-weight: bold; padding: 10px 0px 0px 0px;">' . sprintf(esc_html__('Fast delivery to %1$s! Your order arrives in just %2$d days with our expedited shipping.', 'shiprocket-woo-shipping'), esc_html($filteredItems[0]['city']), esc_html($filteredItems[0]['estimated_delivery_days'])) . '</p>';
            }
        }


        // Escape the $ShowOutput variable before echoing
        echo esc_html($ShowOutput);
    }



    wp_die();
}