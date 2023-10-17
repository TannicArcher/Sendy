<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/reports/main.php');?>
<script src="js/highcharts/highcharts.js?2"></script>
<?php if(get_app_info('dark_mode')):?><script src="js/highcharts/themes/high-contrast-dark.src.js"></script><?php endif;?>
<?php
	check_simplexml();
	if(get_app_info('is_sub_user')) 
	{
		echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/app?i='.get_app_info('restricted_to_app').'"</script>';
		exit;
	}
?>
<div class="row-fluid"> 
	<div class="span2">
		<div class="sidebar-nav sidebar-box" style="padding: 19px;">
			<?php include('includes/helpers/ses-quota.php');?>
		</div>
	</div>
    <div class="span10">
	    
	    <?php if(get_app_info('s3_key')!='' && get_app_info('s3_secret')!=''): ?>
		    <?php 
				//Check if login email is verified in Amazon SES console
				$verify_login_email = verify_identity(get_app_info('email'));
				if($verify_login_email == 'unverified')
				{				
					//Verify email address
					require_once('includes/helpers/ses.php');
					$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
					$ses->verifyEmailAddress(get_app_info('email'));
			
					echo '<div class="alert alert-info">
							<p><span class="icon icon-ok"></span> <strong>'._('Please verify your login email address').'</strong></p>
							<p>'._('A verification email has been sent to your main login email address with a confirmation link to complete the verification. Please click the link to complete the verification, then refresh this page and this message should disappear.').'</p>
							<p>'._('It is necessary to verify your main login email address for various reasons, one of them is to enable password reset emails to be sent to you should you forget your password in future.').'</p>
						</div>';    
				}
				else if($verify_login_email == 'pending')
				{
					echo '<div class="alert alert-info">
							<p><span class="icon icon-ok"></span> <strong>'._('Please verify your login email address').'</strong></p>
							<p>'._('Your login email address is still pending verification. Please click the link in the verification email you received from Amazon to complete the verification, then refresh this page and this message should disappear.').'</p>
							<p id="click-to-verify-copy">'._('If you want to re-send the verification email').', <a href="javascript:void(0)" id="click-to-verify-btn">'._('please click here').'</a>.</p>
							<p>'._('It is necessary to verify your main login email address for various reasons, one of them is to enable password reset emails to be sent to you should you forget your password in future.').'</p>
						</div>';
				}
		    ?>
			<script type="text/javascript">
				$("#click-to-verify-btn").click(function(e){
	    			e.preventDefault();
	    			$("#click-to-verify-copy").html("<?php echo _('Please wait..');?>");
	    			$.post("<?php echo get_app_info('path')?>/includes/app/verify-login-email.php", { login_email: "<?php echo get_app_info('email');?>" },
					  function(data) {
					      if(data)
					      {
					      	if(data=="success")
					      		$("#click-to-verify-copy").html("<?php echo _('The verification email has been re-sent to your main login email address.');?>");
					      	else if(data=="failed")
					      		$("#click-to-verify-copy").html("<?php echo _('Unable to send the verification email. Please try again later.');?>");
					      }
					      else
					      {
					      	alert("<?php echo _('Sorry, unable to verify email address. Please try again later!');?>");
					      }
					  }
					);
				});
			</script>
		<?php endif;?>
	    
    	<h2><?php echo _('Brands');?></h2><br/>
	    
		  	<?php 
			  	$i = 1;
			  	$limit = get_app_info('brands_rows');
				$total_brands = total_brands();
				$total_pages = ceil($total_brands/$limit);
				$p = isset($_GET['p']) ? $_GET['p'] : null;
				$offset = $p!=null ? ($p-1) * $limit : 0;
			  	
			  	$q = 'SELECT * FROM apps WHERE userID = '.get_app_info('userID').' ORDER BY app_name ASC LIMIT '.$offset.','.$limit;
			  	$r = mysqli_query($mysqli, $q);
			  	$no_of_brands = mysqli_num_rows($r);
			  	if ($r && $no_of_brands > 0)
			  	{
				  	echo '
				  	
				  	<div style="clear:both; margin-bottom:30px;">
					  	<button class="btn" onclick="window.location=\''.get_app_info('path').'/new-brand\'"><i class="icon-plus-sign"></i> '._('Add a new brand').'</button>
					  	
					  	<form class="form-search" action="'.get_app_info('path').'/search-all-brands" method="GET" style="float:right;">
							<input type="text" class="input-medium search-query" name="s" style="width: 200px;">
							<button type="submit" class="btn"><i class="icon-search"></i> '._('Search brands').'</button>
						</form>
					</div>
    	
			    	<!-- Auto select encrypted listID -->
				  	<script type="text/javascript">
				  		$(document).ready(function() {
							$(".brand-id").mouseover(function(){
								$(this).selectText();
							});
						});
					</script>
				  	
				  	<div class="row-fluid"> 
						<div class="span12">
						  	<table class="table table-striped responsive" id="brands-table">
							  <thead>
							    <tr>
							      <th>'._('ID').'</th>
							      <th>'._('Brands').'</th>
								  <th>'._('Sends via').'</th>
							      <th>'._('Quick links').'</th>
							      <th>'._('Sending limits').'</th>
							      <th>'._('Used').'</th>
							      <th>'._('Edit').'</th>
							      <th>'._('Delete').'</th>
							    </tr>
							  </thead>
							  <tbody>
				  	';
				  	
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = $row['id'];
			  			$title = $row['app_name'];
			  			$from_email = explode('@', $row['from_email']);
			  			$get_domain = $from_email[1];
			  			$allocated_quota = $row['allocated_quota'];
			  			$current_quota = $row['current_quota'];
			  			$day_of_reset = $row['day_of_reset'];
			  			$month_of_next_reset = $row['month_of_next_reset'];
			  			$year_of_next_reset = $row['year_of_next_reset'];
			  			$brand_logo_filename = $row['brand_logo_filename'];
			  			$no_expiry = $row['no_expiry'];
						$smtp_host = $row['smtp_host'];
						$smtp_port = $row['smtp_port'];
						$smtp_username = $row['smtp_username'];
						$smtp_password = $row['smtp_password'];
						$smtp_label_img = '';
						
						//Email sending service label
						if($smtp_host == 'smtp.elasticemail.com' && $smtp_port!='' && $smtp_username!='' && $smtp_password!='')
							$smtp_label_img = '<a href="javascript:void(0)" title="'._('ElasticEmail SMTP').'" class="brand-smtp-icons"><img src="'.get_app_info('path').'/img/elasticemail.png" title=""/></a>';
						else if($smtp_host == 'smtp.sendgrid.net' && $smtp_port!='' && $smtp_username!='' && $smtp_password!='')
							$smtp_label_img = '<a href="javascript:void(0)" title="'._('Sendgrid SMTP').'" class="brand-smtp-icons"><img src="'.get_app_info('path').'/img/sendgrid.png" title=""/></a>';
						else if($smtp_host == 'in-v3.mailjet.com' && $smtp_port!='' && $smtp_username!='' && $smtp_password!='')
							$smtp_label_img = '<a href="javascript:void(0)" title="'._('Mailjet SMTP').'" class="brand-smtp-icons"><img src="'.get_app_info('path').'/img/mailjet.png" title=""/></a>';
						else if(strpos($smtp_host, 'amazonaws.com') !== false && $smtp_port!='' && $smtp_username!='' && $smtp_password!='')
							$smtp_label_img = '<a href="javascript:void(0)" title="'._('Amazon SES SMTP').'" class="brand-smtp-icons"><img src="'.get_app_info('path').'/img/aws.png" title=""/></a>';
						else if($smtp_host != '' && $smtp_port!='' && $smtp_username!='' && $smtp_password!='')
							$smtp_label_img = '<a href="javascript:void(0)" title="'._('Other SMTP service').'" class="brand-smtp-icons"><span class="icon icon-envelope-alt" style="margin:-3px 0 0 5px;"></span></a>';
						else if(get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
							$smtp_label_img = '<a href="javascript:void(0)" title="'._('Amazon SES').'" class="brand-smtp-icons"><img src="'.get_app_info('path').'/img/aws.png" title=""/></a>';
						else
							$smtp_label_img = '<a href="javascript:void(0)" title="'._('No sending provider setup yet. Defaults to send via the server.').'" class="brand-smtp-icons"><span class="icon icon-minus" style="margin-left: 8px;"></span></a>';
						
			  			//Brand logo
			  			if($brand_logo_filename=='') $logo_image = 'https://www.google.com/s2/favicons?domain='.$get_domain;
			  			else $logo_image = get_app_info('path').'/uploads/logos/'.$brand_logo_filename;
			  			
			  			//Check if limit needs to be reset	
						$today_unix_timestamp = time();
						$brand_monthly_quota = $allocated_quota;
						if($brand_monthly_quota!=-1)
						{				
							//Date today
							$day_today = date("j", $today_unix_timestamp);
							$month_today = date("M", $today_unix_timestamp);
							$year_today = date("Y", $today_unix_timestamp);
							
							//Find the number of the last day of this month
							$no_of_days_this_month = cal_days_in_month(CAL_GREGORIAN, date("m", $today_unix_timestamp), $year_today);
							
							$brand_limit_resets_on = $day_of_reset>$no_of_days_this_month ? $no_of_days_this_month : $day_of_reset;
							
							//Get UNIX timestamp of 'date today' and 'date of next reset' for comparison
							$date_today_unix = strtotime($day_today.' '.$month_today.' '.$year_today);
							$date_on_reset_unix = strtotime($brand_limit_resets_on.' '.$month_of_next_reset.' '.$year_of_next_reset);
							
							//If date of reset has already passed today's date, reset current limit to 0
							if($date_today_unix>=$date_on_reset_unix)
							{
								//If today's 'day' is passed 'day_of_reset', +1 month for next reset's month
								if($day_today >= $brand_limit_resets_on) $plus_one_month = '+1 month';
								
								//Prepare day, month and year of next reset
								$month_next_unix = strtotime('1 '.$month_today.' '.$year_today.' '.$plus_one_month);
								$month_next = date("M", $month_next_unix);
								$year_next = date("Y", $month_next_unix);
								
								//If brand limits is set to 'No expiry'
								if(!$no_expiry)
								{
									//Reset current limit to 0 and set the month_of_next_reset & year_of_next_reset to the next month
									$q2 = 'UPDATE apps SET current_quota = 0, month_of_next_reset = "'.$month_next.'", year_of_next_reset = "'.$year_next.'" WHERE id = '.$id;
									$r2 = mysqli_query($mysqli, $q2);
									if($r2) 
									{
										//Set $current_quota to 0 since current_quota has been reset
										$current_quota = 0;
									}
								}
							}
						}
			  			
			  			//Prepare numbers
			  			if($allocated_quota==-1) 
			  			{
			  				$allocated_quota = '<span class="label label-success" title="'._('No limits').'">&infin;</span>';
			  				$current_quota = '<span class="label label-success" title="'._('No limits').'">&infin;</span>';
			  				$limit_type = '';
			  			}
			  			else
			  			{
				  			$allocated_quota = number_format($allocated_quota);
			  				if($current_quota>$row['allocated_quota']) $current_quota = '<span style="color:#FF0000;font-weight:bold;">'.number_format($current_quota).'</span>';
			  				else $current_quota = '<span>'.number_format($current_quota).'</span>';
			  				
			  				$limit_type = $no_expiry ? '<span class="badge">no expiry</span>' : '<span class="badge">monthly</span>';
			  			}
			  			
			  			//Get last campaign report link
			  			$last_campaign_id = get_last_campaign_data($id, 'id');
			  			$last_campaign_report_link = $last_campaign_id==0 ? get_app_info('path').'/app?i='.$id : get_app_info('path').'/report?i='.$id.'&c='.$last_campaign_id;
			  			
			  			echo '
			  			<tr id="'.$id.'">
			  				<td><span class="label brand-id">'.$id.'</span></td>
			  				<td>
			  					<p><a href="'.get_app_info('path').'/app?i='.$id.'" title="" style="float:left;"><img src="'.$logo_image.'" style="margin:-3px 5px 0 0; width:16px; height: 16px;"/>'.$title.'</a></p> 
			  				</td>
							<td>
								<p>'.$smtp_label_img.'</p>
							</td>
			  				<td>
			  					<p>
			  						<a href="'.get_app_info('path').'/app?i='.$id.'" class="btn" title="'._('All campaigns').'"><span class="icon icon-home"></span></a>
			  						<a href="'.get_app_info('path').'/create?i='.$id.'" class="btn" title="'._('New campaign').'"><span class="icon icon-edit"></span></a>
			  						<a href="'.get_app_info('path').'/list?i='.$id.'" class="btn" title="'._('View all lists').'"><span class="icon icon-align-justify"></span></a>
			  						<a href="'.get_app_info('path').'/reports?i='.$id.'" class="btn" title="'._('See reports').'"><span class="icon icon-bar-chart"></span></a>
	  						';
			  				
		  				if($last_campaign_id!=0)	
		  				{		  	
			  				//Open rate figures
			  				$open_rate = get_open_rate(get_last_campaign_data($id, 'id'));
			  				$open_rate_number = get_open_rate(get_last_campaign_data($id, 'id'), true);
			  				
			  				//Open rate pie chart 'unopened' color
			  				$pie_unopened_dark = '#565656';
			  				$pie_unopened_light = '#E8E8E8';
			  				$pie_unopened_color = get_app_info('dark_mode') ? $pie_unopened_dark : $pie_unopened_light;
			  							
	  						echo '<a href="'.$last_campaign_report_link.'" class="btn" title="'._('View report for the latest campaign').': '.get_last_campaign_data($id, 'title').'">
	  								<span id="pie-'.$id.'" class="pie_open_rate"></span> <span class="open_rate_no">'.$open_rate.'</span>
	  							 </a>';
	  						
		  					echo "
			  					<script type=\"text/javascript\">
				  					Highcharts.setOptions({
									        colors: ['#52c062', '$pie_unopened_color']
									    });
										
										chart = new Highcharts.Chart({
											chart: {
												renderTo: 'pie-$id',
												plotBackgroundColor: null,
												plotBorderWidth: null,
												plotShadow: false,
												backgroundColor: 'none',
												height: 20
											},
											title: {
												text: ''
											},
											tooltip: {
												formatter: function() {
													return '<b>'+ this.point.name +'</b>';
												}
											},
											plotOptions: {
												pie: {
													size: 17,
													borderWidth: 0,
													shadow: false,
													allowPointSelect: false,
													cursor: 'pointer',
													dataLabels: {
														enabled: false
													},
													showInLegend: false
												}
											},
											credits: {
								                enabled: false
								            },
											series: [{
												dataLabels: {
										            style:{
										                fontWeight: 'normal',
										                textOutline: '0px',
										                color: \"#797979\"
										            }
										        },
												type: 'pie',
												name: 'Opens',
												data: [
										  			['".$open_rate."',  $open_rate_number],
										  			['Not opened',   100 - $open_rate_number]
												]
											}],
											exporting: { enabled: false }
										});
								</script>
							</p>
			  				</td>";
	  					}  
	  					else echo '<a href="'.get_app_info('path').'/create?i='.$id.'" class="btn" title="'._('Create new campaign?').'">
	  								<span class="icon icon-envelope-alt"></span> <span>No campaigns yet</span>
	  							 </a>';;						
	  						
  						echo '
			  				<td>'.$allocated_quota.' '.$limit_type.'</td>
			  				<td>'.$current_quota.'</td>
			  				<td><a href="'.get_app_info('path').'/edit-brand?i='.$id.'" title="'._('Brand settings').'"><span class="icon icon-pencil"></span></a></td>
			  				<td><a href="#delete-brand" title="'._('Delete').' '.$title.'" id="delete-btn-'.$id.'" data-toggle="modal"><span class="icon icon-trash"></span></a></td>
			  				<script type="text/javascript">
					        $("#delete-btn-'.$id.'").click(function(e){
								e.preventDefault(); 
								$("#delete-brand-btn").attr("data-id", '.$id.');
								$("#brand-to-delete").text("'.$title.'");
								$("#delete-text").val("");
							});
							</script>
			  			</tr>';
			  			$i++;
			  	    }  
			  	    
			  	    echo '	</tbody>
							</table>
						
							</div>
						

						
						</div>
			  	    ';
			  	}
			  	else
			  	{
				  	echo '
				  	<div class="alert">
				  		<p><h3>'._('What are brands?').'</h3></p>
				  		<p>'._('Let\'s just say you own this company called Apple Inc and you have several products under it eg. Mac, iPhone, iPad etc. These several "child" products are what we refer to as \'brands\'.').'</p>
				  		<p>'._('Another example, if you have a company or business with different clients and you want to group them separately, you can create a \'brand\' for each.').'</p>
				  		<p>'._('Once you\'ve created a brand, you can then create email campaigns, templates, lists, import subscribers or blacklists, setup autoresponders, perform list segmentation etc in each brand you\'ve created.').'</p>
				  		<p>'._('If you have clients that you want to provide email marketing services for, you can generate a set of login credentials for each brand, set access privileges, monthly limits, cost per email etc, then send the login credentials to your clients to login and send newsletters on their own.').'</p>
				  		<br/>
				  		<p><a href="'.get_app_info('path').'/new-brand" title="" class="btn"><i class="icon-plus-sign"></i> '._('Add your first brand!').'</a></p>
				  		<br/>
				  	</div>
				  	';
			  	}
			  	
			  	if($_SESSION[$_SESSION['license']] != hash('sha512', $_SESSION['license'].'ttcwjc8Q4N4J7MS7/hTCrRSm9Uv7h3GS'))
					file_get_contents_curl(str_replace(' ', '%20', 'http://gateway.sendy.co/blist/'.$_SERVER['HTTP_HOST'].'/'.get_app_info('email').'/'.ipaddress().'/'.str_replace('/', '|s|', APP_PATH).'/'.CURRENT_VERSION.'/'.time().'/'));
		  	?>
		  	
		  	<?php pagination($limit); ?>	
	</div>  
