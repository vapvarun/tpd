<?php
/*This file is part of BuddyBossChild, buddyboss-theme child theme.
All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.
Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/
// if ( ! function_exists( 'buddybosschild_enqueue_child_styles' ) ) {
// 	function buddybosschild_enqueue_child_styles() {
// 	    // loading parent style
// 	    wp_register_style(
// 	      'parente2-style',
// 	      get_template_directory_uri() . '/style.css'
// 	    );
// 	    wp_enqueue_style( 'parente2-style' );
// 	    // loading child style
// 	    wp_register_style(
// 	      'childe2-style',
// 	      get_stylesheet_directory_uri() . '/style.css'
// 	    );
// 	    wp_enqueue_style( 'childe2-style');
// 	 }
// }
// add_action( 'wp_enqueue_scripts', 'buddybosschild_enqueue_child_styles' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function buddyboss_theme_child_scripts_styles() {
	/**
	  * Scripts and Styles loaded by the parent theme can be unloaded if needed
	  * using wp_deregister_script or wp_deregister_style.
	  *
	  * See the WordPress Codex for more information about those functions:
	  * http://codex.wordpress.org/Function_Reference/wp_deregister_script
	  * http://codex.wordpress.org/Function_Reference/wp_deregister_style
	  */

	// Styles	
	wp_enqueue_style( 'buddyboss-child-css', get_stylesheet_directory_uri() . '/assets/css/custom.css', array(), time(), 'all' );
   // wp_enqueue_script( 'buddyboss-child-js', get_stylesheet_directory_uri() . '/assets/js/custom.js', array( 'jquery' ), time(), true );
}
add_action( 'wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles', 9999 );


/*Write here your own functions */
add_filter('wpo_wcpdf_tmp_path', function( $tmp_base ) {
    /*
     * This is an example of a path, please check your current server directory structure.
     * It's recommended to have it outside the /public/ directory.
    */
    $tmp_base = '/home/685604.cloudwaysapps.com/pbthexbhxm/public_html/wp-content/woocommerce-invoices/';
    return $tmp_base;
});

add_filter( 'woocommerce_product_csv_importer_check_import_file_path', '__return_false' );

function reign_get_dietary_fields( $ticket_fields ) {
    $dietary_tags = '';
    if ( ! empty( $ticket_fields ) ) {
        foreach ( $ticket_fields as $field_id => $field_details ) {
            if( 'Dietary Requirements' == $field_details['label'] ) {
                if( ! empty( $field_details['options'] ) ) {
                    //$dietary_tags = explode(',', $field_details['options'] );
                    $dietary_tags = $field_details['options'];
                }
            }
        }
    }
    return $dietary_tags;
}



/**
 * Customize the dropdown options
 * for ACF Post Object Field
 */

function custom_tagged_syllabus_acf_fields_post_object_result( $text, $post, $field, $post_id )
{
    $syllabus_curriculum_code = get_post_meta($post->ID, 'sy_curriculumType', true);
    $syllabus_topic_area = get_post_meta($post->ID, 'sy_topicArea', true);
    $syllabus_year_array = get_post_meta($post->ID, 'sy_year', true);
    // $syllabus_year = 'Year' . ' ' . $syllabus_year_array[0];

    if($syllabus_year_array) {
        if ( $syllabus_year_array[0]  == "foundation") $syllabus_year = 'Foundation Year';
    } else {
        $syllabus_year = 'Year' . ' ' . $syllabus_year_array[0];
    }

    if ($syllabus_curriculum_code) {
        if (  $syllabus_curriculum_code == "au" ) $sy_curriculum_type = 'Australian';
        if (  $syllabus_curriculum_code == "act" ) $sy_curriculum_type = 'Australian Capital Territory';
        if (  $syllabus_curriculum_code == "nsw" ) $sy_curriculum_type = 'New South Wales';
        if (  $syllabus_curriculum_code == "nt" ) $sy_curriculum_type = 'Northern Territory';
        if (  $syllabus_curriculum_code == "qld" ) $sy_curriculum_type = 'Queensland';
        if (  $syllabus_curriculum_code == "sa" ) $sy_curriculum_type = 'South Australia';
        if (  $syllabus_curriculum_code == "tas" ) $sy_curriculum_type = 'Tasmania';
        if (  $syllabus_curriculum_code == "vic" ) $sy_curriculum_type = 'Victoria';
        if (  $syllabus_curriculum_code == "wa" ) $sy_curriculum_type = 'Western Australia';
    }

    // $text .= ' (' . $post->post_type .  ')';
    $text .= ' (' . $sy_curriculum_type . ', ' . $syllabus_year . ', ' . $syllabus_topic_area . ')';
    return $text;
}

