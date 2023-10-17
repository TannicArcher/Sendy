<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$dark_mode = isset($_POST['dark_mode']) && is_numeric($_POST['dark_mode']) ? mysqli_real_escape_string($mysqli, (int)$_POST['dark_mode']) : exit;
	
	//Change appearance
	$q = 'UPDATE login SET dark_mode = '.$dark_mode.' WHERE id = '.get_app_info('userID');
	$r = mysqli_query($mysqli, $q);
	if ($r) echo true;
	else echo false;
?>