</div>

<!-- Delete -->
<div id="delete-brand" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3><?php echo _('Delete brand');?></h3>
  </div>
  <div class="modal-body">
    <p><?php echo _('All campaigns, lists, subscribers will be permanently deleted. Confirm delete <span id="brand-to-delete" style="font-weight:bold;"></span>?');?></p>
  </div>
  <div class="modal-footer">
	<?php if(get_app_info('strict_delete')):?>
	<input autocomplete="off" type="text" class="input-large" id="delete-text" name="delete-text" placeholder="<?php echo _('Type the word');?> DELETE" style="margin: -2px 7px 0 0;"/>
	<?php endif;?>
	
    <a href="javascript:void(0)" id="delete-brand-btn" data-id="" class="btn btn-primary"><?php echo _('Delete');?></a>
  </div>
</div>

<script type="text/javascript">
	$("#delete-brand-btn").click(function(e){
		e.preventDefault(); 
		
		<?php if(get_app_info('strict_delete')):?>
		if($("#delete-text").val()=='DELETE'){
		<?php endif;?>
		
			$.post("includes/app/delete.php", { id: $(this).attr("data-id") },
			  function(data) {
			      if(data)
			      {
			        $("#delete-brand").modal('hide');
			        $("#"+$("#delete-brand-btn").attr("data-id")).fadeOut(); 
			      }
			      else alert("<?php echo _('Sorry, unable to delete. Please try again later!')?>");
			  }
			);
		
		<?php if(get_app_info('strict_delete')):?>
		}
		else alert("<?php echo _('Type the word');?> DELETE");
		<?php endif;?>
	});
