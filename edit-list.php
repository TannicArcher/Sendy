<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/list/main.php');?>
<?php 
	$lid = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, (int)$_GET['l']) : exit;
		
	$app_path = get_app_info('path');
?>

<script src="<?php echo $app_path;?>/js/ckeditor/ckeditor.js?11"></script>
<?php $dark_mode = get_app_info('dark_mode');?>
<?php include('js/lists/editor.php');?>
<script type="text/javascript">
	$(document).ready(function() {
		$("#advanced-options-btn-1").click(function(){
    		$("#advanced-options-1").toggle("slide");
		});
		$("#advanced-options-btn-2").click(function(){
    		$("#advanced-options-2").toggle("slide");
		});
		$("#advanced-options-btn-3").click(function(){
    		$("#advanced-options-3").toggle("slide");
		});
	});
</script>

<form action="<?php echo $app_path?>/includes/list/edit.php" method="POST" accept-charset="utf-8" class="form-vertical">

<div class="row-fluid">
	<div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    
    <div class="span10">
    	
    	<div class="row-fluid">
	    	<div class="span12">
				<div>
			    	<p class="lead">
		    	<?php if(get_app_info('is_sub_user')):?>
			    	<?php echo get_app_data('app_name');?>
		    	<?php else:?>
			    	<a href="<?php echo $app_path; ?>/edit-brand?i=<?php echo get_app_info('app');?>" data-placement="right" title="<?php echo _('Edit brand settings');?>"><?php echo get_app_data('app_name');?> <span class="icon icon-pencil top-brand-pencil"></span></a>
		    	<?php endif;?>
		    </p>
		    	</div>
		    	<h2><?php echo _('List settings');?></h2><br/>
			</div>
    	</div>
    
	    <div class="row-fluid">
	    
		    <div class="span12">
		    	
		    	<label class="control-label" for="list_name"><?php echo _('List name');?></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="list_name" name="list_name" placeholder="<?php echo _('The list name');?>" value="<?php echo get_lists_data('name', $lid);?>">
		            </div>
		        </div>
			    
		    </div>   
		    
	    </div>
	    
	    <hr/>
	    
	    <div class="row-fluid">
		    <div class="span12">
			    <h2><?php echo _('Subscribe settings');?></h2><br/>
		    </div>
	    </div>
	    
	    <div class="row-fluid">
	    
	    	<div class="span4">
		        <div class="well">
			        <label class="control-label"><strong><?php echo _('List type');?></strong></label>
		        	<p><?php echo _('If you select double opt-in, users will be required to click a link in a confirmation email they\'ll receive when they sign up via the subscribe form or API.');?></p>
		        	<p>
				    	<div class="btn-group" data-toggle="buttons-radio">
						  <a href="javascript:void(0)" title="" class="btn" id="single"><i class="icon icon-angle-right"></i> <?php echo _('Single Opt-In');?></a>
						  <a href="javascript:void(0)" title="" class="btn" id="double"><i class="icon icon-double-angle-right"></i> <?php echo _('Double Opt-In');?></a>
						</div>
						<script type="text/javascript">
							$(document).ready(function() {
								<?php 
									$opt_in = get_lists_data('opt_in', $lid);
									if($opt_in==0):
								?>
								$("#single").button('toggle');
								$("#opt_in").val("0");
								<?php else:?>
								$("#double").button('toggle');
								$("#opt_in").val("1");
								<?php endif;?>
								
								$("#single").click(function(){
									$("#opt_in").val("0");
								});
								$("#double").click(function(){
									$("#opt_in").val("1");
								});
							});
						</script>
			    	</p>
		        </div>
		        
		        <div class="well">
			        <label class="control-label" for="subscribed_url"><strong><?php echo _('Subscribe success page');?></strong></label>
		        	<p><?php echo _('To redirect users to a custom page letting them know they\'re subscribed successfully, enter the link below. If you chose \'Double Opt-In\' as your \'List type\', this page will tell them a confirmation email has been sent to them.');?></p>
		        	<label class="control-label" for="subscribed_url"><?php echo _('Page URL');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="subscribed_url" name="subscribed_url" placeholder="http://" style="width: 98%;" value="<?php echo get_lists_data('subscribed_url', $lid);?>">
			              <a href="javascript:void(0)" style="font-size: 12px;" id="advanced-options-btn-1"><span class="icon icon-plus-sign"></span> <?php echo _('Advanced options');?></a>
			            </div>
			        </div>
			        <div class="alert alert-info" id="advanced-options-1" style="display:none;">
			        	<p><?php echo _('You can also pass variables into your custom \'Subscribe success page\' like so');?>:</p>
			        	<p><?php echo _('Example');?>:<br/><pre>http://domain.com/subscribed.php?name=%n&email=%e&listid=%l</pre></p>
			        	<p>
				        	<ul>
					        	<li><?php echo _('<code>%n</code> will be converted into the subscriber\'s name');?></li>
					        	<li><?php echo _('<code>%e</code> will be converted into the \'email\'');?></li>
					        	<li><?php echo _('<code>%l</code> will be converted into the \'listID\'');?></li>
				        	</ul>
			        	</p>
		        	</div>
		        </div>
		        
		        <div class="well">
			        <label class="control-label" for="confirm_url"><strong><?php echo _('Subscription confirmed page');?></strong> (<i><?php echo _('only applies for double opt-ins');?></i>)</label>
		        	<p><?php echo _('To redirect users to a custom URL letting them know their \'Double Opt-in\' subscription is confirmed, enter the link below.');?></p>
		        	<label class="control-label" for="confirm_url"><?php echo _('Page URL');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="confirm_url" name="confirm_url" placeholder="http://" style="width: 98%;" value="<?php echo get_lists_data('confirm_url', $lid);?>">
			              <a href="javascript:void(0)" style="font-size: 12px;" id="advanced-options-btn-2"><span class="icon icon-plus-sign"></span> <?php echo _('Advanced options');?></a>
			            </div>
			        </div>
			        <div class="alert alert-info" id="advanced-options-2" style="display:none;">
			        	<p><?php echo _('You can also pass variables into your custom \'Subscription confirmed page\' like so');?>:</p>
			        	<p><?php echo _('Example');?>:<br/><pre>http://domain.com/confirmed.php?name=%n&email=%e&listid=%l</pre></p>
			        	<p>
				        	<ul>
					        	<li><?php echo _('<code>%n</code> will be converted into the subscriber\'s name');?></li>
					        	<li><?php echo _('<code>%e</code> will be converted into the \'email\'');?></li>
					        	<li><?php echo _('<code>%l</code> will be converted into the \'listID\'');?></li>
				        	</ul>
				        </p>
		        	</div>
		        </div>
		        
		        <div class="well">
			        <label class="control-label" for="already_subscribed_url"><strong><?php echo _('Already subscribed page');?></strong></label>
		        	<p><?php echo _('To redirect users to a custom URL letting them know they\'re already subscribed, enter the link below.');?></p>
		        	<label class="control-label" for="already_subscribed_url"><?php echo _('Page URL');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="already_subscribed_url" name="already_subscribed_url" placeholder="http://" style="width: 98%;" value="<?php echo get_lists_data('already_subscribed_url', $lid);?>">
			            </div>
			        </div>
		        </div>
		        
		        <?php if(get_app_data('gdpr_options')):?>
		        
		        <div class="well">
			        <label class="control-label" for="reconsent_success_url"><strong><?php echo _('GDPR reconsent success page');?></strong></label>
		        	<p><?php echo _('When you send a reconsent campaign with the <code>[reconsent]</code> tag, the converted reconsent link will send the user to a generic page thanking them for their confirmation. To redirect them to a custom URL, enter the link below.');?></p>
		        	<label class="control-label" for="reconsent_success_url"><?php echo _('Page URL');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="reconsent_success_url" name="reconsent_success_url" placeholder="http://" style="width: 98%;" value="<?php echo get_lists_data('reconsent_success_url', $lid);?>">
			            </div>
			        </div>
		        </div>
		        
		        <div class="well">
			        <label class="control-label" for="no_consent_url"><strong><?php echo _('GDPR consent not given page');?></strong></label>
		        	<p><?php echo _('If you enabled \'GDPR fields\' for your subscribe form, users will be required to tick a checkbox in order to complete the subscription. If they don\'t, they\'ll be sent to a generic page with an error message "Consent not given". To redirect them to a custom URL, enter the link below.');?></p>
		        	<label class="control-label" for="no_consent_url"><?php echo _('Page URL');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="no_consent_url" name="no_consent_url" placeholder="http://" style="width: 98%;" value="<?php echo get_lists_data('no_consent_url', $lid);?>">
			            </div>
			        </div>
		        </div>
		        
		        <?php endif;?>
	    	</div>
		    
		    <div class="span8">
			    
				<div class="well">
				    <div class="control-group">
				        <div class="controls">
				          <label class="checkbox">
				          	<?php $thankyou = get_lists_data('thankyou', $lid); ?>
				            <input type="checkbox" id="thankyou_email" name="thankyou_email" <?php if($thankyou == 1){echo 'checked';}?>>
				            <?php echo _('Send user a thank you email after they subscribe through the subscribe form or API?');?>
				          </label>
				        </div>
				      </div>
				  
				  <?php 
						if(get_lists_data('thankyou_subject', $lid)=='')
							$thankyou_subject = _('You\'re on our list!');
					    else
						    $thankyou_subject = get_lists_data('thankyou_subject', $lid);
				  ?>
				  <label class="control-label" for="thankyou_subject" style="line-height: 22px;"><strong><?php echo _('Thank you email subject');?></strong></label>
					<div class="control-group">
				    	<div class="controls">
				          <input type="text" class="input-xlarge" id="thankyou_subject" name="thankyou_subject" placeholder="<?php echo _('Email subject');?>" style="width: 98%;" value="<?php echo $thankyou_subject;?>">
				        </div>
				    </div>
				  
				  <label class="control-label" for="thankyou_message" style="line-height: 22px;"><strong><?php echo _('Thank you email message');?></strong> <br/><em>* <?php echo _('You can use personalization tags as well as any custom field tags in the subject and message of your thank you email. Eg. ');?> <code class="tag">[Name,fallback=]</code>, <code>[Name]</code>, <code class="tag">[Email]</code>. <?php echo _('You can also use <code>[unsubscribe]</code> or <code>&lt;unsubscribe&gt;&lt;/unsubscribe&gt;</code> in the HTML of your thank you email.');?></em></label>
				  <div class="control-group">
				    	<div class="controls">
				          <textarea class="input-xlarge" id="thankyou_message" name="thankyou_message" rows="10" placeholder="<?php echo _('Email message');?>">							  
							  <?php 
							    $thankyou_email_saved = get_lists_data('thankyou_message', $lid);
							  	$thankyou_email = "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title></title></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout:fixed;background-color:#ffffff;\" id=\"bodyTable\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-right:10px;padding-left:10px;\" id=\"bodyCell\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperWebview\"><tbody><tr><td align=\"center\" valign=\"top\"></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperBody\"><tbody><tr><td align=\"center\" valign=\"top\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color:#FFFFFF;border-color:#E5E5E5; border-style:solid; border-width:0 1px 1px 1px;\" width=\"100%\" class=\"tableCard\"><tbody><tr><td height=\"3\" style=\"clear: both; height: 5px; background: url('$app_path/img/top-pattern2.gif') repeat-x 0 0; background-size: 46px;\" class=\"topBorder\">&nbsp;</td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 10px;\" class=\"imgHero\"><a href=\"#\" target=\"_blank\" style=\"text-decoration:none;\"><img src=\"$app_path/img/email-notifications/applause.gif\" width=\"150\" alt=\"\" border=\"0\" style=\"width:100%; max-width:150px; height:auto; display:block;\"></a></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 5px; padding-left: 20px; padding-right: 20px;\" class=\"mainTitle\"><h2 class=\"text\" style=\"color:#000000; font-family: Helvetica, Arial, sans-serif; font-size:28px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:36px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Welcome onboard!')."</h2></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 30px; padding-left: 20px; padding-right: 20px;\" class=\"subTitle\"><h4 class=\"text\" style=\"color:#848484; font-family: Helvetica, Arial, sans-serif; font-size:16px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:24px; text-transform:none; text-align:center; padding:0; margin:0\">"._('You are on our list. Changed your mind? You can opt out below.')."</h4></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-left:20px;padding-right:20px;\" class=\"containtTable ui-sortable\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"tableButton\" style=\"\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-top:0px;padding-bottom:20px;\"><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td align=\"center\" class=\"ctaButton\" style=\"background-color:#000000;padding-top:12px;padding-bottom:12px;padding-left:35px;padding-right:35px;border-radius:50px\"><a class=\"text\" href=\"[unsubscribe]\" target=\"_blank\" style=\"color:#FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; font-style:normal;letter-spacing:1px; line-height:20px; text-transform:uppercase; text-decoration:none; display:block\">"._('Unsubscribe me')."</a></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td height=\"20\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"space\"><tbody><tr><td height=\"30\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table></td></tr></tbody></table><p style=\"color:#666666; font-family:'Open Sans', Helvetica, Arial, sans-serif; font-size:12px; line-height:22px; padding:0; margin:0\">Powered by <a href=\"https://sendy.co\" style=\"color:#666666;text-decoration:underline;\">Sendy</a></p></td></tr></tbody></table></body></html>";
																	
								if(strlen(trim(preg_replace('/\xc2\xa0/',' ', $thankyou_email_saved))) == 0 || trim($thankyou_email_saved)=='<p><br></p>' || $output = trim(str_replace(array("\r\n", "\r", "\n", "	"), '', $thankyou_email_saved))=="<html><head><title></title></head><body></body></html>")
									echo $thankyou_email;
								else 
								  	echo $thankyou_email_saved;
							  ?>
				          </textarea>
				        </div>
				    </div>
				</div>
			  
				<br/>
			  
			  	<?php 
				  	if(get_lists_data('confirmation_subject', $lid)=='')
					  	$confirmation_subject = _('Please confirm your subscription');
					else
						$confirmation_subject = get_lists_data('confirmation_subject', $lid);
				?>
				<div class="well">
					<label class="control-label" for="confirmation_subject" style="line-height: 22px;"><strong><?php echo _('Confirmation email subject');?></strong> (<i><?php echo _('only applies for double opt-ins');?></i>)<br/><em>* <?php echo _('A generic subject line will be used if you leave this field empty.');?></em></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="confirmation_subject" name="confirmation_subject" placeholder="<?php echo _('Subject of confirmation email');?>" style="width: 98%;" value="<?php echo $confirmation_subject;?>">
			            </div>
			        </div>
				    
				  <label class="control-label" for="confirmation_email" style="line-height: 22px;"><strong><?php echo _('Double Opt-In confirmation message');?></strong> (<i><?php echo _('only applies for double opt-ins');?></i>)<br/><em>* <?php echo _('A generic email message will be used if you leave this field empty.');?></em><br/><em>* <?php echo _('Don\'t forget to include the confirmation link tag');?> </em><code id="confirmation_link_tag">[confirmation_link]</code><em> <?php echo _('somewhere in your message');?></em><br/><em>* <?php echo _('You can use personalization tags as well as any custom field tags in the subject and message of your confirmation email. Eg. ');?> <code class="tag">[Name,fallback=]</code>, <code>[Name]</code>, <code class="tag">[Email]</code>.</em></label>
					<script type="text/javascript">
					$(document).ready(function() {
						$("#confirmation_link_tag").click(function(){
							$(this).selectText();
						});
					});
					</script>
				  <div class="control-group">
				    	<div class="controls">
				          <textarea class="input-xlarge" id="confirmation_email" name="confirmation_email" rows="10" placeholder="<?php echo _('Email message');?>">							  
							  <?php 								  
								  $confirmation_email = "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title></title></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout:fixed;background-color:#ffffff;\" id=\"bodyTable\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-right:10px;padding-left:10px;\" id=\"bodyCell\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperWebview\"><tbody><tr><td align=\"center\" valign=\"top\"></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperBody\"><tbody><tr><td align=\"center\" valign=\"top\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color:#FFFFFF;border-color:#E5E5E5; border-style:solid; border-width:0 1px 1px 1px;\" width=\"100%\" class=\"tableCard\"><tbody><tr><td height=\"3\" style=\"clear: both; height: 5px; background: url('$app_path/img/top-pattern2.gif') repeat-x 0 0; background-size: 46px;\" class=\"topBorder\">&nbsp;</td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 10px;\" class=\"imgHero\"><a href=\"#\" target=\"_blank\" style=\"text-decoration:none;\"><img src=\"$app_path/img/email-notifications/almost-there.gif\" width=\"150\" alt=\"\" border=\"0\" style=\"width:100%; max-width:150px; height:auto; display:block;\"></a></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 5px; padding-left: 20px; padding-right: 20px;\" class=\"mainTitle\"><h2 class=\"text\" style=\"color:#000000; font-family: Helvetica, Arial, sans-serif; font-size:28px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:36px; text-transform:none; text-align:center; padding:0; margin:0\">"._('You\'re almost there!')."</h2></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 30px; padding-left: 20px; padding-right: 20px;\" class=\"subTitle\"><h4 class=\"text\" style=\"color:#848484; font-family: Helvetica, Arial, sans-serif; font-size:16px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:24px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Please confirm your subscription by clicking the button below').":</h4></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-left:20px;padding-right:20px;\" class=\"containtTable ui-sortable\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"tableButton\" style=\"\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-top:0px;padding-bottom:20px;\"><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td align=\"center\" class=\"ctaButton\" style=\"background-color:#000000;padding-top:12px;padding-bottom:12px;padding-left:35px;padding-right:35px;border-radius:50px\"><a class=\"text\" href=\"[confirmation_link]\" target=\"_blank\" style=\"color:#FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; font-style:normal;letter-spacing:1px; line-height:20px; text-transform:uppercase; text-decoration:none; display:block\">"._('Confirm your subscription')."</a></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td height=\"20\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"space\"><tbody><tr><td height=\"30\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table></td></tr></tbody></table><p style=\"color:#666666; font-family:'Open Sans', Helvetica, Arial, sans-serif; font-size:12px; line-height:22px; padding:0; margin:0\">Powered by <a href=\"https://sendy.co\" style=\"color:#666666;text-decoration:underline;\">Sendy</a></p></td></tr></tbody></table></body></html>";
								  
								  if(get_lists_data('confirmation_email', $lid)=='')
									  echo $confirmation_email;
								  else 
								  	  echo get_lists_data('confirmation_email', $lid);
							  ?>
				          </textarea>
				        </div>
				    </div>
				</div>
			    
		    </div> 
		</div>
		
		<br/>
		
		<hr/>
		
		<div class="row-fluid">
		    <div class="span12">
			    <h2><?php echo _('Unsubscribe settings');?></h2><br/>
		    </div>
	    </div>
	    
	    <div class="row-fluid">
	    
	    	<div class="span4">
		        <div class="well">
			        <label class="control-label"><strong><?php echo _('Unsubscribe behavior');?></strong></label>
		        	<p><?php echo _('If you select double opt-out, users will be required to click a confirmation link in the unsubscribe page to complete their unsubscription.');?></p>
		        	<p>
				    	<div class="btn-group" data-toggle="buttons-radio">
						  <a href="javascript:void(0)" title="" class="btn" id="single-unsubscribe"><i class="icon icon-angle-right"></i> <?php echo _('Single Opt-Out');?></a>
						  <a href="javascript:void(0)" title="" class="btn" id="double-unsubscribe"><i class="icon icon-double-angle-right"></i> <?php echo _('Double Opt-Out');?></a>
						</div>
						<script type="text/javascript">
							$(document).ready(function() {
								<?php 
									$opt_in = get_lists_data('unsubscribe_confirm', $lid);
									if($opt_in==0):
								?>
								$("#single-unsubscribe").button('toggle');
								$("#opt_out").val("0");
								<?php else:?>
								$("#double-unsubscribe").button('toggle');
								$("#opt_out").val("1");
								<?php endif;?>
								
								$("#single-unsubscribe").click(function(){
									$("#opt_out").val("0");
								});
								$("#double-unsubscribe").click(function(){
									$("#opt_out").val("1");
								});
							});
						</script>
			    	</p>
		        </div>
		    	
		        <div class="well">
			        <label class="control-label"><strong><?php echo _('Unsubscribe user');?></strong></label>
		        	<p><?php echo _('When a user unsubscribes from a newsletter or through the API, choose whether to unsubscribe them from this list only, or unsubscribe them from all lists in this brand.');?></p>
		        	<p>
				    	<div class="btn-group" data-toggle="buttons-radio">
						  <a href="javascript:void(0)" title="" class="btn" id="this-list"><i class="icon icon-minus"></i> <?php echo _('Only this list');?></a>
						  <a href="javascript:void(0)" title="" class="btn" id="all-list"><i class="icon icon-reorder"></i> <?php echo _('All lists');?></a>
						</div>
						<script type="text/javascript">
							$(document).ready(function() {
								<?php 
									$ual = get_lists_data('unsubscribe_all_list', $lid);
									if($ual==0):
								?>
								$("#this-list").button('toggle');
								$("#unsubscribe_all_list").val("0");
								<?php else:?>
								$("#all-list").button('toggle');
								$("#unsubscribe_all_list").val("1");
								<?php endif;?>
								
								$("#this-list").click(function(){
									$("#unsubscribe_all_list").val("0");
								});
								$("#all-list").click(function(){
									$("#unsubscribe_all_list").val("1");
								});
							});
						</script>
			    	</p>
		        </div>
		        
		        <div class="well">
			        <label class="control-label" for="unsubscribed_url"><strong><?php echo _('Unsubscribe confirmation page');?></strong></label>
		        	<p><?php echo _('When users unsubscribe from a newsletter, they\'ll be sent to a generic unsubscription confirmation page. To redirect users to a page of your preference, enter the link below.');?></p>
		        	<label class="control-label" for="subscribed_url"><?php echo _('Page URL');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="unsubscribed_url" name="unsubscribed_url" placeholder="http://" style="width: 98%;" value="<?php echo get_lists_data('unsubscribed_url', $lid);?>">
			              <a href="javascript:void(0)" style="font-size: 12px;" id="advanced-options-btn-3"><span class="icon icon-plus-sign"></span> <?php echo _('Advanced options');?></a>
			            </div>
			        </div>
			        <div class="alert alert-info" id="advanced-options-3" style="display:none;">
			        	<p><?php echo _('You can also pass variables into your custom \'Unsubscribe confirmation page\' like so');?>:</p>
			        	<p><?php echo _('Example');?>:<br/><pre>http://domain.com/unsubscribed.php?email=%e&listid=%l&resubscribe_url=%s</pre></p>
			        	<p>
				        	<ul>
					        	<li><?php echo _('<code>%e</code> will be converted into the \'email\'');?></li>
					        	<li><?php echo _('<code>%l</code> will be converted into the \'listID\'');?></li>
					        	<li><?php echo _('<code>%s</code> will be converted into the full \'re-subscribe\' URL');?></li>
				        	</ul>
				        </p>
		        	</div>
		        </div>
	    	</div>
		    
		    <div class="span8">
			    
				<div class="well">
					<div class="control-group">
				        <div class="controls">
				          <label class="checkbox">
				          	<?php $goodbye = get_lists_data('goodbye', $lid); ?>
				            <input type="checkbox" id="goodbye_email" name="goodbye_email" <?php if($goodbye == 1){echo 'checked';}?>>
				            <?php echo _('Send user a confirmation email after they unsubscribe from a newsletter or through the API?');?>
				          </label>
				        </div>
					</div>
				    
					<?php 
						if(get_lists_data('goodbye_subject', $lid)=='')
							$goodbye_subject = _('You\'re unsubscribed');
						else
							$goodbye_subject = get_lists_data('goodbye_subject', $lid);
				  	?>
					<label class="control-label" for="goodbye_subject" style="line-height: 22px;"><strong><?php echo _('Goodbye email subject');?></strong></label>
					<div class="control-group">
						<div class="controls">
					      <input type="text" class="input-xlarge" id="goodbye_subject" name="goodbye_subject" placeholder="<?php echo _('Email subject');?>" style="width: 98%;" value="<?php echo $goodbye_subject;?>">
					    </div>
					</div>
				  
					<label class="control-label" for="goodbye_message" style="line-height: 22px;"><strong><?php echo _('Goodbye email message');?></strong><br/><em>* <?php echo _('You can use personalization tags as well as any custom field tags in the subject and message of your goodbye email. Eg. ');?> <code class="tag">[Name,fallback=]</code>, <code>[Name]</code>, <code class="tag">[Email]</code>. <?php echo _('You can also use <code>[resubscribe]</code> or <code>&lt;resubscribe&gt;&lt;/resubscribe&gt;</code> in the HTML of your goodbye email.');?></em></label>
					<div class="control-group">
						<div class="controls">
					      <textarea class="input-xlarge" id="goodbye_message" name="goodbye_message" rows="10" placeholder="<?php echo _('Email message');?>">
							  <?php 
								  $goodbye_email_saved = get_lists_data('goodbye_message', $lid);
								  $goodbye_email = "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title></title></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout:fixed;background-color:#ffffff;\" id=\"bodyTable\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-right:10px;padding-left:10px;\" id=\"bodyCell\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperWebview\"><tbody><tr><td align=\"center\" valign=\"top\"></td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;\" width=\"100%\" class=\"wrapperBody\"><tbody><tr><td align=\"center\" valign=\"top\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color:#FFFFFF;border-color:#E5E5E5; border-style:solid; border-width:0 1px 1px 1px;\" width=\"100%\" class=\"tableCard\"><tbody><tr><td height=\"3\" style=\"clear: both; height: 5px; background: url('$app_path/img/top-pattern2.gif') repeat-x 0 0; background-size: 46px;\" class=\"topBorder\">&nbsp;</td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 10px;\" class=\"imgHero\"><a href=\"#\" target=\"_blank\" style=\"text-decoration:none;\"><img src=\"$app_path/img/email-notifications/sad.gif\" width=\"150\" alt=\"\" border=\"0\" style=\"width:100%; max-width:150px; height:auto; display:block;\"></a></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 5px; padding-left: 20px; padding-right: 20px;\" class=\"mainTitle\"><h2 class=\"text\" style=\"color:#000000; font-family: Helvetica, Arial, sans-serif; font-size:28px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:36px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Goodbye.')."</h2></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-bottom: 30px; padding-left: 20px; padding-right: 20px;\" class=\"subTitle\"><h4 class=\"text\" style=\"color:#848484; font-family: Helvetica, Arial, sans-serif; font-size:16px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:24px; text-transform:none; text-align:center; padding:0; margin:0\">"._('Unsubscribed by accident? You can re-subscribe below.')."</h4></td></tr><tr><td align=\"center\" valign=\"top\" style=\"padding-left:20px;padding-right:20px;\" class=\"containtTable ui-sortable\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"tableButton\" style=\"\"><tbody><tr><td align=\"center\" valign=\"top\" style=\"padding-top:0px;padding-bottom:20px;\"><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td align=\"center\" class=\"ctaButton\" style=\"background-color:#000000;padding-top:12px;padding-bottom:12px;padding-left:35px;padding-right:35px;border-radius:50px\"><a class=\"text\" href=\"[resubscribe]\" target=\"_blank\" style=\"color:#FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; font-style:normal;letter-spacing:1px; line-height:20px; text-transform:uppercase; text-decoration:none; display:block\">"._('Re-subscribe me')."</a></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td height=\"20\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"space\"><tbody><tr><td height=\"30\" style=\"font-size:1px;line-height:1px;\">&nbsp;</td></tr></tbody></table></td></tr></tbody></table><p style=\"color:#666666; font-family:'Open Sans', Helvetica, Arial, sans-serif; font-size:12px; line-height:22px; padding:0; margin:0\">Powered by <a href=\"https://sendy.co\" style=\"color:#666666;text-decoration:underline;\">Sendy</a></p></td></tr></tbody></table></body></html>";
																  	
							  	if(strlen(trim(preg_replace('/\xc2\xa0/',' ', $goodbye_email_saved))) == 0 || trim($goodbye_email_saved)=='<p><br></p>' || $output = trim(str_replace(array("\r\n", "\r", "\n", "	"), '', $goodbye_email_saved))=="<html><head><title></title></head><body></body></html>")
								  	echo $goodbye_email;
							  	else 
									echo $goodbye_email_saved;
								?>
					      </textarea>
					    </div>
					</div>
				    
				    <input type="hidden" name="id" value="<?php echo get_app_info('app');?>">
			        <input type="hidden" name="list" value="<?php echo $lid;?>">
			        <input type="hidden" name="opt_in" id="opt_in" value="">
			        <input type="hidden" name="opt_out" id="opt_out" value="">
			        <input type="hidden" name="unsubscribe_all_list" id="unsubscribe_all_list" value="">
			        <input type="hidden" name="gdpr_options" id="gdpr_options" value="<?php echo get_app_data('gdpr_options');?>">
				</div>
			    
		    </div> 
		</div>
	</div>
    
</div>

<div class="row-fluid">
	<div class="span2"></div>
	<div class="span10">
		<button type="submit" class="btn btn-inverse" style="float:right;"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></button>
	</div>
</div>

</form>

<?php include('includes/footer.php');?>
