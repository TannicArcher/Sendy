<?php 
	include('functions.php');
	include('../../../../config.php');
	
	//Get raw body POSTed by Zapier
	if (!isset($HTTP_RAW_POST_DATA)) $HTTP_RAW_POST_DATA = file_get_contents('php://input');
	
	//Format JSON data
	$obj = json_decode($HTTP_RAW_POST_DATA);
	$list = $obj->{'list'};
	$email = $obj->{'email'};
	$app_path = APP_PATH;
	
	//Unsubscribe
	$postdata = http_build_query(
	    array(
	    'email' => $email,
	    'list' => $list,
	    'boolean' => 'true'
	    )
	);
	$result = file_get_contents_curl_post($app_path.'/unsubscribe', $postdata);
?>