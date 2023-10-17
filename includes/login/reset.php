<?php
	
// If brand user attempts to reset password, the 'From email' that's saved in the brand settings will be used to send the password reset email via Amazon SES using the main user's IAM credentials. 
// Email will be sent to the login email address.
	
//------------------------------------------------------//
//                          INIT                        //
//------------------------------------------------------//

include('../functions.php');
include('../helpers/PHPMailerAutoload.php');
require_once('../helpers/ses.php');
require_once('../helpers/EmailAddressValidator.php');
require_once('../helpers/short.php');

//Get api key and id from GET string
$data = mysqli_real_escape_string($mysqli, $_GET['d']);
$data = decrypt_string($data);
$data = json_decode($data);
$rpk = $data->{'rpk'};
$uid = (int)$data->{'id'};

$app_path = get_app_info('path');

//------------------------------------------------------//
//                         EVENTS                       //
//------------------------------------------------------//

//Get 'main user' login email address
$r = mysqli_query($mysqli, 'SELECT id, username, s3_key, s3_secret, ses_endpoint, api_key, reset_password_key FROM login ORDER BY id ASC LIMIT 1');
if ($r) 
{
	while($row = mysqli_fetch_array($r)) 
	{
		$main_user_id = $row['id'];
		$main_user_email_address = $row['username'];
		$aws_key = $row['s3_key'];
		$aws_secret = $row['s3_secret'];
		$ses_endpoint = $row['ses_endpoint'];
		$reset_password_key = $row['reset_password_key'];
	}

	if($reset_password_key == '') 
	{
		header("Location: $app_path");
		exit;
	}
	if($rpk != $reset_password_key)
	{
		header("Location: $app_path/login?e=3");
		exit;
	}
}

