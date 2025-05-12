<?php
/*
Theme Name: BuddyBoss Child
Description: A child theme of BuddyBoss Theme. To ensure easy updates, make your own edits in this theme.
*/

// Fix WooCommerce data store registration conflicts
add_action('init', function() {
    // Check if we're in the admin or frontend
    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
        // Ensure scripts are loaded in proper order
        add_filter('script_loader_tag', function($tag, $handle, $src) {
            // Add defer to non-critical scripts to prevent timing conflicts
            if (strpos($handle, 'wc-') !== false && strpos($handle, 'data') === false) {
                return str_replace(' src', ' defer src', $tag);
            }
            return $tag;
        }, 10, 3);
    }
}, 5);

// Fix WooCommerce data store loading conflicts
add_action('wp_enqueue_scripts', function() {
    // Ensure WooCommerce scripts load in correct order
    if (function_exists('is_woocommerce') && is_woocommerce()) {
        // Remove duplicate enqueues
        global $wp_scripts;
        
        if (isset($wp_scripts->registered['wp-data'])) {
            $wp_scripts->registered['wp-data']->deps = array_unique($wp_scripts->registered['wp-data']->deps);
        }
    }
}, 15);

// Prevent multiple initializations of WooCommerce components
add_action('woocommerce_init', function() {
    // Check if data stores are already registered
    static $stores_initialized = false;
    
    if (!$stores_initialized) {
        $stores_initialized = true;
        do_action('woocommerce_stores_initialized');
    }
}, 5);

/**
 * Enqueues scripts and styles for child theme front-end.
 */
