<?php
	include('includes/header.php');
	include('includes/create/timezone.php');
	include('_compatibility.php');
	ini_set('display_errors', 0);
	
	//Define current version
	if(!defined('CURRENT_VERSION')) define('CURRENT_VERSION', '6.0.2');
	
	//check license
	$url = $_SERVER["SERVER_NAME"];
	$licensed = true;

	//if database already setup, redirect to home
	$q = "SELECT COUNT(*)
FROM information_schema.tables WHERE table_schema = '$dbName' 
AND (table_name = 'apps' OR table_name = 'campaigns' OR table_name = 'links' OR table_name = 'lists' OR table_name = 'login' OR table_name = 'subscribers')";
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		while($row = mysqli_fetch_array($r))
	    {
			$table_count = $row['COUNT(*)'];
			if($table_count>0)
			{
				if($table_count==6)
					echo '<script type="text/javascript">window.location = "'.get_app_info('path').'";</script>';
					
				else if($table_count>0 && $table_count!=6)
					echo '<h2>Use a new database instead</h2><br/>You\'re using a database with existing tables that will conflict with table names of what Sendy will be using.<br/><br/>Please create a new database to install Sendy on.';
					exit;
			}
	    } 
	}

	echo '
		<script type="text/javascript">$(document).ready(function() {$("#license").focus();});</script>
	
		<div class="row-fluid">
		 <div class="span3">
		 	<div class="well">
		 		<h3>Server compatibility checklist</h3><br/>
	';
	
	//return results
	foreach($result as $results){
		echo $results.'<br/>';
	}
	if($score<TOTAL_SCORE)
		echo '<br/>If you do not pass the compatibility test, either adjust your server settings via php.ini or contact your hosting support. A search via Google for more information may help as well.';
	else if($score==TOTAL_SCORE)
		echo '<br/>Great. Looks like your server is configured perfectly to run Sendy. :)';
		
	if($result[1]=='<span class="label label-important"><i class="icon-remove icon-white"></i> mysqli extension is not installed</span>')
	echo '<br/><br/><b class="label label-important">mysqli extension</b><br/> Sendy uses "mysqli" instead of the old "mysql" extension (<a href="http://php.net/manual/en/migration55.deprecated.php" target="_blank"><u>now deprecated in PHP 5.5</u></a>), so Sendy is future proof. Install mysqli extension or request your host to do so, otherwise you\'ll get a "500 internal server error". If your host refuse to do so, it\'s time to bring your business somewhere else.';
	
	if($result[2]=='<span class="label label-warning"><i class="icon-remove icon-white"></i> mod_rewrite is not enabled</span>')
	echo '<br/><br/><b class="label label-warning">mod_rewrite</b><br/> mod_rewrite may not be detected especially if you\'re on a shared server. If the mod_rewrite item is yellow, you can still proceed to install Sendy. If you get a "404 page not found error" after being redirected to the login page, <a href="https://sendy.co/forum/discussion/5/404-error-after-install/p1" target="_blank"><u>check this thread</u></a> on our forum for the fix.';
	
	if($result[3]=='<span class="label label-important"><i class="icon-remove icon-white"></i> display_errors is turned on</span>')
	echo '<br/><br/><b class="label label-important">display_errors</b><br/> display_errors should be turned off for security reasons. Please turn it off or request your host to do this if you\'re not sure.';
	
	if($result[4]=='<span class="label label-important"><i class="icon-remove icon-white"></i> hash is not installed</span>')
	echo '<br/><br/><b class="label label-important">hash</b><br/> hash is required to encrypt passwords. Please enable it or request your host to do this if you\'re not sure.';
	
	if($result[5]=='<span class="label label-important"><i class="icon-remove icon-white"></i> curl is not installed</span>')
	echo '<br/><br/><b class="label label-important">curl</b><br/> curl is required for Sendy to verify your license as well as send emails via Amazon SES. Installation cannot proceed if curl is disabled. Please enable it or request your host to do this if you\'re not sure.';
	
	if($result[6]=='<span class="label label-important"><i class="icon-remove icon-white"></i> gettext is not installed</span>')
	echo '<br/><br/><b class="label label-important">gettext</b><br/> Sendy uses GNU gettext localization framework for translation. If gettext is not enabled, Sendy may fail to load correctly.';
	
	if($result[7]=='<span class="label label-important"><i class="icon-remove icon-white"></i> simplexml is not installed</span>')
	echo '<br/><br/><b class="label label-important">simplexml</b><br/> If this extension is not installed, Sendy\'s interface may not load correctly. <a href="http://stackoverflow.com/questions/31206186/call-to-undefined-function-simplexml-load-string-in-cron-file" target="_blank"><u>Visit this page</u></a> to learn how to install this extension on your server. Or request your host to do so.';
	
	if($result[8]=='<span class="label label-important"><i class="icon-remove icon-white"></i> curl_exec is not installed</span>')
	echo '<br/><br/><b class="label label-important">curl_exec</b><br/> curl_exec is required for Sendy to verify your license as well as send emails via Amazon SES. Installation cannot proceed if curl_exec is disabled. Remove \'curl_exec\' from \'disable_functions\' in php.ini. Request your host to do this if you\'re not sure.';
	
	if($result[9]=='<span class="label label-important"><i class="icon-remove icon-white"></i> curl_multi_exec is not installed</span>')
	echo '<br/><br/><b class="label label-important">curl_multi_exec</b><br/> curl_multi_exec is required for Sendy to send your emails using multi-threading. Remove \'curl_multi_exec\' from \'disable_functions\' in php.ini. Request your host to do this if you\'re not sure.';
		
	echo '</div>
	    
	    <div class="well">
	    	<h3>Note</h3><br/>
	    	<p>Make sure to configure your database credentials and specify the URL to your Sendy installation in <span class="label">includes/config.php</span> before continuing.</p>
	    	<p>Also, don\'t forget to read our <a href="https://sendy.co/get-started" target="_blank" style="text-decoration:underline;">Get Started Guide</a> to help you get Sendy up and running.</p>
	    </div>
    </div>
    <div class="span9">
    	<h2>Install Sendy</h2><br/>';
    	
	echo '<div class="alert alert-danger" id="error1" style="display:none;">
			<p><strong>Please fill in all fields marked with asterisk *</strong></p>
		</div>
		
		<div class="alert alert-danger" id="error2" style="display:none">
			<p><strong>Your login email address is invalid</strong></p>
		</div>';
    
    echo '<div class="alert alert-success" style="display:none;">
		  <button class="close" onclick="$(\'.alert-success\').hide();">×</button>
		  <strong>Settings have been saved!</strong>
		</div>
		
		<div class="alert alert-error" style="display:none;">
		  <button class="close" onclick="$(\'.alert-error\').hide();">×</button>
		  <strong>Sorry, unable to save. Please try again later!</strong>
		</div>
		
	    <form action="" method="POST" accept-charset="utf-8" class="form-vertical">
	        
	        <label class="control-label" for="license">License key* | <a target="_blank" href="https://bit.ly/3Iparq4">NULLED Scripts!</a></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="license" name="license" placeholder="Your license key" value="ACTIVATED: UNLIMITED" readonly="readonly">
	            </div>
	        </div>
	        
	    	<label class="control-label" for="company">Company*</label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="company" name="company" placeholder="Your company">
	            </div>
	        </div>
	        
	        <label class="control-label" for="personal_name">Name*</label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="personal_name" name="personal_name" placeholder="Your name">
	            </div>
	        </div>
	        
	        <label class="control-label" for="email">Email*</label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="email" name="email" placeholder="Specify your login email" autocomplete="off">
	            </div>
	        </div>
	        
	        <label class="control-label" for="password">Password*</label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="password" class="input-xlarge" id="password" name="password" placeholder="Specify your login password" autocomplete="off">
	            </div>
	        </div>
	        
	        <div>
	        <label for="timezone">Select your timezone</label>
	    		<select id="timezone" name="timezone">
				  <option value="America/New_York">America/New_York</option> 
				  ';
				  
	echo get_timezone_list();
				  
	echo '
				</select>
			</div><br/>
	        
	        <label class="control-label" for="aws_key">AWS Access Key ID</label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="aws_key" name="aws_key" placeholder="AWS Access Key ID">
	            </div>
	        </div>
	        
	        <label class="control-label" for="aws_secret">AWS Secret Access Key</label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="aws_secret" name="aws_secret" placeholder="AWS Secret Access Key">
	            </div>
	        </div>
	        
	        <button type="submit" class="btn btn-inverse">Install now</button>
	    </form>
    </div>   