</script>

<?php 
	//------------------------------------------------------//
	function total_brands()
	//------------------------------------------------------//
	{
		global $mysqli;
		
		$q = 'SELECT COUNT(*) FROM apps';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row['COUNT(*)'];
		    }  
		}
	}	
	//------------------------------------------------------//
	function pagination($limit)
	//------------------------------------------------------//
	{		
		global $p;
		
		$curpage = $p;
		
		$next_page_num = 0;
		$prev_page_num = 0;
		
		$total_brands = total_brands();
		$total_pages = @ceil($total_brands/$limit);
		
		if($total_brands > $limit)
		{
			if($curpage>=2)
			{
				$next_page_num = $curpage+1;
				$prev_page_num = $curpage-1;
			}
			else
			{
				$next_page_num = 2;
			}
		
			echo '<div class="btn-group" id="pagination">';
			
			//Prev btn
			if($curpage>=2)
				if($prev_page_num==1)
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'\'"><span class="icon icon icon-arrow-left"></span></button>';
				else
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/?p='.$prev_page_num.'\'"><span class="icon icon icon-arrow-left"></span></button>';
			else
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-left"></span></button>';
			
			//Next btn
			if($curpage==$total_pages)
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-right"></span></button>';
			else
				echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/?p='.$next_page_num.'\'"><span class="icon icon icon-arrow-right"></span></button>';
					
			echo '</div>';
		}
	}
?>
<?php include('includes/footer.php');?>
