<?php

 

ini_set('display_errors', isset($_GET['display_errors']) ? 1 : 0); 

session_start();

include('config.php');
include('helpers/locale.php');
include('helpers/ses.php');
include('helpers/EmailAddressValidator.php');
include('helpers/random_compat/lib/random.php');

//Define current version
if(!defined('CURRENT_VERSION')) define('CURRENT_VERSION', '6.0.2');
if(!defined('CURRENT_DOMAIN')) define('CURRENT_DOMAIN', $_SERVER['HTTP_HOST']);
//Get domain of APP_PATH
$parse = parse_url(APP_PATH);
if(!defined('APP_PATH_DOMAIN')) define('APP_PATH_DOMAIN', $parse['host']);

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
        fail("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"/><link rel=\"Shortcut Icon\" type=\"image/ico\" href=\"/img/favicon.png\"><title>"._('Can\'t connect to database')."</title></head><style type=\"text/css\">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #f2f2f2;width: 300px;height: 130px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;line-height: 18px;font-size: 12px;padding: 0 30px;}h2{font-weight: normal;text-align: center;font-size: 20px;}a{color: #000;}a:hover{text-decoration: none;}</style><body><div id=\"wrapper\"><p><h2>"._('Can\'t connect to database')."</h2></p><p>"._('There is a problem connecting to the database. Please try again later or see this <a href="https://sendy.co/troubleshooting#cannot-connect-to-database" target="_blank">troubleshooting tip</a>.')."</p></div></body></html>");
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

//if database has no tables, redirect to install
$q = "SELECT COUNT(*)
FROM information_schema.tables WHERE table_schema = '$dbName' 
AND (table_name = 'apps' OR table_name = 'campaigns' OR table_name = 'links' OR table_name = 'lists' OR table_name = 'login' OR table_name = 'subscribers')";
$r = mysqli_query($mysqli, $q);
if ($r)
{
	while($row = mysqli_fetch_array($r))
    {
		$table_count = $row['COUNT(*)'];
		
		if($table_count!=6)
		{	
			if(currentPage()!='_install.php')
			{
				if(get_app_info('path')=='http://your_sendy_installation_url') 
				{
					fail("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"/><link rel=\"Shortcut Icon\" type=\"image/ico\" href=\"/img/favicon.png\"><title>"._('APP_PATH not set')."</title></head><style type=\"text/css\">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #f2f2f2;width: 300px;height: 130px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;line-height: 18px;font-size: 12px;padding: 0 30px;}h2{font-weight: normal;text-align: center;font-size: 20px;}a{color: #000;}a:hover{text-decoration: none;}</style><body><div id=\"wrapper\"><p><h2>"._('APP_PATH not set')."</h2></p><p>"._('Please set your APP_PATH in /includes/config.php to your Sendy installation URL.')."</p></div></body></html>");
				}
				else header("Location: ".get_app_info('path').'/_install.php'); 
				exit;
			}
		}
    } 
}
include('update.php');

$_SESSION['company'] = '';
$_SESSION['is_sub_user'] = '';

//==================================================================================//
//										FUNCTIONS									//
//==================================================================================//

//--------------------------------------------------------------//
function unlog_session() //destroy all session data
//--------------------------------------------------------------//
{
	session_destroy();
	
	if(setcookie('logged_in', "", time()-60000, '/', COOKIE_DOMAIN))
	    return true;
}

//--------------------------------------------------------------//
function currentPage()
//--------------------------------------------------------------//
{
	$currentFile = $_SERVER["PHP_SELF"];
	$parts = Explode('/', $currentFile);
    return $parts[count($parts) - 1];
}

//--------------------------------------------------------------//
function ipaddress()
//--------------------------------------------------------------//
{
	global $mysqli;
	
	//get user's ip address
	if (getenv("HTTP_CLIENT_IP")) {
		$ip = getenv("HTTP_CLIENT_IP");
	} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	} else {
		$ip = getenv("REMOTE_ADDR");
	}
	return mysqli_real_escape_string($mysqli, $ip);
}

//--------------------------------------------------------------//
function ran_string($minlength, $maxlength, $useupper, $usespecial, $usenumbers)
//--------------------------------------------------------------//
{
	$key = '';
	$charset = "abcdefghijklmnopqrstuvwxyz";
	if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	if ($usenumbers) $charset .= "0123456789";
	if ($usespecial) $charset .= "~@#$%^*()_+-={}|][";
	if ($minlength > $maxlength) $length = random_int($maxlength, $minlength);
	else $length = random_int($minlength, $maxlength);
	for ($i=0; $i<$length; $i++) $key .= $charset[(random_int(0,(strlen($charset)-1)))];
	return $key;
}

