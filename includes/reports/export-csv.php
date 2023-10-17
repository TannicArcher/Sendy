<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 

/********************************/
$userID = get_app_info('main_userID');
$campaign_id = isset($_GET['c']) && is_numeric($_GET['c']) ? mysqli_real_escape_string($mysqli, (int)$_GET['c']) : '';
$link_id = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, (int)$_GET['l']) : '';
$action = isset($_GET['a']) ? $_GET['a'] : '';
$create = isset($_GET['create']) ? true : false;
$listID = isset($_GET['list-id']) && is_numeric($_GET['list-id']) ? mysqli_real_escape_string($mysqli, (int)$_GET['list-id']) : '';
$additional_query = '';
/********************************/

//echo '$action: '.$action.'<br/>$listID: '.$listID.'<br/>$campaign_id: '.$campaign_id.'<br/>$link_id: '.$link_id;exit;

//Check if user wants to import list, if so, check if the list has a current import processing
if($create)
{
	//Get currently_processing status
	$q = 'SELECT currently_processing FROM lists WHERE id = '.$listID;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $currently_processing = $row['currently_processing'];
	
	//If '1', a CSV is currently already importing in the list, show an error
	if($currently_processing)
	{
		show_error(_('Import already in progress'), '<p>'._('This list is currently importing a CSV. Please wait until the import is completed.').'</p>', true);
		exit;
	}
}

//Get campaign subject/label and brand id
$q = 'SELECT app, title, label FROM campaigns WHERE id = '.$campaign_id;
$r = mysqli_query($mysqli, $q);
if ($r)
{
	while($row = mysqli_fetch_array($r)) 
	{
		$app = $row['app'];
		$label = $row['label'];
		$campaign_title = $label=='' ? $row['title'] : $row['label'];
	}
}

if($action == 'clicks')
{
	//file name
	$filename = 'clicked.csv';
	$additional_query = 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0';
	
	//get
	$clicks_join = '';
	$clicks_array = array();
	$clicks_unique = 0;
	
	$q = 'SELECT id, clicks FROM links WHERE campaign_id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
	    	$id = stripslashes($row['id']);
			$clicks = stripslashes($row['clicks']);
			if($clicks!='')
				$clicks_join .= $clicks.',';				
	    }  
	}
	
	$clicks_array = explode(',', $clicks_join);
	$clicks_unique = array_unique($clicks_array);
	$subscribers = substr(implode(',', $clicks_unique), 0, -1);
}
else if($action == 'opens')
{
	//file name
	$filename = 'opened.csv';
	$additional_query = 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0';
	
	$q = 'SELECT opens FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
  			$opens = $row['opens'];
  			preg_match_all('!\d+!', $opens, $matches_var);
			$opens_array_no_country = $matches_var[0];  			
  			$opens_unique = array_unique($opens_array_no_country);
	  		$subscribers = implode(',', $opens_unique);
	    }  
	}
}
else if($action == 'unopens')
{
	//file name
	$filename = 'unopened.csv';
	$additional_query = 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0 AND subscribers.confirmed = 1';
	
	$q = 'SELECT opens FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {  			
  			$opens = $row['opens'];
  			preg_match_all('!\d+!', $opens, $matches_var);
			$opens_array_no_country = $matches_var[0];    			
  			$opens_unique_ini = array_unique($opens_array_no_country);
  			$opens_unique = array();
  			foreach($opens_unique_ini as $ou2)
  			{
	  			$opens_unique[$ou2] = $ou2;
  			}
	    }  
	}
	
	//Get lists the campaign was sent to
	$q = 'SELECT to_send_lists, segs FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) 
	{
		while($row = mysqli_fetch_array($r)) 
		{
			$to_send_lists = $row['to_send_lists'];
			$segs = $row['segs'];
		}
	}
	
	$sid_not_opened = array();
	$subscribers = '';
	
	$q = 'SELECT id, email FROM subscribers WHERE list IN ('.$to_send_lists.') AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1 AND last_campaign = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$sid = $row['id'];
			$email = $row['email'];
			if(!isset($opens_unique[$sid])) $sid_not_opened[$email] = $sid;
	    }  
	}
	
	$q = 'SELECT subscribers_seg.subscriber_id as subscriber_id, subscribers.email as email FROM subscribers_seg LEFT JOIN subscribers ON (subscribers.id = subscribers_seg.subscriber_id) WHERE subscribers_seg.seg_id IN ('.$segs.') '.$additional_query;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$sid = $row['subscriber_id'];
			$email = $row['email'];
			if(!isset($opens_unique[$sid])) $sid_not_opened[$email] = $sid;
	    }  
	}	
	
    $subscribers = implode(',', $sid_not_opened);
}
else if($action == 'unsubscribes')
{
	//file name
	$filename = 'unsubscribed.csv';
	
	$q = 'SELECT id FROM subscribers WHERE last_campaign = '.$campaign_id.' AND unsubscribed = 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$unsubscribes_array = array();
	    while($row = mysqli_fetch_array($r))
	    {
  			$unsubscriber_id = $row['id'];
  			array_push($unsubscribes_array, $unsubscriber_id);
	    }  
	    
	    $subscribers = implode(',', $unsubscribes_array);
	}
}
else if($action == 'bounces')
{
	//file name
	$filename = 'bounced.csv';
	
	$q = 'SELECT id FROM subscribers WHERE last_campaign = '.$campaign_id.' AND bounced = 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$unsubscribes_array = array();
	    while($row = mysqli_fetch_array($r))
	    {
  			$unsubscriber_id = $row['id'];
  			array_push($unsubscribes_array, $unsubscriber_id);
	    }  
	    
	    $subscribers = implode(',', $unsubscribes_array);
	}
}
else if($action == 'complaints')
{
	//file name
	$filename = 'marked-as-spam.csv';
	
	$q = 'SELECT id FROM subscribers WHERE last_campaign = '.$campaign_id.' AND complaint = 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$unsubscribes_array = array();
	    while($row = mysqli_fetch_array($r))
	    {
  			$unsubscriber_id = $row['id'];
  			array_push($unsubscribes_array, $unsubscriber_id);
	    }  
	    
	    $subscribers = implode(',', $unsubscribes_array);
	}
}
else if($action == 'recipient_clicks')
{
	//file name
	$filename = 'recipients-who-clicked.csv';
	$additional_query = 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0';
	
	//get strings of click ids
	$q = 'SELECT clicks, link FROM links WHERE id = '.$link_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) 
	{	
		while($row = mysqli_fetch_array($r)) 
		{
			$subscribers = $row['clicks'];
			$the_link = $row['link'];
		}
	}
	
	//Get only unique subscriber ids
	$sid_array = explode(',', $subscribers);
	$sid_array_unique = array_unique($sid_array);
	$subscribers = implode(',', $sid_array_unique);
}
else
{
	//file name
	$filename = $action.'.csv';
	
	$q = 'SELECT opens FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
  			$opens = $row['opens'];
  			
  			$opens_array = explode(',', $opens);
  			$opens_array_country_match = array();
  			
  			foreach($opens_array as $o)
  			{
	  			$f = explode(':', $o);
	  			if(array_key_exists(1, $f)) $ff = $f[1];
	  			else $ff = '';
	  			
	  			if($ff==$action)
	  				array_push($opens_array_country_match, $f[0]);
  			}
  			
  			$opens_unique = array_unique($opens_array_country_match);
	  		$subscribers = implode(',', $opens_unique);
	    }  
	}
}

