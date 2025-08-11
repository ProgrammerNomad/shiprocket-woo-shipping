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
    // Prevent multiple calls on the same page
    static $pincode_form_displayed = false;
    if ($pincode_form_displayed) {
        return;
    }
    $pincode_form_displayed = true;
    
    global $product;

    $settings = get_option('woocommerce_woo_shiprocket_shipping_settings');

    // Check if the "Show Pincode Check" option is enabled in the settings
    if (!isset($settings['show_pincode_check']) || $settings['show_pincode_check'] !== 'yes') {
        return; // Exit if the option is not enabled
    }
    ?>
    <div id="pincode_check_form">
        <label for="shiprocket_pincode_check"><?php _e('Check Pincode Serviceability:', 'shiprocket-woo-shipping'); ?></label>
        <input type="text" id="shiprocket_pincode_check" name="shiprocket_pincode_check" value=""
            placeholder="<?php esc_attr_e('Enter your pincode', 'shiprocket-woo-shipping'); ?>" maxlength="6">

        <button id="check_pincode" type="button" onClick="checkPincode_Shiprocket_Manual()"> 
            <?php _e('Check Pincode', 'shiprocket-woo-shipping'); ?>
        </button>
    </div>
    <div id="pincode_response"></div>
    <script>
        // Universal visibility fix - ensure pincode form shows on all devices
        document.addEventListener('DOMContentLoaded', function() {
            var pincodeForm = document.getElementById('pincode_check_form');
            var pincodeResponse = document.getElementById('pincode_response');
            
            if (pincodeForm) {
                // Force visibility with JavaScript
                pincodeForm.style.display = 'block';
                pincodeForm.style.visibility = 'visible';
                pincodeForm.style.opacity = '1';
                pincodeForm.style.position = 'relative';
                pincodeForm.style.zIndex = '1';
                
                // Add CSS class for universal display
                pincodeForm.classList.add('shiprocket-pincode-universal');
                
                // Ensure it's visible on all screen sizes
                var universalCSS = `
                    #pincode_check_form,
                    .shiprocket-pincode-universal {
                        display: block !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                        position: relative !important;
                        width: 100% !important;
                        max-width: 100% !important;
                        margin: 1.5rem 0 !important;
                        padding: 1.25rem !important;
                        background-color: #f8fafc !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 0.5rem !important;
                        box-sizing: border-box !important;
                    }
                    
                    /* Mobile Layout */
                    @media (max-width: 767px) {
                        #pincode_check_form,
                        .shiprocket-pincode-universal {
                            display: block !important;
                        }
                    }
                    
                    /* Tablet and Desktop Layout */
                    @media (min-width: 768px) {
                        #pincode_check_form,
                        .shiprocket-pincode-universal {
                            display: flex !important;
                            align-items: end !important;
                            gap: 0.75rem !important;
                        }
                    }
                    
                    /* Force visibility in all theme contexts */
                    .lg\\:hidden #pincode_check_form,
                    .hidden #pincode_check_form,
                    .lg\\:block #pincode_check_form,
                    .block #pincode_check_form,
                    .container #pincode_check_form,
                    .woocommerce #pincode_check_form,
                    .single-product #pincode_check_form {
                        display: block !important;
                        visibility: visible !important;
                    }
                    
                    /* Desktop specific overrides */
                    @media (min-width: 1024px) {
                        .lg\\:hidden #pincode_check_form,
                        .hidden #pincode_check_form {
                            display: flex !important;
                        }
                    }
                    
                    /* Input and button styling */
                    #shiprocket_pincode_check {
                        width: 100% !important;
                        max-width: 100% !important;
                        display: block !important;
                    }
                    
                    #check_pincode {
                        display: block !important;
                        width: 100% !important;
                    }
                    
                    @media (min-width: 768px) {
                        #shiprocket_pincode_check {
                            flex: 1 !important;
                            width: auto !important;
                        }
                        
                        #check_pincode {
                            width: auto !important;
                            min-width: 140px !important;
                        }
                    }
                `;
                
                // Inject CSS
                var style = document.createElement('style');
                style.id = 'shiprocket-universal-css';
                style.textContent = universalCSS;
                document.head.appendChild(style);
                
                console.log('Shiprocket: Pincode form visibility forced for all devices');
            } else {
                // If form not found, try to find it after a delay (in case it loads later)
                setTimeout(function() {
                    var delayedForm = document.getElementById('pincode_check_form');
                    if (delayedForm) {
                        delayedForm.style.display = 'block';
                        delayedForm.style.visibility = 'visible';
                        console.log('Shiprocket: Found pincode form after delay, made visible');
                    } else {
                        console.log('Shiprocket: Pincode form not found - may be disabled in settings');
                    }
                }, 1000);
            }
            
            if (pincodeResponse) {
                pincodeResponse.style.display = 'block';
                pincodeResponse.style.visibility = 'visible';
            }
        });

        function checkPincode_Shiprocket_Manual() {
            var pincode = document.getElementById("shiprocket_pincode_check").value;
            var responseDiv = document.getElementById("pincode_response");
            var checkButton = document.getElementById("check_pincode");
            
            if (pincode == '') {
                responseDiv.innerHTML = '<div class="error"><?php _e("Please enter a pincode", "shiprocket-woo-shipping"); ?></div>';
                responseDiv.className = 'show';
                return;
            }
            
            // Validate pincode format (6 digits)
            if (!/^\d{6}$/.test(pincode)) {
                responseDiv.innerHTML = '<div class="error"><?php _e("Please enter a valid 6-digit pincode", "shiprocket-woo-shipping"); ?></div>';
                responseDiv.className = 'show';
                return;
            }
            
            // Show loading state
            checkButton.innerHTML = '<?php _e("Checking...", "shiprocket-woo-shipping"); ?>';
            checkButton.classList.add('loading');
            checkButton.disabled = true;
            responseDiv.innerHTML = '<div class="info"><?php _e("Checking pincode serviceability...", "shiprocket-woo-shipping"); ?></div>';
            responseDiv.className = 'show';
            
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
                success: function (response) {
                    // Check if response contains error messages
                    var isError = response.toLowerCase().includes('no courier') || 
                                 response.toLowerCase().includes('not available') || 
                                 response.toLowerCase().includes('not serviceable') ||
                                 response.toLowerCase().includes('error');
                    
                    var isSuccess = response.toLowerCase().includes('available') || 
                                   response.toLowerCase().includes('serviceable') ||
                                   response.toLowerCase().includes('delivery');
                    
                    var responseClass = isError ? 'error' : (isSuccess ? 'success' : 'info');
                    
                    // Display the response with proper styling, keeping HTML formatting
                    responseDiv.innerHTML = '<div class="' + responseClass + '">' + response + '</div>';
                    responseDiv.className = 'show';
                    
                    // Set the response message in localStorage (keep HTML for storage)
                    localStorage.setItem('shiprocket_pincode_response_html', response);
                    
                    // Also store plain text version for fallback
                    var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = response;
                    var plainTextResponse = tempDiv.textContent || tempDiv.innerText;
                    localStorage.setItem('shiprocket_pincode_response', plainTextResponse);
                },
                error: function() {
                    responseDiv.innerHTML = '<div class="error"><?php _e("Error checking pincode. Please try again.", "shiprocket-woo-shipping"); ?></div>';
                    responseDiv.className = 'show';
                },
                complete: function() {
                    // Reset button state
                    checkButton.innerHTML = '<?php _e("Check Pincode", "shiprocket-woo-shipping"); ?>';
                    checkButton.classList.remove('loading');
                    checkButton.disabled = false;
                }
            });
        }

        // Allow Enter key to trigger the check
        document.getElementById("shiprocket_pincode_check").addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                checkPincode_Shiprocket_Manual();
            }
        });

        // Periodic check to ensure visibility (in case theme JS tries to hide it)
        setInterval(function() {
            var pincodeForm = document.getElementById('pincode_check_form');
            if (pincodeForm && (pincodeForm.style.display === 'none' || pincodeForm.style.visibility === 'hidden')) {
                console.log('Shiprocket: Re-enforcing pincode form visibility');
                pincodeForm.style.display = 'block';
                pincodeForm.style.visibility = 'visible';
                pincodeForm.style.opacity = '1';
            }
        }, 2000); // Check every 2 seconds

        // Check if a pincode is saved in localStorage and pre-fill the input field
        var savedPincode = localStorage.getItem('shiprocket_pincode');
        if (savedPincode) {
            document.getElementById("shiprocket_pincode_check").value = savedPincode;
        }

        // Check if a pincode response is saved in localStorage and display the message
        var savedResponseHtml = localStorage.getItem('shiprocket_pincode_response_html');
        var savedResponse = localStorage.getItem('shiprocket_pincode_response');
        
        if (savedResponseHtml || savedResponse) {
            var responseDiv = document.getElementById("pincode_response");
            var displayResponse = savedResponseHtml || savedResponse;
            
            // Check if response contains error messages
            var isError = displayResponse.toLowerCase().includes('no courier') || 
                         displayResponse.toLowerCase().includes('not available') || 
                         displayResponse.toLowerCase().includes('not serviceable') ||
                         displayResponse.toLowerCase().includes('error');
            
            var isSuccess = displayResponse.toLowerCase().includes('available') || 
                           displayResponse.toLowerCase().includes('serviceable') ||
                           displayResponse.toLowerCase().includes('delivery');
            
            var responseClass = isError ? 'error' : (isSuccess ? 'success' : 'info');
            
            responseDiv.innerHTML = '<div class="' + responseClass + '">' + displayResponse + '</div>';
            responseDiv.className = 'show';
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
                $ShowOutput = '<p style="font-weight: bold; padding: 10px 0px 0px 0px;">' . sprintf(esc_html__('Fast delivery to %1$s! Your order arrives in just %2$d days with our expedited shipping.', 'shiprocket-woo-shipping'), esc_html($filteredItems[0]['city']), esc_html($filteredItems[0]['estimated_delivery_days'])) . '</p>';
            }
        }


        // Escape the $ShowOutput variable before echoing
        echo esc_html($ShowOutput);
    }



    wp_die();
}