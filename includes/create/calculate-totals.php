<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../helpers/short.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$email_list_incl = isset($_POST['include_lists']) ? mysqli_real_escape_string($mysqli, $_POST['include_lists']) : exit;	
	$email_list_excl = isset($_POST['exclude_lists']) ? mysqli_real_escape_string($mysqli, $_POST['exclude_lists']) : exit;	
	$email_list_seg_incl = isset($_POST['include_lists_seg']) ? mysqli_real_escape_string($mysqli, $_POST['include_lists_seg']) : exit;	
	$email_list_seg_excl = isset($_POST['exclude_lists_seg']) ? mysqli_real_escape_string($mysqli, $_POST['exclude_lists_seg']) : exit;	
	
	//Check input
	$input = $email_list_incl.','.$email_list_excl.','.$email_list_seg_incl.','.$email_list_seg_excl;
	$input = str_replace(',', '', $input);
	if(!is_numeric($input)) exit;
	
	if($email_list_incl==0 && $email_list_seg_incl==0) 
	{
		echo 0; 
		exit;
	}
	if(($email_list_excl != 0 || $email_list_seg_excl != 0) && ($email_list_incl==0 && $email_list_seg_incl==0)) 
	{
		echo 0; 
		exit;
	}
	
	//Include main list query
	$main_query = $email_list_incl == 0 ? '' : 'subscribers.list in ('.$email_list_incl.') ';
	
	//Include segmentation query
	$seg_query = $main_query != '' && $email_list_seg_incl != 0 ? 'OR ' : ''; 
	$seg_query .= $email_list_seg_incl == 0 ? '' : '(subscribers_seg.seg_id IN ('.$email_list_seg_incl.')) ';
	
	//Exclude list query
	$exclude_query = $email_list_excl == 0 ? '' : 'subscribers.email NOT IN (SELECT email FROM subscribers WHERE list IN ('.$email_list_excl.')) ';
	
	//Exclude segmentation query
	$exclude_seg_query = $exclude_query != '' && $email_list_seg_excl != 0 ? 'AND ' : ''; 
	$exclude_seg_query .= $email_list_seg_excl == 0 ? '' : 'subscribers.email NOT IN (SELECT subscribers.email FROM subscribers LEFT JOIN subscribers_seg ON (subscribers.id = subscribers_seg.subscriber_id) WHERE subscribers_seg.seg_id IN ('.$email_list_seg_excl.'))';
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//Remove ONLY_FULL_GROUP_BY from sql_mode
	$q = 'SET SESSION sql_mode = ""';
	$r = mysqli_query($mysqli, $q);
	if (!$r) error_log("[Unable to set sql_mode]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
	
	//Check if we should send to GDPR subscribers only
	if($email_list_incl!=0) $q = 'SELECT gdpr_only FROM apps LEFT JOIN lists ON (apps.id = lists.app) WHERE lists.id IN ('.$email_list_incl.') LIMIT 1';
	else $q = 'SELECT gdpr_only FROM apps LEFT JOIN seg ON (apps.id = seg.app) WHERE seg.id IN ('.$email_list_seg_incl.') LIMIT 1';
	$r = mysqli_query($mysqli, $q);
	if ($r) while($row = mysqli_fetch_array($r)) $gdpr_only = $row['gdpr_only'];
	$gdpr_line = $gdpr_only ? 'AND gdpr = 1 ' : '';
	
	//Get totals from lists
	$q  = 'SELECT id, list, email FROM subscribers';
	$q .= $email_list_seg_incl==0 && $email_list_seg_excl==0 ? ' ' : ' LEFT JOIN subscribers_seg ON (subscribers.id = subscribers_seg.subscriber_id) ';
	$q .= 'WHERE ('.$main_query.$seg_query.') ';
	$q .= $exclude_query != '' || $exclude_seg_query != '' ? 'AND ('.$exclude_query.$exclude_seg_query.') ' : '';
	$q .= 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0 AND subscribers.confirmed = 1 '.$gdpr_line.'
		   GROUP BY subscribers.email';
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    $total = mysqli_num_rows($r);
		$subs = $total.'|{';
		$i = 1;
		
		while($row = mysqli_fetch_array($r))
		{
			$s_id = $row['id'];
			$s_list = $row['list'];
			$s_email = $row['email'];
			$subs .= '
			"s'.$i.'": {
					"id":"'.encrypt_val($s_id).'",
					"list_id":"'.encrypt_val($s_list).'",
					"email":"'.$s_email.'"
				},';
			
			//Return 100 subscribers max
			if($i==100) break;
			else $i++;
		} 
		
		$subs = substr($subs, 0, -1);
		$subs .= '}';
		
		echo $subs;
	}
	else echo 'failed';
?>