function buddyboss_theme_child_scripts_styles() {
    // Enqueue parent theme style
    wp_enqueue_style('buddyboss-theme', get_template_directory_uri() . '/style.css', array(), '1.0.0');
    
    // Enqueue child theme style
    wp_enqueue_style('buddyboss-child-css', get_stylesheet_directory_uri() . '/assets/css/custom.css', array('buddyboss-theme'), time());
    
    // Enqueue custom JavaScript with proper dependencies
    if (class_exists('WooCommerce')) {
        wp_enqueue_script(
            'buddyboss-child-woocommerce-fixes',
            get_stylesheet_directory_uri() . '/assets/js/woocommerce-fixes.js',
            array('jquery', 'wp-data'),
            time(),
            true
        );
        
        // Localize script with necessary data
        wp_localize_script('buddyboss-child-woocommerce-fixes', 'buddyboss_child_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('buddyboss_child_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles', 9999);

// Woocommerce customization functions
add_filter('wpo_wcpdf_tmp_path', function($tmp_base) {
    $tmp_base = '/home/685604.cloudwaysapps.com/pbthexbhxm/public_html/wp-content/woocommerce-invoices/';
    return $tmp_base;
});

add_filter('woocommerce_product_csv_importer_check_import_file_path', '__return_false');

// ACF Post Object Field customization
function custom_tagged_syllabus_acf_fields_post_object_result($text, $post, $field, $post_id) {
    $syllabus_curriculum_code = get_post_meta($post->ID, 'sy_curriculumType', true);
    $syllabus_topic_area = get_post_meta($post->ID, 'sy_topicArea', true);
    $syllabus_year_array = get_post_meta($post->ID, 'sy_year', true);
    
    $syllabus_year = '';
    if ($syllabus_year_array) {
        if ($syllabus_year_array[0] == "foundation") {
            $syllabus_year = 'Foundation Year';
        } else {
            $syllabus_year = 'Year ' . $syllabus_year_array[0];
        }
    }

    $sy_curriculum_type = '';
    if ($syllabus_curriculum_code) {
        $curriculum_types = array(
            "au" => 'Australian',
            "act" => 'Australian Capital Territory',
            "nsw" => 'New South Wales',
            "nt" => 'Northern Territory',
            "qld" => 'Queensland',
            "sa" => 'South Australia',
            "tas" => 'Tasmania',
            "vic" => 'Victoria',
            "wa" => 'Western Australia'
        );
        
        $sy_curriculum_type = isset($curriculum_types[$syllabus_curriculum_code]) ? $curriculum_types[$syllabus_curriculum_code] : '';
    }

    $text .= ' (' . $sy_curriculum_type . ', ' . $syllabus_year . ', ' . $syllabus_topic_area . ')';
    return $text;
}
add_filter('acf/fields/post_object/result', 'custom_tagged_syllabus_acf_fields_post_object_result', 10, 4);

// Change "Enroll Me" text
function change_string_from_wdm_label($text) {
    return __('Enrol Me', 'buddyboss-theme');
}
add_filter('wdm_enroll_me_label', 'change_string_from_wdm_label', 10, 1);

// Customize variation dropdown text
function alternate_variant_dropdown_text($args) {
    $args['show_option_none'] = __('Select Option', 'buddyboss-theme');
    return $args;
}
add_filter('woocommerce_dropdown_variation_attribute_options_args', 'alternate_variant_dropdown_text', 10, 2);

// Change vendor biography title
add_filter('dokan_vendor_biography_title', function() {
    return 'My Profile';
});

// Display units sold on product page
add_action('woocommerce_single_product_summary', function() {
    global $product;
    if (!is_a($product, 'WC_Product')) {
        return;
    }
    $totals_html = 'Units Sold: ' . $product->get_total_sales();
    echo sprintf('<p class="product-totals-html">%s</p>', esc_html($totals_html));
}, 25);

// Disable admin email notifications for password changes
add_filter('wp_password_change_notification_email', function($email, $user, $blogname) {
    if (isset($email) && is_array($email)) {
        unset($email['to']);
    }
    return $email;
}, 99, 3);

// Calculate vendor total sales
function wbcom_vendor_total_sell($vendor_id) {
    if (empty($vendor_id)) {
        return 0;
    }

    $query = new WC_Product_Query(array(
        'limit' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'author' => $vendor_id
    ));
    
    $products = $query->get_products();
    $count = 0;
    
    if (!empty($products)) {
        foreach ($products as $product) {
            $count += (int) $product->get_total_sales();
        }
    }

    return $count;
}

// Improved commission calculation
function wbcom_get_earning_by_order($order, $context = 'admin') {
    $earning = 0;
    
    if (!$order || !is_a($order, 'WC_Order')) {
        return $earning;
    }
    
    try {
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            
            if (!$product) {
                continue;
            }

            // Set line item quantity
            if (method_exists(dokan()->commission, 'set_order_qunatity')) {
                dokan()->commission->set_order_qunatity($item->get_quantity());
            }

            $product_id = $product->get_id();
            $refund = $order->get_total_refunded_for_item($item_id);
            $vendor_id = (int) get_post_field('post_author', $product_id);

            if (function_exists('dokan_is_admin_coupon_applied') && dokan_is_admin_coupon_applied($order, $vendor_id, $product_id)) {
                $earning += dokan_pro()->coupon->get_earning_by_admin_coupon($order, $item, $context, $product, $vendor_id, $refund);
            } else {
                $item_price = $item->get_total();
                $item_price = $refund ? $item_price - $refund : $item_price;
                $earning += dokan()->commission->get_earning_by_product($product_id, $context, $item_price);
            }
        }

        // Shipping calculations
        if ($context === dokan()->commission->get_shipping_fee_recipient($order->get_id())) {
            $earning += wc_format_decimal(floatval($order->get_shipping_total())) - $order->get_total_shipping_refunded();
        }

        // Tax calculations
        if ($context === dokan()->commission->get_tax_fee_recipient($order->get_id())) {
            $earning += ((float) $order->get_total_tax() - (float) $order->get_total_tax_refunded()) - 
                       ((float) $order->get_shipping_tax() - (float) dokan()->commission->get_total_shipping_tax_refunded($order));
        }
        
    } catch (Exception $e) {
        error_log('Commission calculation error: ' . $e->getMessage());
    }
    
    return floatval(wc_format_decimal($earning));
}

// Control access modes for instructors
function wbcom_instructor_access_mode($access_modes) {
    return array(        
        'closed'    => __('Closed', 'ld-dashboard'),
        'open'      => __('Open', 'ld-dashboard'),
        'free'      => __('Free', 'ld-dashboard'),
        'paynow'    => __('Buy Now', 'ld-dashboard'),
        'subscribe' => __('Recurring', 'ld-dashboard'),        
    );
}

$user_roles = wp_get_current_user()->roles;
if (in_array('ld_instructor', $user_roles)) {
    add_filter('ld_dashboard_course_access_modes', 'wbcom_instructor_access_mode');
}

// Modify subscription price string
add_filter('woocommerce_subscriptions_product_price_string', function($subscription_string, $product, $include) {
    if ($include['sign_up_fee']) { 
        $subscription_string = str_replace('sign-up fee', 'one time fee', $subscription_string);
    }
    return $subscription_string;
}, 10, 3);

// Vendor total sale shortcode
add_shortcode('vendor_total_sale', 'wbcom_display_vendor_total_sale');
function wbcom_display_vendor_total_sale() {
    ob_start();
    $store_user = dokan()->vendor->get(get_query_var('author'));
    ?>
    <li class="dokan-total-sale">
        <i class="fas fa-info-circle"></i>
        <?php esc_html_e('Total Sales:', 'dokan'); ?>
        <?php echo esc_html(number_format_i18n(wbcom_vendor_total_sell($store_user->get_id()), 0)); ?>
    </li>
    <?php
    return ob_get_clean();
}

// Add body classes for vendor pages
add_filter('body_class', function($classes) {
    if (dokan_is_store_page()) {
        global $wp;
        if (dokan_is_store_review_page()) {
            $classes[] = 'vendor-review';
        } elseif (strpos($wp->request, 'biography')) {
            $classes[] = 'vendor-biography';
        }
    }
    return $classes;
});

// Change order status labels
add_filter('wc_order_statuses', function($order_statuses) {
    $order_statuses['wc-completed'] = _x('Paid', 'Order status', 'woocommerce');
    $order_statuses['wc-on-hold'] = _x('Pending Payment', 'Order status', 'woocommerce');
    return $order_statuses;
}, 20, 1);

// Add instructor role to vendors after purchase
add_action('woocommerce_thankyou', function($order_id) {
    $order = wc_get_order($order_id);
    if (is_a($order, 'WC_Order')) {
        $user = $order->get_user();
        if (is_a($user, 'WP_User')) {
            if (in_array('vendor', $user->roles) && !in_array('ld_instructor', $user->roles)) {                
                $user->add_role('ld_instructor');
            }
        }
    }
});

// Disable new user notification emails
function wbcom_disable_new_user_notifications() {
    remove_action('register_new_user', 'wp_send_new_user_notifications');
    remove_action('edit_user_created_user', 'wp_send_new_user_notifications', 10, 2);
    remove_action('after_password_reset', 'wp_password_change_notification');
    
    add_action('register_new_user', 'wbcom_send_new_user_notifications');
    add_action('edit_user_created_user', 'wbcom_send_new_user_notifications', 10, 2);
}
add_action('init', 'wbcom_disable_new_user_notifications');

function wbcom_send_new_user_notifications($user_id, $notify = 'user') {
    if (empty($notify) || $notify == 'admin') {
        return;
    } elseif ($notify == 'both') {
        $notify = 'user';
    }
    wp_send_new_user_notifications($user_id, $notify);
}

// Disable password change emails
add_filter('send_password_change_email', '__return_false');

// Dokan dashboard customization
add_action('dokan_product_edit_after_options', 'wbcom_dokan_preselect');
function wbcom_dokan_preselect() {
    ?>
    <style>
        label[for="_virtual"], label[for="_downloadable"] { opacity: 0; }
    </style>
    <script>
        jQuery(function($) {
            $('input[name=_virtual]').prop('checked', true);
            $('input[name=_downloadable]').prop('checked', true);
        });
    </script>
    <?php
}

// Commission customization
add_action('wp_loaded', function() {
    global $wp_filter;
    $hook = 'dokan_prepare_for_calculation';
    $class = '\WeDevs\DokanPro\Hooks';

    if (isset($wp_filter[$hook])) {
        foreach ($wp_filter[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function']) && $callback['function'][0] instanceof $class) {
                    remove_filter($hook, $callback['function'], $priority);
                }
            }
        }
    }
});

add_filter('dokan_prepare_for_calculation', 'wbcom_add_combine_commission', 10, 6);
function wbcom_add_combine_commission($earning, $commission_rate, $commission_type, $additional_fee, $product_price, $order_id) {
    if ('combine' === $commission_type) {
        if ($commission_rate > 100) {
            return (float) wc_format_decimal($product_price);
        }

        $item_total = get_post_meta($order_id, '_dokan_item_total', true);
        $product_price = (float) wc_format_decimal($product_price);
        
        if ($order_id && $item_total) {
            $order = wc_get_order($order_id);
            $additional_fee = ($additional_fee / $item_total) * $product_price;
        }

        $earning = ($product_price * $commission_rate) / 100;
        $total_earning = $earning + $additional_fee;
        $earning = $product_price - $total_earning;

        if ($earning < 0) {
            $earning = 0;           
        }
    }

    return floatval(wc_format_decimal($earning));
}

// Remove vendor coupon conflicts
add_action('init', function() {
    remove_action('woocommerce_coupon_is_valid_for_product', array('WeDevs\DokanPro\Coupons\AdminCoupons', 'coupon_is_valid_for_product'), 15, 3);
});

// Prevent duplicate orders
add_action('woocommerce_checkout_process', 'wbcom_prevent_duplicate_order_within_one_hour');
function wbcom_prevent_duplicate_order_within_one_hour() {
    $current_user = wp_get_current_user();
    
    if (!$current_user->ID) {
        return;
    }
    
    $args = array(
        'customer_id' => $current_user->ID,
        'date_created' => '>' . (time() - HOUR_IN_SECONDS / 2),
        'status' => array('on-hold', 'processing', 'completed'),
        'limit' => 1
    );

    $orders = wc_get_orders($args);
    
    if (!empty($orders)) {
        foreach ($orders as $order) {
            if ($order->get_total() === WC()->cart->total) {
                wc_add_notice(__('A similar order has been placed in the last hour. Please wait before placing a new order.', 'woocommerce'), 'error');
                return;
            }
        }
    }
}

// PayPal gateway fee adjustment
add_filter('dokan_orders_vendor_net_amount', function($net_amount, $vendor_earning, $gateway_fee, $tmp_order, $order) {
    $net_amount = $net_amount + $gateway_fee;
    return $net_amount;
}, 50, 5);

// Custom order status change handler
add_action('wp_loaded', function() {
    global $wp_filter;
    $hook = 'woocommerce_order_status_changed';
    $class = '\WeDevs\Dokan\Order\Hooks';
    $method = 'on_sub_order_change';
    
    if (isset($wp_filter[$hook])) {
        foreach ($wp_filter[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function']) && 
                    $callback['function'][0] instanceof $class && 
                    $callback['function'][1] === $method) {
                    remove_filter($hook, $callback['function'], $priority);
                }
            }
        }
    }
});