//Export
$select = 'SELECT subscribers.id, subscribers.name, subscribers.email, subscribers.join_date, subscribers.timestamp, subscribers.list, subscribers.ip, subscribers.country, subscribers.referrer, subscribers.method, subscribers.added_via, subscribers.gdpr, lists.name as list_name  
			FROM subscribers 
			LEFT JOIN lists
			ON (subscribers.list = lists.id)
			where subscribers.id IN ('.$subscribers.') '.$additional_query;
$export = mysqli_query($mysqli, $select);
if($export)
{	
	while($row = mysqli_fetch_array($export))
    {
		$subr_id = $row['id'];
		$name = '"'.$row['name'].'"';
		$email = '"'.$row['email'].'"';
		$list_name = '"'.$row['list_name'].'"';
		
		//Join date, IP, Country and Referrer
		$join_date = $row['join_date'];
		$last_activity = $row['timestamp'];
		$ip = $row['ip'];
		$signedup_country_code = $row['country'];
		$signedup_country = country_code_to_country($signedup_country_code);
		$referrer = $row['referrer'];
		
		//Opt-in method
		$optin_method = $row['method'];
		if($optin_method==1) $optin_method = 'Single opt-in';
		else if($optin_method==2) $optin_method = 'Double opt-in';
		
		//Added via
		$added_via = $row['added_via'];	
		if($added_via=='')
		{	
			if($join_date=='') $added_via = 'App interface';
			else $added_via = 'API';
		}
		else
		{
			if($added_via==1 || $join_date=='')
				$added_via = 'App interface';
			else if($added_via==2 || ($join_date!='' && $ip=='No data' && $signedup_country=='No data'))
				$added_via = 'API';
			else if($added_via==3)
				$added_via = 'Standard subscribe form';
		}
		
		//GDPR
		$gdpr = $row['gdpr'];
		$gdpr_status = $gdpr ? 'Yes' : 'No';
		
		//Format the CSV
		
		//Count number of times subscriber opened the campaign
		$add_line_1 = $add_line_2 = '';
		if($action == 'opens')
		{
			$opened_times_count = array_count_values($opens_array_no_country);
			$opened_times = $opened_times_count[$subr_id];
			$add_line_1 = '"'.$opened_times.'",';
			$add_line_2 = '"'._('Opens').'",';
		}
		//Count number of times subscriber clicked any of the links in the campaign
		else if($action == 'clicks')
		{
			$clicked_times_count = array_count_values($clicks_array);
			$clicked_times = $clicked_times_count[$subr_id];
			$add_line_1 = '"'.$clicked_times.'",';
			$add_line_2 = '"'._('Clicks').'",';
		}
		//Count number of times subscriber clicked a specific link in the campaign
		else if($action == 'recipient_clicks')
		{
			$clicked_times_count = array_count_values($sid_array);
			$clicked_times = $clicked_times_count[$subr_id];
			$add_line_1 = '"'.$clicked_times.'",';
			$add_line_2 = '"'._('Clicks').'",';
		}
		
		//Parse join_date & last activity date
		$join_date = $join_date=='' ? '' : parse_date_csv($join_date, 'long', false);
		$last_activity = $last_activity=='' ? '' : parse_date_csv($last_activity, 'long', false);
		
		//If importing CSV into a list, include only name and email data
		if($create)
			$data .= $name.','.$email.','.$row['added_via'].','.$row['method'].','.$ip.','.$signedup_country_code.','.$referrer.','.$gdpr_status."\n";
		//Otherwise include all data of subscribers
		else
			$data .= $name.','.$email.','.$list_name.',"'.$join_date.'","'.$last_activity.'",'.$add_line_1.'"'.$added_via.'","'.$optin_method.'","'.$ip.'","'.$signedup_country.'","'.$signedup_country_code.'","'.$referrer.'","'.$gdpr_status.'"'."\n";
    } 
    
    $data = substr($data, 0, -1);
    
    //If importing CSV into a list, include only name and email data
    if($create)
    	$first_line = '"'._('Name').'","'._('Email').'","'._('Added via').'","'._('Opt-in method').'","'._('IP address').'","'._('Country code').'","'._('Signed up from').'","'._('GDPR').'"'."\n";
    else
    //Otherwise include all data of subscribers
	    $first_line = '"'._('Name').'","'._('Email').'","'._('List').'","'._('Joined').'","'._('Last activity').'",'.$add_line_2.'"'._('Added via').'","'._('Opt-in method').'","'._('IP address').'","'._('Country').'","'._('Country code').'","'._('Signed up from').'","'._('GDPR').'"'."\n";
    
    $data = $first_line.str_replace("\r" , "" , $data);
    
	if($data == "") $data = "\n(0) Records Found!\n";
	
	//Create CSV and import into list
	if($create)
	{
		//Create /csvs/ directory in /uploads/ if it doesn't exist
		if(!file_exists("../../uploads/csvs")) 
		{
			//Create /csvs/ directory
			if(!mkdir("../../uploads/csvs", 0777))
			{
				show_error(_('Unable to create \'/csvs/\' folder in \'/uploads/\' folder'), '<p>'._('Please ensure that the \'/uploads/\' directory is chmod to \'0777\'.').'</p>', true);
				exit;
			}
			else
			{
				//chmod uploaded file
				chmod("../../uploads/csvs",0777);
			}
		}
		
		//Create CSV file in /uploads/csvs/ folder
		$filename2 = '../../uploads/csvs/'.$userID.'-'.$listID.'.csv';
		$csvfile = fopen ($filename2, "w");
		fputs($csvfile, $data);
		fclose($csvfile);
		
		//chmod uploaded file
		chmod("../../uploads/csvs/".$userID.'-'.$listID.'.csv',0777);
		
		//Update total_records
		$linecount = count(file("../../uploads/csvs/".$userID.'-'.$listID.'.csv'));
		$q = 'UPDATE lists SET total_records = '.$linecount.' WHERE id = '.$listID;
		mysqli_query($mysqli, $q);
		
		//Run import-csv.php script to start CSV import
		file_get_contents_curl(get_app_info('path').'/import-csv.php', 'from_report');
				
		//CSV uploaded successfully, redirect back to list
		header("Location: ".get_app_info('path')."/subscribers?i=".$app."&l=$listID");
	}
	//Export CSV
	else
	{
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Pragma: no-cache");
		header("Expires: 0");
		print "$data";
	}
}
else show_error(_('Can\'t export CSV'), '<p>'._('There is either nothing to export, or the number of records may be too large. If it\'s the latter, try increasing MySQL\'s max_allowed_packet.').'</p>', true);
?>