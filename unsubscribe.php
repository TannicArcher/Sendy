<?php 
	ini_set('display_errors', 0);
	mysqli_report(MYSQLI_REPORT_OFF);
	include('includes/config.php');
	include('includes/helpers/locale.php');
	include('includes/helpers/integrations/zapier/triggers/functions.php');
	include('includes/helpers/integrations/rules.php');
	include('includes/helpers/subscription.php');
	include('includes/helpers/EmailAddressValidator.php');
	
	//--------------------------------------------------------------//
	function dbConnect() { //Connect to database
	//--------------------------------------------------------------//
	    // Access global variables
	    global $mysqli;
	    global $dbHost;
	    global $dbUser;
	    global $dbPass;
	    global $dbName;
	    global $dbPort;
	    
	    // Attempt to connect to database server
	    if(isset($dbPort)) $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
	    else $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
	
	    // If connection failed...
	    if ($mysqli->connect_error) {
	        fail("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"/><link rel=\"Shortcut Icon\" type=\"image/ico\" href=\"/img/favicon.png\"><title>"._('Can\'t connect to database')."</title></head><style type=\"text/css\">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #f2f2f2;width: 300px;height: 110px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;line-height: 18px;font-size: 12px;padding: 0 30px;}h2{font-weight: normal;text-align: center;font-size: 20px;}a{color: #000;}a:hover{text-decoration: none;}</style><body><div id=\"wrapper\"><p><h2>"._('Can\'t connect to database')."</h2></p><p>"._('There is a problem connecting to the database. Please try again later.')."</p></div></body></html>");
	    }
	    
	    global $charset; mysqli_set_charset($mysqli, isset($charset) ? $charset : "utf8");
	    
	    return $mysqli;
	}
	//--------------------------------------------------------------//
	function fail($errorMsg) { //Database connection fails
	//--------------------------------------------------------------//
	    echo $errorMsg;
	    exit;
	}
	// connect to database
	dbConnect();
