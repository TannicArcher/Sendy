<?php 
	include('functions.php');
	include('../../../../config.php');
	
	//Get raw body POSTed by Zapier
	if (!isset($HTTP_RAW_POST_DATA)) $HTTP_RAW_POST_DATA = file_get_contents('php://input');
	
	//Format JSON data
	$obj = json_decode($HTTP_RAW_POST_DATA);
	$api_key = $_GET['api_key'];
	$list = $obj->{'list'};
	$name = $obj->{'name'};
	$email = $obj->{'email'};
	$app_path = APP_PATH;
	
	//Subscribe
	$postdata = http_build_query(
	    array(
	    'api_key' => $api_key,
	    'name' => $name,
	    'email' => $email,
	    'list' => $list,
	    'boolean' => 'true'
	    )
	);
	$result = file_get_contents_curl_post($app_path.'/subscribe', $postdata);
?>