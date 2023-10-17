<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$id = isset($_POST['id']) && is_numeric($_POST['id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['id']) : "NULL";
	$action = mysqli_real_escape_string($mysqli, $_POST['action']);
	if($action=='unsub_from_list')
		$val = isset($_POST['val']) && is_numeric($_POST['val']) ? mysqli_real_escape_string($mysqli, (int)$_POST['val']) : "NULL";
	else
		$val = '"'.mysqli_real_escape_string($mysqli, $_POST['val']).'"';
		
	$actions = array('webhook'=>'endpoint', 'notify'=>'notification_email', 'unsub_from_list'=>'unsubscribe_list_id');
	$column = $actions[$action];

	$q = 'UPDATE rules SET '.$column.'='.$val.' WHERE id = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		echo true;
	}
	else
	{
		error_log("[Unable to edit rule]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
		echo false;
	}
?>