<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : "NULL";
	$trigger = mysqli_real_escape_string($mysqli, $_POST['trigger']);
	$list = isset($_POST['list']) && is_numeric($_POST['list']) ? mysqli_real_escape_string($mysqli, (int)$_POST['list']) : "NULL";
	$ares = isset($_POST['ares']) && is_numeric($_POST['ares']) ? mysqli_real_escape_string($mysqli, (int)$_POST['ares']) : "NULL";
	$ares = isset($_POST['ares']) && is_numeric($_POST['ares']) ? mysqli_real_escape_string($mysqli, (int)$_POST['ares']) : "NULL";
	$action = mysqli_real_escape_string($mysqli, $_POST['action']);
	$endpoint = mysqli_real_escape_string($mysqli, $_POST['webhook_url']);
	$notification_email = mysqli_real_escape_string($mysqli, $_POST['email_address']);
	$unsubscribe_list_id = isset($_POST['unsubscribe_list_id']) && is_numeric($_POST['unsubscribe_list_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['unsubscribe_list_id']) : "NULL";
	
	//If trigger is 'campaign_sent' or 'campaign_sending', then set the `app` column, otherwise set `app` column to null
	$app_id = $trigger=='campaign_sent' || $trigger=='campaign_sending' ? $app : "NULL";
	
	$q = 'INSERT INTO rules 
		(`brand`, `trigger`, `action`, `endpoint`, `notification_email`, `unsubscribe_list_id`, `list`, `app`, `ares_id`)
		VALUES
		('.$app.', "'.$trigger.'", "'.$action.'", "'.$endpoint.'", "'.$notification_email.'", '.$unsubscribe_list_id.', '.$list.', '.$app_id.', '.$ares.')
	';
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		//Redirect back to Rules page
	    header("Location: ".get_app_info('path')."/rules?i=$app");
	}
	else
	{
		show_error(_('Unable to save the rule'), '<p>'.mysqli_error($mysqli).'</p>', true);
		error_log("[Unable to save the rule]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
		exit;
	}
?>