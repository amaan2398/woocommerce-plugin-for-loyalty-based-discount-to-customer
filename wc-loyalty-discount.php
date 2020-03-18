<?php
/*
Plugin Name: Loyalty Discount
Description: This is an plugin which will give discount to customer based on customer loyalty score by the use of ML/AI supervise model.
Version:0.11
Author: Amaan Shaikh
Author URI:www.linkedin.com/in/amaan-shaikh-a91735178
*/

function add_my_custom_menu(){
	add_menu_page("loyaltydiscount","Loyalty Discount","manage_options","loyalty-discount","plugin_home_view","dashicons-dashboard",6);
}
add_action("admin_menu","add_my_custom_menu");

function plugin_home_view(){

}

function api_call(){
	$user=wp_get_current_user();
	$cust_id =$user->ID;
	$pro_id = array();
	foreach(WC()->cart->get_cart() as $c_item){
		$pro_id[]= $c_item['product_id'];
	}
	$arrjs=array("id"=>$cust_id,"product_c"=>$pro_id);
	$send_json=json_encode($arrjs);
	$url="192.168.1.105:8099/discount";
	$ch= curl_init($url);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$send_json);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:application/json'));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	$result = curl_exec($ch);
	curl_close($ch);
	return 20;
}

function prefix_add_discount_line( $cart ) {

  //$discount = api_call();
	$discount=5;
  $cart->add_fee(__("Loyalty discount"), -$discount );

}
add_action( 'woocommerce_cart_calculate_fees', 'prefix_add_discount_line',20,1);
//add_action( 'woocommerce_before_calculate_totals', 'add_custom_price', 10, 1);
function add_custom_price( $cart_object ) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    foreach ( $cart_object->get_cart() as $cart_item ) {
        ## Price calculation ##
				//print_r($cart_item);
        $price = $cart_item['data']->price - 0;

        ## Set the price with WooCommerce compatibility ##
        if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
            $cart_item['data']->price = $price; // Before WC 3.0
        } else {
            $cart_item['data']->set_price( $price ); // WC 3.0+
        }
    }
}