//--------------------------------------------------------------//
function start_app()
//--------------------------------------------------------------//
{	
	global $mysqli;
	
	$q = 'SELECT * FROM login WHERE id = '.$_SESSION['userID'];
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$_SESSION['name'] = stripslashes($row['name']);
			$_SESSION['company'] = stripslashes($row['company']);
			$_SESSION['email'] = stripslashes($row['username']);
			$_SESSION['password'] = stripslashes($row['password']);
			$_SESSION['s3_key'] = stripslashes($row['s3_key']);
			$_SESSION['s3_secret'] = stripslashes($row['s3_secret']);
			$_SESSION['license'] = 'L12345678';
			$_SESSION['tied_to'] = stripslashes($row['tied_to']);
			$_SESSION['restricted_to_app'] = stripslashes($row['app']);
			$_SESSION['timezone'] = stripslashes($row['timezone']);
			$_SESSION['language'] = stripslashes($row['language']);
			$_SESSION['cron'] = stripslashes($row['cron']);
			$_SESSION['send_rate'] = stripslashes($row['send_rate']);
			$_SESSION['ses_endpoint'] = stripslashes($row['ses_endpoint']);
			$_SESSION['auth_salt'] = stripslashes($row['auth_salt']);
			$_SESSION['brands_rows'] = stripslashes($row['brands_rows']);
			$_SESSION['strict_delete'] = stripslashes($row['strict_delete']);
			$_SESSION['dark_mode'] = stripslashes($row['dark_mode']);
			$_SESSION['L12345678'] = hash('sha512', 'L12345678' . 'ttcwjc8Q4N4J7MS7/hTCrRSm9Uv7h3GS');
		
			//set user's timezone
			if($_SESSION['timezone']=='') $_SESSION['timezone'] = date_default_timezone_get();
			date_default_timezone_set($_SESSION['timezone']);
			
			//set language
			if($_SESSION['language'] != 'en_US')
				set_locale($_SESSION['language']);
			
			//check if sub user
			if($_SESSION['tied_to']!='')
			{
				$q = 'SELECT app, s3_key, s3_secret, license, ses_endpoint, strict_delete, auth_salt FROM login WHERE id = '.$_SESSION['tied_to'];
				$r = mysqli_query($mysqli, $q);
				if ($r && mysqli_num_rows($r) > 0)
				{
					while($row = mysqli_fetch_array($r))
				    {
						$_SESSION['s3_key'] = stripslashes($row['s3_key']);
						$_SESSION['s3_secret'] = stripslashes($row['s3_secret']);
						$_SESSION['license'] = 'L12345678';
						$_SESSION['ses_endpoint'] = stripslashes($row['ses_endpoint']);
						$_SESSION['auth_salt'] = stripslashes($row['auth_salt']);
						$_SESSION['app'] = stripslashes($row['app']);
						$_SESSION['strict_delete'] = stripslashes($row['strict_delete']);
					}
				}
				
				$_SESSION['is_sub_user'] = true;
				
				//Check if brand can see reports only and not access the rest of the app
				$q2 = 'SELECT campaigns_only, templates_only, lists_only, reports_only FROM apps WHERE id = '.(int)$_GET['i'];
				$r2 = mysqli_query($mysqli, $q2);
				if ($r2) 
				{
					while($row = mysqli_fetch_array($r2)) 
					{
						$_SESSION['campaigns_only'] = $row['campaigns_only'];  
						$_SESSION['templates_only'] = $row['templates_only'];  
						$_SESSION['lists_only'] = $row['lists_only'];  
						$_SESSION['reports_only'] = $row['reports_only'];  
					}
				}
			}
			else
			{
				$_SESSION['is_sub_user'] = false;
				$_SESSION['tied_to'] = $_SESSION['userID'];
				$_SESSION['campaigns_only'] = 0;
				$_SESSION['templates_only'] = 0;
				$_SESSION['lists_only'] = 0;
				$_SESSION['reports_only'] = 0;
			}
	    }  
	}
	
	//Get API key
	$q2 = 'SELECT api_key FROM login ORDER BY id ASC LIMIT 1';
	$r2 = mysqli_query($mysqli, $q2);
	if ($r2 && mysqli_num_rows($r2) > 0) while($row = mysqli_fetch_array($r2)) $_SESSION['api_key'] = $row['api_key'];
	
	//check version
	if(!isset($_COOKIE['version']))
	{
		$version_latest = file_get_contents_curl('http://gateway.sendy.co/version-checker');
		
		//set it
		if(setcookie('version', $version_latest, time()+86400, '/', COOKIE_DOMAIN))
		{
			$_SESSION['version_latest'] = $version_latest;
		}
	}
	else
	{	
		//if cookie is set, check the license
		$_SESSION['version_latest'] = $_COOKIE['version'];
	}
	
	//Set Zapier Zap IDs
	if(!isset($_COOKIE['zaps']))
	{
		$zaps = urldecode(file_get_contents_curl('http://gateway.sendy.co/zaps'));
		
		//set it
		if(setcookie('zaps', $zaps, time()+86400, '/', COOKIE_DOMAIN))
		{
			$_SESSION['zaps'] = $zaps;
		}
	}
	else
	{	
		//if cookie is set, check the license
		$_SESSION['zaps'] = $_COOKIE['zaps'];
	}
	
	//-------------------------------------------------- Check license on login --------------------------------------------------//	

	$_SESSION['L12345678'] = hash('sha512', 'L12345678' . 'ttcwjc8Q4N4J7MS7/hTCrRSm9Uv7h3GS');

		
	//--------------------------------------------------------------------------------------------------------------------//

	//Check custom domain in these pages
	if(
		currentPage()=='new-brand.php'
	 || currentPage()=='edit-brand.php'
	 || currentPage()=='send-to.php'
	 )
	{
		//Check custom domain
		check_custom_domain_licenses();
	}
	
	session_write_close();
}

