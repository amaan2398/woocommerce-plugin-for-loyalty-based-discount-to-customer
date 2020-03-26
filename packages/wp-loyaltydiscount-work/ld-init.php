<?php

function add_my_custom_menu()
{
  add_menu_page("loyaltydiscount","Loyalty Discount","manage_options","loyalty-discount","plugin_dashboard_view","dashicons-dashboard",6);
  add_submenu_page("loyalty-discount","dashboard","Dashboard","manage_options","loyalty-discount","plugin_dashboard_view");
  add_submenu_page("loyalty-discount","settings","Settings","manage_options","settings","plugin_settings_view");
}

function plugin_dashboard_view()
{
  include_once(PLUGIN_DIR_PATH."/views/dashboard.php");
}

function plugin_settings_view()
{
  include_once(PLUGIN_DIR_PATH."/views/settings.php");
}

add_action("admin_menu","add_my_custom_menu");


?>
