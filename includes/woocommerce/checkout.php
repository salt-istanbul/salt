<?php


//checkout coupon replace
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
add_action( 'woocommerce_review_order_before_payment', 'woocommerce_checkout_coupon_form' );




//Shipping clculator remove
// 1 Disable State
add_filter( 'woocommerce_shipping_calculator_enable_state', '__return_false' );
// 2 Disable City
add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_false' );
// 3 Disable Postcode
add_filter( 'woocommerce_shipping_calculator_enable_postcode', '__return_false' );



//redirect checkout & cart page to login page if not logged
function wpse_131562_redirect() {
    if (! is_user_logged_in() && (is_cart() || is_checkout())) {
        wp_redirect(wc_get_account_endpoint_url('my-account'));
        exit;
    }
}
add_action('template_redirect', 'wpse_131562_redirect');








//add_filter( 'woocommerce_checkout_fields' , 'remove_billing_fields_from_checkout' );
function remove_billing_fields_from_checkout( $fields ) {
    $fields[ 'billing' ] = array();
    return $fields;
}
// Removes Order Notes Title - Additional Information & Notes Field
add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );

// Remove Order Notes Field
add_filter( 'woocommerce_checkout_fields' , 'remove_order_notes' );

function remove_order_notes( $fields ) {
     unset($fields['order']['order_comments']);
     return $fields;
}






// hide coupon field on checkout page
function hide_coupon_field_on_checkout( $enabled ) {
    if ( is_checkout() ) {
        $enabled = false;
    }
    return $enabled;
}
//add_filter( 'woocommerce_coupons_enabled', 'hide_coupon_field_on_checkout' );






/* Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function bbloomer_unset_shipping_when_free_is_available_all_zones( $rates, $package ) {   
    $all_free_rates = array();   
    foreach ( $rates as $rate_id => $rate ) {
        if ( 'free_shipping' === $rate->method_id ) {
            $all_free_rates[ $rate_id ] = $rate;
            break;
        }
    } 
    if ( empty( $all_free_rates )) {
         return $rates;
    } else {
        return $all_free_rates;
    } 
}
//add_filter( 'woocommerce_package_rates', 'bbloomer_unset_shipping_when_free_is_available_all_zones', 10, 2 );






function free_shipping_remaining_amount(){
    $value = alg_wc_get_left_to_free_shipping( "%amount_left_for_free_shipping% left for free shipping" );
    if(!empty($value)){
        $price_left = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        $alertType = !empty($price_left)?"warning":"success";
        echo( "<div class='alert alert-".$alertType."'>". $value ."</div>");
    }
}
function free_shipping_remaining_amount_notice(){
   $value = alg_wc_get_left_to_free_shipping( "%amount_left_for_free_shipping% left for free shipping" );
    if(!empty($value)){
       wc_add_notice($value);
    }
}
//add_action( 'wp', 'free_shipping_remaining_amount_notice' );
//add_action( 'woocommerce_cart_totals_before_order_total', 'free_shipping_remaining_amount');
//add_filter( 'woocommerce_package_rates', 'free_shipping_remaining_amount', 10, 2 ); causes problem on cart ajax when chabe address




/**
 * Conditionally show gift add-ons if shipping address differs from billing
**/
function wc_checkout_add_ons_conditionally_show_gift_add_on() {

    wc_enqueue_js( "
        $( 'input[name=dff528b]' ).change( function () {
            if ( $( this ).is( ':checked' ) ) {
                $( '#f443ada_field' ).removeClass('d-none');
            } else {
                $( '#f443ada_field' ).addClass('d-none');
            }
        } ).change();
    " );
}
///add_action( 'wp_enqueue_scripts', 'wc_checkout_add_ons_conditionally_show_gift_add_on' );




remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );






add_action( 'woocommerce_customer_save_address', 'jsforwp_update_address_for_orders', 10, 2 );
function jsforwp_update_address_for_orders( $user_id, $load_address ) {
    $customer_meta = get_user_meta( $user_id );
    $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $user_id,
        'post_type'   => wc_get_order_types(),
        'post_status' => array_keys( wc_get_order_statuses() )
    ) );
    foreach( $customer_orders as $order ) {
          update_post_meta( $order->ID, '_billing_first_name', $customer_meta['billing_first_name'][0] );
          update_post_meta( $order->ID, '_billing_last_name', $customer_meta['billing_last_name'][0] );
          update_post_meta( $order->ID, '_billing_company', $customer_meta['billing_company'][0] );
          update_post_meta( $order->ID, '_billing_address_1', $customer_meta['billing_address_1'][0] );
          update_post_meta( $order->ID, '_billing_address_2', $customer_meta['billing_address_2'][0] );
          update_post_meta( $order->ID, '_billing_city', $customer_meta['billing_city'][0] );
          update_post_meta( $order->ID, '_billing_state', $customer_meta['billing_state'][0] );
          update_post_meta( $order->ID, '_billing_postcode', $customer_meta['billing_postcode'][0] );
          update_post_meta( $order->ID, '_billing_country', $customer_meta['billing_country'][0] );
          update_post_meta( $order->ID, '_billing_email', $customer_meta['billing_email'][0] );
          update_post_meta( $order->ID, '_billing_phone', $customer_meta['billing_phone'][0] );
    }
};