//--------------------------------------------------------------//
function check_custom_domain_licenses() 
//--------------------------------------------------------------//
{
	//Check custom domain
	return;
}

//--------------------------------------------------------------//
function parse_date($val, $longshort, $relative=true) //parse date according to user preference
//--------------------------------------------------------------//
{
	if($relative)
	{
		$diff = time() - $val;
		if ($diff<60)
			return $diff==1 ? $diff . " "._('sec ago') : $diff." "._('secs ago');
			
		$diff = round($diff/60);
		if ($diff<60)
			return $diff==1 ? $diff . " "._('min ago') : $diff." "._('mins ago');
			
		$diff = round($diff/60);
		if ($diff<24)
			return $diff==1 ? $diff . " "._('hr ago') : $diff." "._('hrs ago');
			
		$diff = round($diff/24);
		if ($diff<7)
			return $diff==1 ? $diff . " "._('day ago') : $diff." "._('days ago');
			
		$diff = round($diff/7);
		if ($diff<4)
			return $diff==1 ? $diff . " "._('week ago') : $diff." "._('weeks ago');
	}
	
	if($longshort=='long') return strftime("%a, %b %d, %Y, %I:%M%p", $val);
	else if($longshort=='modal') return strftime("%b %d, %Y, %I:%M%p", $val);
	else if($longshort=='short') return strftime("%b %d, %Y, %I:%M%p", $val);
}

//--------------------------------------------------------------//
function parse_date_csv($val) //parse date according to user preference
//--------------------------------------------------------------//
{
	return strftime("%Y/%m/%d, %I:%M%p", $val);
}

//--------------------------------------------------------------//
function company_name(){
//--------------------------------------------------------------//
	global $mysqli;
	
	$q = 'SELECT company FROM login LIMIT 1';
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			return $company = $row['company'];
	    }  
	}
	else
	{
		return 'Sendy';
	}
}

//--------------------------------------------------------------//
function file_get_contents_curl($url, $http_referrer='') 
//--------------------------------------------------------------//
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_REFERER, $http_referrer);
	$data = curl_exec($ch);
	$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	if($response_code!=200) return 'blocked';
	else return $data;
}

//------------------------------------------------------//
function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) 
//------------------------------------------------------//
{
	$url = 'https://www.gravatar.com/avatar/';
	$url .= md5( strtolower( trim( $email ) ) );
	$url .= "?s=$s&d=$d&r=$r";
	if ( $img ) {
		$url = '<img src="' . $url . '"';
		foreach ( $atts as $key => $val )
			$url .= ' ' . $key . '="' . $val . '"';
		$url .= ' />';
	}
	return $url;
}

//------------------------------------------------------//
function delete_between($beginning, $end, $string) 
//------------------------------------------------------//
{
	$beginningPos = strpos($string, $beginning);
	$endPos = strpos($string, $end);
	if ($beginningPos === false || $endPos === false) return $string;
	$textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
	return str_replace($textToDelete, '', $string);
}

//------------------------------------------------------//
function show_error($title, $msg_html, $back=true) 
//------------------------------------------------------//
{
	echo "<!DOCTYPE html><html><head> <meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"/> <link rel=\"Shortcut Icon\" type=\"image/ico\" href=\"/img/favicon.png\"> <title>$title</title></head><style type=\"text/css\"> body{background: #f7f9fc; font-family: Helvetica, Arial;}#wrapper{background: #ffffff;-webkit-box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);-moz-box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75); width: 360px; height: auto; margin: -250px 0 0 -180px; padding-bottom: 10px; position: absolute; top: 50%; left: 50%; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px;}p{text-align: center; line-height: 18px; font-size: 14px; padding: 0 30px;}h2{font-weight: normal; text-align: center; font-size: 20px;}a{color: #000; text-decoration: underline;}a:hover{text-decoration: none;}</style><body> <div id=\"wrapper\"> 
	
	<h2>$title</h2> 
	
	<p>$msg_html</p>"; 
	
	if($back) echo "<p><a href=\"javascript:window.history.back();\" style=\"text-decoration:none;color:#4371ab;\">&larr; Back</a></p>";
	
	echo "</div></body></html>";
}

//------------------------------------------------------//
function check_simplexml()
//------------------------------------------------------//
{
	if(!extension_loaded('simplexml'))
	{
		//ONLY_FULL_GROUP_BY is enabled in sql_mode, campaign cannot be send until 'ONLY_FULL_GROUP_BY' is removed from sql_mode
		echo '<div class="alert alert-danger">
				<p><strong>'._('\'simplexml\' extension not installed on your server').'</strong></p>
				<p>'._('We have detected that the \'simplexml\' is not installed on your server. Sendy may not be able to load your Amazon SES quotas. Please see the answer on this page to resolve this issue').' → <a href="https://stackoverflow.com/questions/31206186/call-to-undefined-function-simplexml-load-string-in-cron-file" target="_blank">https://stackoverflow.com/questions/31206186/call-to-undefined-function-simplexml-load-string-in-cron-file</a></p>
				<p>'._('Once done, refresh this page and this error message should disappear.').'</p>
			</div>';
	}
}

