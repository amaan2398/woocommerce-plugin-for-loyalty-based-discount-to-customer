<?php

function api_call($cust_id,$pid){
  $arrjs=array("id"=>$cust_id,"product_c"=>$pid);
  $send_json=json_encode($arrjs);
  $url="192.168.1.107:8099/discount";
  $ch= curl_init($url);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$send_json);
  curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:application/json'));
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  $result =(array) json_decode(curl_exec($ch));
  curl_close($ch);
  return $result['response'];
}

function prefix_add_discount_line( $cart ) {

  //$discount = api_call();
  $discount=10;
  print_r($cart);
  $cart->add_fee("Loyalty discount", -$discount,false,'Zero rate');
  print_r($cart);
}
//add_action( 'woocommerce_cart_calculate_fees', 'prefix_add_discount_line',20,1);
add_action( 'woocommerce_before_calculate_totals', 'add_custom_price', 10, 1);
function add_custom_price( $cart_object ) {
    $user=wp_get_current_user();
    $cust_id = $user->ID;
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    foreach ( $cart_object->get_cart() as $cart_item ) {
        $pid = $cart_item['data']->get_id();
        $discount=api_call($cust_id,$pid);
        ## Price calculation ##
        $price = $cart_item['data']->price - $discount;

        ## Set the price with WooCommerce compatibility ##
        if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
            $cart_item['data']->price = $price; // Before WC 3.0
        } else {
            $cart_item['data']->set_price( $price ); // WC 3.0+
        }
    }
}

?>
