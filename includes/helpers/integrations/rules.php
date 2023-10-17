<?php	
	//--------------------------------------------------------------//
	function run_rule($rules_data)
	//--------------------------------------------------------------//
	{
		global $mysqli;
		global $app_path;
		
		$trigger = $rules_data['trigger'];
		
		switch ($trigger) 
		{
			//subscribe
			case 'subscribe':
			
				//Init
				$name = $rules_data['name'];
				$email = $rules_data['email'];
				$list = decrypt_int($rules_data['list_id']);
				$list_name = $rules_data['list_name'];
				$list_url = $rules_data['list_url'];
				$gravatar = $rules_data['gravatar'];
				
				//Run rule
		    	$q = 'SELECT * FROM rules WHERE list = '.$list.' AND `trigger` = "'.$trigger.'"';
		    	$r = mysqli_query($mysqli, $q);
				if ($r && mysqli_num_rows($r) > 0)
				{
				    while($row = mysqli_fetch_array($r))
				    {
						$id = $row['id'];
						$action = $row['action'];
						$endpoint = $row['endpoint'];
						$notification_email = $row['notification_email'];
						$unsubscribe_list_id = $row['unsubscribe_list_id'];
						$list = $row['list'];
						
						//If action is 'webhook'
					    if($action=='webhook')
					    {
						    //POST to endpoint
							$postdata = http_build_query($rules_data);				
							$result = post_to_webhook($endpoint, $postdata, $id);
						}
						else if($action=='notify')
						{
							//Get custom fields
							$no_of_items_in_array = count($rules_data);
							$no_of_items_before_custom_fields = 7;
							$have_custom_fields = $no_of_items_in_array > $no_of_items_before_custom_fields ? true : false;
							$custom_field_lines = '';
			
							if($have_custom_fields)
							{
								$no_of_custom_fields = $no_of_items_in_array - $no_of_items_before_custom_fields;
								
								$i = 0;
								foreach($rules_data as $key => $value)
								{
									if($i >= $no_of_items_before_custom_fields)
										$custom_field_lines .= '<strong>'.$key.': </strong>'.$value.'<br/>';
									$i++;
								}
							}
							
							//Subject and message
							$notification_subject = '['._('New subscriber').'] '._('List').': '.$list_name;
							$notification_message = "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title></title></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout:fixed;background-color:#ffffff;\" id=\"bodyTable\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-right:10px;padding-left:10px;\" id=\"bodyCell\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperWebview\"><tbody><tr><td align=\"center\" valign=\"top\"></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperBody\"><tbody><tr><td align=\"center\" valign=\"top\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color:#FFFFFF;border-color:#E5E5E5; border-style:solid; border-width:0 1px 1px 1px;\" width=\"100%\" class=\"tableCard\"><tbody><tr><td height=\"3\" style=\"clear: both; height: 5px; background: url('$app_path/img/top-pattern2.gif') repeat-x 0 0; background-size: 46px;\" class=\"topBorder\">&nbsp;</td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 10px;\" class=\"imgHero\"><a href=\"#\" target=\"_blank\" style=\"text-decoration:none;\"><img src=\"$app_path/img/email-notifications/new-subscriber.gif?2\" width=\"150\" alt=\"\" border=\"0\" style=\"width:100%; max-width:150px; height:auto; display:block;\"></a></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 5px; padding-left: 20px; padding-right: 20px;\" class=\"mainTitle\"><h2 class=\"text\" style=\"color:#000000; font-family: Helvetica, Arial, sans-serif; font-size:28px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:36px; text-transform:none; text-align:center; padding:0; margin:0\">"._('You have a new subscriber!')."</h2></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 30px; padding-left: 20px; padding-right: 20px;\" class=\"subTitle\"><h4 class=\"text\" style=\"color:#848484; font-family: Helvetica, Arial, sans-serif; font-size:16px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:24px; text-transform:none; text-align:center; padding:0; margin:0\">"._('The following user signed up to your list')."</h4></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-left:20px;padding-right:20px;\" class=\"containtTable ui-sortable\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"tableDescription\" style=\"margin-bottom: 20px;\"><tbody><tr><td align=\"left\" valign=\"top\" style=\"padding: 15px; background: #F8F9FC;\" class=\"description\"><p class=\"text\" style=\"color:#666666; font-family:'Open Sans', Helvetica, Arial, sans-serif; font-size:14px; font-weight:400; font-style:normal; letter-spacing:normal; line-height:22px; text-transform:none; text-align:left; padding:0; margin:0\"><strong>"._('Name').": </strong>$name<br/><strong>"._('Email').": </strong>$email<br/>$custom_field_lines<strong>"._('List').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$list_url\">$list_name</a></p></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"tableButton\" style=\"\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-top:20px;padding-bottom:20px;\"><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td align=\"center\" class=\"ctaButton\" style=\"background-color:#000000;padding-top:12px;padding-bottom:12px;padding-left:35px;padding-right:35px;border-radius:50px\"><a class=\"text\" href=\"$list_url\" target=\"_blank\" style=\"color:#FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; font-style:normal;letter-spacing:1px; line-height:20px; text-transform:uppercase; text-decoration:none; display:block\">"._('Visit your list')."</a></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td height=\"20\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"space\"><tbody><tr><td height=\"30\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>";
							
							//Send notification email
							send_email($notification_subject, $notification_message, $notification_email, '');
						}
						else if($action=='unsub_from_list')
						{
							unsubscribe_from_list($email, $unsubscribe_list_id);
						}
				    }  
				}
		
		    	break;
		    
		    //unsubscribe
			case 'unsubscribe':
			
				//Init
				$name = $rules_data['name'];
				$email = $rules_data['email'];
				$list = decrypt_int($rules_data['list_id']);
				$list_name = $rules_data['list_name'];
				$list_url = $rules_data['list_url'];
				$gravatar = $rules_data['gravatar'];
				
				//Run rule
		    	$q = 'SELECT * FROM rules WHERE list = '.$list.' AND `trigger` = "'.$trigger.'"';
		    	$r = mysqli_query($mysqli, $q);
				if ($r && mysqli_num_rows($r) > 0)
				{
				    while($row = mysqli_fetch_array($r))
				    {
					    $id = $row['id'];
						$action = $row['action'];
						$endpoint = $row['endpoint'];
						$notification_email = $row['notification_email'];
						$unsubscribe_list_id = $row['unsubscribe_list_id'];
						$list = $row['list'];
						
						//If action is 'webhook'
					    if($action=='webhook')
					    {
						    //POST to endpoint
							$postdata = http_build_query($rules_data);				
							$result = post_to_webhook($endpoint, $postdata, $id);
						}
						else if($action=='notify')
						{
							//Get custom fields
							$no_of_items_in_array = count($rules_data);
							$no_of_items_before_custom_fields = 7;
							$have_custom_fields = $no_of_items_in_array > $no_of_items_before_custom_fields ? true : false;
							$custom_field_lines = '';
			
							if($have_custom_fields)
							{
								$no_of_custom_fields = $no_of_items_in_array - $no_of_items_before_custom_fields;
								
								$i = 0;
								foreach($rules_data as $key => $value)
								{
									if($i >= $no_of_items_before_custom_fields)
										$custom_field_lines .= '<strong>'.$key.': </strong>'.$value.'<br/>';
									$i++;
								}
							}
							
							//Subject and message
							$notification_subject = '['._('User unsubscribed').'] '._('List').': '.$list_name;
							$notification_message = "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title></title></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout:fixed;background-color:#ffffff;\" id=\"bodyTable\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-right:10px;padding-left:10px;\" id=\"bodyCell\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperWebview\"><tbody><tr><td align=\"center\" valign=\"top\"></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperBody\"><tbody><tr><td align=\"center\" valign=\"top\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color:#FFFFFF;border-color:#E5E5E5; border-style:solid; border-width:0 1px 1px 1px;\" width=\"100%\" class=\"tableCard\"><tbody><tr><td height=\"3\" style=\"clear: both; height: 5px; background: url('$app_path/img/top-pattern2.gif') repeat-x 0 0; background-size: 46px;\" class=\"topBorder\">&nbsp;</td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 10px;\" class=\"imgHero\"><a href=\"#\" target=\"_blank\" style=\"text-decoration:none;\"><img src=\"$app_path/img/email-notifications/unsubscribed.gif\" width=\"150\" alt=\"\" border=\"0\" style=\"width:100%; max-width:150px; height:auto; display:block;\"></a></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 5px; padding-left: 20px; padding-right: 20px;\" class=\"mainTitle\"><h2 class=\"text\" style=\"color:#000000; font-family: Helvetica, Arial, sans-serif; font-size:28px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:36px; text-transform:none; text-align:center; padding:0; margin:0\">"._('User unsubscribed')."</h2></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 30px; padding-left: 20px; padding-right: 20px;\" class=\"subTitle\"><h4 class=\"text\" style=\"color:#848484; font-family: Helvetica, Arial, sans-serif; font-size:16px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:24px; text-transform:none; text-align:center; padding:0; margin:0\">"._('The following user unsubscribed from your list')."</h4></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-left:20px;padding-right:20px;\" class=\"containtTable ui-sortable\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"tableDescription\" style=\"margin-bottom: 20px;\"><tbody><tr><td align=\"left\" valign=\"top\" style=\"padding: 15px; background: #F8F9FC;\" class=\"description\"><p class=\"text\" style=\"color:#666666; font-family:'Open Sans', Helvetica, Arial, sans-serif; font-size:14px; font-weight:400; font-style:normal; letter-spacing:normal; line-height:22px; text-transform:none; text-align:left; padding:0; margin:0\"><strong>"._('Name').": </strong>$name<br/><strong>"._('Email').": </strong>$email<br/>$custom_field_lines<strong>"._('List').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$list_url\">$list_name</a></p></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"tableButton\" style=\"\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-top:20px;padding-bottom:20px;\"><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td align=\"center\" class=\"ctaButton\" style=\"background-color:#000000;padding-top:12px;padding-bottom:12px;padding-left:35px;padding-right:35px;border-radius:50px\"><a class=\"text\" href=\"$list_url\" target=\"_blank\" style=\"color:#FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; font-style:normal;letter-spacing:1px; line-height:20px; text-transform:uppercase; text-decoration:none; display:block\">"._('Visit your list')."</a></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td height=\"20\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"space\"><tbody><tr><td height=\"30\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>";
							
							//Send notification email
							send_email($notification_subject, $notification_message, $notification_email, '');
						}
				    }  
				}
		
		    	break;
		    	
		    //campaign_sent
			case 'campaign_sent':
			
				//Init
				$app_name = $rules_data['app_name'];
				$campaign_title = $rules_data['campaign_title'];
				$subject = $rules_data['subject'];
				$from_name = $rules_data['from_name'];
				$from_email = $rules_data['from_email'];
				$reply_to = $rules_data['reply_to'];
				$sent = $rules_data['sent'];
				$no_of_recipients = $rules_data['no_of_recipients'];
				$webversion = $rules_data['webversion'];
				$brand_id = $rules_data['brand_id'];
				$campaign_id = $rules_data['campaign_id'];
				$report_url = $rules_data['report_url'];
				
				//Subject and message
				$notification_subject = '['._('Campaign sent').'] '.$subject;
				$notification_message = "
			    <!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title></title></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout:fixed;background-color:#ffffff;\" id=\"bodyTable\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-right:10px;padding-left:10px;\" id=\"bodyCell\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperWebview\"><tbody><tr><td align=\"center\" valign=\"top\"></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperBody\"><tbody><tr><td align=\"center\" valign=\"top\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color:#FFFFFF;border-color:#E5E5E5; border-style:solid; border-width:0 1px 1px 1px;\" width=\"100%\" class=\"tableCard\"><tbody><tr><td height=\"3\" style=\"clear: both; height: 5px; background: url('$app_path/img/top-pattern2.gif') repeat-x 0 0; background-size: 46px;\" class=\"topBorder\">&nbsp;</td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 10px;\" class=\"imgHero\"><a href=\"#\" target=\"_blank\" style=\"text-decoration:none;\"><img src=\"$app_path/img/email-notifications/email-sent.gif\" width=\"150\" alt=\"\" border=\"0\" style=\"width:100%; max-width:150px; height:auto; display:block;\"></a></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 5px; padding-left: 20px; padding-right: 20px;\" class=\"mainTitle\"><h2 class=\"text\" style=\"color:#000000; font-family: Helvetica, Arial, sans-serif; font-size:28px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:36px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Your campaign has been sent')."</h2></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 30px; padding-left: 20px; padding-right: 20px;\" class=\"subTitle\"><h4 class=\"text\" style=\"color:#848484; font-family: Helvetica, Arial, sans-serif; font-size:16px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:24px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Your campaign has been successfully sent to')." $no_of_recipients "._('recipients')."!</h4></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-left:20px;padding-right:20px;\" class=\"containtTable ui-sortable\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"tableDescription\" style=\"margin-bottom: 20px;\"><tbody><tr><td align=\"left\" valign=\"top\" style=\"padding: 15px; background: #F8F9FC;\" class=\"description\"><p class=\"text\" style=\"color:#666666; font-family:'Open Sans', Helvetica, Arial, sans-serif; font-size:14px; font-weight:400; font-style:normal; letter-spacing:normal; line-height:22px; text-transform:none; text-align:left; padding:0; margin:0\"><strong>"._('Brand').": </strong>$app_name<br/><strong>"._('Campaign').": </strong>$campaign_title<br/><strong>"._('Recipients').": </strong>$no_of_recipients<br/><strong>"._('Web version').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$webversion\">$webversion</a><br/><strong>"._('View report').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$report_url\">$report_url</a></p></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"tableButton\" style=\"\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-top:20px;padding-bottom:20px;\"><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td align=\"center\" class=\"ctaButton\" style=\"background-color:#000000;padding-top:12px;padding-bottom:12px;padding-left:35px;padding-right:35px;border-radius:50px\"><a class=\"text\" href=\"$report_url\" target=\"_blank\" style=\"color:#FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; font-style:normal;letter-spacing:1px; line-height:20px; text-transform:uppercase; text-decoration:none; display:block\">"._('View campaign report')."</a></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td height=\"20\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"space\"><tbody><tr><td height=\"30\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>
			    ";
				
				//Check if user wants main account user to be BCC'd of the campaign sent
				$q = 'SELECT apps.notify_campaign_sent, login.name, login.username as email FROM apps, login WHERE apps.id = '.$brand_id.' AND login.id = apps.userID';
				$r = mysqli_query($mysqli, $q);
				if ($r && mysqli_num_rows($r) > 0)
				{
				    while($row = mysqli_fetch_array($r))
				    {
						$notify_campaign_sent = $row['notify_campaign_sent'];
						$user_name = $notify_campaign_sent ? $row['name'] : '';
						$user_email = $notify_campaign_sent ? $row['email'] : '';
				    }  
				}
				
				//Run rule
		    	$q = 'SELECT * FROM rules WHERE app = '.$brand_id.' AND `trigger` = "'.$trigger.'"';
		    	$r = mysqli_query($mysqli, $q);
				if ($r && mysqli_num_rows($r) > 0)
				{
				    while($row = mysqli_fetch_array($r))
				    {
					    $id = $row['id'];
						$action = $row['action'];
						$endpoint = $row['endpoint'];
						$notification_email = $row['notification_email'];
						
						//If action is 'webhook'
					    if($action=='webhook')
					    {
						    //POST to endpoint
							$postdata = http_build_query($rules_data);				
							$result = post_to_webhook($endpoint, $postdata, $id);
						}
						else if($action=='notify')
						{							
							//Send notification email
							send_email($notification_subject, $notification_message, $notification_email, '', $user_name, $user_email);
							
							//Don't BCC main login user more than once
							$user_name = '';
							$user_email = '';
						}
				    }  
				}
				
				//Check if there are any 'notify' actions, if not, send campaign sent notification email to main admin user
				if($notify_campaign_sent)
				{
					$q = 'SELECT action FROM rules WHERE app = '.$brand_id.' AND `trigger` = "'.$trigger.'" AND action = "notify"';
					$r = mysqli_query($mysqli, $q);
					if ($r && mysqli_num_rows($r) == 0)
					{
					    //Send notification email
						send_email($notification_subject, $notification_message, $user_email, $user_name);
					}
				}
		
		    	break;
		    	
		    //campaign_sending
			case 'campaign_sending':
			
				//Init
				$app_name = $rules_data['app_name'];
				$campaign_title = $rules_data['campaign_title'];
				$subject = $rules_data['subject'];
				$from_name = $rules_data['from_name'];
				$from_email = $rules_data['from_email'];
				$reply_to = $rules_data['reply_to'];
				$sent = $rules_data['sent'];
				$no_of_recipients = $rules_data['no_of_recipients'];
				$webversion = $rules_data['webversion'];
				$brand_id = $rules_data['brand_id'];
				$campaign_id = $rules_data['campaign_id'];
				$report_url = $rules_data['report_url'];
				
				//Run rule
		    	$q = 'SELECT * FROM rules WHERE app = '.$brand_id.' AND `trigger` = "'.$trigger.'"';
		    	$r = mysqli_query($mysqli, $q);
				if ($r && mysqli_num_rows($r) > 0)
				{
				    while($row = mysqli_fetch_array($r))
				    {
					    $id = $row['id'];
						$action = $row['action'];
						$endpoint = $row['endpoint'];
						$notification_email = $row['notification_email'];
						
						//If action is 'webhook'
					    if($action=='webhook')
					    {
						    //POST to endpoint
							$postdata = http_build_query($rules_data);				
							$result = post_to_webhook($endpoint, $postdata, $id);
						}
						else if($action=='notify')
						{							
							//Subject and message
							$notification_subject = '['._('Campaign now sending').'] '.$subject;
							$notification_message = "
						    <!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title></title></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout:fixed;background-color:#ffffff;\" id=\"bodyTable\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-right:10px;padding-left:10px;\" id=\"bodyCell\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperWebview\"><tbody><tr><td align=\"center\" valign=\"top\"></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperBody\"><tbody><tr><td align=\"center\" valign=\"top\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color:#FFFFFF;border-color:#E5E5E5; border-style:solid; border-width:0 1px 1px 1px;\" width=\"100%\" class=\"tableCard\"><tbody><tr><td height=\"3\" style=\"clear: both; height: 5px; background: url('$app_path/img/top-pattern2.gif') repeat-x 0 0; background-size: 46px;\" class=\"topBorder\">&nbsp;</td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 10px;\" class=\"imgHero\"><a href=\"#\" target=\"_blank\" style=\"text-decoration:none;\"><img src=\"$app_path/img/email-notifications/email-sending.gif\" width=\"150\" alt=\"\" border=\"0\" style=\"width:100%; max-width:150px; height:auto; display:block;\"></a></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 5px; padding-left: 20px; padding-right: 20px;\" class=\"mainTitle\"><h2 class=\"text\" style=\"color:#000000; font-family: Helvetica, Arial, sans-serif; font-size:28px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:36px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Your campaign is now sending')."</h2></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 30px; padding-left: 20px; padding-right: 20px;\" class=\"subTitle\"><h4 class=\"text\" style=\"color:#848484; font-family: Helvetica, Arial, sans-serif; font-size:16px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:24px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Your campaign has started sending to')." $no_of_recipients "._('recipients')."!</h4></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-left:20px;padding-right:20px;\" class=\"containtTable ui-sortable\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"tableDescription\" style=\"margin-bottom: 20px;\"><tbody><tr><td align=\"left\" valign=\"top\" style=\"padding: 15px; background: #F8F9FC;\" class=\"description\"><p class=\"text\" style=\"color:#666666; font-family:'Open Sans', Helvetica, Arial, sans-serif; font-size:14px; font-weight:400; font-style:normal; letter-spacing:normal; line-height:22px; text-transform:none; text-align:left; padding:0; margin:0\"><strong>"._('Brand').": </strong>$app_name<br/><strong>"._('Campaign').": </strong>$campaign_title<br/><strong>"._('Recipients').": </strong>$no_of_recipients<br/><strong>"._('Web version').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$webversion\">$webversion</a><br/><strong>"._('View report').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$report_url\">$report_url</a></p></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"tableButton\" style=\"\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-top:20px;padding-bottom:20px;\"><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td align=\"center\" class=\"ctaButton\" style=\"background-color:#000000;padding-top:12px;padding-bottom:12px;padding-left:35px;padding-right:35px;border-radius:50px\"><a class=\"text\" href=\"$report_url\" target=\"_blank\" style=\"color:#FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; font-style:normal;letter-spacing:1px; line-height:20px; text-transform:uppercase; text-decoration:none; display:block\">"._('View campaign report')."</a></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td height=\"20\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"space\"><tbody><tr><td height=\"30\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>
						    ";
							
							//Send notification email
							send_email($notification_subject, $notification_message, $notification_email, '');
						}
				    }  
				}
		
		    	break;
		    	
		    //campaign_sending
			case 'ares_sent':
			
				//Init
				$subject = $rules_data['subject'];
				$from_name = $rules_data['from_name'];
				$from_email = $rules_data['from_email'];
				$reply_to = $rules_data['reply_to'];
				$to_name = $rules_data['to_name'];
				$to_email = $rules_data['to_email'];
				$sent = $rules_data['sent'];
				$webversion = $rules_data['webversion'];
				$list_name = $rules_data['list_name'];
				$ares_name = $rules_data['ares_name'];
				$list_id = $rules_data['list_id'];
				$ares_id = $rules_data['ares_id'];
				$ares_email_id = $rules_data['ares_email_id'];
				$report_url = $rules_data['report_url'];
				
				//Run rule
		    	$q = 'SELECT * FROM rules WHERE ares_id = '.$ares_id.' AND `trigger` = "'.$trigger.'"';
		    	$r = mysqli_query($mysqli, $q);
				if ($r && mysqli_num_rows($r) > 0)
				{
				    while($row = mysqli_fetch_array($r))
				    {
					    $id = $row['id'];
						$action = $row['action'];
						$endpoint = $row['endpoint'];
						$notification_email = $row['notification_email'];
						
						//If action is 'webhook'
					    if($action=='webhook')
					    {
						    //POST to endpoint
							$postdata = http_build_query($rules_data);							
							$result = post_to_webhook($endpoint, $postdata, $id);
						}
						else if($action=='notify')
						{							
							//Subject and message
							$notification_subject = '['._('Autoresponder email sent').'] '.$subject;
							$notification_message = "
						    <!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title></title></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout:fixed;background-color:#ffffff;\" id=\"bodyTable\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-right:10px;padding-left:10px;\" id=\"bodyCell\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperWebview\"><tbody><tr><td align=\"center\" valign=\"top\"></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperBody\"><tbody><tr><td align=\"center\" valign=\"top\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color:#FFFFFF;border-color:#E5E5E5; border-style:solid; border-width:0 1px 1px 1px;\" width=\"100%\" class=\"tableCard\"><tbody><tr><td height=\"3\" style=\"clear: both; height: 5px; background: url('$app_path/img/top-pattern2.gif') repeat-x 0 0; background-size: 46px;\" class=\"topBorder\">&nbsp;</td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 10px;\" class=\"imgHero\"><a href=\"#\" target=\"_blank\" style=\"text-decoration:none;\"><img src=\"$app_path/img/email-notifications/email-sent.gif\" width=\"150\" alt=\"\" border=\"0\" style=\"width:100%; max-width:150px; height:auto; display:block;\"></a></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 5px; padding-left: 20px; padding-right: 20px;\" class=\"mainTitle\"><h2 class=\"text\" style=\"color:#000000; font-family: Helvetica, Arial, sans-serif; font-size:28px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:36px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Autoresponder email sent')."</h2></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 30px; padding-left: 20px; padding-right: 20px;\" class=\"subTitle\"><h4 class=\"text\" style=\"color:#848484; font-family: Helvetica, Arial, sans-serif; font-size:16px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:24px; text-transform:none; text-align:center; padding:0; margin:0\">"._('An Autoresponder email was triggered')."</h4></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-left:20px;padding-right:20px;\" class=\"containtTable ui-sortable\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"tableDescription\" style=\"margin-bottom: 20px;\"><tbody><tr><td align=\"left\" valign=\"top\" style=\"padding: 15px; background: #F8F9FC;\" class=\"description\"><p class=\"text\" style=\"color:#666666; font-family:'Open Sans', Helvetica, Arial, sans-serif; font-size:14px; font-weight:400; font-style:normal; letter-spacing:normal; line-height:22px; text-transform:none; text-align:left; padding:0; margin:0\"><strong>"._('Autoresponder subject').": </strong>$subject<br/><strong>"._('Autoresponder').": </strong>$ares_name<br/><strong>"._('List name').": </strong>$list_name<br/><strong>"._('Sent to').": </strong>$to_name ($to_email)<br/><strong>"._('Web version').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$webversion\">$webversion</a><br/><strong>"._('View report').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$report_url\">$report_url</a></p></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"tableButton\" style=\"\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-top:20px;padding-bottom:20px;\"><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td align=\"center\" class=\"ctaButton\" style=\"background-color:#000000;padding-top:12px;padding-bottom:12px;padding-left:35px;padding-right:35px;border-radius:50px\"><a class=\"text\" href=\"$report_url\" target=\"_blank\" style=\"color:#FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; font-style:normal;letter-spacing:1px; line-height:20px; text-transform:uppercase; text-decoration:none; display:block\">"._('View campaign report')."</a></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td height=\"20\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"space\"><tbody><tr><td height=\"30\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>
						    ";
							
							//Send notification email
							send_email($notification_subject, $notification_message, $notification_email, '');
						}
				    }  
				}
		
		    	break;
	    }
	}
	
	//--------------------------------------------------------------//
	function post_to_webhook($url, $params, $id) 
	//--------------------------------------------------------------//
	{
		global $mysqli;
		
		//POST
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$data = curl_exec($ch);
		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$response_msg = curl_error($ch);
		curl_close($ch);
		
		//Log webhook event
		$q = 'INSERT INTO webhooks_log (rule, endpoint, payload, status_code, status_message, timestamp) VALUES ('.$id.', "'.$url.'", "'.urldecode($params).'", "'.$response_code.'", "'.$response_msg.'", "'.time().'")';
		$r = mysqli_query($mysqli, $q);
		if ($r) 
		{
			//Delete logs older than 30 days
			$q2 = 'DELETE FROM webhooks_log WHERE `timestamp` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));';
			$r2 = mysqli_query($mysqli, $q2);
			if (!$r2) error_log("[Unable to DELETE old records from webhooks_log]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
		}
		else error_log("[Unable to INSERT into webhooks_log]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);			
	}
?>