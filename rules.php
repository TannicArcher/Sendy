<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/rules/main.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/rules?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
	}
	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/app?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
		else if(get_app_info('campaigns_only')==1 && get_app_info('templates_only')==1 && get_app_info('lists_only')==1 && get_app_info('reports_only')==1)
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/logout"</script>';
			exit;
		}
		else if(get_app_info('campaigns_only')==1 && get_app_info('lists_only')==1)
		{
			go_to_next_allowed_section();
		}
	}
?>
<link href="<?php echo get_app_info('path');?>/css<?php echo get_app_info('dark_mode') ? '/dark' : '';?>/tablesorter.css?30" rel="stylesheet">
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/tablesorter/jquery.tablesorter.widgets.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('table').tablesorter({
			widgets        : ['saveSort'],
			usNumberFormat : true,
			sortReset      : true,
			sortRestart    : true,
			headers: { 4: { sorter: false}, 5: {sorter: false} }	
		});
	});
</script>
<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10">
    	<div>
	    	<p class="lead">
		    	<?php if(get_app_info('is_sub_user')):?>
			    	<?php echo get_app_data('app_name');?>
		    	<?php else:?>
			    	<a href="<?php echo get_app_info('path'); ?>/edit-brand?i=<?php echo get_app_info('app');?>" data-placement="right" title="<?php echo _('Edit brand settings');?>"><?php echo get_app_data('app_name');?> <span class="icon icon-pencil top-brand-pencil"></span></a>
		    	<?php endif;?>
		    </p>
    	</div>
    	<h2><?php echo _('Rules');?></h2>
    	
    	<br/>
    	
    	<?php 
	    	//Init
	    	$create_rule_btn = '<i class="icon icon-magic"></i> '._('Create a new rule');
	    	
			$triggers = array(
				  'subscribe'=> '<i class="icon icon-plus-sign"></i> '._('On subscribe')
				, 'unsubscribe'=> '<i class="icon  icon-ban-circle"></i> '._('On unsubscribe')
				, 'campaign_sending' => '<i class="icon  icon-spinner"></i> '._('When campaign starts sending')
				, 'campaign_sent' => '<i class="icon  icon-ok"></i> '._('On campaign sent')
				, 'ares_sent' => '<i class="icon  icon-ok"></i> '._('On autoresponder email sent')
			);
			$actions = array(
				  'webhook' => '<i class="icon icon-globe"></i> '._('Trigger webhook')
				, 'notify' => '<i class="icon icon-envelope"></i> '._('Send email notification')
				, 'unsub_from_list' => '<i class="icon icon-ban-circle"></i> '._('Unsubscribe from list')
			);
			
			//Check client privileges if logged in as brand user
			if(get_app_info('is_sub_user'))
			{
				//If no 'campaigns privilege
				if(get_app_info('campaigns_only')==1)
				{
					unset($triggers['campaign_sending']);
					unset($triggers['campaign_sent']);
					$privileges = ' AND (`trigger` != "campaign_sending" AND `trigger` != "campaign_sent") ';
				}
				//If no 'lists' privilege
				if(get_app_info('lists_only')==1)
				{
					unset($triggers['subscribe']);
					unset($triggers['unsubscribe']);
					unset($triggers['ares_sent']);
					$privileges = ' AND (`trigger` != "subscribe" AND `trigger` != "unsubscribe" AND `trigger` != "ares_sent") ';
				}
			}
			
			//Get 'From email' from brand
			$q = 'SELECT from_email FROM apps WHERE id = '.get_app_info('app');
			$r = mysqli_query($mysqli, $q);
			if ($r) while($row = mysqli_fetch_array($r)) $from_email = $row['from_email'];
			
			//loader
			$loader = get_app_info('dark_mode') ? 'loader-dark.gif' : 'loader-light.gif';
			$loader = get_app_info('path').'/img/'.$loader;
			
			//border color
			$border_color = get_app_info('dark_mode') ? '3a3a3a' : 'cccccc';
			
			//get list of lists
			$lists = get_lists($app);
    	?>
    	
		<!-- Add rule form -->
    	<form action="<?php echo get_app_info('path')?>/includes/rules/save.php" method="POST" accept-charset="utf-8" class="form-vertical " enctype="multipart/form-data" id="rules-form">
	    	<!-- Trigger drop down -->
	    	<div class="dropdown rule-dropdown" id="trigger-dropdown">
				<button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
					<span class="dropdown-label first-dropdown">
						<i class="icon icon-magic"></i> <?php echo _('Create a new rule');?>
					</span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li class="dropdown-header"><?php echo _('Select a trigger');?></li>
					<li class="divider"></li>
					<?php 
						foreach($triggers as $key => $value)
						{
							echo '<li><a href="javascript:void(0)" class="trigger" data-trigger="'.$key.'" title="">'.$value.'</a></li>';
						}
					?>
				</ul>
			</div>
			
			<!-- List drop down -->
			<div class="dropdown rule-dropdown" id="list-dropdown">
				<span class="right-arrow">&rarr;</span>
				<button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
					<span class="dropdown-label list-dropdown">
						<i class="icon  icon-align-justify"></i> <?php echo _('Select a list');?>
					</span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<?php echo $lists;?>
				</ul>
			</div>
			
			<!-- Autoresponders drop down -->
			<div class="dropdown rule-dropdown" id="ares-dropdown">
				<span class="right-arrow">&rarr;</span>
				<button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
					<span class="dropdown-label ares-dropdown">
						<i class="icon icon-group"></i> <?php echo _('Select an autoresponder');?>
					</span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu"></ul>
			</div>
			
			<!-- Action drop down -->
			<div class="dropdown rule-dropdown" id="action-dropdown">
				<span class="right-arrow">&rarr;</span>
				<button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
					<span class="dropdown-label action-dropdown">
						<i class="icon  icon-share-alt"></i> <?php echo _('Select an action');?>
					</span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li class="dropdown-header"><?php echo _('Actions');?></li>
					<li class="divider"></li>
					<?php 
						foreach($actions as $key => $value)
						{
							echo '<li><a href="javascript:void(0)" class="action" data-action="'.$key.'" title="">'.$value.'</a></li>';
						}
					?>
				</ul>
			</div>
			
			<!-- Webhook text field -->
	    	<div class="control-group" id="webhook_field">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="webhook_url" name="webhook_url" placeholder="<?php echo _('eg. https://your_webhook_url');?>" style="height: 24px;">
	            </div>
	        </div>
	        
	        <!-- Email text field -->
	    	<div class="control-group" id="email_field">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="email_address" name="email_address" placeholder="<?php echo _('eg.').' '.$from_email;?>" style="height: 24px;">
	            </div>
	        </div>
	        
	        <!-- Unsub list drop down -->
			<div class="dropdown rule-dropdown" id="unsub-list-dropdown">
				<button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
					<span class="dropdown-label unsub-list-dropdown">
						<i class="icon icon-align-justify"></i> <?php echo _('Select a list to unsubscribe');?>
					</span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<?php echo $lists;?>
				</ul>
			</div>
			
			<br/>
	        
	        <input type="hidden" name="app" id="app" value="<?php echo get_app_info('app');?>">
	        <input type="hidden" name="trigger" id="trigger">
	        <input type="hidden" name="list" id="list">
	        <input type="hidden" name="ares" id="ares">
	        <input type="hidden" name="action" id="action">
	        <input type="hidden" name="unsubscribe_list_id" id="unsubscribe_list_id">
	        
	        <div id="rule-form-bottom-btns" style="display:none;">
		        <button type="submit" class="btn btn-inverse" id="add-btn"><i class="icon icon-plus"></i> <?php echo _('Add');?></button> 
		        <a href="javascript:void(0)" id="close-rule-btn"><span class="icon icon-remove-sign"></span> <?php echo _('Cancel');?></a>
	        </div>
	        
	    </form>
	    <!-- Add rule form -->
	    
	    <script type="text/javascript">
		    $(document).ready(function() {
			    //Add button
			    $("#add-btn").click(function(e){
				    e.preventDefault();
				    
				    //Validate form
				    if($("#webhook_field").is(":visible"))
				    {
					    if($("#webhook_url").val()!="")
					    {
						    $("#webhook_url").css("border-color", "#<?php echo $border_color;?>");
					    	$("#rules-form").submit();
					    }
					    else	 $("#webhook_url").css("border-color", "red");   	
				    }
				    if($("#email_field").is(":visible"))
				    {
					    if($("#email_address").val()!="")
					    {
						    $("#email_address").css("border-color", "#<?php echo $border_color;?>");
					    	$("#rules-form").submit();
					    }
					    else	 $("#email_address").css("border-color", "red");   	
				    }
				    if($("#unsub-list-dropdown").is(":visible"))
				    {
					    if($("#unsubscribe_list_id").val()!="")
					    {
						    $("#unsub-list-dropdown button").css("border-color", "#<?php echo $border_color;?>");
					    	$("#rules-form").submit();
					    }
					    else	 $("#unsub-list-dropdown button").css("border-color", "red");   	
				    }
			    });
			    
			    //Close rule form
				$("#close-rule-btn").click(function(){
					$("#rules-form").removeClass("well");
					$("#close-rule-btn").show();
					$("#add-btn").hide();
					$("#rule-form-bottom-btns").hide();
					
					//Reposition webhooks log button
					$("#webhooks-log-btn").css("margin", "-40px 20px 0 0");
					
					//Reset all dropdown titles
					$(".first-dropdown").html('<i class="icon icon-magic"></i> <?php echo _('Create a new rule');?>');
					$(".list-dropdown").html('<i class="icon icon-align-justify"></i> <?php echo _('Select a list');?>');
					$(".ares-dropdown").html('<i class="icon icon-group"></i> <?php echo _('Select an autoresponder');?>');
					$(".action-dropdown").html('<i class="icon icon-share-alt"></i> <?php echo _('Select an action');?>');
					$(".unsub-list-dropdown").html('<i class="icon icon-align-justify"></i> <?php echo _('Select a list to unsubscribe');?>');
					$("#webhook_url").val("");
					$("#email_address").val("");
					
					//Hide all drop downs and text fields
					$("#list-dropdown, #ares-dropdown, #action-dropdown, #webhook_field, #email_field, #unsub-list-dropdown").hide();
					
					//reset all hidden fields
			    	$("#trigger, #list, #ares, #action, #unsubscribe_list_id").val("");
				});
			    
			    //Create rule dropdown click
		    	$(".trigger").click(function(){					
			    	//Add background and fade in 'Cancel' button
			    	$("#rules-form").addClass("well");
			    	$("#add-btn").hide();
			    	$("#close-rule-btn").show();
			    	$("#rule-form-bottom-btns").fadeIn();
			    	
			    	//Set drop down title names
			    	$(".first-dropdown").html($(this).html());
			    	$(".list-dropdown").html('<i class="icon  icon-align-justify"></i> <?php echo _('Select a list');?>');
			    	$(".ares-dropdown").html('<i class="icon  icon-group"></i> <?php echo _('Select an autoresponder');?>');
			    	$(".action-dropdown").html('<i class="icon icon-share-alt"></i> <?php echo _('Select an action');?>');
			    	$(".unsub-list-dropdown").html('<i class="icon icon-align-justify"></i> <?php echo _('Select a list to unsubscribe');?>');
					$("#webhook_url").val("");
					$("#email_address").val("");
			    	
			    	//Update hidden fields
			    	$("#trigger").val($(this).data("trigger"));
			    	$("#list").val("");
			    	$("#ares").val("");
			    	$("#action").val("");
			    	$("#unsubscribe_list_id").val("");
					
					//Reposition webhooks log button
					$("#webhooks-log-btn").css("margin", "0px 0 40px 0");
			    	
			    	//Hide action fields
			    	$("#webhook_field, #email_field, #unsub-list-dropdown").hide();
			    	
			    	//If trigger is not 'subscribe', hide 'Unsubscribe from list' item from 'Actions' dropdown list
			    	if($("#trigger").val() != 'subscribe')
				    	$('#action-dropdown ul.dropdown-menu li:last-child').hide();
				    //Otherwise, show hide 'Unsubscribe from list' item
				    else
				    	$('#action-dropdown ul.dropdown-menu li:last-child').show();
			    	
			    	//If trigger requires list selection
			    	if(
			    		$("#trigger").val()=="subscribe" ||
			    		$("#trigger").val()=="unsubscribe" ||
			    		$("#trigger").val()=="ares_sent"
			    	)
			    	{
				    	//show list drop down
				    	$("#list-dropdown").fadeIn();
				    	$("#ares-dropdown").hide();
				    	$("#action-dropdown").hide();
				    }
				    //If trigger does not require list selection
				    else if(
					    $("#trigger").val()=="campaign_sending" ||
					    $("#trigger").val()=="campaign_sent"
				    )
				    {
					    //show actions drop down
					    $("#list-dropdown").hide();
					    $("#ares-dropdown").hide();
				    	$("#action-dropdown").fadeIn();
				    }
		    	});
		    	
		    	//List dropdown click
		    	$("#list-dropdown .list").click(function(){
			    	list_id = $(this).data("list-id");
			    	
			    	if(list_id!="")
			    	{
				    	//Set list dropdown name and hidden field
				    	$("#list").val(list_id);
				    	$(".list-dropdown").html($(this).html());
				    	
				    	//Hide and reset all drop downs and text fields
						$("#action-dropdown, #webhook_field, #email_field, #unsub-list-dropdown").hide();
						$(".unsub-list-dropdown").html('<i class="icon icon-align-justify"></i> <?php echo _('Select a list to unsubscribe');?>');
						$(".action-dropdown").html('<i class="icon icon-share-alt"></i> <?php echo _('Select an action');?>');
						$("#webhook_url").val("");
						$("#email_address").val("");
						$("#add-btn").hide();
						
						//reset all hidden fields
				    	$("#unsubscribe_list_id").val("");
				    	
				    	//If trigger is for autoresponder
				    	if($("#trigger").val()=="ares_sent")
				    	{
					    	//Show ares dropdown, reset it
					    	$("#ares-dropdown").fadeIn();
					    	$(".ares-dropdown").html('<?php echo '<img src="'.$loader.'" style="width:16px;"/> '._('Loading autoresponders..');?>');
					    	$("#ares").val("");
					    	
					    	//Load autoresponders from chosen list
					    	$.post("includes/rules/get_ares.php", { list: list_id },
				    		  function(data) {
				    		      if(data)
				    		      {
				    		      	$(".ares-dropdown").html('<i class="icon  icon-group"></i> <?php echo _('Select an autoresponder');?>');
									$("#ares-dropdown ul.dropdown-menu").html(data);
									$("#list").val("");
									
									//Ares dropdown click
							    	$(".ares").click(function(){
								    	ares_id = $(this).data("ares-id");
								    	
								    	if(ares_id!="")
								    	{
									    	//Set drop down title and hidden field
									    	$(".ares-dropdown").html($(this).html());
									    	$("#ares").val(ares_id);
									    	
									    	//Show actions dropdown
									    	$("#action-dropdown").fadeIn();
									    }
							    	});
				    		      }
				    		      else alert("Sorry, unable to load autoresponders. Please try again later!");
				    		  }
				    		);
					    }
					    //Otherwise, show actions drop down
					    else $("#action-dropdown").fadeIn();
					}
		    	});
		    	
		    	//Action dropdown click
		    	$(".action").click(function(){
			    	//Set drop down title and hidden field
			    	$(".action-dropdown").html($(this).html());
			    	$("#action").val($(this).data("action"));
			    	
			    	//Hide all action fields
			    	$("#webhook_field").hide();
			    	$("#email_field").hide();
			    	$("#unsub-list-dropdown").hide();
			    	
			    	//Reset all hidden fields
			    	$("#unsubscribe_list_id").val("");
			    	$(".unsub-list-dropdown").html('<i class="icon icon-align-justify"></i> <?php echo _('Select a list to unsubscribe');?>');
			    	
			    	//show 'Add' button
			    	$("#add-btn").show();
			    	
			    	//Show 'webhook' or 'email address' textfield or 'list' dropdown
			    	if($(this).data("action")=='webhook')
			    	{
				    	$("#webhook_field").fadeIn();
				    	$("#webhook_url").css("border-color", "#<?php echo $border_color;?>");
				    	$("#webhook_url").select();
				    }
				    else if($(this).data("action")=='notify')
				    {
				    	$("#email_field").fadeIn();
				    	$("#email_address").css("border-color", "#<?php echo $border_color;?>");
				    	$("#email_address").val("<?php echo $from_email?>");
				    	$("#email_address").select();
				    }
				    else if($(this).data("action")=='unsub_from_list')
				    {
				    	$("#unsub-list-dropdown").fadeIn();
				    	$("#unsub-list-dropdown button").css("border-color", "#<?php echo $border_color;?>");
				    }
		    	});
		    	
		    	//Unsubscribe from list dropdown click
		    	$("#unsub-list-dropdown .list").click(function(){
			    	list_id = $(this).data("list-id");
			    	$("#unsub-list-dropdown button").css("border-color", "#<?php echo $border_color;?>");
			    	
			    	if(list_id!="")
			    	{
				    	$("#unsubscribe_list_id").val(list_id);
				    	$(".unsub-list-dropdown").html($(this).html());
				    	
					}
		    	});
		    });
	    </script>
	    
		<!-- <a href="<?php echo get_app_info('path');?>/webhooks-log?i=<?php echo get_app_info('app');?>" class="btn" id="webhooks-log-btn"><span class="icon icon-reorder"></span> Webhooks log</a> -->
		
	    <br/>
    	
    	<!-- Existing rules -->
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
			  <th><?php echo _('Type');?></th>
			  <th><?php echo _('Name');?></th>
		      <th><?php echo _('Trigger');?></th>
		      <th><?php echo _('Action');?></th>
		      <th><?php echo _('Edit');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	
		  	<?php 
			  	//Pagination
		  		$limit = get_app_data('campaign_report_rows');
				$total_subs = totals(get_app_info('app'));
				$total_pages = ceil($total_subs/$limit);
				$p = isset($_GET['p']) ? $_GET['p'] : null;
				$offset = $p!=null ? ($p-1) * $limit : 0;
				
				//Get existing rules
			  	$q = 'SELECT * FROM rules WHERE brand='.get_app_info('app').' '.$privileges.' ORDER BY `trigger` ASC LIMIT '.$offset.','.$limit;
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = $row['id'];
			  			$trigger = $row['trigger'];
			  			$action = $row['action'];
			  			$list = $row['list'];
			  			$app = $row['app'];
			  			$ares_id = $row['ares_id'];
			  			$endpoint = $row['endpoint'];
			  			$notification_email = $row['notification_email'];
			  			$unsubscribe_list_id = $row['unsubscribe_list_id'];
			  			
			  			//Section labels
			  			$section_label = '';
			  			$section_label = $list!='' ? _('List') : $section_label;
			  			$section_label = $app!='' ? _('Campaign') : $section_label;
			  			$section_label = $ares_id!='' ? _('Autoresponder') : $section_label;
			  			
			  			//Section names
			  			$section_name = '';
			  			$section_name = $list!='' ? '<a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$list.'">'.get_data('name', 'lists', $list).'</a>' : $section_name;
			  			$section_name = $app!='' ? '<a href="'.get_app_info('path').'/app?i='.get_app_info('app').'">Any campaign in this brand</a>' : $section_name;
			  			$section_name = $ares_id!='' ? '<a href="'.get_app_info('path').'/autoresponders-emails?i='.get_app_info('app').'&a='.$ares_id.'">'.get_data('name', 'ares', $ares_id).'</a>' : $section_name;
			  			
			  			//Trigger name
			  			$trigger_name = $triggers[$trigger];
			  			
			  			//Action name
			  			$action_name = $actions[$action];
			  			
			  			//Get list name
			  			$list_name = $unsubscribe_list_id!='' ? get_data('name', 'lists', $unsubscribe_list_id) : '';
			  			
			  			//Get ares name
			  			$ares_name = $ares_id!='' ? get_data('name', 'ares', $ares_id) : '';
			  			
			  			//Get list name of ares
			  			if($ares_id!='')
			  			{
				  			$ares_name = get_data('name', 'ares', $ares_id);
				  			$ares_list_id = get_data('list', 'ares', $ares_id);
				  			$list_name = get_data('name', 'lists', $ares_list_id);
			  			}
			  			else $ares_name = '';
			  			
			  			//To hide or show webhook info icon button
			  			$show_hide_webhook_icon = $action=='webhook' ? '' : 'display:none';
			  						  			
			  			echo '
		  				<tr id="'.$id.'">
			  			  <td><span class="label label-info">'.$section_label.'</span></td>
			  			  <td>'.$section_name.'</td>
					      <td><span class="label trigger-action">'.$trigger_name.'</span></td>
					      <td>
					      	<span class="label trigger-action">'.$action_name.'</span>
					      	<a href="#webhooks-info" title="See webhook information" data-trig="'.$trigger.'" data-lid="'.$list.'" data-toggle="modal" class="webhooks-info" style="'.$show_hide_webhook_icon.'"><span class="icon icon-info-sign trigger-webhook-icon"></span></a>
					      	<br/>
					      	
					      	<!-- Webhook text field -->
					    	<div class="control-group webhook_field-edit well">
						    	<div class="controls">
					              <input type="text" class="input-xlarge" class="webhook_url" id="webhook_url-'.$id.'" name="webhook_url" placeholder="eg. '._('https://your_webhook_url').'" style="height: 24px;" value="'.$endpoint.'"> 
					            </div>
					            <!-- Save / Close buttons -->
								<div class="save-close-btns">
									<button type="submit" class="btn save-webhook-btn-'.$id.'">
										<i class="icon icon-ok" id="webhook-ok-'.$id.'"></i>
										<img src="'.$loader.'" class="edit-loader" id="webhook-loader-'.$id.'"/>
										 '._('Save').'
									</button> 
									<a href="javascript:void(0)" class="cancel-webhook-btn-'.$id.'" style="text-decoration: none; margin-left: 10px;"><span class="icon icon-remove-sign"></span> '._('Close').'</a>
								</div>
					        </div>
					        
					        <!-- Email text field -->
					    	<div class="control-group email_field-edit well">
						    	<div class="controls">
					              <input type="text" class="input-xlarge" class="email_address" id="email_address-'.$id.'" name="email_address" placeholder="'._('eg.').' '.$from_email.'" style="height: 24px;" value="'.$notification_email.'"> 
					            </div>
					            <!-- Save / Close buttons -->
								<div class="save-close-btns">
									<button type="submit" class="btn save-email-btn-'.$id.'">
										<i class="icon icon-ok" id="email-ok-'.$id.'"></i>
										<img src="'.$loader.'" class="edit-loader" id="email-loader-'.$id.'"/> 
										'._('Save').'
										</button> 
									<a href="javascript:void(0)" class="cancel-email-btn-'.$id.'" style="text-decoration: none; margin-left: 10px;"><span class="icon icon-remove-sign"></span> '._('Close').'</a>
								</div>
					        </div>
					        
					        <!-- List drop down -->
							<div class="dropdown rule-dropdown list-dropdown-edit well" style="margin-bottom: 20px;">
								<button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
									<span class="dropdown-label list-dropdown" id="list-dropdown-'.$id.'">
										<i class="icon icon-align-justify"></i> '.$list_name.'
									</span>
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" id="list_dropdown-'.$id.'">
									'.$lists.'
								</ul> 
								<img src="'.$loader.'" id="list-loader-'.$id.'" style="float:right; width:16px; margin: 8px 0 0 5px; display:none;"/>
								<br/>
								<a href="javascript:void(0)" class="cancel-list-btn-'.$id.'" style="float:left; text-decoration: none; margin: 10px 0 0 10px;"><span class="icon icon-remove-sign"></span> '._('Close').'</a>
							</div>
			
					      </td>
					      <td>
					        <a href="javascript:void(0)" title="'._('Edit rule?').'" id="edit-btn-'.$id.'" data-action-field="">
						      	<span class="icon icon-pencil"></span>
						    </a>
					      </td>
					      <td>
					      	<a href="#delete-rule" title="'._('Delete rule?').'" id="delete-btn-'.$id.'" data-toggle="modal">
						      	<span class="icon icon-trash"></span>
					      	</a>
					      </td>
					    </tr>
					    
					    <script type="text/javascript">
					        $("#delete-btn-'.$id.'").click(function(e){
								e.preventDefault(); 
								$("#delete-rule-btn").attr("data-id", '.$id.');
								$("#delete-text").val("");
								$("#delete-warning").text("'._('This will permanently delete the rule.').'");
							});
							$("#edit-btn-'.$id.'").click(function(e){
								e.preventDefault(); 
								
								//hide all fields
								$(".webhook_field-edit, .email_field-edit, .list-dropdown-edit").slideUp("fast");
								';
								if($action=='webhook')
									echo '
									if($("#'.$id.' .webhook_field-edit").is(":hidden"))
									{
										$("#'.$id.' .webhook_field-edit").slideDown("fast", function(){
											$(this).find("div input").select();
										});
									}
									';
								else if($action=='notify')
									echo '
									if($("#'.$id.' .email_field-edit").is(":hidden"))
									{
										$("#'.$id.' .email_field-edit").slideDown("fast", function(){
											$(this).find("div input").select();
										});
									}
									';
								else if($action=='unsub_from_list')
									echo '
									if($("#'.$id.' .list-dropdown-edit").is(":hidden"))
									{
										$("#'.$id.' .list-dropdown-edit").slideDown("fast", function(){
											$(this).find("div input").select();
										});
									}
									';	
						echo'
							});
							
							$(".save-webhook-btn-'.$id.'").click(function(){
								edit_rule("webhook", "#webhook_url-'.$id.'", '.$id.');
							});
							$(".save-email-btn-'.$id.'").click(function(){
								edit_rule("notify", "#email_address-'.$id.'", '.$id.');
							});
							$("#webhook_url-'.$id.'").keydown(function(e){
								if(e.keyCode == 13)
								{
									edit_rule("webhook", "#webhook_url-'.$id.'", '.$id.');
								}
							});
							$("#email_address-'.$id.'").keydown(function(e){
								if(e.keyCode == 13)
								{
									edit_rule("notify", "#email_address-'.$id.'", '.$id.');
								}
							});
							$(".cancel-webhook-btn-'.$id.'").click(function(){
								$(".webhook_field-edit").slideUp("fast");
							});
							$(".cancel-email-btn-'.$id.'").click(function(){
								$(".email_field-edit").slideUp("fast");
							});
							$(".cancel-list-btn-'.$id.'").click(function(){
								$(".list-dropdown-edit").slideUp("fast");
							});
							
							$("#list_dropdown-'.$id.' .list").click(function(){
								//save list changes
								$("#list-loader-'.$id.'").fadeIn();
								selected_html = $(this).html();
								lid = $(this).data("list-id");
								$.post("'.get_app_info('path').'/includes/rules/edit.php", { id: '.$id.', action: "unsub_from_list", val: lid },
								  function(data) {
								      if(data)
								      {
									      $("#list-dropdown-'.$id.'").html(selected_html);
									      $("#list-loader-'.$id.'").hide();
									      $(".list-dropdown-edit").slideUp("fast");
								      }
								      else alert("Sorry, unable to save. Please try again later!");
								  }
								);
							});
							
							//Save edited rule change
							function edit_rule(type, element, id)
							{
								element = $(element);
								
								//save changes
								if(element.val()!="")
								{
									element.css("border-color", "#'.$border_color.'");
									
									//Show loading icon
									$("#email-ok-"+id).hide();
									$("#webhook-ok-"+id).hide();
									$("#email-loader-"+id).show();
									$("#webhook-loader-"+id).show();
									
									//HTTP POST
									$.post("'.get_app_info('path').'/includes/rules/edit.php", { id: id, action: type, val: element.val() },
									  function(data) {
									      if(data)
									      {
										      //Show ok icon
										      $("#email-ok-"+id).show();
											  $("#webhook-ok-"+id).show();
											  $("#email-loader-"+id).hide();
											  $("#webhook-loader-"+id).hide();
											  
											  //Close field dialog
											  $(".webhook_field-edit").slideUp("fast");
											  $(".email_field-edit").slideUp("fast");
									      }
									      else alert("Sorry, unable to save. Please try again later!");
									  }
									);
								}
								else
								{
									element.css("border-color", "red")
								}
							}
							
					   </script>
					    ';
			  	    }  
			  	}
			  	else
			  	{
				  	echo '
				  		<tr>
					      <td>'._('You haven\'t created any rules yet').'.</td>
					      <td></td>
					      <td></td>
					      <td></td>
					      <td></td>
					      <td></td>
					    </tr>
				  	';
			  	}
		  	?>
		    
		  </tbody>
		</table>
		<!-- Existing rules -->
		
		<?php pagination($limit); ?>
		
		<!-- Delete -->
		<div id="delete-rule" class="modal hide fade">
		  <div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		    <h3><?php echo _('Delete rule');?></h3>
		  </div>
		  <div class="modal-body">
		    <p><span id="delete-warning"></span> <?php echo _('Confirm delete this rule?');?></p>
		  </div>
		  <div class="modal-footer">
			<?php if(get_app_info('strict_delete')):?>
			<input autocomplete="off" type="text" class="input-large" id="delete-text" name="delete-text" placeholder="<?php echo _('Type the word');?> DELETE" style="margin: -2px 7px 0 0;"/>
			<?php endif;?>
			
		    <a href="javascript:void(0)" id="delete-rule-btn" data-id="" class="btn btn-primary"><?php echo _('Delete');?></a>
		  </div>
		</div>
		
		<!-- Webhooks info modal window -->
		<div id="webhooks-info" class="modal hide fade">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal">&times;</button>
		  <h3><?php echo _('Webhook information');?></h3>
		</div>
		<div class="modal-body">
			<div id="subscribe-content" class="webhooks-info-content">
			    <p><?php echo _('When a user subscribes to your list, the following data will be sent to your webhook URL via HTTP POST:');?></p>
			    <ul class="well webhook-data-list sub_unsub_list">
				    <li><img src="<?php echo $loader;?>" style="width:16px;"/></li>
			    </ul>
			</div>
			<div id="unsubscribe-content" class="webhooks-info-content">
			    <p><?php echo _('When a user unsubscribes from your list, the following data will be sent to your webhook URL via HTTP POST:');?></p>
			    <ul class="well webhook-data-list sub_unsub_list">
				  <li><img src="<?php echo $loader;?>" style="width:16px;"/></li>
			    </ul>
			</div>
			<div id="campaign_sent-content" class="webhooks-info-content">
			    <p><?php echo _('When a campaign is sent, the following data will be sent to your webhook URL via HTTP POST:');?></p>
			    <ul class="well webhook-data-list">
				  <li><code>trigger</code> campaign_sent</li>
				  <li><code>campaign_title</code> <?php echo _('Title of the campaign');?></li>
				  <li><code>subject</code> <?php echo _('Subject of the campaign');?></li>
				  <li><code>from_name</code> <?php echo _('\'From name\' for the campaign');?></li>
				  <li><code>from_email</code> <?php echo _('\'From email\' address for the campaign');?></li>
				  <li><code>reply_to</code> <?php echo _('\'Reply to\' email address for the campaign');?></li>
				  <li><code>sent</code> <?php echo _('Date & time the campaign was sent');?></li>
				  <li><code>no_of_recipients</code> <?php echo _('Number of recipients the campaign was sent to');?></li>
				  <li><code>webversion</code> <?php echo _('Web version URL of the campaign');?></li>
				  <li><code>campaign_id</code> <?php echo _('Campaign ID of the campaign');?></li>
				  <li><code>brand_id</code> <?php echo _('Brand ID of the campaign');?></li>
				  <li><code>report_url</code> <?php echo _('URL of the campaign report');?></li>
			    </ul>
			</div>
			<div id="campaign_sending-content" class="webhooks-info-content">
			    <p><?php echo _('When a campaign starts sending, the following data will be sent to your webhook URL via HTTP POST:');?></p>
			    <ul class="well webhook-data-list">
				  <li><code>trigger</code> campaign_sending</li>
				  <li><code>campaign_title</code> <?php echo _('Title of the campaign');?></li>
				  <li><code>subject</code> <?php echo _('Subject of the campaign');?></li>
				  <li><code>from_name</code> <?php echo _('\'From name\' for the campaign');?></li>
				  <li><code>from_email</code> <?php echo _('\'From email\' address for the campaign');?></li>
				  <li><code>reply_to</code> <?php echo _('\'Reply to\' email address for the campaign');?></li>
				  <li><code>sent</code> <?php echo _('Date & time the campaign was sent');?></li>
				  <li><code>no_of_recipients</code> <?php echo _('Number of recipients the campaign was sent to');?></li>
				  <li><code>webversion</code> <?php echo _('Web version URL of the campaign');?></li>
				  <li><code>campaign_id</code> <?php echo _('Campaign ID of the campaign');?></li>
				  <li><code>brand_id</code> <?php echo _('Brand ID of the campaign');?></li>
				  <li><code>report_url</code> <?php echo _('URL of the campaign report');?></li>
			    </ul>
			</div>
			<div id="ares_sent-content" class="webhooks-info-content">
			    <p><?php echo _('When the autoresponder sends an email, the following data will be sent to your webhook URL via HTTP POST:');?></p>
			    <ul class="well webhook-data-list">
				  <li><code>trigger</code> ares_sent</li>
				  <li><code>subject</code> <?php echo _('Subject of the autoresponder email');?></li>
				  <li><code>from_name</code> <?php echo _('\'From name\' for the autoresponder');?></li>
				  <li><code>from_email</code> <?php echo _('\'From email\' address for the autoresponder');?></li>
				  <li><code>reply_to</code> <?php echo _('\'Reply to\' email address for the autoresponder');?></li>
				  <li><code>to_name</code> <?php echo _('Name of the subscriber the autoresponder was sent to');?></li>
				  <li><code>to_email</code> <?php echo _('Email of the subscriber the autoresponder was sent to');?></li>
				  <li><code>sent</code> <?php echo _('Date & time the autoresponder was sent');?></li>
				  <li><code>webversion</code> <?php echo _('Web version URL of the campaign');?></li>
				  <li><code>list_name</code> <?php echo _('Name of the list the autoresponder belongs to');?></li>
				  <li><code>ares_name</code> <?php echo _('Name of the autoresponder');?></li>
				  <li><code>list_id</code> <?php echo _('Encrypted list ID of the list the autoresponder belongs to');?></li>
				  <li><code>ares_id</code> <?php echo _('ID of autoresponder');?></li>
				  <li><code>ares_email_id</code> <?php echo _('ID of the autoresponder email');?></li>
				  <li><code>report_url</code> <?php echo _('URL of the autoresponder report');?></li>				  
			    </ul>
			</div>
		</div>
		<div class="modal-footer">
		  <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign" style="margin-top: 5px;"></i> <?php echo _('Close');?></a>
		</div>
		</div>
		
		<script type="text/javascript">
			$(document).ready(function() {
				$("#delete-rule-btn").click(function(e){
					e.preventDefault(); 
					
					<?php if(get_app_info('strict_delete')):?>
					if($("#delete-text").val()=='DELETE'){
					<?php endif;?>
					
						$.post("includes/rules/delete.php", { rule_id: $(this).attr("data-id") },
						  function(data) {
						      if(data)
						      {
						        $("#delete-rule").modal('hide');
						        $("#"+$("#delete-rule-btn").attr("data-id")).fadeOut(); 
						      }
						      else alert("<?php echo _('Sorry, unable to delete. Please try again later!')?>");
						  }
						);
					
					<?php if(get_app_info('strict_delete')):?>
					}
					else alert("<?php echo _('Type the word');?> DELETE");
					<?php endif;?>
				});
				
				$(".webhooks-info").click(function(){
					
					trigger = $(this).data("trig");
					$(".webhooks-info-content").hide();
					
					if(trigger=='subscribe' || trigger=='unsubscribe')
					{
						$("#"+trigger+"-content").show();
						$(".sub_unsub_list").html("<li><img src=\"<?php echo $loader;?>\" style=\"width:16px;\"/></li>");
						
						//Get custom fields
						$.post("<?php echo get_app_info('path')?>/includes/rules/get_custom_fields.php", { list: $(this).data("lid"), type: trigger },
				  		  function(data) {
				  		      if(data)
				  		      {
				  		      	$(".sub_unsub_list").html(data);
				  		      }
				  		  }
				  		);
					}
					else if(trigger=='campaign_sent')
						$("#campaign_sent-content").show();
					else if(trigger=='campaign_sending')
						$("#campaign_sending-content").show();
					else if(trigger=='ares_sent')
						$("#ares_sent-content").show();
						
				});
			});
		</script>
		
    </div>   
</div>
<?php include('includes/footer.php');?>