?>
<?php
	include_once('includes/helpers/short.php');
	include_once('includes/helpers/PHPMailerAutoload.php');
	
	//vars
	$time = time();	
	$feedback = '';
	
	//get variable
	if(isset($_GET['i']))
	{
		$i_array = array();
		$i_array = explode('/', $_GET['i']);
		//new campaign encrypted string
		if(count($i_array)==1)
		{
			$i_array = array();
			$i_array = explode('/', decrypt_string($_GET['i']));
			$email = mysqli_real_escape_string($mysqli, encrypt_val($i_array[0]));
			$list_id = is_numeric($i_array[1]) ? $i_array[1] : exit;
			$campaign_id = is_numeric($i_array[2]) ? $i_array[2] : exit;
		}
		//new AR encrypted string
		else if($i_array[1]=='a')
		{
			$i_array = array();
			$i_array1 = array();
			$i_array1 = explode('/', $_GET['i']);
			$i_array2 = $i_array1[0];
			$i_array = explode('/', decrypt_string($i_array2));
			$email = mysqli_real_escape_string($mysqli, encrypt_val(trim($i_array[0])));
			$list_id = is_numeric($i_array[1]) ? $i_array[1] : exit;
			$campaign_id = is_numeric($i_array[2]) ? $i_array[2] : exit;
			$i_array[3] = 'a';
		}
		//old encrypted string
		else
		{
			$email = mysqli_real_escape_string($mysqli, trim($i_array[0]));
			$email = str_replace(" ", "+", $email);
	        $email = str_replace("%20", "+", $email);
			$list_id = decrypt_int($i_array[1]);
			$return_boolean = $i_array[2];
			$campaign_id = $return_boolean!='' ? decrypt_int($return_boolean) : '';
		}
		
		//Set language
		$q = 'SELECT login.language FROM lists, login WHERE lists.id = '.$list_id.' AND login.app = lists.app';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $language = $row['language'];
		set_locale($language);
		
		//check if email is passed in as an email address instead of encrypted string
		$validator = new EmailAddressValidator;
		if ($validator->check_email_address($email)) 
		{
			if($return_boolean=='true')
			{
				echo 'Invalid email address.';
				exit;
			}
			else $feedback = _('Email address is invalid.');
		}
		else
		{
			$email = decrypt_string($email, true);
			
			//check if email is valid
			$validator = new EmailAddressValidator;
			if ($validator->check_email_address($email)) {}
			else
			{
				if($return_boolean=='true')
				{
					echo 'Invalid email address.';
					exit;
				}
				else $feedback = _('Email address is invalid.');
			}
		}
	}
	else if(isset($_POST['email']))
	{
		//parameters
		$email = trim(mysqli_real_escape_string($mysqli, $_POST['email'])); //compulsory
		$list_id = decrypt_int($_POST['list']); //compulsory
		$return_boolean = mysqli_real_escape_string($mysqli, $_POST['boolean']); //compulsory
		
		//Set language
		$q = 'SELECT login.language FROM lists, login WHERE lists.id = '.$list_id.' AND login.app = lists.app';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $language = $row['language'];
		set_locale($language);
		
		//check if no data passed
		if($email=='' || $list_id=='')
		{
			if($return_boolean=='true')
			{
				echo 'Some fields are missing.';
				exit;
			}
			else
				$feedback = _('Some fields are missing.');
		}
		else
		{
			//check if email is valid
			$validator = new EmailAddressValidator;
			if ($validator->check_email_address($email)) {}
			else
			{
				if($return_boolean=='true')
				{
					echo 'Invalid email address.';
					exit;
				}
				else
				    $feedback = _('Email address is invalid.');
			}
		}
	}
	else if($_GET['i']=='')
	{
		exit;
	}
	
	//get AWS creds
	$q = 'SELECT s3_key, s3_secret FROM login ORDER BY id ASC LIMIT 1;';
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		while($row = mysqli_fetch_array($r))
		{
			$s3_key = $row['s3_key'];
			$s3_secret = $row['s3_secret'];
		}
	}
	
	//Check if email exists in the list
	$q = 'SELECT id FROM subscribers WHERE email = "'.$email.'" AND list = '.$list_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) == 0)
	{
		if($return_boolean=='true')
		{
			echo 'Email does not exist.';
			exit;
		}
		else $feedback = _('Email does not exist.');
	}
	
	//Get app id of this subscriber
	$q = 'SELECT app, name, custom_fields FROM lists WHERE id = '.$list_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) 
	{
		while($row = mysqli_fetch_array($r)) 
		{
			$app = $row['app'];
			$list_name = $row['name'];
			$custom_fields = $row['custom_fields'];
		}
	}
	
	//Check if user set "double opt-out" in the list settings
	$q = 'SELECT unsubscribe_confirm FROM lists WHERE id = '.$list_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) while($row = mysqli_fetch_array($r)) $unsubscribe_confirm = $row['unsubscribe_confirm'];
	
	//If user wants "double opt-out"	, ask recipient to confirm unsubscription
	if($unsubscribe_confirm && $return_boolean!='true')
		$feedback = !isset($_GET['confirm']) ? _('Confirm unsubscribe?') : '';
		
	//get from name and from email
	$q3 = 'SELECT from_name, from_email, reply_to, smtp_host, smtp_port, smtp_ssl, smtp_username, smtp_password, allocated_quota, custom_domain, custom_domain_protocol, custom_domain_enabled FROM apps WHERE id = '.$app;
	$r3 = mysqli_query($mysqli, $q3);
	if ($r3)
	{
	    while($row = mysqli_fetch_array($r3))
	    {
			$from_name = $row['from_name'];
			$from_email = $row['from_email'];
			$reply_to = $row['reply_to'];
			$smtp_host = $row['smtp_host'];
			$smtp_port = $row['smtp_port'];
			$smtp_ssl = $row['smtp_ssl'];
			$smtp_username = $row['smtp_username'];
			$smtp_password = $row['smtp_password'];
			$allocated_quota = $row['allocated_quota'];
			$custom_domain = $row['custom_domain'];
			$custom_domain_protocol = $row['custom_domain_protocol'];
			$custom_domain_enabled = $row['custom_domain_enabled'];
			if($custom_domain!='' && $custom_domain_enabled)
			{
				$parse = parse_url(APP_PATH);
				$domain = $parse['host'];
				$protocol = $parse['scheme'];
				$app_path = str_replace($domain, $custom_domain, APP_PATH);
				$app_path = str_replace($protocol, $custom_domain_protocol, $app_path);
			}
			else $app_path = APP_PATH;
	    }  
	}
	
	if($feedback!=_('Some fields are missing.') 
	&& $feedback!=_('Email address is invalid.') 
	&& $feedback!=_('Email does not exist.')
	&& $feedback!=_('Confirm unsubscribe?'))
	{
		//check if unsubscribe_all_list
		$q = 'SELECT userID, unsubscribe_all_list, unsubscribed_url, goodbye, goodbye_subject, goodbye_message FROM lists WHERE id = '.$list_id;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$userID = $row['userID'];
				$unsubscribe_all_list = $row['unsubscribe_all_list'];
				$unsubscribed_url = $row['unsubscribed_url'];
				$goodbye = $row['goodbye'];
				$goodbye_subject = stripslashes($row['goodbye_subject']);
				$goodbye_message = stripslashes($row['goodbye_message']);
		    }
		}
		
		//get comma separated lists belonging to this app
		$q = 'SELECT id FROM lists WHERE app = '.$app;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			$all_lists = '';
		    while($row = mysqli_fetch_array($r)) $all_lists .= $row['id'].',';
		    $all_lists = substr($all_lists, 0, -1);
		}
		
		if(empty($campaign_id) || $return_boolean=='true')
		{
			if($unsubscribe_all_list) //if user wants to unsubscribe email from ALL lists
				$q = 'UPDATE subscribers SET unsubscribed = 1, timestamp = '.$time.' WHERE email = "'.$email.'" AND list IN ('.$all_lists.')';
			else
				$q = 'UPDATE subscribers SET unsubscribed = 1, timestamp = '.$time.' WHERE email = "'.$email.'" AND list = '.$list_id;
		}
		else
		{
			if($unsubscribe_all_list) //if user wants to unsubscribe email from ALL lists
			{
				//unsubscribe email from all lists
				$q = 'UPDATE subscribers SET unsubscribed = 1, timestamp = '.$time.' WHERE email = "'.$email.'" AND list IN ('.$all_lists.')'; 
				
				//then update last_campaign for only the list user unsubscribed from (so that report will show unsubscribed number correctly)
				//if this is an autoresponder unsubscribe,
				if(count($i_array)==4 && $i_array[3]=='a')
					mysqli_query($mysqli, 'UPDATE subscribers SET last_ares = '.$campaign_id.' WHERE email = "'.$email.'" AND list = '.$list_id); 
				else
					mysqli_query($mysqli, 'UPDATE subscribers SET last_campaign = '.$campaign_id.' WHERE email = "'.$email.'" AND list = '.$list_id); 
			}
			else
			{
				//if this is an autoresponder unsubscribe,
				if(count($i_array)==4 && $i_array[3]=='a')
					$q = 'UPDATE subscribers SET unsubscribed = 1, timestamp = '.$time.', last_ares = '.$campaign_id.' WHERE email = "'.$email.'" AND list = '.$list_id;
				else
					$q = 'UPDATE subscribers SET unsubscribed = 1, timestamp = '.$time.', last_campaign = '.$campaign_id.' WHERE email = "'.$email.'" AND list = '.$list_id;
			}
		}
		$r = mysqli_query($mysqli, $q);
		if ($r){
			$feedback = _('You\'re unsubscribed.');
			
			//Retrieve subscriber's name
			$q = 'SELECT id, name, custom_fields FROM subscribers WHERE email = "'.$email.'" AND list = "'.$list_id.'"';
			$r = mysqli_query($mysqli, $q);
			if ($r && mysqli_num_rows($r) > 0) //if a record exists, then trigger Zapier below
			{
				while($row = mysqli_fetch_array($r)) 
				{
					$name = $row['name'];
					$email_id = $row['id'];
					$custom_values = $row['custom_fields'];
				}
				
				//Zapier Trigger 'new_user_unsubscribed' event
				zapier_trigger_new_user_unsubscribed($name, $email, $list_id);
				
				//Run rules
				$rules_data = array(
				    'trigger' => 'unsubscribe',
				    'name' => $name,
				    'email' => $email,
				    'list_id' => encrypt_val($list_id),
				    'list_name' => $list_name,
				    'list_url' => $app_path.'/subscribers?i='.$app.'&l='.$list_id,
				    'gravatar' => get_gravatar($email, 88)
			    );
				
				//Populate custom fields (if available)
				if($custom_fields!='')
				{
					$custom_field_lines = '';
					$custom_fields_array = explode('%s%', $custom_fields);
					$custom_fields_values_array = explode('%s%', $custom_values);
					for($c=0;$c<count($custom_fields_array);$c++)
					{
						$fields_array = explode(':', $custom_fields_array[$c]);
						$values_array = $fields_array[1]=='Date' ? date("M d, Y", (int)$custom_fields_values_array[$c]) : $custom_fields_values_array[$c];
						$rules_data[$fields_array[0]] = $values_array;
					}
				}
			    
			    //Run rules
				run_rule($rules_data);
			}
		}
		
		if($goodbye)
		{
			//Convert personaliztion tags
			convert_tags($goodbye_subject, $email_id, 'goodbye', 'subject');
			convert_tags($goodbye_message, $email_id, 'goodbye', 'message');
			
			//Convert name tag
			$goodbye_message = str_replace('[Name]', $name, $goodbye_message);
			$goodbye_subject = str_replace('[Name]', $name, $goodbye_subject);
			
			//Convert email tag
			$goodbye_message = str_replace('[Email]', $email, $goodbye_message);
			$goodbye_subject = str_replace('[Email]', $email, $goodbye_subject);
			
			//Resubscribe tag
			$goodbye_message = str_replace('<resubscribe', '<a href="'.$app_path.'/subscribe/'.encrypt_val($email).'/'.encrypt_val($list_id).'" ', $goodbye_message);
	    	$goodbye_message = str_replace('</resubscribe>', '</a>', $goodbye_message);
			$goodbye_message = str_replace('[resubscribe]', $app_path.'/subscribe/'.encrypt_val($email).'/'.encrypt_val($list_id), $goodbye_message);
			
			//Send goodbye email
			send_email($goodbye_subject, $goodbye_message, $email, $name, '', '', encrypt_val($list_id));
		}
	}
	
