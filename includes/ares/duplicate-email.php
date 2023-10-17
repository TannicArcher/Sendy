<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$app = isset($_GET['app']) && is_numeric($_GET['app']) ? mysqli_real_escape_string($mysqli, (int)$_GET['app']) : exit;
	$a = isset($_GET['a']) && is_numeric($_GET['a']) ? mysqli_real_escape_string($mysqli, (int)$_GET['a']) : exit;
	$ae = isset($_GET['ae']) && is_numeric($_GET['ae']) ? mysqli_real_escape_string($mysqli, (int)$_GET['ae']) : exit;
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//Duplicate the email
	$q = 'INSERT INTO ares_emails 
				(ares_id, from_name, from_email, reply_to, title, plain_text, html_text, query_string, time_condition, created, wysiwyg, opens_tracking, links_tracking, segs, segs_excl) 
			SELECT 
				ares_id, from_name, from_email, reply_to, title, plain_text, html_text, query_string, time_condition, UNIX_TIMESTAMP(NOW()), wysiwyg, opens_tracking, links_tracking, segs, segs_excl 
			FROM
				ares_emails
			WHERE id = '.$ae;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    header("Location: ".get_app_info('path')."/autoresponders-emails?i=".$app.'&a='.$a);
	}
	else
		echo 'Error duplicating.';
?>