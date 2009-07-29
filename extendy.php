<?php
/*
Plugin Name: Extendy
Plugin URI: http://www.extendy.com/
Description: The Extendy plugin installs your toolbar from http://extendy.com on your blog.
Version: 0.1.1
Author: Mason Browne
Author URI: http://www.extendy.com/
*/

$EXTENDY_DEBUG = 1;

if($EXTENDY_DEBUG){
  $EXTENDY_API_DOMAIN = "http://extendy.local:3000";
} else {
  $EXTENDY_API_DOMAIN = "http://www.extendy.com";
}
$EXTENDY_API_QUERY_URI = "/api/v1/installations/wordpress/";
$EXTENDY_LOADER = "/api/v1/loader/";

function extendy_api_endpoint($key) {
  global $EXTENDY_API_DOMAIN, $EXTENDY_API_QUERY_URI;
  return "${EXTENDY_API_DOMAIN}${EXTENDY_API_QUERY_URI}${key}";
}

function extendy_embed($campaign) {
  global $EXTENDY_API_DOMAIN, $EXTENDY_LOADER;
  return "${EXTENDY_API_DOMAIN}${EXTENDY_LOADER}/${campaign}.js";
}

function extendy_init(){
  global $extendy_campaign_id;
  $extendy_campaign_id = get_option('extendy_campaign_id');
  if(is_admin()){
    add_action('admin_menu', 'extendy_config_page');
    add_action('admin_init', 'register_extendy_settings');
  }
  add_action('wp_footer', 'extendy_footer');
}
add_action('init', 'extendy_init');

function extendy_admin_init(){
  global $extendy_campaign_api_key, $extendy_campaign_id;
  $extendy_campaign_api_key = get_option('extendy_campaign_api_key');
  $missing_extendy_settings = !($extendy_campaign_api_key && $extendy_campaign_id);
  if(($missing_extendy_settings && !$_POST['extendy_campaign_api_key']) || $_POST['extendy_uninstall']){
    add_action('admin_notices', 'extendy_warning');
  }
}

add_action('admin_init', 'extendy_admin_init');

function register_extendy_settings(){
  register_setting('extendy-settings', 'extendy_campaign_api_key');
  register_setting('extendy-settings', 'extendy_campaign_disabled');
}

function extendy_config_page() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('plugins.php', __('Extendy Configuration'), __('Extendy Configuration'), 'manage_options', 'extendy-config', 'extendy_conf');
}

if ( !function_exists('wp_nonce_field') ) {
	function extendy_nonce_field($action = -1) { return; }
	$akismet_nonce = -1;
} else {
	function extendy_nonce_field($action = -1) { return wp_nonce_field($action); }
	$akismet_nonce = 'extendy-update-key';
}

function extendy_verify_key_format($v){
  // Naive checking for now. Just make sure it... y'know... has a length
  if(strlen($v) > 0){
    return $v;
  } else {
    return null;
  }
}

function extendy_query_details($key){
  $endpoint = extendy_api_endpoint($key);
  // Simplest way to query a URI...
  try {
    $result = file_get_contents($endpoint);
    $result_xml = simplexml_load_string($result);
    if($result_xml->getName() == 'campaign'){
      return array('id' => ((int)$result_xml['id']), 'name' => ((string)$result_xml->name));
    } else {
      return null;
    }
  } catch (Exception $e){
    return null;
  }
  
}

function extendy_conf(){
  global $extendy_nonce, $extendy_campaign_api_key, $extendy_campaign_id;
  
  // First, check to see if we've received an uninstall request...
  if($_POST['extendy_uninstall']){
    // If so, remove all information relating to the campaign, and redirect
    // back to this page.
    
    delete_option('extendy_campaign_api_key');
    delete_option('extendy_campaign_id');
    delete_option('extendy_campaign_name');
    delete_option('extendy_campaign_disabled');
    $extendy_campaign_api_key = "";
  }
  
  
  if($_POST['extendy_campaign_api_key']){
    // Verify length
    // Query for campaign details
    // Update wordpress settings
    // Only save campaign_api_key if it returned a valid name/id
    $key = extendy_verify_key_format($_POST['extendy_campaign_api_key']);
    if($key){
      $details = extendy_query_details($key);
      if(is_array($details)){
        // We have something valid! Yay! Save it. Save it all.
        $extendy_campaign_name = $details['name'];
        $extendy_campaign_id = $details['id'];

        update_option('extendy_campaign_api_key', $key);
        update_option('extendy_campaign_id', $extendy_campaign_id);
        update_option('extendy_campaign_name', $extendy_campaign_name);
      }
      $extendy_campaign_api_key = $key;
    }
    
  } else {
    $extendy_campaign_name = get_option('extendy_campaign_name');
  }
  
  
  if(!is_null($_POST['extendy_campaign_disabled'])){
    $extendy_campaign_disabled = $_POST['extendy_campaign_disabled'];
    update_option('extendy_campaign_disabled', $extendy_campaign_disabled);
  } else {
    $extendy_campaign_disabled = get_option('extendy_campaign_disabled');
  }
  
  
  include(dirname(__FILE__) . '/extendy_options.php');
}

function extendy_warning() {
  echo "
	<div id='extendy-warning' class='updated fade'><p><strong>".__('Extendy is almost ready.')."</strong> ".sprintf(__('You must <a href="%1$s">enter a campaign key</a> for it to work.'), "plugins.php?page=extendy-config")."</p></div>
	";
}

function extendy_footer(){
  global $extendy_campaign_id;
  $disabled = get_option('extendy_campaign_disabled');
  if($extendy_campaign_id && !$disabled){
    $loader = extendy_embed($extendy_campaign_id);
    echo "<script type=\"text/javascript\">
    document.write(unescape(\"%3Cscript src='${loader}' type='text/javascript'%3E%3C/script%3E\"));
    </script>
    ";
  }
}

function extendy_style(){
  include(dirname(__FILE__) . '/extendy_style.php');
}
add_action('admin_head', 'extendy_style');