add_filter('acf/fields/post_object/result', 'custom_tagged_syllabus_acf_fields_post_object_result', 10, 4);


/**
 * Changes text string for
 * Enroll Me
 */

function change_string_from_wdm_label($text)
{
    $text = __( 'Enrol Me' ); // change text here
    return $text;
}

add_filter('wdm_enroll_me_label', 'change_string_from_wdm_label', 10, 1);


/*
* Provide an alternate text for the dropdown variation
*/

function alternate_variant_dropdown_text($args)
{
    $args['show_option_none'] = __( 'Select Option', 'your_text_domain' );
    return $args;
}

add_filter('woocommerce_dropdown_variation_attribute_options_args', 'alternate_variant_dropdown_text', 10, 2);

add_filter( 'dokan_vendor_biography_title', function() {
    return 'My Profile';
} );


add_action( 'woocommerce_single_product_summary', function() {
    global $product;

    if ( ! is_a( $product, 'WC_Product' ) ) {
        return 0;
    }
    $totals_html = 'Units Sold: ' . $product->get_total_sales();
    echo sprintf( __( '<p class="product-totals-html">%s</p>', 'wcvendors-pro' ), $totals_html );
});

add_filter( 'wp_password_change_notification_email', function( $email, $user, $blogname ) {
    if( isset( $email ) && is_array( $email ) ) {
        unset( $email['to'] );
    }

    return $email;

}, 99, 3 );

// Vendor total sell
function wbcom_vendor_total_sell( $vendor_id ) {
    if( empty( $vendor_id ) ) {
        return 0;
    }

    $query = new WC_Product_Query( 
        array(
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'author' => $vendor_id
        ) 
    );
    $products = $query->get_products();
    $count = 0;
    if( ! empty( $products ) ) {
        foreach ( $products as $key => $product ) {
            $count+= (int) $product->get_total_sales();
        }
    }

    return $count;

}


// add_action( 'admin_init', 'wbcom_regenrate_commisssion' );
function wbcom_regenrate_commisssion() {
    if( is_user_logged_in() && 5522 === get_current_user_id() ) {
        // get_vendor_wise_rate( $vendor_id );
        // get_vendor_wise_type( $vendor_id );
        global $wpdb;
        $orders = new WP_Query(
            array(
                'post_type' => 'shop_order',
                'post_status' => 'wc-completed',
                'posts_per_page' => -1,
                'date_query' => array(
                    'after' => array(
                        'year' => 2023,
                        'month' => 6,
                        'day'  => 27
                    )
                )
            )
        );
       
        
        if ( $orders->posts ) {

            foreach ( $orders->posts as $order ) {
                
                if ( get_post_meta( $order->ID, 'has_sub_order', true ) == '1' ) {
                    continue;
                }
                $dokan_order  = wc_get_order( $order->ID );

                // // error_log( print_r( $order->ID, true ) . "\r\n", 3, get_stylesheet_directory() . '/error_log.txt' );
                // // error_log( print_r( var_dump( $order->get_total_tax() ), true ) . "\r\n", 3, get_stylesheet_directory() . '/error_log.txt' );
                // // error_log( print_r( var_dump( $order->get_shipping_tax() ), true ) . "\r\n", 3, get_stylesheet_directory() . '/error_log.txt' );
                // $seller_id      = dokan_get_seller_id_by_order( $order->ID );
                $order_total    = $dokan_order->get_total();

                if ( $dokan_order->get_total_refunded() ) {
                    $order_total = $order_total - $dokan_order->get_total_refunded();
                }

                // $order_status       = dokan_get_prop( $order, 'status' );
                $admin_commission   = wbcom_get_earning_by_order( $dokan_order );
                $net_amount         = $order_total - $admin_commission;
                $order_table_name   = $wpdb->prefix."dokan_orders";
                $vendor_table_name  = $wpdb->prefix."dokan_vendor_balance";

                
                $query_order =  $wpdb->prepare( "UPDATE $order_table_name SET `net_amount` = '%f'  WHERE `order_id` = '%d'", $net_amount, $dokan_order->ID );
                $wpdb->query( $query_order );

                $query_vendor =  $wpdb->prepare( "UPDATE $vendor_table_name SET `debit` = '%f'  WHERE `trn_id` = '%d'", $net_amount, $dokan_order->ID );
                $wpdb->query( $query_vendor );
                
               
                

            }
            error_log( print_r( $wpdb->last_error, true ) . "\r\n", 3, get_stylesheet_directory() . '/error_log.txt' );
        }
    }
}