$q = 'SELECT id, name, username, company, app FROM login WHERE id = '.$uid;
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
	while($row = mysqli_fetch_array($r))
    {
    	$uid = $row['id'];
		$company = stripslashes($row['company']);
		$email = $row['username'];
		$app = $row['app'];
    } 
    
	$email_domain_array = explode('@', $email);
	$email_domain = $email_domain_array[1];
	$new_pass = ran_string(12, 12, true, false, true);
	$pass_encrypted = hash('sha512', $new_pass.'PectGtma');
    
    $q2 = 'SELECT from_email FROM apps WHERE id = '.$app;
    $r2 = mysqli_query($mysqli, $q2);
    if ($r2) while($row = mysqli_fetch_array($r2)) $from_email = $row['from_email'];
    $from_email = $from_email=='' ? $email : $from_email;
    
    //Change user's password to the new one
    $q = 'UPDATE login SET password = "'.$pass_encrypted.'" WHERE id = '.$uid;
    $r = mysqli_query($mysqli, $q);
    if ($r)
    {
    	//send a message to let them know
    	$plain_text = _('Your password has been reset, here\'s your new one').':

'._('Password').': '.$new_pass.'

'._('For better security, we recommend changing your password immediately once you log back in.');

        $message = "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title></title></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout:fixed;background-color:#ffffff;\" id=\"bodyTable\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-right:10px;padding-left:10px;\" id=\"bodyCell\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperWebview\"><tbody><tr><td align=\"center\" valign=\"top\"></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperBody\"><tbody><tr><td align=\"center\" valign=\"top\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color:#FFFFFF;border-color:#E5E5E5; border-style:solid; border-width:0 1px 1px 1px;\" width=\"100%\" class=\"tableCard\"><tbody><tr><td height=\"3\" style=\"clear: both; height: 5px; background: url('$app_path/img/top-pattern2.gif') repeat-x 0 0; background-size: 46px;\" class=\"topBorder\">&nbsp;</td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 10px;\" class=\"imgHero\"><a href=\"#\" target=\"_blank\" style=\"text-decoration:none;\"><img src=\"$app_path/img/email-notifications/new-password.gif\" width=\"150\" alt=\"\" border=\"0\" style=\"width:100%; max-width:150px; height:auto; display:block;\"></a></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 5px; padding-left: 20px; padding-right: 20px;\" class=\"mainTitle\"><h2 class=\"text\" style=\"color:#000000; font-family: Helvetica, Arial, sans-serif; font-size:28px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:36px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Your new password')."</h2></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 30px; padding-left: 20px; padding-right: 20px;\" class=\"subTitle\"><h4 class=\"text\" style=\"color:#848484; font-family: Helvetica, Arial, sans-serif; font-size:16px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:24px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Your password has been reset, here\'s your new one')."</h4></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-left:20px;padding-right:20px;\" class=\"containtTable ui-sortable\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"tableDescription\" style=\"margin-bottom: 20px;\"><tbody><tr><td align=\"left\" valign=\"top\" style=\"padding: 15px; background: #F8F9FC;\" class=\"description\"><p class=\"text\" style=\"color:#666666; font-family:'Open Sans', Helvetica, Arial, sans-serif; font-size:14px; font-weight:400; font-style:normal; letter-spacing:normal; line-height:22px; text-transform:none; text-align:left; padding:0; margin:0\"><strong>"._('Password').": </strong>$new_pass<br/></p></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"tableButton\" style=\"\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-top:20px;padding-bottom:20px;\"><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td align=\"center\" class=\"ctaButton\" style=\"background-color:#000000;padding-top:12px;padding-bottom:12px;padding-left:35px;padding-right:35px;border-radius:50px\"><a class=\"text\" href=\"$app_path\" target=\"_blank\" style=\"color:#FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; font-style:normal;letter-spacing:1px; line-height:20px; text-transform:uppercase; text-decoration:none; display:block\">"._('Login to Sendy')."</a></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td height=\"20\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"space\"><tbody><tr><td height=\"30\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>";
	    
	    //send email to me
		$mail = new PHPMailer();
		if($aws_key!='' && $aws_secret!='')
		{
			//Initialize ses class
			$ses = new SimpleEmailService($aws_key, $aws_secret, $ses_endpoint);
			
			//Check if user's AWS keys are valid
			$testAWSCreds = $ses->getSendQuota();
			if($testAWSCreds)
			{			
				//Check if login email is verified in Amazon SES console
				$v_addresses = $ses->ListIdentities();
				$verifiedEmailsArray = array();
				$verifiedDomainsArray = array();
				foreach($v_addresses['Addresses'] as $val){
					$validator = new EmailAddressValidator;
					if ($validator->check_email_address($val)) array_push($verifiedEmailsArray, $val);
					else array_push($verifiedDomainsArray, $val);
				}
				$veriStatus = true;
				$getIdentityVerificationAttributes = $ses->getIdentityVerificationAttributes($email);
				foreach($getIdentityVerificationAttributes['VerificationStatus'] as $getIdentityVerificationAttribute) 
					if($getIdentityVerificationAttribute=='Pending') $veriStatus = false;
				
				//If login email address is in Amazon SES console,
				if(in_array($email, $verifiedEmailsArray) || in_array($email_domain, $verifiedDomainsArray))
				{
					//and the email address is 'Verified'
					if($veriStatus)
					{
						//Send password reset email via Amazon SES
						$mail->IsAmazonSES();
						$mail->AddAmazonSESKey($aws_key, $aws_secret);
					}
				}
			}
		}
		$mail->CharSet	  =	"UTF-8";
		$mail->From       = $from_email;
		$mail->FromName   = $company;
		$mail->Subject = '['.$company.'] '._('Your new password');
		$mail->AltBody = $plain_text;
		$mail->Body = $message;
		$mail->IsHTML(true);
		$mail->AddAddress($email, $company);
		$mail->Send();
		
		$q2 = 'UPDATE login SET reset_password_key = "" WHERE id = '.$main_user_id;
	    mysqli_query($mysqli, $q2);
    }
    
	header("Location: $app_path/login?i=1");
    exit;
}
else
{
	echo _('Can\'t reset password.');
	exit;
}
?>