<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 	
	//init
	$campaign_id = isset($_POST['campaign_id']) && is_numeric($_POST['campaign_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['campaign_id']) : exit;
	$loader = get_app_info('dark_mode') ? 'loader-dark.gif' : 'loader-light.gif';
	
	//get send count
	$q = 'SELECT to_send, recipients FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$to_send = $row['to_send'];
			$recipients = $row['recipients'];
	    }  
	}
	
	$percentage = $recipients!=0 ? $recipients / $to_send * 100 : 0;
	
	if($to_send == $recipients)
		echo $recipients;
	else
		echo $recipients.' <span style="color:#488846;">('.round($percentage).'%)</span> <img src="'.get_app_info('path').'/img/'.$loader.'" style="width:16px;"/>';
?>