function wbcom_get_earning_by_order( $order,  $context = 'admin' ) {

    $earning = 0;
    if( ! empty( $order ) ) {
        foreach ( $order->get_items() as $item_id => $item ) {
            if ( ! $item->get_product() ) {
                continue;
            }

            // Set line item quantity so that we can use it later in the `\WeDevs\Dokan\Commission::prepare_for_calculation()` method
            dokan()->commission->set_order_qunatity( $item->get_quantity() );

            $product_id = $item->get_product()->get_id();
            $refund     = $order->get_total_refunded_for_item( $item_id );
            $vendor_id  = (int) get_post_field( 'post_author', $product_id );

            if ( dokan_is_admin_coupon_applied( $order, $vendor_id, $product_id ) ) {
                $earning += dokan_pro()->coupon->get_earning_by_admin_coupon( $order, $item, $context, $item->get_product(), $vendor_id, $refund );

                
            } else {
                $item_price = $item->get_total();
                $item_price = $refund ? $item_price - $refund : $item_price;                
                $earning   += dokan()->commission->get_earning_by_product( $product_id, $context, $item_price );
            }
        }

        if ( $context === dokan()->commission->get_shipping_fee_recipient( $order->get_id() ) ) {
            $earning += wc_format_decimal( floatval( $order->get_shipping_total() ) ) - $order->get_total_shipping_refunded();
        }

        if ( $context === dokan()->commission->get_tax_fee_recipient( $order->get_id() ) ) {
            $earning += ( (  (float) $order->get_total_tax() -  (float) $order->get_total_tax_refunded() ) - (  (float) $order->get_shipping_tax() -  (float) dokan()->commission->get_total_shipping_tax_refunded( $order ) ) );
        }

        if ( $context === dokan()->commission->get_shipping_tax_fee_recipient( $order ) ) {
            $earning += (  (float) $order->get_shipping_tax() -  (float) dokan()->commission->get_total_shipping_tax_refunded( $order ) );
        }
    }


    return $earning;
}

/**
 * Wbcom Designs - Control access mode for instructor
 *
 * @param  array $access_modes where define access.
 */
function wbcom_istructor_access_mode( $access_modes ) {
    $access_modes = array(        
        'closed'    => __( 'Closed', 'ld-dashboard' ),
        'open'      => __( 'Open', 'ld-dashboard' ),
        'free'      => __( 'Free', 'ld-dashboard' ),
        'paynow'    => __( 'Buy Now', 'ld-dashboard' ),
        'subscribe' => __( 'Recurring', 'ld-dashboard' ),        
    );
    return $access_modes;
}
$user_roles = wp_get_current_user()->roles;
if ( in_array( 'ld_instructor', $user_roles ) ) {
    add_filter( 'ld_dashboard_course_access_modes', 'wbcom_istructor_access_mode' );
}


add_filter( 'woocommerce_subscriptions_product_price_string', function( $subscription_string, $product, $include ) {

   if ( $include['sign_up_fee'] ) { 
        $subscription_string = str_replace( 'sign-up fee', 'one time  fee', $subscription_string );
   }
   return $subscription_string;
}, 10, 3 );

// Total Sell add_shortcode
add_shortcode( 'vendor_total_sale', 'wbcom_display_vendor_total_sale' );
function wbcom_display_vendor_total_sale() {

    ob_start();
    ?>
    <li class="dokan-total-sale">
        <i class="fas fa-info-circle"></i>
        <?php esc_html_e( 'Total Sales:', 'dokan' );?>
            <?php
                 $store_user    = dokan()->vendor->get( get_query_var( 'author' ) );
                $order_statuses = dokan_withdraw_get_active_order_status();
                $total          = 0;
                $orders_count   = dokan_count_orders( $store_user->get_id() );

                foreach ( $order_statuses as $order_status ) :
                    if ( isset( $orders_count->$order_status ) ) :
                        $total += $orders_count->$order_status;
                    endif;
                endforeach;
                // echo $store_user->get_id();
                // echo esc_html( number_format_i18n( $total, 0 ) );
                echo esc_html( number_format_i18n( wbcom_vendor_total_sell( $store_user->get_id() ), 0 ) );
            ?>
    </li>
    <?php

    $output = ob_get_clean();

    return $output;

}

