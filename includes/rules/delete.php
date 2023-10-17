<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$rule_id = isset($_POST['rule_id']) && is_numeric($_POST['rule_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['rule_id']) : exit;
	
	$q = 'DELETE FROM rules WHERE id = '.$rule_id; 
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		echo true;
	}
	else
	{
		error_log("[Unable to delete rule]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
		echo false;
	}
	
?>