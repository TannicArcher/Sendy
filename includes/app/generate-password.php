<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	//Only allow script to continue if it's the main admin user generating a password for a brand
	if(get_app_info('is_sub_user')) exit;
	
	//Init
	$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : exit;
	$company = mysqli_real_escape_string($mysqli, $_POST['brand_name']);
	$name = mysqli_real_escape_string($mysqli, $_POST['from_name']);
	$username = mysqli_real_escape_string($mysqli, $_POST['from_email']);
	$password = ran_string(12, 12, true, false, true);
	$pass_encrypted = hash('sha512', $password.'PectGtma');
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	$q = 'SELECT id FROM login WHERE app = '.$app;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		//update password
	    $q = 'UPDATE login SET password = "'.$pass_encrypted.'" WHERE app = '.$app;
	    $r = mysqli_query($mysqli, $q);
	    if ($r) echo $password;
	}
	else
	{
		//insert new record
		$q = 'INSERT INTO login (name, company, username, password, tied_to, app) VALUES ("'.$name.'", "'.$company.'", "'.$username.'", "'.$pass_encrypted.'", '.get_app_info('userID').', '.$app.')';
		$r = mysqli_query($mysqli, $q);
		if ($r)  echo $password;
	}
?>