<?php

function plugin_assets(){
  wp_enqueue_style("style_css",PLUGIN_URL."/loyalty-discount/assets/css/style.css","",PLUGIN_VERSION);
  wp_enqueue_script("plugin_script",PLUGIN_URL."/loyalty-discount/assets/js/script.js","",PLUGIN_VERSION,true);
}
add_action("init","plugin_assets");


?>
