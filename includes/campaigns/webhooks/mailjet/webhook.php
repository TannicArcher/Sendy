<?php 
	mysqli_report(MYSQLI_REPORT_OFF);
	include('../../../config.php');
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
	        fail();
	    }
	    
	    global $charset; mysqli_set_charset($mysqli, isset($charset) ? $charset : "utf8");
	    
	    return $mysqli;
	}
	//--------------------------------------------------------------//
	function fail() { //Database connection fails
	//--------------------------------------------------------------//
	    print 'Database error';
	    exit;
	}
	// connect to database
	dbConnect();
?>
<?php 
	include('../../../helpers/short.php');
	
	$time = time();
	
	$data = file_get_contents("php://input");
	$events = json_decode($data, true);
	
	foreach ($events as $evt) {
		
		//Get the email in question
		$problem_email = !filter_var($evt['email'],FILTER_VALIDATE_EMAIL) ? exit : mysqli_real_escape_string($mysqli, $evt['email']);
		
		//Get list ID
		$list_id = mysqli_real_escape_string($mysqli, $evt['CustomID']);
		$list_id = decrypt_string($list_id);
		$list_id = is_numeric($list_id) ? $list_id : exit;
		
		//Get event type ('bounce', 'spam', or 'unsub')
		$event = mysqli_real_escape_string($mysqli, $evt['event']);
		$hard_bounce = $evt['hard_bounce']!='' ? mysqli_real_escape_string($mysqli, $evt['hard_bounce']) : false;
		
		//If email is hard bounced
		if($event=='bounce' && $hard_bounce)
		{
			//mark email as bounced
			$q = 'UPDATE subscribers SET bounced = 1, timestamp = '.$time.' WHERE email = "'.$problem_email.'"';
			$r = mysqli_query($mysqli, $q);
			if (!$r)
			{
				error_log("[Can't update bounced status]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
			}
		}
		//If user mark email as spam
		else if($event=='spam')
		{
			//Get app ID of this complaint email
			$q = 'SELECT lists.app FROM lists, subscribers WHERE subscribers.list = "'.$list_id.'" AND subscribers.list = lists.id';
			$r = mysqli_query($mysqli, $q);
			if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $app = $row['app'];
			
			//get comma separated lists belonging to this app
			$q = 'SELECT id FROM lists WHERE app = '.$app;
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
				$all_lists = '';
				while($row = mysqli_fetch_array($r)) $all_lists .= $row['id'].',';
				$all_lists = substr($all_lists, 0, -1);
			}
			
			//Mark as spam in ALL lists in the brand for this email
			$q = 'UPDATE subscribers SET unsubscribed = 0, bounced = 0, complaint = 1, timestamp = '.$time.' WHERE email = "'.$problem_email.'" AND list IN ('.$all_lists.')';
			$r = mysqli_query($mysqli, $q);
			if(!$r)
			{
				error_log("[Can't update complaint status]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
			}		
		}
		//If user clicked Sendgrid's unsubscribe link, call Sendy's 'unsubscribe' API
		else if($event=='unsub')
		{
			//unsubscribe
			$unsubscribe_endpoint = APP_PATH.'/unsubscribe';
			
			//POST to Sendy's 'unsubscribe' API
			$postdata = http_build_query(array('email' => $problem_email, 'list' => encrypt_val($list_id), 'boolean' => 'true'));	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $unsubscribe_endpoint);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			$data = curl_exec($ch);
			curl_close($ch);
			
			if($data != 'true')
			{
				error_log("[Can't unsubscribe - $data]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
			}
		}
	}
?>