if($return_boolean=='true'):
	echo true;
else:
	//if user sets a redirection URL
	if($unsubscribed_url != ''):
		$unsubscribed_url = str_replace('%e', urlencode($email), $unsubscribed_url);
		$unsubscribed_url = str_replace('%l', encrypt_val($list_id), $unsubscribed_url);
		$unsubscribed_url = str_replace('%s', $app_path.'/subscribe/'.encrypt_val($email).'/'.encrypt_val($list_id), $unsubscribed_url);
		header("Location: ".$unsubscribed_url);
	else:
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<link rel="Shortcut Icon" type="image/ico" href="<?php echo $app_path;?>/img/favicon.png">
		<title><?php echo $feedback==_('Confirm unsubscribe?') ? $feedback : _('Unsubscribed');?></title>
	</head>
	<style type="text/css">
		body{
			background: #f7f9fc;
			font-family: Helvetica, Arial;
		}
		#wrapper 
		{
			background: #ffffff;
			-webkit-box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			-moz-box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			
			width: 300px;
			padding-bottom: 10px;
			
			margin: -180px 0 0 -150px;
			position: absolute;
			top: 50%;
			left: 50%;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
		}
		p{
			text-align: center;
		}
		h2{
			font-weight: normal;
			text-align: center;
		}
		a{
			color: #000;
			text-decoration: none;
		}
		a:hover{
			text-decoration: underline;
		}
		#top-pattern{
			margin-top: -8px;
			height: 8px;
			background: url("<?php echo $app_path; ?>/img/top-pattern2.gif") repeat-x 0 0;
			background-size: auto 8px;
		}
	</style>
	<body>
		<div id="top-pattern"></div>
		<div id="wrapper">
			<h2><?php echo $feedback;?></h2>
			<?php echo $feedback==_('You\'re unsubscribed.') ? '<p><img src="'.$app_path.'/img/email-notifications/unsubscribed.gif" height="150" /></p>' : '';?>
			<?php if($feedback!=_('Email address is invalid.') && $feedback!=_('Email does not exist.')):?>
				<?php if($feedback==_('Confirm unsubscribe?')):?>
					<?php if($campaign_id==''):?>
						<p><a href="<?php echo $app_path; ?>/unsubscribe/<?php echo encrypt_val($email);?>/<?php echo encrypt_val($list_id);?>&confirm" title=""><?php echo _('Yes. Unsubscribe me.');?></a></p>
					<?php else:?>
						<p><a href="<?php echo $app_path; ?>/unsubscribe/<?php echo encrypt_val($email);?>/<?php echo encrypt_val($list_id);?>/<?php echo encrypt_val($campaign_id);?>&confirm" title=""><?php echo _('Yes. Unsubscribe me.');?></a></p>
					<?php endif;?>
				<?php else:?>
					<p><a href="<?php echo $app_path; ?>/subscribe/<?php echo encrypt_val($email);?>/<?php echo encrypt_val($list_id);?>" title=""><?php echo _('Re-subscribe?');?></a></p>
				<?php endif;?>
			<?php endif;?>
		</div>
	</body>
</html>
<?php endif;?>
<?php endif;?>