//------------------------------------------------------//
function go_to_next_allowed_section()
//------------------------------------------------------//
{
	global $mysqli;
	
	//Go to section that does not have any restrictions from client privileges
	$q = 'SELECT reports_only, campaigns_only, templates_only, lists_only FROM apps WHERE id = '.get_app_info('restricted_to_app');
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$restrictions_array = array();
	    while($row = mysqli_fetch_array($r))
	    {
			$restrictions_array['app'] = $row['campaigns_only'];
			$restrictions_array['templates'] = $row['templates_only'];
			$restrictions_array['list'] = $row['lists_only'];
			$restrictions_array['reports'] = $row['reports_only'];
	    }  
	    
	    foreach($restrictions_array as $key => $val)
	    {
		    if($val==0)
		    {
			    echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/'.$key.'?i='.get_app_info('restricted_to_app').'"</script>';
				exit;
		    }
	    }
	}
}

//------------------------------------------------------//
function verify_identity($the_email)
//------------------------------------------------------//
{
	//Get email's domain
	$from_email_domain_array = explode('@', $the_email);
	$from_email_domain = $from_email_domain_array[1];
	$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
	$v_addresses = $ses->ListIdentities();
	if(!$v_addresses) return 'api_error';
	
	$verifiedEmailsArray = array();
	$verifiedDomainsArray = array();
	foreach($v_addresses['Addresses'] as $val){
		$validator = new EmailAddressValidator;
		if ($validator->check_email_address($val)) array_push($verifiedEmailsArray, $val);
		else array_push($verifiedDomainsArray, $val);
	}
	
	$veriStatus = true;
	$getIdentityVerificationAttributes = $ses->getIdentityVerificationAttributes($the_email);
	foreach($getIdentityVerificationAttributes['VerificationStatus'] as $getIdentityVerificationAttribute) 
		if($getIdentityVerificationAttribute=='Pending') $veriStatus = false;
		
	if((!in_array($the_email, $verifiedEmailsArray) && !in_array($from_email_domain, $verifiedDomainsArray)))
		return 'unverified';
	else if(!$veriStatus)
		return 'pending';
	else
		return 'verified';
}

//------------------------------------------------------//
function is_valid_domain_name($domain)
//------------------------------------------------------//
{
    if( $domain = 'localhost' || $domain = '::1' ) {
      return true;
    } else {
    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain) //valid chars check
            && preg_match("/^.{1,253}$/", $domain) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain)   ); //length of each label
   }
}

//------------------------------------------------------//
function licensed_custom_domain_count()
//------------------------------------------------------//
{
	return 50000;
}

//------------------------------------------------------//
function licensed_custom_domain_used()
//------------------------------------------------------//
{
	global $mysqli;
	
	$q = 'SELECT COUNT(*) FROM apps WHERE custom_domain != "" AND custom_domain_enabled != 0';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $used = $row['COUNT(*)'];
	
	return $used;
}

//------------------------------------------------------//
function licensed_custom_domain_maxed()
//------------------------------------------------------//
{
		return false;
}

