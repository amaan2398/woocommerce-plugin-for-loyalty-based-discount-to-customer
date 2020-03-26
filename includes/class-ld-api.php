<?php

class ld_api extends WP_REST_Controller {
  public function register_routes() {
    $namespace = 'ld/v1';
    $path = 'posts/(?P<id>[a-zA-Z0-9\ ]+)';

    register_rest_route( $namespace, '/' . $path, [
      array(
        'methods'             => 'POST',
        'callback'            => array( $this, 'get_items' ),
        'permission_callback' => array( $this, 'get_items_security_check' )
      )
        ]);
    }

    public function get_items_security_check($request) {
      global $wpdb;
    	require_once(ABSPATH."wp-admin/includes/upgrade.php");
      $query="SELECT security_key FROM wp_ld_api_keys WHERE security_key = '".$request->get_header('SecurityKey')."'";
      $dbresult=$wpdb->get_results(
        $wpdb->prepare(
              $query,''
          )
      );
      if (count($dbresult)==0){
        return false;
      }
      return true;
    }

    public function get_items($request) {
      global $wpdb;
    	require_once(ABSPATH."wp-admin/includes/upgrade.php");
      $query="SELECT * FROM wp_loyaltydiscount WHERE customer_id = ".$request['id']." AND product_id = ".$request['pid']." AND LD_aquire = 'No'";
      $data=$wpdb->get_results(
        $wpdb->prepare(
              $query,''
          )
      );
      return $data;
    }
}
