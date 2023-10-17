<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$list_id = isset($_POST['l']) && is_numeric($_POST['l']) ? mysqli_real_escape_string($mysqli, (int)$_POST['l']) : exit;
	$hide = isset($_POST['hide']) && is_numeric($_POST['hide']) ? mysqli_real_escape_string($mysqli, (int)$_POST['hide']) : exit;
	
	$q = 'UPDATE lists SET hide = '.$hide.' WHERE id = '.$list_id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		echo $hide;
	}
	else
	{
		error_log("[Unable to run query]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
		exit;
	}
?>