<?php 
	ini_set('display_errors', 0);
	mysqli_report(MYSQLI_REPORT_OFF);
	include('includes/config.php');
	include('includes/helpers/locale.php');
	include('includes/helpers/integrations/zapier/triggers/functions.php');
	include('includes/helpers/integrations/rules.php');
	include('includes/helpers/subscription.php');
	
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
	include('includes/helpers/short.php');
	include_once('includes/helpers/PHPMailerAutoload.php');
	
	//Decrypt string
	$email_id = decrypt_int($_GET['e']);
	$list_id = decrypt_int($_GET['l']);
	
	$time = time();
	$join_date = round($time/60)*60;
	
	//Set language
	$q = 'SELECT login.language FROM lists, login WHERE lists.id = '.$list_id.' AND login.app = lists.app';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $language = $row['language'];
	set_locale($language);
	
	//get thank you message etc
	$q2 = 'SELECT app, name, userID, thankyou, thankyou_subject, thankyou_message, confirm_url, custom_fields FROM lists WHERE id = '.$list_id;
	$r2 = mysqli_query($mysqli, $q2);
	if ($r2)
	{
		while($row = mysqli_fetch_array($r2))
		{
			$userID = $row['userID'];
			$app = $row['app'];
			$list_name = $row['name'];
			$thankyou = $row['thankyou'];
			$thankyou_subject = stripslashes($row['thankyou_subject']);
			$thankyou_message = stripslashes($row['thankyou_message']);
			$confirm_url = stripslashes($row['confirm_url']);
			$custom_fields = $row['custom_fields'];
		}  
	}
	//get email address of subscribing user
	$q3 = 'SELECT name, email, custom_fields FROM subscribers WHERE id = '.$email_id;
	$r3 = mysqli_query($mysqli, $q3);
	if ($r3)
	{
		while($row = mysqli_fetch_array($r3))
		{
			$name = $row['name'];
			$email = $row['email'];
			$custom_values = $row['custom_fields'];
		}  
	}
	//get smtp credentials and other data
	$q4 = 'SELECT from_name, from_email, reply_to, smtp_host, smtp_port, smtp_ssl, smtp_username, smtp_password, allocated_quota, custom_domain, custom_domain_protocol, custom_domain_enabled FROM apps WHERE id = '.$app;
	$r4 = mysqli_query($mysqli, $q4);
	if ($r4)
	{
		while($row = mysqli_fetch_array($r4))
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
	//get AWS creds
	$q = 'SELECT s3_key, s3_secret FROM login WHERE id = '.$userID;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		while($row = mysqli_fetch_array($r))
		{
			$s3_key = $row['s3_key'];
			$s3_secret = $row['s3_secret'];
		}
	}
	
	$q = 'UPDATE subscribers SET confirmed = 1, timestamp = "'.$time.'", join_date = CASE WHEN join_date IS NULL THEN '.$join_date.' ELSE join_date END WHERE id = '.$email_id.' AND list = '.$list_id.' AND confirmed = 0';
	$r = mysqli_query($mysqli, $q);
	if ($r && $mysqli->affected_rows==1)
	{		
		//Zapier Trigger 'new_user_subscribed' event
		zapier_trigger_new_user_subscribed($name, $email, $list_id);
		
		//Run rules
		$rules_data = array(
		    'trigger' => 'subscribe',
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
		
		//Update segments
		update_segments($app_path, $list_id);
		
		if($thankyou)
		{		
			//Convert personaliztion tags
			convert_tags($thankyou_subject, $email_id, 'thankyou', 'subject');
			convert_tags($thankyou_message, $email_id, 'thankyou', 'message');
			
			//Convert name tag
			$thankyou_message = str_replace('[Name]', $name, $thankyou_message);
			$thankyou_subject = str_replace('[Name]', $name, $thankyou_subject);
			
			//Convert email tag
			$thankyou_message = str_replace('[Email]', $email, $thankyou_message);
			$thankyou_subject = str_replace('[Email]', $email, $thankyou_subject);
			
			//Unsubscribe tag
			if($smtp_host == 'smtp.elasticemail.com' && ($s3_key=='' && $s3_secret==''))
			{
				$thankyou_message = str_replace('<unsubscribe', '<a href="{unsubscribe}" ', $thankyou_message);
				$thankyou_message = str_replace('</unsubscribe>', '</a>', $thankyou_message);
				$thankyou_message = str_replace('[unsubscribe]', '{unsubscribe}', $thankyou_message);
			}
			else
			{
				$thankyou_message = str_replace('<unsubscribe', '<a href="'.$app_path.'/unsubscribe/'.encrypt_val($email).'/'.encrypt_val($list_id).'" ', $thankyou_message);
				$thankyou_message = str_replace('</unsubscribe>', '</a>', $thankyou_message);
				$thankyou_message = str_replace('[unsubscribe]', $app_path.'/unsubscribe/'.encrypt_val($email).'/'.encrypt_val($list_id), $thankyou_message);
			}
			
			//Send thankyou email
			send_email($thankyou_subject, $thankyou_message, $email, $name, '', '', encrypt_val($list_id));
			
			//Update quota if a monthly limit was set
			if($allocated_quota!=-1)
			{
				//if so, update quota
				$q4 = 'UPDATE apps SET current_quota = current_quota+1 WHERE id = '.$app;
				mysqli_query($mysqli, $q4);
			}
		}
	}
	
	//if user sets a redirection URL
	if($confirm_url != ''):
		$confirm_url = str_replace('%n', urlencode($name), $confirm_url);
		$confirm_url = str_replace('%e', urlencode($email), $confirm_url);
		$confirm_url = str_replace('%l', encrypt_val($list_id), $confirm_url);
		header("Location: ".$confirm_url);
	else:
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<link rel="Shortcut Icon" type="image/ico" href="<?php echo $app_path;?>/img/favicon.png">
		<title><?php echo _('You\'re subscribed!');?></title>
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
			<h2><?php echo _('You\'re subscribed!');?></h2>
			<p><img src="<?php echo $app_path;?>/img/email-notifications/subscribed.gif" height="150" /></p></p>
		</div>
	</body>
</html>
<?php endif;?>