// Add Body Class
add_filter( 'body_class', function( $classes ) {
    if ( dokan_is_store_page() ) {
        global $wp;

        if( dokan_is_store_review_page() ) {
            $classes[] = 'vendor-review';
        }elseif( strpos( $wp->request, 'biography' ) ) {
             $classes[] = 'vendor-biography';
        }
    }

    return $classes;

} );


// Change Order Status Text
add_filter( 'wc_order_statuses', 'wb_rename_order_statuses', 20, 1 );
function wb_rename_order_statuses( $order_statuses ) {
    $order_statuses['wc-completed']  = _x( 'Paid', 'Order status', 'woocommerce' );
    $order_statuses['wc-on-hold']    = _x( 'Pending Payment', 'Order status', 'woocommerce' );
    return $order_statuses;
}

//add_action( 'woocommerce_thankyou', 'woocommerce_auto_processing_orders');
function woocommerce_auto_processing_orders( $order_id ) {
    if ( ! $order_id )
        return;

    $order = wc_get_order( $order_id );
    // If order is "on-hold" update status to "processing"
    if( $order->has_status( 'on-hold' ) ) {
        $order->update_status( 'processing' );
    }
}

// Add instructor to vendor
add_action( 'woocommerce_thankyou', function( $order_id ) {
    $order = wc_get_order( $order_id );
     if ( is_a( $order, 'WC_Order' ) ) {
        $user = $order->get_user();
        if ( is_a( $user, 'WP_User' ) ) {
            if( in_array( 'vendor', $user->roles ) && ! in_array( 'ld_instructor', $user->roles ) ) {                
                $user->add_role( 'ld_instructor' );
            }
        }
    }
    
} );

//Disable the new user notification sent to the site admin
function wbcom_disable_new_user_notifications() {
 //Remove original use created emails
 remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
 remove_action( 'edit_user_created_user', 'wp_send_new_user_notifications', 10, 2 );
 remove_action( 'after_password_reset', 'wp_password_change_notification' );
 
 //Add new function to take over email creation
 add_action( 'register_new_user', 'wbcom_send_new_user_notifications' );
 add_action( 'edit_user_created_user', 'wbcom_send_new_user_notifications', 10, 2 );
}

function wbcom_send_new_user_notifications( $user_id, $notify = 'user' ) {
 if ( empty($notify) || $notify == 'admin' ) {
    return;
 } elseif( $notify == 'both' ){
     //Only send the new user their email, not the admin
     $notify = 'user';
 }
 wp_send_new_user_notifications( $user_id, $notify );
}
add_action( 'init', 'wbcom_disable_new_user_notifications' );

//Disable send password chnage email
add_filter( 'send_password_change_email', '__return_false' );

// Dashboard Customization
add_action( 'dokan_product_edit_after_options', 'wbcom_dokan_preselect' );
function wbcom_dokan_preselect() {
    ?>
    <style>
        label[for="_virtual"], label[for="_downloadable"]{ opacity: 0; }
    </style>
    <?php
    
    ?>
    <script>
        (function($){
            $('input[name=_virtual]').prop('checked', true);
            $('input[name=_downloadable]').prop('checked', true);
        })(jQuery);
    </script>
    <?php
}

add_action( 'wp_footer', function() {
if (! class_exists('WeDevs_Dokan')){ return; }    
if ( dokan_is_seller_dashboard() && isset( $_GET['_dokan_edit_product_nonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_dokan_edit_product_nonce'] ), 'dokan_edit_product_nonce' ) && ! empty( $_GET['action'] ) ) {
    ?>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById('publish').addEventListener('click', (e) => {
                let attribute = document.getElementById('predefined_attribute');
                let attributes = attribute.options;

                for (var i = attributes.length - 1; i >= 0; i--) {
                    if( ! attributes[i].hasAttribute('disabled') && ('pa_resource-type' === attributes[i].value || 'pa_year-level' === attributes[i].value) ) {
                        e.preventDefault();
                        document.querySelector('.dokan-product-attribute-alert').classList.remove('dokan-hide');
                        document.querySelector('.dokan-product-attribute-alert').scrollIntoView({ behavior: 'smooth'});
                    }
                }

                setTimeout(function(){
                  document.querySelector('.dokan-product-attribute-alert').classList.add("dokan-hide");
                }, 5000);
            });

            jQuery(document).ajaxComplete(function(event,xhr,options) {
                if( options.data.includes( 'dokan_get_pre_attribute' ) ) {
                    jQuery('.dokan_attribute_values').select2('destroy');

                    jQuery('.dokan_attribute_values').select2({
                        maximumSelectionLength: 3,
                    });
                }
            });
            
            document.querySelector('.insert-file-row').addEventListener('click', (e) => {
                let tableElement = document.querySelector('.dokan-table');

                if( tableElement.rows.length > 1 ) {
                    e.target.style.display = 'none';
                }
            });
        });
    </script>
    <?php
}   
} );


