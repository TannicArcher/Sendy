<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/ares/main.php');?>
<?php include('includes/helpers/short.php');?>

<?php 
	//IDs
	$aid = isset($_GET['a']) && is_numeric($_GET['a']) ? mysqli_real_escape_string($mysqli, $_GET['a']) : exit;
	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/autoresponders-emails?i='.get_app_info('restricted_to_app').'&a='.$aid.'"</script>';
			exit;
		}
	}
	
	//loader
	$loader = get_app_info('dark_mode') ? 'loader-dark.gif' : 'loader-light.gif';
	$loader = get_app_info('path').'/img/'.$loader;
?>

<script type="text/javascript" src="<?php echo get_app_info('path')?>/js/fancybox/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path')?>/js/fancybox/jquery.fancybox.css" media="screen" />
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {		
		//iframe preview
		$(".iframe-preview").click(function(e) {
			e.preventDefault();
			
			$.fancybox.open({
				src : $(this).attr("href"),
				type : 'iframe',
				padding : 0,
				iframe : {
					preload : false
				}
			});
		});
	});
</script>

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
			    	<a href="<?php echo get_app_info('path'); ?>/edit-brand?i=<?php echo get_app_info('app');?>" data-placement="right" title="<?php echo _('Edit brand settings');?>"><?php echo get_app_data('app_name');?> <span class="icon icon-pencil top-brand-pencil"></span></a>
		    	<?php endif;?>
		    </p>
		    	</div>
		    	<h2><?php echo _('Autoresponder emails');?></h2>
		    	
		    	<br/>
		    	<p class="well"><?php echo _('List');?>: <a href="<?php echo get_app_info('path');?>/subscribers?i=<?php echo get_app_info('app');?>&l=<?php echo get_ares_data('list');?>" title=""><span class="label label-info"><?php echo get_lists_data('name', get_ares_data('list'));?></span></a> | <a href="<?php echo get_app_info('path')?>/list?i=<?php echo get_app_info('app');?>" title=""><?php echo _('Back to lists');?></a>
		    	</p>
		    	
		    	<strong><?php echo get_ares_type_name('type');?></strong>: <a href="javascript:void(0)" title="<?php echo _('Click to change the title of this Autoresponder');?>" id="edit-ar-title"><span class="label label-info" style=""><?php echo get_ares_data('name');?></span></a>
		    	<input type="text" name="ar-title-field" id="ar-title-field" value="<?php echo get_ares_data('name');?>" style="width: 200px; margin-top: 7px; display: none;" />
		    	
		    	<script type="text/javascript">
	    		$(document).ready(function() {
	    			$("#edit-ar-title").click(function(){
		    			$(this).hide();
		    			$("#ar-title-field").show();
		    			$("#ar-title-field").focus();
	    			});
	    			$("#ar-title-field").blur(function(){
		    			$(this).hide();
		    			$("#edit-ar-title").show();
	    			});
	    			$("#ar-title-field").keypress(function(e){
					    if(e.which == 13)
					    {
					    	update_ar_title();
					    }
					});
					function update_ar_title()
					{
						if($("#ar-title-field").val()!="")
						{
							$.post("<?php echo get_app_info('path');?>/includes/ares/update-ar-title.php", { ares_id: "<?php echo $aid;?>", ares_title: $("#ar-title-field").val() },
							  function(data) {
							      if(data)
							      {
								    $("#edit-ar-title span").text($("#ar-title-field").val());
							      	$("#ar-title-field").hide();
							      	$("#edit-ar-title").show();
							      }
							      else
							      {
							      	alert("Sorry, unable to save. Please try again later!");
							      }
							  }
							);
						}
						else
						{
							alert("<?php echo _('Autoresponder title cannot be empty');?>");
							$("#ar-title-field").val($("#edit-ar-title span").text());
						}
					}
	    		});
    		</script>
	    	</div>
	    </div>
	    
	    <br/>
	    
	    <?php if(!have_templates()):?>
	    <a href="<?php echo get_app_info('path')?>/autoresponders-create?i=<?php echo get_app_info('app')?>&a=<?php echo $aid?>" title="" class="btn"><i class="icon icon-plus"></i> <?php echo _('Add a new email to this autoresponder');?></a>
	    <?php else:?>
    		<div class="dropdown">
			  <button class="btn dropdown-toggle" type="button" data-toggle="dropdown" style="float:left; margin-bottom: 20px;"><i class="icon-plus-sign"></i> <?php echo _('Add a new email to this autoresponder');?>
			  <span class="caret"></span></button>
			  <ul class="dropdown-menu" style="margin-top: 35px;">
				  <li class="dropdown-header"><?php echo _('New Autoresponder email');?></li>
				  <li><a href="<?php echo get_app_info('path');?>/autoresponders-create?i=<?php echo get_app_info('app');?>&a=<?php echo $aid?>"><?php echo _('Create a new Autoresponder email');?></a></li>
				  <li class="divider"></li>
				  <li class="dropdown-header"><?php echo _('Or use a template');?></li>
				  <?php 
					  $q = 'SELECT id, template_name FROM template WHERE app = '.get_app_info('app').' ORDER BY id DESC';
					  $r = mysqli_query($mysqli, $q);
					  if ($r && mysqli_num_rows($r) > 0)
					  {
					      while($row = mysqli_fetch_array($r))
					      {
					  		$template_id = $row['id'];
					  		$template_name = stripslashes($row['template_name']);
					  		
					  		echo '<li><a href="'.get_app_info('path').'/includes/templates/use-template.php?i='.get_app_info('app').'&t='.$template_id.'&a='.$_GET['a'].'&a_type='.ares_type().'">'.$template_name.'</a></li>';
					      }  
					  }
				  ?>
			  </ul>
			</div>
		<?php endif;?> 
	    
	    <br/><br/>
	    
	    <div class="row-fluid">
	    	<div class="span12 ares">
				<table class="table ares-email-table responsive">
		          <thead>
		            <tr>
		              <th><?php echo _('Send');?></th>
		              <th><?php echo _('Email');?></th>
		              <th><?php echo _('Sent');?></th>
		              <th><?php echo _('Unique Opens');?></th>
		              <th><?php echo _('Unique Clicks');?></th>
		              <th><?php echo _('Enabled');?></th>
		            </tr>
		          </thead>
		          <tbody>
		          	<?php 
					  //Load Autoresponder emails chronologically
			          $q = " SELECT * ,
					  SUBSTRING_INDEX(time_condition, ' ', 1)
					  *
					  REPLACE(
					  REPLACE(
					  REPLACE(
					  REPLACE(
					  REPLACE(
					  REPLACE( SUBSTRING_INDEX(time_condition, ' ', -1)
					  , 'months', 43800)
					  , 'weeks', 10080)
					  , 'days', 1440)
					  , 'hours', 60)
					  , 'minutes', 1)
					  , 'immediately', 0) AS result_min
					  
					  FROM ares_emails
					  WHERE ares_id = $aid
					  ORDER BY result_min ASC, id ASC";
			          	$r = mysqli_query($mysqli, $q);
			          	if ($r && mysqli_num_rows($r) > 0)
			          	{
			          	    while($row = mysqli_fetch_array($r))
			          	    {
			          			$ares_email_id = $row['id'];
			          			$time_condition = $row['time_condition'];
			          			$title = $row['title'];
			          			$recipients = $row['recipients'];
			          			$opens = $row['opens'];
			          			$from_name = $row['from_name'];
			          			$from_email = $row['from_email'];
			          			$enabled = $row['enabled'];
			          			
			          			//opens
			          			if($opens=='')
					  			{
					  				$percentage_opened = 0;
						  			$opens_unique = 0;
					  			}
					  			else
					  			{
						  			$opens_array = explode(',', $opens);
						  			$opens_unique = count(array_unique($opens_array));
						  			$percentage_opened = round($opens_unique/$recipients * 100, 2);
						  		}
						  		
						  		if($recipients==0 || $recipients=='') $percentage_clicked = round(get_click_percentage($ares_email_id) *100, 2);
					  			else $percentage_clicked = round(get_click_percentage($ares_email_id)/$recipients *100, 2);
			          			
			          			//format time condition
			          			if($time_condition=='immediately')
				          			$time_condition = _('On').'<br/><b>'._('sign up').'</b>';
				          		else if($time_condition=='')
				          		{
				          			if(get_ares_data('type')==2)
					          			$time_condition = _('Annually on').'<br/><b>'.get_ares_data('custom_field').'</b>';
					          		else
					          			$time_condition = _('Once on').'<br/><b>'.get_ares_data('custom_field').'</b>';
				          		}
				          		else
				          		{
					          		if(substr($time_condition, 0, 1)=='+')
					          		{
						          		switch(get_ares_data('type'))
							    		{
								    		case 1:
								    		$time_condition_beforeafter = _('after').'<br/><b>'._('signup').'</b>';
								    		break;
								    		
								    		default:
								    		$time_condition_beforeafter = _('after').'<br/><b>'.get_ares_data('custom_field').'</b>';
							    		}
						          	}
						          	else
						          	{
						          		switch(get_ares_data('type'))
							    		{
								    		case 1:
								    		$time_condition_beforeafter = _('before').'<br/><b>'._('signup').'</b>';
								    		break;
								    		
								    		default:
								    		$time_condition_beforeafter = _('before').'<br/><b>'.get_ares_data('custom_field').'</b>';
							    		}
						          	}
					          		
					          		$time_condition = substr($time_condition, 1);
					          		$time_condition_array = explode(' ', $time_condition);
					          		switch($time_condition_array[0])
					          		{
						          		case 1:
								    	$time_condition = '1 '.substr($time_condition_array[1], 0, -1).' '.$time_condition_beforeafter;
								    	break;
								    	
								    	default:
								    	$time_condition = $time_condition.' '.$time_condition_beforeafter;
								    	break;
					          		}
				          		}
			          			
			          			//tags for subject
								preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $title, $matches_var, PREG_PATTERN_ORDER);
								preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $title, $matches_val, PREG_PATTERN_ORDER);
								preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $title, $matches_all, PREG_PATTERN_ORDER);
								preg_match_all('/\[([^\]]+),\s*fallback=/i', $title, $matches_var, PREG_PATTERN_ORDER);
								preg_match_all('/,\s*fallback=([^\]]*)\]/i', $title, $matches_val, PREG_PATTERN_ORDER);
								preg_match_all('/(\[[^\]]+,\s*fallback=[^\]]*\])/i', $title, $matches_all, PREG_PATTERN_ORDER);
								$matches_var = $matches_var[1];
								$matches_val = $matches_val[1];
								$matches_all = $matches_all[1];
								for($i=0;$i<count($matches_var);$i++)
								{		
									$field = $matches_var[$i];
									$fallback = $matches_val[$i];
									$tag = $matches_all[$i];
									//for each match, replace tag with fallback
									$title = str_replace($tag, $fallback, $title);
								}
								$title = str_replace('[Name]', $from_name, $title);
								$title = str_replace('[Email]', $from_email, $title);
								
								//convert date
								if(get_app_info('timezone')!='') date_default_timezone_set(get_app_info('timezone'));
								$today = time();
								$currentdaynumber = date('d', $today);
								$currentday = date('l', $today);
								$currentmonthnumber = date('m', $today);
								$currentmonth = date('F', $today);
								$currentyear = date('Y', $today);
								$unconverted_date = array('[currentdaynumber]', '[currentday]', '[currentmonthnumber]', '[currentmonth]', '[currentyear]');
								$converted_date = array($currentdaynumber, $currentday, $currentmonthnumber, $currentmonth, $currentyear);
								$title = str_replace($unconverted_date, $converted_date, $title);
		          	?>
		          	<tr id="email-<?php echo $ares_email_id;?>">
			          <td class="cols"><?php echo $time_condition;?></td>
		              <td>
		              	<strong class="cols"><a href="<?php echo get_app_info('path');?>/autoresponders-report.php?i=<?php echo get_app_info('app')?>&a=<?php echo $aid;?>&ae=<?php echo $ares_email_id; ?>" style="text-decoration: none;" title="<?php echo _('View report');?>"><?php echo $title;?></a></strong><br/>
		              	<div class="btns">
		              	
		              		<ul class="ares_email_options">
			              		<li><a href="<?php echo get_app_info('path');?>/w/<?php echo encrypt_val($ares_email_id);?>/a?<?php echo time();?>" title="<?php echo _('Preview');?>" data-placement="bottom" class="iframe-preview btn"><span class="icon icon-eye-open"></span></a></li>
			              		<li><a href="javascript:void(0)" class="btn" title="<?php echo _('Send test email');?>" data-placement="bottom" id="test-email-btn-<?php echo $ares_email_id;?>"><span class="icon icon-envelope-alt"></span></a></li>
					            <li><a href="<?php echo get_app_info('path')?>/autoresponders-edit?i=<?php echo get_app_info('app')?>&a=<?php echo $aid?>&ae=<?php echo $ares_email_id?>" class="btn" title="<?php echo _('Edit');?>" data-placement="bottom"><span class="icon icon-pencil"></span></a></li>
					            <li><a href="<?php echo get_app_info('path')?>/includes/ares/duplicate-email.php?a=<?php echo $aid?>&ae=<?php echo $ares_email_id?>&app=<?php echo get_app_info('app');?>" class="btn" title="<?php echo _('Duplicate');?>" data-placement="bottom"><span class="icon icon-copy"></span></a></li>
					            <li><a href="javascript:void(0)" class="btn" title="<?php echo _('Delete');?>" data-placement="bottom" id="delete-<?php echo $ares_email_id;?>" data-id="<?php echo $ares_email_id;?>"><span class="icon icon-trash"></span></a></li>
					            <li><a href="<?php echo get_app_info('path');?>/autoresponders-report.php?i=<?php echo get_app_info('app')?>&a=<?php echo $aid;?>&ae=<?php echo $ares_email_id; ?>" class="btn" title="<?php echo _('View report');?>" data-placement="bottom"><span class="icon icon-bar-chart"></span></a></li>
					        </ul>
				            
				            <script type="text/javascript">
				            	$("#delete-<?php echo $ares_email_id?>").click(function(e){
				            		e.preventDefault(); 
									c = confirm("<?php echo _('Confirm delete');?> '<?php echo $title;?>'?");
									if(c)
									{
						            	$.post("<?php echo get_app_info('path')?>/includes/ares/delete-email.php", { id: $(this).data("id") },
					            		  function(data) {
					            		      if(data)
					            		      {
					            		      	$("#email-<?php echo $ares_email_id;?>").fadeOut();
					            		      }
					            		      else
					            		      {
					            		      	alert("<?php echo _('Sorry, unable to delete. Please try again later!');?>");
					            		      }
					            		  }
					            		);
					            	}
				            	});
				            </script>
				        </div>
				        
				        <!-- Test send email -->
					    <div id="test-send-form-<?php echo $ares_email_id;?>" class="ares-test-form well" style="display:none;">
						    
						    <div class="alert alert-success" id="test-send-<?php echo $ares_email_id;?>" style="display:none;">
							  <button class="close" onclick="$('.alert-success').hide();">×</button>
							  <strong style="font-size:14px;"><?php echo _('Email has been sent!');?></strong>
							</div>
							
							<div class="alert alert-error" id="test-send-error-<?php echo $ares_email_id;?>" style="display:none;">
							  <button class="close" onclick="$('.alert-error').hide();">×</button>
							  <strong style="font-size:14px;"><?php echo _('Sorry, unable to send. Please try again later!');?></strong>
							</div>
							
							<div class="alert alert-error" id="test-send-error2-<?php echo $ares_email_id;?>" style="display:none;">
							  <button class="close" onclick="$('.alert-error').hide();">×</button>
							  <p id="test-send-error2-msg-<?php echo $ares_email_id;?>" style="font-size:14px;"></p>
							</div>
						    
						    <form action="<?php echo get_app_info('path')?>/includes/create/test-send.php" method="POST" accept-charset="utf-8" class="form-vertical" id="test-form-<?php echo $ares_email_id;?>">	    
						    	<label class="control-label" for="test_email-<?php echo $ares_email_id;?>"><?php echo _('Test email(s)');?></label>
						    	<div class="control-group">
							    	<div class="controls">
						              <input type="text" class="input-xlarge" id="test_email-<?php echo $ares_email_id;?>" name="test_email-<?php echo $ares_email_id;?>" placeholder="<?php echo _('Email addresses, separated by commas');?>" value="<?php echo get_app_data('test_email');?>">
						            </div>
						        </div>
						        <input type="hidden" name="cid-<?php echo $ares_email_id;?>" id="cid-<?php echo $ares_email_id;?>" value="<?php echo $ares_email_id;?>">
						        <input type="hidden" name="display_errors-<?php echo $ares_email_id;?>" id="display_errors-<?php echo $ares_email_id;?>" value="<?php echo isset($_GET['display_errors']) ? '1' : '0';?>">
						        <input type="hidden" name="is_ares-<?php echo $ares_email_id;?>" id="is_ares-<?php echo $ares_email_id;?>" value="1">
						        <button type="submit" class="btn" id="test-send-btn-<?php echo $ares_email_id;?>"><i class="icon icon-envelope-alt"></i> <?php echo _('Send test email');?></button> 
						        <a href="javascript:void(0)" id="cancel-btn-<?php echo $ares_email_id;?>" style="text-decoration: none; margin-left: 10px;"><span class="icon icon-remove-sign"></span> <?php echo _('Close');?></a>
						    </form>
					    </div>
					    
					    <script type="text/javascript">
						    //test email button
						    $("#test-email-btn-<?php echo $ares_email_id;?>").click(function(){
							    $("#test-send-form-<?php echo $ares_email_id;?>").slideDown("fast");
							    $("#test_email-<?php echo $ares_email_id;?>").select();
						    });
						    $("#cancel-btn-<?php echo $ares_email_id;?>").click(function(){
								$("#test-send-form-<?php echo $ares_email_id;?>").slideUp("fast");
							});
						    
						    //send test email
							$("#test-form-<?php echo $ares_email_id;?>").submit(function(e){
								e.preventDefault(); 
								
								test_email = $("#test_email-<?php echo $ares_email_id;?>").val();
								campaign_id = $("#cid-<?php echo $ares_email_id;?>").val();
								display_errors = $("#display_errors-<?php echo $ares_email_id;?>").val();
								is_ares = $("#is_ares-<?php echo $ares_email_id;?>").val();
								url = "<?php echo get_app_info('path')?>/includes/create/test-send.php";
								//validate email
								AtPos = test_email.indexOf("@")
								StopPos = test_email.lastIndexOf(".")
								
								if(test_email=='')
								{
									$("#test_email-<?php echo $ares_email_id;?>").effect("shake", { times:3 }, 60);
									$("#test-send-btn-<?php echo $ares_email_id;?>").effect("shake", { times:3 }, 60);
								}
								else if (AtPos == -1 || StopPos == -1) 
								{
									$("#test_email-<?php echo $ares_email_id;?>").effect("shake", { times:3 }, 60);
									$("#test-send-btn-<?php echo $ares_email_id;?>").effect("shake", { times:3 }, 60);
								}
								else
								{
									$("#test-send-btn-<?php echo $ares_email_id;?>").html("<img src=\"<?php echo $loader;?>\" class=\"edit-loader\" style=\"display:block;\"/> <?php echo _('Sending');?>...");
									$("#test-send-<?php echo $ares_email_id;?>, #test-send-error-<?php echo $ares_email_id;?>, #test-send-error2-<?php echo $ares_email_id;?>").css("display", "none");
									
									$.post(url, { test_email: test_email, campaign_id: campaign_id, display_errors: display_errors, is_ares: is_ares },
									  function(data) {
										  
										  data_array = data.split(",");
										  all_ok = 'ok';
										  for(var i=0; i<data_array.length-1; i++) 
										  	if(data_array[i]!='ok') all_ok = 'failed';
										  
										  if(all_ok=='ok')
										  {
											  $("#test-send-<?php echo $ares_email_id;?>").css("display", "block");
										  	  $("#test-send-error-<?php echo $ares_email_id;?>").css("display", "none");
										  	  $("#test-send-error2-<?php echo $ares_email_id;?>").css("display", "none");
										      $("#test-send-btn-<?php echo $ares_email_id;?>").html("<i class=\"icon icon-envelope-alt\"></i> <?php echo _('Send test email');?>");
										  }
										  else
										  {
											  $("#test-send-<?php echo $ares_email_id;?>").css("display", "none");
										  	  $("#test-send-error-<?php echo $ares_email_id;?>").css("display", "none");
										  	  $("#test-send-error2-<?php echo $ares_email_id;?>").css("display", "block");
										  	  $("#test-send-btn-<?php echo $ares_email_id;?>").html("<i class=\"icon icon-envelope-alt\"></i> <?php echo _('Send test email');?>");
										  	  
										  	  //Show more info & instructions if user's Amazon SES accont is suspended by Amazon
										  	  actual_msg = data.split(": ");
										  	  if(actual_msg[2]=="Sending suspended for this account. For more information, please check the inbox of the email address associated with your AWS account.")
										  	  {
											  	  suspension_msg = "<strong>"+data+"</strong><br/><br/><?php echo _('Please find Amazon\'s email in your inbox as well as spam folder. They will include a reason for the suspension. You will need to reply to that email in order to re-activate your account. If you can\'t find the email, please contact Amazon\'s support at their <a href=\'https://forums.aws.amazon.com/forum.jspa?forumID=90\' target=\'_blank\'>Amazon SES forum</a>');?>.<br/><br/><?php echo _('For more information on Amazon SES suspension, please see <a href=\'http://docs.aws.amazon.com/ses/latest/DeveloperGuide/e-faq-sp.html\' target=\'_blank\'>http://docs.aws.amazon.com/ses/latest/DeveloperGuide/e-faq-sp.html');?></a>";
											  	  $("#test-send-error2-msg-<?php echo $ares_email_id;?>").html(suspension_msg);
										  	  }
										  	  else $("#test-send-error2-msg-<?php echo $ares_email_id;?>").html("<strong>"+data+"</strong>");
										  }
									  }
									);
								}
							});
					    </script>
				        
		              </td>
		              <td class="cols"><?php echo $recipients;?></td>
		              <td class="cols"><span class="label label-success"><?php if(get_saved_data('opens_tracking', $ares_email_id)): ?><?php echo $percentage_opened;?>%</span> <?php echo number_format($opens_unique);?> <?php echo _('opened');?><?php else: ?><?php echo _('Tracking disabled'); endif;?></td>
		              <td class="cols"><span class="label label-info"><?php if(get_saved_data('links_tracking', $ares_email_id)): ?><?php echo $percentage_clicked;?>%</span> <?php echo number_format(get_click_percentage($ares_email_id));?> <?php echo _('clicked');?><?php else: ?><?php echo _('Tracking disabled'); endif;?></td>
		              <td>
						<div class="btn-group" data-toggle="buttons-radio">
							<a href="javascript:void(0)" title="" class="btn" id="enabled-<?php echo $ares_email_id;?>" style="text-decoration: none;"><i></i> <?php echo _('Yes');?></a>
							<a href="javascript:void(0)" title="" class="btn" id="disabled-<?php echo $ares_email_id;?>" style="text-decoration: none;"><i></i> <?php echo _('No');?></a>
						</div>
						<script type="text/javascript">
							$(document).ready(function() {
								<?php if($enabled): ?>
									$("#enabled-<?php echo $ares_email_id;?>").button('toggle');
									$("#enabled-<?php echo $ares_email_id;?> i").addClass("icon icon-circle");
									$("#disabled-<?php echo $ares_email_id;?> i").addClass("icon icon-circle-blank");
								<?php else: ?>
									$("#email-<?php echo $ares_email_id;?> .cols").css("opacity", "0.3");
									$("#disabled-<?php echo $ares_email_id;?>").button('toggle');
									$("#disabled-<?php echo $ares_email_id;?> i").addClass("icon icon-circle");
									$("#enabled-<?php echo $ares_email_id;?> i").addClass("icon icon-circle-blank");
								<?php endif;?>
								
								$("#enabled-<?php echo $ares_email_id;?>").click(function(){
									$(this).attr("disabled", true);
									$.post("<?php echo get_app_info('path')?>/includes/ares/toggle-autoresponder.php", { ares_id: <?php echo $ares_email_id;?>, enable: 1 },
									  function(data) {
									      if(data)
									      {
										      if(data=='success')
										      {
											      $("#enabled-<?php echo $ares_email_id;?>").removeAttr("disabled");
											      $("#enabled-<?php echo $ares_email_id;?>").button('toggle');
											      $("#enabled-<?php echo $ares_email_id;?> i").removeClass("icon icon-circle-blank");
											      $("#enabled-<?php echo $ares_email_id;?> i").addClass("icon icon-circle");
											      $("#disabled-<?php echo $ares_email_id;?> i").removeClass("icon icon-circle");
											      $("#disabled-<?php echo $ares_email_id;?> i").addClass("icon icon-circle-blank");
											      $("#email-<?php echo $ares_email_id;?> .cols").css("opacity", "1");
										      }
										      else if(data=='failed') alert("Sorry, unable to save. Please try again later!");
										  } 
									  }
									);
								});
								$("#disabled-<?php echo $ares_email_id;?>").click(function(){
									$(this).attr("disabled", true);
									$.post("<?php echo get_app_info('path')?>/includes/ares/toggle-autoresponder.php", { ares_id: <?php echo $ares_email_id;?>, enable: 0 },
									  function(data) {
									      if(data)
									      {
										      if(data=='success')
										      {
											      $("#disabled-<?php echo $ares_email_id;?>").removeAttr("disabled");
											      $("#disabled-<?php echo $ares_email_id;?>").button('toggle');
											      $("#disabled-<?php echo $ares_email_id;?> i").removeClass("icon icon-circle-blank");
											      $("#disabled-<?php echo $ares_email_id;?> i").addClass("icon icon-circle");
											      $("#enabled-<?php echo $ares_email_id;?> i").removeClass("icon icon-circle");
											      $("#enabled-<?php echo $ares_email_id;?> i").addClass("icon icon-circle-blank");
											      $("#email-<?php echo $ares_email_id;?> .cols").css("opacity", "0.3");
										      }
										      else if(data=='failed') alert("Sorry, unable to save. Please try again later!");
										  } 
									  }
									);
								});
							});
						</script>
		              </td>
		            </tr>
		            <?php 
				            }  
			          	}
			          	else
			          	{
				          	echo '
				          		<tr>
				          			<td colspan="5">'._('No autoresponder emails are set up.').'</td>
				          		</tr>
				          	';
			          	}
		            ?>
		          </tbody>
		        </table>
		        <br/>
		        <a href="<?php echo get_app_info('path');?>/autoresponders-list?i=<?php echo get_app_info('app')?>&l=<?php echo get_ares_data('list');?>" title=""><i class="icon icon-chevron-left"></i> <?php echo _('Back to autoresponders list');?></a>
			</div>
	    </div>
	    
    </div>
</div>

<?php include('includes/footer.php');?>
