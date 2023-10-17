<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	$tid = isset($_GET['t']) && is_numeric($_GET['t']) ? mysqli_real_escape_string($mysqli, $_GET['t']) : exit;
	$aid = isset($_GET['i']) && is_numeric($_GET['i']) ? get_app_info('app') : exit;
	$ares_id = isset($_GET['a']) && is_numeric($_GET['a']) ? mysqli_real_escape_string($mysqli, $_GET['a']) : 0;
	$ares_type = isset($_GET['a_type']) && is_numeric($_GET['a_type']) ? mysqli_real_escape_string($mysqli, $_GET['a_type']) : 0;
	$time_condition = $ares_type==1 ? 'immediately' : '';
	$from_creation_page = isset($_GET['from_creation_page']) && is_numeric($_GET['from_creation_page']) ? mysqli_real_escape_string($mysqli, $_GET['from_creation_page']) : 0;
	if(isset($ares_id))
	{
		if($from_creation_page)
		{
			$in_list_seg = isset($_GET['in_list_seg']) ? mysqli_real_escape_string($mysqli, $_GET['in_list_seg']) : '';
			$ex_list_seg = isset($_GET['ex_list_seg']) ? mysqli_real_escape_string($mysqli, $_GET['ex_list_seg']) : '';
			$segmentation_line1 = ', segs, segs_excl';
			$segmentation_line2 = ', "'.$in_list_seg.'", "'.$ex_list_seg.'"';
		}
	}
	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/templates?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
		$q = 'SELECT app FROM template WHERE id = '.$tid;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$a = $row['app'];
		    }  
		    if($a!=get_app_info('restricted_to_app'))
		    {
			    echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/templates?i='.get_app_info('restricted_to_app').'"</script>';
				exit;
		    }
		}
	}
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//Get brand info
	$q = 'SELECT app_name, from_name, from_email, reply_to, query_string, opens_tracking, links_tracking FROM apps WHERE id = '.$aid;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$app_name = $row['app_name'];
			$from_name = $row['from_name'];
			$from_email = $row['from_email'];
			$reply_to = $row['reply_to'];
			$query_string = $row['query_string'];
			$opens_tracking = $row['opens_tracking'];
			$links_tracking = $row['links_tracking'];
	    }  
	}
	
	//Get template
	$q = 'SELECT template_name, html_text, plain_text FROM template WHERE id = '.$tid;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$template_name = $row['template_name'];
			$html_text = stripslashes($row['html_text']);
			$plain_text = stripslashes($row['plain_text']);
	    }  
	}
	
	//If using template for Campaigns
	if(!$ares_id) //Create new campaign with template
		$q = 'INSERT INTO campaigns (userID, app, from_name, from_email, reply_to, query_string, title, html_text, plain_text, wysiwyg, opens_tracking, links_tracking) VALUES ('.get_app_info('main_userID').', '.$aid.', "'.$from_name.'", "'.$from_email.'", "'.$reply_to.'", "'.$query_string.'", "'.$template_name.'", "'.addslashes($html_text).'", "'.addslashes($plain_text).'", 1, '.$opens_tracking.', '.$links_tracking.')';
	else
		$q = 'INSERT INTO ares_emails (ares_id, from_name, from_email, reply_to, query_string, title, html_text, plain_text, wysiwyg, created, time_condition '.$segmentation_line1.', opens_tracking, links_tracking) VALUES ('.$ares_id.', "'.$from_name.'", "'.$from_email.'", "'.$reply_to.'", "'.$query_string.'", "'.$template_name.'", "'.addslashes($html_text).'", "'.addslashes($plain_text).'", 1, '.time().', "'.$time_condition.'" '.$segmentation_line2.', '.$opens_tracking.', '.$links_tracking.')';
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    $inserted_id = mysqli_insert_id($mysqli);
	    
	    //Redirect to editing page
	    
	    if(!$ares_id)
		    header("Location: ".get_app_info('path')."/edit?i=".get_app_info('app')."&c=$inserted_id");
		else
		{
			if($from_creation_page)	
				echo get_app_info('path').'/autoresponders-edit?i='.get_app_info('app').'&a='.$ares_id.'&ae='.$inserted_id;
			else
				header("Location: ".get_app_info('path')."/autoresponders-edit?i=".get_app_info('app')."&a=$ares_id&ae=$inserted_id");
		}
	}
	else 
		show_error(_('Failed to create with template'), '<p>'.mysqli_error($mysqli).'</p>', true);
?>