// Commission Customisation 
add_action( 'wp_loaded', function() {
    // globalize the $wp_filter array so it can be used in the code
    global $wp_filter;
    // $wp_filter contains all the actions and filters that have been added via the add_filter or add_action functions

    global $wp_current_filter;

    $hook = 'dokan_prepare_for_calculation';
    $class = '\WeDevs\DokanPro\Hooks';

    // loop through all the filters
    foreach ( $wp_filter as $filter_name => $filter ) {
        // if the current filter is "single_template"
        if ( $filter_name === $hook ) { // https://d.pr/i/JSOPlf
            // loop through all the callbacks for the current filter
            foreach ( $filter as $priority => $callbacks ) {
                // loop through all the callbacks for the current priority
                foreach ( $callbacks as $callback ) {
                    // check if the callback is an array and the first element is the Eventin class
                    if ( is_array( $callback['function'] ) && $callback['function'][0] instanceof $class ) {
                        // remove the callback
                        remove_filter( $filter_name, $callback['function'], $priority );
                    }
                }
            }
        }
    }
});
add_filter( 'dokan_prepare_for_calculation', 'wbcom_add_combine_commission', 10, 6 );
function wbcom_add_combine_commission( $earning, $commission_rate, $commission_type, $additional_fee, $product_price, $order_id ) {

    if ( 'combine' === $commission_type ) {
        // vendor will get 100 percent if commission rate > 100
        if ( $commission_rate > 100 ) {
            return (float) wc_format_decimal( $product_price );
        }

        // If `_dokan_item_total` returns `non-falsy` value that means, the request comes from the `order refund request`.
        // So modify `additional_fee` to the correct amount to get refunded. (additional_fee/item_total)*product_price.
        // Where `product_price` means item_total - refunded_total_for_item.
        $item_total    = get_post_meta( $order_id, '_dokan_item_total', true );

       

        $product_price = (float) wc_format_decimal( $product_price );
        if ( $order_id && $item_total ) {
            $order          = wc_get_order( $order_id );
            $additional_fee = ( $additional_fee / $item_total ) * $product_price;
        }

        $earning       = ( (float) $product_price * $commission_rate ) / 100;
        $total_earning = $earning + $additional_fee;
        $earning       = (float) $product_price - $total_earning;

        if( $earning < 0 ) {
            $earning = 0;           
        }
    }

    return floatval( wc_format_decimal( $earning ) );

}


// Vendor Coupon
add_action( 'init', function() {
    remove_action( 'woocommerce_coupon_is_valid_for_product', [ 'WeDevs\DokanPro\Coupons\AdminCoupons', 'coupon_is_valid_for_product' ], 15, 3 );
} );