</div>';
		
	//else, check for POST data
	if(count($_POST)!=0)
	{	
		$q1 = "CREATE TABLE `apps` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `userID` int(11) DEFAULT NULL,
			  `app_name` varchar(100) DEFAULT NULL,
			  `from_name` varchar(100) DEFAULT NULL,
			  `from_email` varchar(100) DEFAULT NULL,
			  `reply_to` varchar(100) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
		";
		$q2 = "CREATE TABLE `campaigns` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `userID` int(11) DEFAULT NULL,
			  `app` int(11) DEFAULT NULL,
			  `from_name` varchar(100) DEFAULT NULL,
			  `from_email` varchar(100) DEFAULT NULL,
			  `reply_to` varchar(100) DEFAULT NULL,
			  `title` varchar(500) DEFAULT NULL,
			  `plain_text` mediumtext,
			  `html_text` mediumtext,
			  `sent` varchar(100) DEFAULT '',
			  `recipients` int(100) DEFAULT '0',
			  `opens` longtext,
			  `wysiwyg` int(11) DEFAULT '0',
			  PRIMARY KEY (`id`)
			) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
		";
		$q3 = "CREATE TABLE `links` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `campaign_id` int(11) DEFAULT NULL,
			  `link` varchar(1500) DEFAULT NULL,
			  `clicks` longtext,
			  PRIMARY KEY (`id`)
			) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
		";
		$q4 = "CREATE TABLE `lists` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `app` int(11) DEFAULT NULL,
			  `userID` int(11) DEFAULT NULL,
			  `name` varchar(100) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
		";
		$q5 = "CREATE TABLE `login` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(100) DEFAULT NULL,
			  `company` varchar(100) DEFAULT NULL,
			  `username` varchar(100) DEFAULT NULL,
			  `password` varchar(500) DEFAULT NULL,
			  `s3_key` varchar(500) DEFAULT NULL,
			  `s3_secret` varchar(500) DEFAULT NULL,
			  `api_key` varchar(500) DEFAULT NULL,
			  `license` varchar(100) DEFAULT NULL,
			  `timezone` varchar(100) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
		";
		$q6 = "CREATE TABLE `subscribers` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `userID` int(11) DEFAULT NULL,
			  `name` varchar(100) DEFAULT NULL,
			  `email` varchar(100) DEFAULT NULL,
			  `list` int(11) DEFAULT NULL,
			  `unsubscribed` int(11) DEFAULT '0',
			  `bounced` int(11) DEFAULT '0',
			  `last_campaign` int(11) DEFAULT NULL,
			  `timestamp` int(100) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
		";
		
		$license = 'L12345678';
		$company = mysqli_real_escape_string($mysqli, $_POST['company']);
		$name = mysqli_real_escape_string($mysqli, $_POST['personal_name']);
		$email = mysqli_real_escape_string($mysqli, $_POST['email']);
		$password = mysqli_real_escape_string($mysqli, $_POST['password']);
		$timezone = mysqli_real_escape_string($mysqli, $_POST['timezone']);
		$pass_encrypted = hash('sha512', $password.'PectGtma');	
		$aws_key = mysqli_real_escape_string($mysqli, $_POST['aws_key']);
		$aws_secret = mysqli_real_escape_string($mysqli, $_POST['aws_secret']);
		$api_key = ran_string(20, 20, true, false, true);
		$current_domain = $_SERVER['HTTP_HOST'];
		
		if($company!='' && $name!='' && $email!='' && $password!='' && $license!='')
		{
			//Validate login email
			$validator = new EmailAddressValidator;
			if (!$validator->check_email_address($email)) 
			{
				echo '<script type="text/javascript">$("#error2").show();</script>'; 
				include('includes/footer.php');
				exit;
			}
			
			//check license
			$licensed = true;
			if($licensed)
			{
				$r1 = mysqli_query($mysqli, $q1);
				$r2 = mysqli_query($mysqli, $q2);
				$r3 = mysqli_query($mysqli, $q3);
				$r4 = mysqli_query($mysqli, $q4);
				$r5 = mysqli_query($mysqli, $q5);
				$r6 = mysqli_query($mysqli, $q6);
				if ($r1 && $r2 && $r3 && $r4 && $r5 && $r6)
				{
				   $q = 'INSERT INTO login (company, name, username, password, s3_key, s3_secret, api_key, license, timezone) VALUES ("'.$company.'", "'.$name.'", "'.$email.'", "'.$pass_encrypted.'", "'.$aws_key.'", "'.$aws_secret.'", "'.$api_key.'", "'.$license.'", "'.$timezone.'")';
				   $r = mysqli_query($mysqli, $q);
				   if ($r)
				   {
				       echo '<script type="text/javascript">window.location = "'.get_app_info('path').'/login";</script>'; 
				   }
				}
			}
		}
		else
		{
			echo '<script type="text/javascript">$("#error1").show();</script>'; 
			include('includes/footer.php');
			exit;
		}
	}
	include('includes/footer.php');