/*disable zip code validation on checkout */
add_filter( 'woocommerce_checkout_fields' , 'bbloomer_alternative_override_postcode_validation' );
function bbloomer_alternative_override_postcode_validation( $fields ) {
    $fields['billing']['billing_postcode']['required'] = false;
    $fields['shipping']['shipping_postcode']['required'] = false;
    return $fields;
}












function mysite_pending($order_id) {
    error_log("$order_id set to PENDING");
}
function mysite_failed($order_id) {
    error_log("$order_id set to FAILED");
}
function mysite_hold($order_id) {
    error_log("$order_id set to ON HOLD");
}
function mysite_processing($order_id) {
    error_log("$order_id set to PROCESSING");
}
function mysite_completed($order_id) {
    remove_filter('acf/update_value/name=tour_plan_status', 'tour_plan_status_update', 10, 3);
    $product_id = get_product_by_order_id($order_id);
    $tour_plan_id = get_field("tour_plan_id", $product_id);
    update_field("tour_plan_status", "archive", $tour_plan_id );
}
function mysite_refunded($order_id) {
    error_log("$order_id set to REFUNDED");
}
function mysite_cancelled($order_id) {
    error_log("$order_id set to CANCELLED");
}

add_action( 'woocommerce_order_status_pending', 'mysite_pending', 10, 1);
add_action( 'woocommerce_order_status_failed', 'mysite_failed', 10, 1);
add_action( 'woocommerce_order_status_on-hold', 'mysite_hold', 10, 1);
// Note that it's woocommerce_order_status_on-hold, and NOT on_hold.
add_action( 'woocommerce_order_status_processing', 'mysite_processing', 10, 1);
add_action( 'woocommerce_order_status_completed', 'mysite_completed', 10, 1);
add_action( 'woocommerce_order_status_refunded', 'mysite_refunded', 10, 1);
add_action( 'woocommerce_order_status_cancelled', 'mysite_cancelled', 10, 1);


function mysite_woocommerce_payment_complete( $order_id ) {
    /*remove_filter('acf/update_value/name=tour_plan_status', 'tour_plan_status_update', 10, 3);
    $product_id = get_product_by_order_id($order_id);
    $tour_plan_id = get_field("tour_plan_id", $product_id);
    update_field("tour_plan_status", "paid", $tour_plan_id );*/
}
//add_action( 'woocommerce_payment_complete', 'mysite_woocommerce_payment_complete', 10, 1 );






//add_action( 'woocommerce_before_calculate_totals', 'custom_cart_items_prices', 10, 1 );
function custom_cart_items_prices( $cart ) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    foreach ( $cart->get_cart() as $cart_item ) {
        // Get the product id (or the variation id)
        $product_id = $cart_item['data']->get_id();
        $currency = $cart_item['data']->get_meta("tour_currency");
        if($cart_item['data']->get_meta("_wc_deposits_enable_deposit") == "yes"){
            $tour_plan_offer_id = $cart_item['data']->get_meta("tour_plan_offer_id");
            if($currency != "USD"){
                $deposit_paid = product_deposit_payment_is_complete($product_id);
                $total_amount =  $cart_item['data']->get_meta("tour_price");
                $deposit_amount = $cart_item['data']->get_meta("_wc_deposits_deposit_amount");
                if(!$deposit_paid){
                   $price = $total_amount;//($total_amount/100) * $deposit_amount;
                   $new_price = currencyConvert($price, $currency, "USD");
                   $cart_item['data']->set_price( $new_price );        
                }else{
                   //$new_price = 69;
                }
                        
            }
        }else{
            if($currency != "USD"){
                $price = $cart_item['data']->get_meta("tour_price");
                $new_price = currencyConvert($price, $currency, "USD");
                $cart_item['data']->set_price( $new_price ); 
            }
        }
    }
}