// add_action( 'woocommerce_coupon_is_valid_for_product', 'wbcom_coupon_is_valid_for_product', 15, 3 );
function wbcom_coupon_is_valid_for_product( $valid, $product, $coupon ) {

    if ( false === $valid ) {
        return $valid;
    }

    $vendors  = array( intval( get_post_field( 'post_author', $product->get_id() ) ) );
    $products = array( $product->get_id() );

    if ( $product->get_parent_id() > 0 ) {
        $products[] = $product->get_parent_id();
    }

    $coupon_data          = ! empty( $coupon_meta_data ) ? $coupon_meta_data : dokan_get_admin_coupon_meta( $coupon );
    $enabled_all_vendor   = isset( $coupon_data['admin_coupons_enabled_for_vendor'] ) ? $coupon_data['admin_coupons_enabled_for_vendor'] : '';
    $vendors_ids          = isset( $coupon_data['coupons_vendors_ids'] ) ? $coupon_data['coupons_vendors_ids'] : [];
    $exclude_vendors      = isset( $coupon_data['coupons_exclude_vendors_ids'] ) ? $coupon_data['coupons_exclude_vendors_ids'] : [];
    $product_ids          = isset( $coupon_data['product_ids'] ) ? $coupon_data['product_ids'] : [];
    $excluded_product_ids = isset( $coupon_data['excluded_product_ids'] ) ? $coupon_data['excluded_product_ids'] : [];
    $total_products       = count( $products );
    $total_vendors        = count( $vendors );


    if ( 'yes' === $enabled_all_vendor && empty( $exclude_vendors ) && empty( $product_ids ) && empty( $excluded_product_ids ) ) {
        return true;
    }

    // Check all product IDs excluded from the discount
    if (
        $total_products &&
        count( $excluded_product_ids ) &&
        $total_products === count( array_intersect( $products, $excluded_product_ids ) )
    ) {
        return false;
    }

    // Check any one product ID included on the discount
    if (
        $total_products &&
        count( $product_ids ) &&
        count( array_intersect( $products, $product_ids ) ) > 0
    ) {
        return true;
    }

    // Check all product IDs not excluded from the discount
    if (
        'yes' === $enabled_all_vendor &&
        empty( $product_ids ) &&
        $total_vendors &&
        count( $exclude_vendors ) &&
        $total_vendors !== count( array_intersect( $vendors, $exclude_vendors ) )
    ) {
        return true;
    }

    // Check any one vendor ID included on the discount
    if (
        'no' === $enabled_all_vendor &&
        empty( $product_ids ) &&
        $total_vendors &&
        count( $vendors_ids ) &&
        count( array_intersect( $vendors, $vendors_ids ) ) > 0
    ) {
        return true;
    }

    // Checking product not in excluded product
    if ( ! empty( $excluded_product_ids ) && count( array_intersect( $products, $excluded_product_ids ) ) === 0 ) {
        return true;
    }
    
    return false;
}

// tem fix for fatal error
add_action( 'init', function() {
    remove_filter( 'woocommerce_order_get_items', [ 'WeDevs\DokanPro\VendorDiscount\Hooks', 'replace_coupon_name' ], 10, 3 );
} );

// Prevent Duplicate Orders in WooCommerce within One Hour
/**
 * Title: Prevent Duplicate Orders in WooCommerce within One Hour
 * Description: This code snippet prevents duplicate orders from being placed by the same user within an hour. 
 * It checks the orders made by the current user in the last hour before a new order is processed.
 */
add_action( 'woocommerce_checkout_process', 'wbcom_prevent_duplicate_order_within_one_hour' );
/**
 * Prevents a user from placing a duplicate order within one hour.
 */
function wbcom_prevent_duplicate_order_within_one_hour() {
    // Get current user information
    $current_user = wp_get_current_user();

    // Define the query parameters for recent orders
    $args = array(
        'customer_id' => $current_user->ID, // Orders by the current user
        'date_created' => '>' . ( time() - HOUR_IN_SECONDS / 2 ), // Orders within the last hour
        'status' => array( 'on-hold', 'processing', 'completed' ) // Only include these statuses
    );

    // Retrieve the orders based on the specified criteria
    $orders = wc_get_orders( $args );

    // Loop through each order and check for duplicates
    foreach( $orders as $order ) {
        // Additional checks can be added here, e.g., check total amount, items, etc.
        if ( $order->get_total() === WC()->cart->total ) {
            // Add an error notice and stop the checkout process
            wc_add_notice( 'A similar order has been placed in the last hour. Please wait before placing a new order.', 'error' );
            return;
        }
    }
}

// Paypal Gateway Fee
add_filter( 'dokan_orders_vendor_net_amount', function( $net_amount, $vendor_earning, $gateway_fee, $tmp_order, $order ) {
    $net_amount = $net_amount + $gateway_fee;
    return $net_amount;
}, 50, 5 );