//------------------------------------------------------//
function country_code_to_country( $code )
//------------------------------------------------------//
{
    $country = '';
    if( $code == 'AF' ) $country = 'Afghanistan';
    if( $code == 'AX' ) $country = 'Aland Islands';
    if( $code == 'AL' ) $country = 'Albania';
    if( $code == 'DZ' ) $country = 'Algeria';
    if( $code == 'AS' ) $country = 'American Samoa';
    if( $code == 'AD' ) $country = 'Andorra';
    if( $code == 'AO' ) $country = 'Angola';
    if( $code == 'AI' ) $country = 'Anguilla';
    if( $code == 'AQ' ) $country = 'Antarctica';
    if( $code == 'AG' ) $country = 'Antigua and Barbuda';
    if( $code == 'AR' ) $country = 'Argentina';
    if( $code == 'AM' ) $country = 'Armenia';
    if( $code == 'AW' ) $country = 'Aruba';
    if( $code == 'AU' ) $country = 'Australia';
    if( $code == 'AT' ) $country = 'Austria';
    if( $code == 'AZ' ) $country = 'Azerbaijan';
    if( $code == 'BS' ) $country = 'Bahamas the';
    if( $code == 'BH' ) $country = 'Bahrain';
    if( $code == 'BD' ) $country = 'Bangladesh';
    if( $code == 'BB' ) $country = 'Barbados';
    if( $code == 'BY' ) $country = 'Belarus';
    if( $code == 'BE' ) $country = 'Belgium';
    if( $code == 'BZ' ) $country = 'Belize';
    if( $code == 'BJ' ) $country = 'Benin';
    if( $code == 'BM' ) $country = 'Bermuda';
    if( $code == 'BT' ) $country = 'Bhutan';
    if( $code == 'BO' ) $country = 'Bolivia';
    if( $code == 'BA' ) $country = 'Bosnia and Herzegovina';
    if( $code == 'BW' ) $country = 'Botswana';
    if( $code == 'BV' ) $country = 'Bouvet Island (Bouvetoya)';
    if( $code == 'BR' ) $country = 'Brazil';
    if( $code == 'IO' ) $country = 'British Indian Ocean Territory (Chagos Archipelago)';
    if( $code == 'VG' ) $country = 'British Virgin Islands';
    if( $code == 'BN' ) $country = 'Brunei Darussalam';
    if( $code == 'BG' ) $country = 'Bulgaria';
    if( $code == 'BF' ) $country = 'Burkina Faso';
    if( $code == 'BI' ) $country = 'Burundi';
    if( $code == 'KH' ) $country = 'Cambodia';
    if( $code == 'CM' ) $country = 'Cameroon';
    if( $code == 'CA' ) $country = 'Canada';
    if( $code == 'CV' ) $country = 'Cape Verde';
    if( $code == 'KY' ) $country = 'Cayman Islands';
    if( $code == 'CF' ) $country = 'Central African Republic';
    if( $code == 'TD' ) $country = 'Chad';
    if( $code == 'CL' ) $country = 'Chile';
    if( $code == 'CN' ) $country = 'China';
    if( $code == 'CX' ) $country = 'Christmas Island';
    if( $code == 'CC' ) $country = 'Cocos (Keeling) Islands';
    if( $code == 'CO' ) $country = 'Colombia';
    if( $code == 'KM' ) $country = 'Comoros the';
    if( $code == 'CD' ) $country = 'Congo';
    if( $code == 'CG' ) $country = 'Congo the';
    if( $code == 'CK' ) $country = 'Cook Islands';
    if( $code == 'CR' ) $country = 'Costa Rica';
    if( $code == 'CI' ) $country = 'Cote d\'Ivoire';
    if( $code == 'HR' ) $country = 'Croatia';
    if( $code == 'CU' ) $country = 'Cuba';
    if( $code == 'CY' ) $country = 'Cyprus';
    if( $code == 'CZ' ) $country = 'Czech Republic';
    if( $code == 'DK' ) $country = 'Denmark';
    if( $code == 'DJ' ) $country = 'Djibouti';
    if( $code == 'DM' ) $country = 'Dominica';
    if( $code == 'DO' ) $country = 'Dominican Republic';
    if( $code == 'EC' ) $country = 'Ecuador';
    if( $code == 'EG' ) $country = 'Egypt';
    if( $code == 'SV' ) $country = 'El Salvador';
    if( $code == 'GQ' ) $country = 'Equatorial Guinea';
    if( $code == 'ER' ) $country = 'Eritrea';
    if( $code == 'EE' ) $country = 'Estonia';
    if( $code == 'ET' ) $country = 'Ethiopia';
    if( $code == 'FO' ) $country = 'Faroe Islands';
    if( $code == 'FK' ) $country = 'Falkland Islands (Malvinas)';
    if( $code == 'FJ' ) $country = 'Fiji the Fiji Islands';
    if( $code == 'FI' ) $country = 'Finland';
    if( $code == 'FR' ) $country = 'France';
    if( $code == 'GF' ) $country = 'French Guiana';
    if( $code == 'PF' ) $country = 'French Polynesia';
    if( $code == 'TF' ) $country = 'French Southern Territories';
    if( $code == 'GA' ) $country = 'Gabon';
    if( $code == 'GM' ) $country = 'Gambia the';
    if( $code == 'GE' ) $country = 'Georgia';
    if( $code == 'DE' ) $country = 'Germany';
    if( $code == 'GH' ) $country = 'Ghana';
    if( $code == 'GI' ) $country = 'Gibraltar';
    if( $code == 'GR' ) $country = 'Greece';
    if( $code == 'GL' ) $country = 'Greenland';
    if( $code == 'GD' ) $country = 'Grenada';
    if( $code == 'GP' ) $country = 'Guadeloupe';
    if( $code == 'GU' ) $country = 'Guam';
    if( $code == 'GT' ) $country = 'Guatemala';
    if( $code == 'GG' ) $country = 'Guernsey';
    if( $code == 'GN' ) $country = 'Guinea';
    if( $code == 'GW' ) $country = 'Guinea-Bissau';
    if( $code == 'GY' ) $country = 'Guyana';
    if( $code == 'HT' ) $country = 'Haiti';
    if( $code == 'HM' ) $country = 'Heard Island and McDonald Islands';
    if( $code == 'VA' ) $country = 'Holy See (Vatican City State)';
    if( $code == 'HN' ) $country = 'Honduras';
    if( $code == 'HK' ) $country = 'Hong Kong';
    if( $code == 'HU' ) $country = 'Hungary';
    if( $code == 'IS' ) $country = 'Iceland';
    if( $code == 'IN' ) $country = 'India';
    if( $code == 'ID' ) $country = 'Indonesia';
    if( $code == 'IR' ) $country = 'Iran';
    if( $code == 'IQ' ) $country = 'Iraq';
    if( $code == 'IE' ) $country = 'Ireland';
    if( $code == 'IM' ) $country = 'Isle of Man';
    if( $code == 'IL' ) $country = 'Israel';
    if( $code == 'IT' ) $country = 'Italy';
    if( $code == 'JM' ) $country = 'Jamaica';
    if( $code == 'JP' ) $country = 'Japan';
    if( $code == 'JE' ) $country = 'Jersey';
    if( $code == 'JO' ) $country = 'Jordan';
    if( $code == 'KZ' ) $country = 'Kazakhstan';
    if( $code == 'KE' ) $country = 'Kenya';
    if( $code == 'KI' ) $country = 'Kiribati';
    if( $code == 'KP' ) $country = 'Korea';
    if( $code == 'KR' ) $country = 'Korea';
    if( $code == 'KW' ) $country = 'Kuwait';
    if( $code == 'KG' ) $country = 'Kyrgyz Republic';
    if( $code == 'LA' ) $country = 'Lao';
    if( $code == 'LV' ) $country = 'Latvia';
    if( $code == 'LB' ) $country = 'Lebanon';
    if( $code == 'LS' ) $country = 'Lesotho';
    if( $code == 'LR' ) $country = 'Liberia';
    if( $code == 'LY' ) $country = 'Libyan Arab Jamahiriya';
    if( $code == 'LI' ) $country = 'Liechtenstein';
    if( $code == 'LT' ) $country = 'Lithuania';
    if( $code == 'LU' ) $country = 'Luxembourg';
    if( $code == 'MO' ) $country = 'Macao';
    if( $code == 'MK' ) $country = 'Macedonia';
    if( $code == 'MG' ) $country = 'Madagascar';
    if( $code == 'MW' ) $country = 'Malawi';
    if( $code == 'MY' ) $country = 'Malaysia';
    if( $code == 'MV' ) $country = 'Maldives';
    if( $code == 'ML' ) $country = 'Mali';
    if( $code == 'MT' ) $country = 'Malta';
    if( $code == 'MH' ) $country = 'Marshall Islands';
    if( $code == 'MQ' ) $country = 'Martinique';
    if( $code == 'MR' ) $country = 'Mauritania';
    if( $code == 'MU' ) $country = 'Mauritius';
    if( $code == 'YT' ) $country = 'Mayotte';
    if( $code == 'MX' ) $country = 'Mexico';
    if( $code == 'FM' ) $country = 'Micronesia';
    if( $code == 'MD' ) $country = 'Moldova';
    if( $code == 'MC' ) $country = 'Monaco';
    if( $code == 'MN' ) $country = 'Mongolia';
    if( $code == 'ME' ) $country = 'Montenegro';
    if( $code == 'MS' ) $country = 'Montserrat';
    if( $code == 'MA' ) $country = 'Morocco';
    if( $code == 'MZ' ) $country = 'Mozambique';
    if( $code == 'MM' ) $country = 'Myanmar';
    if( $code == 'NA' ) $country = 'Namibia';
    if( $code == 'NR' ) $country = 'Nauru';
    if( $code == 'NP' ) $country = 'Nepal';
    if( $code == 'AN' ) $country = 'Netherlands Antilles';
    if( $code == 'NL' ) $country = 'Netherlands';
    if( $code == 'NC' ) $country = 'New Caledonia';
    if( $code == 'NZ' ) $country = 'New Zealand';
    if( $code == 'NI' ) $country = 'Nicaragua';
    if( $code == 'NE' ) $country = 'Niger';
    if( $code == 'NG' ) $country = 'Nigeria';
    if( $code == 'NU' ) $country = 'Niue';
    if( $code == 'NF' ) $country = 'Norfolk Island';
    if( $code == 'MP' ) $country = 'Northern Mariana Islands';
    if( $code == 'NO' ) $country = 'Norway';
    if( $code == 'OM' ) $country = 'Oman';
    if( $code == 'PK' ) $country = 'Pakistan';
    if( $code == 'PW' ) $country = 'Palau';
    if( $code == 'PS' ) $country = 'Palestinian Territory';
    if( $code == 'PA' ) $country = 'Panama';
    if( $code == 'PG' ) $country = 'Papua New Guinea';
    if( $code == 'PY' ) $country = 'Paraguay';
    if( $code == 'PE' ) $country = 'Peru';
    if( $code == 'PH' ) $country = 'Philippines';
    if( $code == 'PN' ) $country = 'Pitcairn Islands';
    if( $code == 'PL' ) $country = 'Poland';
    if( $code == 'PT' ) $country = 'Portugal';
    if( $code == 'PR' ) $country = 'Puerto Rico';
    if( $code == 'QA' ) $country = 'Qatar';
    if( $code == 'RE' ) $country = 'Reunion';
    if( $code == 'RO' ) $country = 'Romania';
    if( $code == 'RU' ) $country = 'Russian Federation';
    if( $code == 'RW' ) $country = 'Rwanda';
    if( $code == 'BL' ) $country = 'Saint Barthelemy';
    if( $code == 'SH' ) $country = 'Saint Helena';
    if( $code == 'KN' ) $country = 'Saint Kitts and Nevis';
    if( $code == 'LC' ) $country = 'Saint Lucia';
    if( $code == 'MF' ) $country = 'Saint Martin';
    if( $code == 'PM' ) $country = 'Saint Pierre and Miquelon';
    if( $code == 'VC' ) $country = 'Saint Vincent and the Grenadines';
    if( $code == 'WS' ) $country = 'Samoa';
    if( $code == 'SM' ) $country = 'San Marino';
    if( $code == 'ST' ) $country = 'Sao Tome and Principe';
    if( $code == 'SA' ) $country = 'Saudi Arabia';
    if( $code == 'SN' ) $country = 'Senegal';
    if( $code == 'RS' ) $country = 'Serbia';
    if( $code == 'SC' ) $country = 'Seychelles';
    if( $code == 'SL' ) $country = 'Sierra Leone';
    if( $code == 'SG' ) $country = 'Singapore';
    if( $code == 'SK' ) $country = 'Slovakia (Slovak Republic)';
    if( $code == 'SI' ) $country = 'Slovenia';
    if( $code == 'SB' ) $country = 'Solomon Islands';
    if( $code == 'SO' ) $country = 'Somalia, Somali Republic';
    if( $code == 'ZA' ) $country = 'South Africa';
    if( $code == 'GS' ) $country = 'South Georgia and the South Sandwich Islands';
    if( $code == 'ES' ) $country = 'Spain';
    if( $code == 'LK' ) $country = 'Sri Lanka';
    if( $code == 'SD' ) $country = 'Sudan';
    if( $code == 'SR' ) $country = 'Suriname';
    if( $code == 'SJ' ) $country = 'Svalbard & Jan Mayen Islands';
    if( $code == 'SZ' ) $country = 'Swaziland';
    if( $code == 'SE' ) $country = 'Sweden';
    if( $code == 'CH' ) $country = 'Switzerland, Swiss Confederation';
    if( $code == 'SY' ) $country = 'Syrian Arab Republic';
    if( $code == 'TW' ) $country = 'Taiwan';
    if( $code == 'TJ' ) $country = 'Tajikistan';
    if( $code == 'TZ' ) $country = 'Tanzania';
    if( $code == 'TH' ) $country = 'Thailand';
    if( $code == 'TL' ) $country = 'Timor-Leste';
    if( $code == 'TG' ) $country = 'Togo';
    if( $code == 'TK' ) $country = 'Tokelau';
    if( $code == 'TO' ) $country = 'Tonga';
    if( $code == 'TT' ) $country = 'Trinidad and Tobago';
    if( $code == 'TN' ) $country = 'Tunisia';
    if( $code == 'TR' ) $country = 'Turkey';
    if( $code == 'TM' ) $country = 'Turkmenistan';
    if( $code == 'TC' ) $country = 'Turks and Caicos Islands';
    if( $code == 'TV' ) $country = 'Tuvalu';
    if( $code == 'UG' ) $country = 'Uganda';
    if( $code == 'UA' ) $country = 'Ukraine';
    if( $code == 'AE' ) $country = 'United Arab Emirates';
    if( $code == 'GB') $country = 'United Kingdom';
    if( $code == 'US' ) $country = 'United States';
    if( $code == 'UM' ) $country = 'United States Minor Outlying Islands';
    if( $code == 'VI' ) $country = 'United States Virgin Islands';
    if( $code == 'UY' ) $country = 'Uruguay, Eastern Republic of';
    if( $code == 'UZ' ) $country = 'Uzbekistan';
    if( $code == 'VU' ) $country = 'Vanuatu';
    if( $code == 'VE' ) $country = 'Venezuela';
    if( $code == 'VN' ) $country = 'Vietnam';
    if( $code == 'WF' ) $country = 'Wallis and Futuna';
    if( $code == 'EH' ) $country = 'Western Sahara';
    if( $code == 'YE' ) $country = 'Yemen';
    if( $code == 'ZM' ) $country = 'Zambia';
    if( $code == 'ZW' ) $country = 'Zimbabwe';
    if( $code == 'IC' ) $country = 'Canary Islands';
    if( $code == 'SX' ) $country = 'Sint Maarten';
    if( $code == 'CW' ) $country = 'Curaçao';
    if( $code == 'XK' ) $country = 'Kosovo';
    if( $country == '') $country = $code;
    return $country;
}

