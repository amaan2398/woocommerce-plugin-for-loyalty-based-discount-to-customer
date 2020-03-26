<?php

function loyaltydiscount_table(){
  global $wpdb;
  require_once(ABSPATH."wp-admin/includes/upgrade.php");

  if(is_null($wpdb->get_var("SHOW TABLES LIKE 'wp_loyaltydiscount'"))==1){
    $sql_query_to_create_table =   "CREATE TABLE `wp_loyaltydiscount` (
                                   `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                   `customer_id` bigint(20) NOT NULL,
                                   `product_id` bigint(20) NOT NULL,
                                   `date_time` datetime NOT NULL DEFAULT current_timestamp(),
                                   `price` float NOT NULL,
                                   `discount` float NOT NULL DEFAULT 0,
                                   `LD_aquire` text NOT NULL DEFAULT 'No',
                                   PRIMARY KEY (`id`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    dbDelta($sql_query_to_create_table);
  }
  if(is_null($wpdb->get_var("SHOW TABLES LIKE 'wp_ld_api_keys'"))==1){
    $sql_query_to_create_table = "CREATE TABLE `wp_ld_api_keys` (
                                   `key_id` bigint(20) NOT NULL AUTO_INCREMENT,
                                   `user_id` bigint(20) NOT NULL,
                                   `description` varchar(200) NOT NULL,
                                   `security_key` varchar(64) NOT NULL,
                                   `last_access` datetime NOT NULL,
                                   PRIMARY KEY (`key_id`),
                                   UNIQUE KEY `security_key` (`security_key`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    dbDelta($sql_query_to_create_table);
  }

}


?>