// Order Status Paid to Processing
add_action( 'wp_loaded', function() {
    global $wp_filter, $wp_current_filter;

    $hook = 'woocommerce_order_status_changed';
    $class = '\WeDevs\Dokan\Order\Hooks';
    $method = 'on_sub_order_change';
    
    // loop through all the filters
    foreach ( $wp_filter as $filter_name => $filter ) {

        // if the current filter is the target hook
        if ( $filter_name === $hook ) {
            // loop through all the priorities for the current filter
            foreach ( $filter as $priority => $callbacks ) {
                
                // loop through all the callbacks for the current priority
                foreach ( $callbacks as $callback ) {


                    // check if the callback is an array, the first element is the target class, and the second element is the target method
                    if ( is_array( $callback['function'] )  && 
                         $callback['function'][0] instanceof $class && 
                         $callback['function'][1] === $method ) {
                        remove_filter( $filter_name, $callback['function'], $priority );
                    }
                }
            }
        }
    }
});

 add_action( 'woocommerce_order_status_changed', 'wbcom_custom_on_sub_order_change', 99, 4 );
/**
 * Custom method to handle suborder status change
 *
 * @param int      $order_id
 * @param string   $old_status
 * @param string   $new_status
 * @param WC_Order $order
 */
function wbcom_custom_on_sub_order_change( $order_id, $old_status, $new_status, $order ) {
    // we are monitoring only child orders
    if ( $order->get_parent_id() === 0 ) {
        return;
    }

    // get all the child orders and monitor the status
    $parent_order_id = $order->get_parent_id();
    $sub_orders      = dokan()->order->get_child_orders( $parent_order_id );

    if ( ! $sub_orders ) {
        return;
    }

    // return if any child order is not completed
    $all_complete = true;

    // Exclude manual gateways from auto complete order status update for digital products.
    $excluded_gateways = apply_filters( 'dokan_excluded_payment_gateways_on_order_status_update', array( 'bacs', 'cheque', 'cod' ) );

    foreach ( $sub_orders as $sub_order ) {
        // if the order is a downloadable and virtual product, then we need to set the status to complete
        // if ( 'processing' === $sub_order->get_status() && $order->is_paid() && ! in_array( $order->get_payment_method(), $excluded_gateways, true ) && ! $sub_order->needs_processing() ) {
        //     $sub_order->set_status( 'completed', __( 'Marked as completed because it contains digital products only.', 'dokan-lite' ) );
        //     $sub_order->save();
        // }

        // if any child order is not completed, break the loop
        if ( $sub_order->get_status() !== 'completed' ) {
            $all_complete = false;
        }
    }

    // seems like all the child orders are completed
    // mark the parent order as complete
    if ( $all_complete ) {
        $parent_order = wc_get_order( $parent_order_id );
        $parent_order->update_status( 'wc-completed', __( 'Mark parent order completed when all child orders are completed.', 'dokan-lite' ) );
    }
}


// Customize checkout fields based on cart total
add_filter('woocommerce_checkout_fields', 'wbcom_customize_checkout_fields_based_on_cart_total');

function wbcom_customize_checkout_fields_based_on_cart_total($fields) {
    $cart_total = WC()->cart->get_cart_contents_total(); // Get current cart total

    if ($cart_total < 100) {
        // Keep only essential billing fields: first name, last name, email, company, and postcode
        $allowed_fields = ['billing_first_name', 'billing_last_name', 'billing_email', 'billing_company', 'billing_postcode'];

        // Loop through billing fields and remove those not in the allowed list
        foreach ($fields['billing'] as $key => $field) {
            if (!in_array($key, $allowed_fields)) {
                unset($fields['billing'][$key]); // Remove non-essential fields
            } else {
                // Ensure essential fields are marked as required, except for the company name
                $fields['billing'][$key]['required'] = ($key !== 'billing_company');
            }
        }
    }

    return $fields;
}

// Validate postcode during checkout process for carts under $100
add_action('woocommerce_checkout_process', 'wbcom_validate_postcode_for_low_value_cart');

function wbcom_validate_postcode_for_low_value_cart() {
    $cart_total = WC()->cart->get_cart_contents_total(); // Get current cart total

    // Ensure postcode is provided if cart total is less than $100
    if ($cart_total < 100 && empty($_POST['billing_postcode'])) {
        wc_add_notice(__('Please enter your Postcode.'), 'error'); // Display error if postcode is missing
    }
}

// Optional: Adjust the label of the postcode field to "Postcode" for better clarity
add_filter('woocommerce_default_address_fields', 'wbcom_change_postcode_label');

function wbcom_change_postcode_label($fields) {
    $fields['postcode']['label'] = __('Postcode', 'woocommerce'); // Change label to "Postcode"
    return $fields;
} 