//--------------------------------------------------------------//
function get_app_info($v) //app reference
//--------------------------------------------------------------//
{	
	global $mysqli;
	
	switch ($v) {
		case 'version':
	    	return CURRENT_VERSION;
	    	break;
	    case 'version_latest':
	    	if(isset($_SESSION['version_latest'])) return $_SESSION['version_latest'];
	    	else return;
	    	break;
		case 'cookie_domain':
	    	return COOKIE_DOMAIN;
	    	break;
	    case 'path':
			
			//If accessing from custom domain, get custom domain details
			if(CURRENT_DOMAIN != APP_PATH_DOMAIN)
			{
				$q = 'SELECT id, custom_domain, custom_domain_protocol FROM apps WHERE custom_domain = "'.CURRENT_DOMAIN.'"';
		    	$r = mysqli_query($mysqli, $q);
		    	if ($r && mysqli_num_rows($r) > 0)
		    	{
		    	    while($row = mysqli_fetch_array($r))
		    	    {
		    			$app_id = $row['id'];
		    			$custom_domain = $row['custom_domain'];
		    			$custom_domain_protocol = $row['custom_domain_protocol'];
						$parse = parse_url(APP_PATH);
						$domain = $parse['host'];
						$protocol = $parse['scheme'];
						$app_path = str_replace($domain, $custom_domain, APP_PATH);
						$app_path = str_replace($protocol, $custom_domain_protocol, $app_path);
		    	    }  
		    	}
		    	else $app_path = APP_PATH;
			}
			else $app_path = APP_PATH;
	    	
			return $app_path; 
	        
	        break;
	    case 's3_key':
	    	if(isset($_SESSION['s3_key'])) return $_SESSION['s3_key'];
	    	else return;
	    	break;
	    case 's3_secret':
	    	if(isset($_SESSION['s3_secret'])) return $_SESSION['s3_secret'];
	    	else return;
	    	break;
	    case 'app':
	    	if(isset($_GET['i']) && is_numeric($_GET['i'])) return mysqli_real_escape_string($mysqli, (int)$_GET['i']);
	    	else if($_GET['i']=='') return '';
	    	else echo '<script type="text/javascript">window.location = "'.APP_PATH.'/logout";</script>';
	    	break;
	    case 'campaigns_only':
	    	if(isset($_SESSION['campaigns_only'])) return $_SESSION['campaigns_only'];
	    	else return;
	    	break;
	    case 'templates_only':
	    	if(isset($_SESSION['templates_only'])) return $_SESSION['templates_only'];
	    	else return;
	    	break;
	    case 'lists_only':
	    	if(isset($_SESSION['lists_only'])) return $_SESSION['lists_only'];
	    	else return;
	    	break;
	    case 'reports_only':
	    	if(isset($_SESSION['reports_only'])) return $_SESSION['reports_only'];
	    	else return;
	    	break;
	    case 'userID':
	    	if(isset($_SESSION['userID'])) return $_SESSION['userID'];
	    	else return;
	    	break;
	    case 'name':
	    	if(isset($_SESSION['name'])) return $_SESSION['name'];
	    	else return;
	    	break;
	    case 'company':
	    	if(isset($_SESSION['company'])) $co = $_SESSION['company'];
	    	else $co = '';
	    	if($co == '')
	    		return company_name();
	    	else
		    	return $co;
	    	break;
	    case 'email':
	    	if(isset($_SESSION['email'])) return $_SESSION['email'];
	    	else return;
	    	break;
	    case 'password':
	    	if(isset($_SESSION['password'])) return $_SESSION['password'];
	    	else return;
	    	break;
	    case 'api_key':
	    	if(isset($_SESSION['api_key'])) return $_SESSION['api_key'];
	    	else return;
	    	break;
	    case 'license':
	    	if(isset($_SESSION['license'])) return $_SESSION['license'];
	    	else return;
	    	break;
	    case 'is_sub_user':
	    	if(isset($_SESSION['is_sub_user'])) return $_SESSION['is_sub_user'];
	    	else return;
	    	break;
	    case 'main_userID':
	    	if(isset($_SESSION['tied_to'])) return $_SESSION['tied_to'];
	    	else return;
	    	break;
	    case 'restricted_to_app':
	    	if(isset($_SESSION['restricted_to_app'])) return $_SESSION['restricted_to_app'];
	    	else return;
	    	break;
	    case 'timezone':
	    	if(isset($_SESSION['timezone'])) return $_SESSION['timezone'];
	    	else return;
	    	break;
	    case 'language':
	    	if(isset($_SESSION['language'])) return $_SESSION['language'];
	    	else return;
	    	break;
	    case 'cron_sending':
	    	if($_SESSION['cron']==1) return true;
	    	else return false;
	    	break;
	    case 'send_rate':
	    	if(isset($_SESSION['send_rate'])) return $_SESSION['send_rate'];
	    	else return;
	    	break;
	    case 'ses_endpoint':
	    	if(isset($_SESSION['ses_endpoint'])) return $_SESSION['ses_endpoint'];
	    	else return;
	    	break;
		case 'auth_salt':
			if(isset($_SESSION['auth_salt'])) return $_SESSION['auth_salt'];
			else return;
			break;
	    case 'ses_region':
	    	if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.us-east-1.amazonaws.com') return 'N. Virginia';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.us-east-2.amazonaws.com') return 'Ohio';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.us-west-2.amazonaws.com') return 'Oregon';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.us-west-1.amazonaws.com') return 'N. California';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.ca-central-1.amazonaws.com') return 'Canada';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.eu-west-1.amazonaws.com') return 'Ireland';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.eu-central-1.amazonaws.com') return 'Frankfurt';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.eu-west-2.amazonaws.com') return 'London';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.eu-south-1.amazonaws.com') return 'Milan';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.eu-west-3.amazonaws.com') return 'Paris';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.eu-north-1.amazonaws.com') return 'Stockholm';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.ap-southeast-1.amazonaws.com') return 'Singapore';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.ap-southeast-2.amazonaws.com') return 'Sydney';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.ap-northeast-1.amazonaws.com') return 'Tokyo';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.ap-northeast-3.amazonaws.com') return 'Osaka';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.ap-northeast-2.amazonaws.com') return 'Seoul';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.ap-south-1.amazonaws.com') return 'Mumbai';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.me-south-1.amazonaws.com') return 'Bahrain';
			else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.af-south-1.amazonaws.com') return 'Cape Town';
	    	else if(isset($_SESSION['ses_endpoint']) && $_SESSION['ses_endpoint']=='email.sa-east-1.amazonaws.com') return 'Sao Paulo';
	    	else if(!isset($_SESSION['ses_endpoint'])) return 'No value';
	    	else return;
	    	break;
	    case 'zaps':
	    	if(isset($_SESSION['zaps'])) return $_SESSION['zaps'];
	    	else return;
	    	break;
	    case 'brands_rows':
	    	if(isset($_SESSION['brands_rows'])) return $_SESSION['brands_rows'];
	    	else return;
	    	break;
	    case 'strict_delete':
	    	if(isset($_SESSION['strict_delete'])) return $_SESSION['strict_delete'];
	    	else return;
	    	break;
	    case 'dark_mode':
	    	if(isset($_SESSION['dark_mode'])) return $_SESSION['dark_mode'];
	    	else return;
	    	break;
	}
}