add_action('woocommerce_order_status_changed', 'wbcom_custom_on_sub_order_change', 99, 4);
function wbcom_custom_on_sub_order_change($order_id, $old_status, $new_status, $order) {
    if ($order->get_parent_id() === 0) {
        return;
    }

    $parent_order_id = $order->get_parent_id();
    $sub_orders = dokan()->order->get_child_orders($parent_order_id);

    if (!$sub_orders) {
        return;
    }

    $all_complete = true;
    foreach ($sub_orders as $sub_order) {
        if ($sub_order->get_status() !== 'completed') {
            $all_complete = false;
            break;
        }
    }

    if ($all_complete) {
        $parent_order = wc_get_order($parent_order_id);
        $parent_order->update_status('wc-completed', __('Mark parent order completed when all child orders are completed.', 'dokan-lite'));
    }
}

// Customize checkout fields based on cart total
add_filter('woocommerce_checkout_fields', 'wbcom_customize_checkout_fields_based_on_cart_total');
function wbcom_customize_checkout_fields_based_on_cart_total($fields) {
    $cart_total = WC()->cart->get_cart_contents_total();

    if ($cart_total < 100) {
        $allowed_fields = array('billing_first_name', 'billing_last_name', 'billing_email', 'billing_company', 'billing_postcode');

        foreach ($fields['billing'] as $key => $field) {
            if (!in_array($key, $allowed_fields)) {
                unset($fields['billing'][$key]);
            } else {
                $fields['billing'][$key]['required'] = ($key !== 'billing_company');
            }
        }
    }

    return $fields;
}

// Validate postcode for low value carts
add_action('woocommerce_checkout_process', 'wbcom_validate_postcode_for_low_value_cart');
function wbcom_validate_postcode_for_low_value_cart() {
    $cart_total = WC()->cart->get_cart_contents_total();

    if ($cart_total < 100 && empty($_POST['billing_postcode'])) {
        wc_add_notice(__('Please enter your Postcode.', 'woocommerce'), 'error');
    }
}

// Change postcode label
add_filter('woocommerce_default_address_fields', 'wbcom_change_postcode_label');
function wbcom_change_postcode_label($fields) {
    $fields['postcode']['label'] = __('Postcode', 'woocommerce');
    return $fields;
}