<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php
	
//variables
$campaign_id = (int)mysqli_real_escape_string($mysqli, $_POST['campaign_id']);
$ispreparing = (int)mysqli_real_escape_string($mysqli, $_POST['ispreparing']);

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
	
	//If campaign is in 'Preparing' status, put the campaign back to 'Draft' status.
	if($ispreparing)
	{
		//Set campaign to 'Sent' 
		$qq = 'UPDATE campaigns SET sent=NULL, to_send=NULL, send_date=NULL, lists=NULL, lists_excl=NULL, segs=NULL, segs_excl=NULL, timezone=NULL WHERE id = '.$campaign_id;
		mysqli_query($mysqli, $qq);
		
		echo true;
	}
	//If the campaign is in 'Sending' status, stop the campaign.
	else
	{
		//Set campaign to 'Sent' 
		$qq = 'UPDATE campaigns SET to_send = '.$recipients.' WHERE id = '.$campaign_id;
		mysqli_query($mysqli, $qq);
		
		//and empty the sending queue
		$qq2 = 'DELETE FROM queue WHERE campaign_id = '.$campaign_id;
		mysqli_query($mysqli, $qq2);
		
		//This will 'exit()' the scheduled.php or includes/create/send-now.php script (depending on whether cron job is setup for sending campaigns)
		$q2 = 'UPDATE campaigns SET campaign_stopped = 1 WHERE id = '.$campaign_id;
		mysqli_query($mysqli, $q2);
		
		echo true; 
	}
}
else
{
	show_error(_('Unable to stop campaign'), '<p>'.mysqli_error($mysqli).'</p>', true);
	error_log("[Unable to stop campaign]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
	exit;
}

?>
