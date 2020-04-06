<?php

$prodDiscount = array();
$t=0;
function api_call($cust_id,$pid){
  $arrjs=array("id"=>$cust_id,"product_c"=>$pid);
  $send_json=json_encode($arrjs);
  $url="192.168.1.107:8099/discount_model";
  $ch= curl_init($url);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$send_json);
  curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:application/json'));
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  $result =(array) json_decode(curl_exec($ch));
  curl_close($ch);
  return $result;
}
/*
function prefix_add_discount_line( $cart ) {

  //$discount = api_call();
  $discount=10;
  print_r($cart);
  $cart->add_fee("Loyalty discount", $discount);
  print_r($cart);
}*/
//add_action( 'woocommerce_cart_calculate_fees', 'prefix_add_discount_line',20,1);
add_action( 'woocommerce_before_calculate_totals', 'add_custom_price', 10, 1);
function add_custom_price( $cart_object ) {
    $user=wp_get_current_user();
    $cust_id = $user->ID;
    global $prodDiscount;
    global $woocommerce;
    global $t;
    $prodDiscount = array('id' => $cust_id);
    $t=0;
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    foreach ( $cart_object->get_cart() as $cart_item ) {
        $pid = $cart_item['data']->get_id();
        $result=api_call($cust_id,$pid);
        $discount=$result['response'];
        $t+=$discount;
        ## Price calculation ##
        $price = $cart_item['data']->price - ($discount/$cart_item['quantity']);
        array_push($prodDiscount,array('pid'=>$pid,'price'=>$price*$cart_item['quantity'],'discount'=>$discount,'code'=>$result['code']));
        ## Set the price with WooCommerce compatibility ##
        if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
            $cart_item['data']->price = $price; // Before WC 3.0
        } else {
            $cart_item['data']->set_price( $price ); // WC 3.0+
        }
    }
}



function discount_view() {

    global $woocommerce;
    global $t;
    $discount_total = $t;

    if ( $discount_total > 0 ) {
    echo '<tr class="cart-discount">
    <th>'. __( 'Your Loyalty Discount', 'woocommerce' ) .'</th>
    <td data-title=" '. __( 'Your Loyalty Discount', 'woocommerce' ) .' ">'
    . wc_price( $discount_total + $woocommerce->cart->discount_cart ) .'</td>
    </tr>';
    }

}

// Hook our values to the Basket and Checkout pages

add_action( 'woocommerce_cart_totals_after_order_total', 'discount_view', 99);
add_action( 'woocommerce_review_order_after_order_total', 'discount_view', 99);


add_action('woocommerce_checkout_order_processed','placed_order_update_database', 10, 1);
function placed_order_update_database($order_id){
  global $prodDiscount;
  //print_r($prodDiscount);
  global $wpdb;
  //print_r(sizeof($prodDiscount)-1);
  require_once(ABSPATH."wp-admin/includes/upgrade.php");
  //global $count;
  //$count+=1;
  //echo $count;
  $i=0;
  for($i=0;$i<sizeof($prodDiscount)-1;$i++){
      $table = $wpdb->prefix.'loyaltydiscount';
      $data=array(
            "customer_id" =>$prodDiscount["id"],
            "product_id" => $prodDiscount[$i]["pid"],
            "price" =>$prodDiscount[$i]["price"],
            "lastOfferCode" => $prodDiscount[$i]["code"],
            "discount" => $prodDiscount[$i]["discount"],
            "LD_aquire" => ($prodDiscount[$i]["discount"] > 0 ? "Yes" : "No")
          );
      if ($data["LD_aquire"]=="Yes"){
          $upData=array("LD_aquire"=>$data["LD_aquire"]);
          $where=array("customer_id" =>$data["customer_id"],
                      "product_id" => $data["product_id"]);
          $wpdb->update(
            $table,
            $upData,
            $where
          );
      }
      $wpdb->insert($table,$data);
  }
}

?>
