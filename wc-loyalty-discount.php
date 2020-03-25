<?php
/*
Plugin Name: Loyalty Discount
Description: This is an plugin which will give discount to customer based on customer loyalty score by the use of ML/AI supervise model.
Version:0.1
Author: Amaan Shaikh
Author URI:www.linkedin.com/in/amaan-shaikh-a91735178
*/

if (!defined('ABSPATH')) {
    exit;
}

function ld_posts(){
	return "AMAAN";
}

add_action('rest_api_init', function(){
	register_rest_route('ld/v1','/posts',[
		'methods' => 'GET',
		'callback' => 'ld_posts'
	]);
});

define("PLUGIN_DIR_PATH",plugin_dir_path(__FILE__));
define("PLUGIN_URL",plugins_url());
define("PLUGIN_VERSION","0.1");

function add_my_custom_menu(){
	add_menu_page("loyaltydiscount","Loyalty Discount","manage_options","loyalty-discount","plugin_dashboard_view","dashicons-dashboard",6);
	add_submenu_page("loyalty-discount","dashboard","Dashboard","manage_options","loyalty-discount","plugin_dashboard_view");
	add_submenu_page("loyalty-discount","settings","Settings","manage_options","settings","plugin_settings_view");
}
add_action("admin_menu","add_my_custom_menu");

function plugin_dashboard_view(){
	include_once(PLUGIN_DIR_PATH."/views/dashboard.php");
}

function plugin_settings_view(){
	include_once(PLUGIN_DIR_PATH."/views/settings.php");
}

add_action("init","plugin_assets");

function plugin_assets(){
	wp_enqueue_style("style_css",PLUGIN_URL."/loyalty-discount/assets/css/style.css","",PLUGIN_VERSION);
	wp_enqueue_script("plugin_script",PLUGIN_URL."/loyalty-discount/assets/js/script.js","",PLUGIN_VERSION,true);
}

function loyaltydiscount_table(){
	global $wpdb;
	require_once(ABSPATH."wp-admin/includes/upgrade.php");

	if(count($wpdb->get_var("SHOW TABLE LIKE 'wp_loyaltydiscount'"))==0){
		$sql_query_to_create_table = "CREATE TABLE `wp_loyaltydiscount` (
 `id` int(11) NOT NULL,
 `customer_id` int(11) NOT NULL,
 `product_id` int(11) NOT NULL,
 `date_time` int(11) NOT NULL,
 `price` float NOT NULL,
 `discount` float NOT NULL,
 `LD_aquire` text NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		dbDelta($sql_query_to_create_table);
	}
}

register_activation_hook(__FILE__,"loyaltydiscount_table");

function loyaltydiscount_table_delete(){
	global $wpdb;
	$wpdb->query("DROP TABLE IF EXISTS wp_loyaltydiscount");
}

register_uninstall_hook(__FILE__,"loyaltydiscount_table_delete");


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
