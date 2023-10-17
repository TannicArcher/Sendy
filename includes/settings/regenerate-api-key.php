<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//Only allow script to continue if it's the main admin user generating a password for a brand
	if(get_app_info('is_sub_user')) exit;

	//Init
	$api_key = ran_string(20, 20, true, false, true);
	$api_key_prev = get_app_info('api_key');
	
	//Update API key
	$q = 'UPDATE login SET api_key = "'.$api_key.'", api_key_prev = "'.$api_key_prev.'" WHERE id = '.get_app_info('userID');
	$r = mysqli_query($mysqli, $q);
	if ($r) echo $api_key;
	else echo "failed";
?>