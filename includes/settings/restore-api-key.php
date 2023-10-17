<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 	
	//Only allow script to continue if it's the main admin user generating a password for a brand
	if(get_app_info('is_sub_user')) exit;
	
	//Init
	$q = 'SELECT api_key_prev FROM login WHERE id = '.get_app_info('userID');
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $api_key_prev = $row['api_key_prev'];
	
	$q = 'UPDATE login SET api_key = "'.$api_key_prev.'", api_key_prev = "" WHERE id = '.get_app_info('userID');
	$r = mysqli_query($mysqli, $q);
	if ($r) echo $api_key_prev;
	else echo "failed";
?>