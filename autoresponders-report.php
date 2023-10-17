<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/ares-reports/main.php');?>
<?php include('includes/helpers/short.php');?>
<?php 
	$ar = is_numeric($_GET['a']) ? (int)$_GET['a'] : exit;
	$ae = is_numeric($_GET['ae']) ? (int)$_GET['ae'] : exit;
	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/autoresponders-report.php?i='.get_app_info('restricted_to_app').'&a='.$ar.'&ae='.$ae.'"</script>';
			exit;
		}
	}
?>
<?php 
	$q = 'SELECT * FROM ares_emails WHERE id = '.mysqli_real_escape_string($mysqli, $ae);
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$id = stripslashes($row['id']);
			$from_name = stripslashes($row['from_name']);
			$from_email = stripslashes($row['from_email']);
  			$title = stripslashes($row['title']);
  			$recipients = stripslashes($row['recipients']);
  			$opens = stripslashes($row['opens']);
  			$opens_tracking = stripslashes($row['opens_tracking']);
  			$links_tracking = stripslashes($row['links_tracking']);
  			$opens_all = '';
  			$opens_array = array();
  			$no_opens_yet = $opens_tracking ? _('No opens yet!') : _('Tracking disabled for opens');
  			$no_links_for_this_campaign = $links_tracking ? _('There are no links for this autoresponder.') : _('Tracking disabled for clicks');
  			
  			if($opens=='')
  			{
  				$percentage_opened = 0;
	  			$opens_unique = 0;
  			}
  			else
  			{
	  			$opens_array = explode(',', $opens);
	  			$opens_all = count($opens_array);
	  			$opens_unique = count(array_unique($opens_array));
	  			$percentage_opened = round($opens_unique/($recipients-get_bounced()) * 100, 2);
	  		}
	  		if($recipients==0 || $recipients=='') 
	  		{
	  			$click_per = round(get_click_percentage($ae) *100, 4);
	  			$unsubscribe_per = round(get_unsubscribes() *100, 4);
	  			$bounce_percentage = round(get_bounced() * 100, 2);
		  		$complaint_percentage = round(get_complaints() * 100, 2);
	  		}
	  		else 
	  		{
	  			$click_per = round(get_click_percentage($ae)/($recipients-get_bounced()) *100, 4);
	  			$unsubscribe_per = round(get_unsubscribes()/($recipients-get_bounced()) *100, 4);
	  			$bounce_percentage = round((get_bounced()/$recipients) * 100, 2);
		  		$complaint_percentage = round((get_complaints()/$recipients) * 100, 2);
	  		}
	  		
	  		if($opens_all=='')
	  			$opens_all = '0';
	    }  
	}
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
<script src="js/highcharts/highcharts.js?2"></script>
<?php if(get_app_info('dark_mode')):?><script src="js/highcharts/themes/high-contrast-dark.src.js"></script><?php endif;?>
<script type="text/javascript">
	$(document).ready(function() {

	var chart;
    $(document).ready(function() {
    	
    	Highcharts.setOptions({
	        colors: ['#999999', '#70bd6c', '#eeca46', '#579fc8', '#ce5c56', '#ececec', '#ececec']
	    });
    	
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container',
                type: 'bar',
                height: 300
            },
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                categories: ['<?php echo _('Activity');?>'],
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: false
                }
            },
            legend: {
	            itemStyle: {
		           color: '#646464',
		           fontWeight: 'normal',
	               fontFamily: 'Roboto',
	               fontSize: '12'
		        }
	        },
            tooltip: {
                formatter: function() {
                    return ''+
                        this.series.name +': '+ this.y;
                }
            },
            plotOptions: {
                bar: {
                	borderWidth: 0,
                	shadow: false,
                	groupPadding: 0,
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            credits: {
                enabled: false
            },            
            series: [
	        {
		        dataLabels: {
		            style:{
		                fontWeight: 'normal',
		                textOutline: '0px',
		                color: "#797979"
		            }
		        },
                name: '<?php echo _('Recipients');?>',
                data: [<?php echo $recipients;?>]
            },
            {
	             dataLabels: {
		            style:{
		                fontWeight: 'normal',
		                textOutline: '0px',
		                color: "#797979"
		            }
		        },
                name: '<?php echo _('Opened');?>',
                data: [<?php echo $opens_unique;?>]
            },
            {
	             dataLabels: {
		            style:{
		                fontWeight: 'normal',
		                textOutline: '0px',
		                color: "#797979"
		            }
		        },
                name: '<?php echo _('Unopened');?>',
                data: [<?php echo $recipients - $opens_unique; ?>]
            },
            {
	             dataLabels: {
		            style:{
		                fontWeight: 'normal',
		                textOutline: '0px',
		                color: "#797979"
		            }
		        },
                name: '<?php echo _('Clicked');?>',
                data: [<?php echo get_click_percentage($ae);?>]
            },
            {
	             dataLabels: {
		            style:{
		                fontWeight: 'normal',
		                textOutline: '0px',
		                color: "#797979"
		            }
		        },
                name: '<?php echo _('Unsubscribed');?>',
                data: [<?php echo get_unsubscribes();?>]
            },
            {
	             dataLabels: {
		            style:{
		                fontWeight: 'normal',
		                textOutline: '0px',
		                color: "#797979"
		            }
		        },
                name: '<?php echo _('Bounced');?>',
                data: [<?php echo get_bounced();?>]
            },
            {
	             dataLabels: {
		            style:{
		                fontWeight: 'normal',
		                textOutline: '0px',
		                color: "#797979"
		            }
		        },
                name: '<?php echo _('Marked as spam');?>',
                data: [<?php echo get_complaints();?>]
            }
            
            ],
            exporting: { enabled: false }
        });
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
    	<h2><?php echo _('Autoresponder report');?></h2><br/>
    	
    	<h3><?php echo _('Subject');?>: <?php echo get_saved_data('title');?> <a href="<?php echo get_app_info('path');?>/w/<?php echo encrypt_val($id);?>/a" title="<?php echo _('View the web version');?>" class="iframe-preview"><span class="icon-eye-open"></span></a></h3><br/>
		
		<blockquote>
    		<p><strong><?php echo _('Autoresponder');?></strong> <span class="label"><?php echo get_ares_data('name');?></span> </p>
			<p><strong><?php echo _('Type');?></strong> <span class="label"><?php echo get_ares_type_name('type');?></span></p>
			<p><strong><?php echo _('From');?></strong> <span class="label"><?php echo $from_name.' &lt;'.$from_email.'&gt;';?></span></p>
			<p><strong><?php echo _('Emails sent');?></strong> <span class="label"><?php echo number_format(get_saved_data('recipients'));?> <?php echo _('subscribers');?></span></p>
		</blockquote>
    	
    	<div class="row-fluid">
    		<div class="span4">
		    	<div id="countries-container" style="min-height:300px;margin:20px 0 0 0;"></div>
	    	</div>
    		<div class="span8">
		    	<div id="container" style="margin-top: 50px;"></div>
		    </div>
	    </div>
    	
    	<br/>
    	<div class="row-fluid">
	    	<div class="span6">
	    		<div class="well">
			    	<h3><?php if($opens_tracking): ?><span class="badge badge-success" style="font-size:16px;"><?php echo $percentage_opened;?>%</span> <?php echo _('opened');?> <span class="label"><?php echo $opens_unique;?> <?php echo _('unique');?> / <?php echo _('opened');?> <?php echo $opens_all;?> <?php echo _('times');?></span><?php else: ?><span class="badge" style="font-size:16px;"><?php echo _('Tracking disabled for opens');?></span><?php endif;?></h3><br/>
			    	<h3><?php if($opens_tracking): ?><span class="badge badge-warning" style="font-size:16px;"><?php echo $recipients - $opens_unique;?></span> <?php echo _('not opened');?><?php else: ?><span class="badge" style="font-size:16px;"><?php echo _('Tracking disabled for opens');?></span><?php endif;?></h3><br/>
			    	<h3><?php if($links_tracking): ?><span class="badge badge-info" style="font-size:16px;"><?php echo $click_per;?>%</span> <?php echo _('clicked a link');?> <span class="label"><?php echo get_click_percentage($ae);?> <?php echo _('clicked');?></span><?php else: ?><span class="badge" style="font-size: 16px;"><?php echo _('Tracking disabled for clicks');?></span><?php endif;?></h3>
			    </div>
	    	</div>
	    	
	    	<div class="span6">
	    		<div class="well">
			    	<h3><span class="badge badge-important" style="font-size:16px;"><?php echo $unsubscribe_per;?>%</span> <?php echo _('unsubscribed');?> <span class="label"><?php echo get_unsubscribes();?> <?php echo _('unsubscribed');?></span></h3><br/>
			    	<h3><span class="badge badge-inverse" style="font-size:16px;"><?php echo $bounce_percentage;?>%</span> <?php echo _('bounced');?> <span class="label"><?php echo get_bounced();?> <?php echo _('bounced');?></span></h3><br/>
			    	<h3><span class="badge badge-inverse" style="font-size:16px;"><?php echo $complaint_percentage;?>%</span> <?php echo _('marked as spam');?> <span class="label"><?php echo get_complaints();?> <?php echo _('marked as spam');?></span></h3>
			    </div>
	    	</div>
	    </div>
	    
	    <!-- Link activity -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
		    	<h2 class="report-titles"><?php echo _('Link activity');?></h2>
				<?php if($links_tracking!=2):?>
			    	<?php if($links_tracking): ?>
				    	<a href="<?php echo get_app_info('path');?>/includes/ares-reports/export-csv.php?c=<?php echo $id?>&a=clicks" title="<?php echo _('Export a CSV of ALL subscribers who clicked');?>" class="report-export"><i class="icon icon-download-alt"></i></a>
				    	<a href="#import-into-list-modal" title="<?php echo _('Import ALL subscribers who clicked at least one link into a list');?>" id="link-activity-export-btn" data-toggle="modal" class="report-export"><i class="icon icon-plus-sign"></i></a>
				    <?php endif;?>
				<?php endif;?>
	    	</div>
	    </div>
	    <br/>
	    <div class="row-fluid">
	    	<table class="table table-striped table-condensed responsive">
			  <thead>
			    <tr>
			      <th><?php echo _('Link (URL)');?></th>
			      <th><?php echo _('Unique');?></th>
			      <th><?php echo _('Total');?></th>
			    </tr>
			  </thead>
			  <tbody>
			  	
			  	<?php 
				  	$q = 'SELECT * FROM links WHERE ares_emails_id = '.mysqli_real_escape_string($mysqli, $ae);
				  	$r = mysqli_query($mysqli, $q);
				  	if ($r && mysqli_num_rows($r) > 0)
				  	{
				  	    while($row = mysqli_fetch_array($r))
				  	    {
				  			$link = stripslashes($row['link']);
				  			$clicks = stripslashes($row['clicks']);
				  			
				  			if($clicks==NULL)
				  			{
				  				$unique_clicks = '0';
				  				$total_clicks = '0';
				  			}
				  			else
				  			{
					  			$total_clicks_array = explode(',', $clicks);
					  			$total_clicks = count($total_clicks_array);
					  			$unique_clicks = count(array_unique($total_clicks_array));
					  		}
				  			
				  			echo '
				  			
				  			<tr>
						      <td><a href="'.$link.'" target="_blank">'.$link.'</a></td>
						      <td>'.$unique_clicks.'</td>
						      <td>'.$total_clicks.'</td>
						    </tr>
				  			
				  			';
				  	    }  
				  	}
				  	else
				  	{
					  	echo '
				  			
			  			<tr>
					      <td>'._('There are no links for this autoresponder.').'</td>
					      <td></td>
					      <td></td>
					    </tr>
			  			
			  			';
				  	}
			  	?>
			    
			  </tbody>
			</table>
	    </div>
	    
	    <!-- Last 10 opened -->
		<?php if($opens_tracking!=2):?>
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
		    	<h2 class="report-titles"><?php echo _('Last 10 opened');?></h2>
		    	<?php if($opens_tracking): ?>
			    	<a href="<?php echo get_app_info('path');?>/includes/ares-reports/export-csv.php?c=<?php echo $id?>&a=opens" title="<?php echo _('Export a CSV of ALL subscribers who opened');?>" class="report-export"><i class="icon icon-download-alt"></i></a>
			    	<a href="#import-into-list-modal" title="<?php echo _('Import ALL subscribers who opened this campaign into a list');?>" id="opened-export-btn" data-toggle="modal" class="report-export"><i class="icon icon-plus-sign"></i></a>
			    <?php endif;?>
	    	</div>
	    </div>
	    <br/>
	    <div class="row-fluid">
	    	<table class="table table-striped table-condensed responsive">
			  <thead>
			    <tr>
			      <th><?php echo _('Name');?></th>
			      <th><?php echo _('Email');?></th>
			      <th><?php echo _('List');?></th>
			      <th><?php echo _('Status');?></th>
			    </tr>
			  </thead>
			  <tbody>
			  	
			  	<?php 
				  	$q = 'SELECT opens FROM ares_emails WHERE id = '.mysqli_real_escape_string($mysqli, $ae);
				  	$r = mysqli_query($mysqli, $q);
				  	if ($r && mysqli_num_rows($r) > 0)
				  	{
				  	    while($row = mysqli_fetch_array($r))
				  	    {
				  	    	$last_opens = $row['opens'];
				  			$last_opens_array = explode(',', $last_opens);
				  			$loop_no = count(array_unique($last_opens_array));
				  			if($loop_no>10) $loop_no = 10;
				  			
				  			if($last_opens=='')
				  			{
					  			echo '
									  			
					  			<tr>
							      <td>'._('No one opened yet.').'</td>
							      <td></td>
							      <td></td>
							      <td></td>
							    </tr>
					  			
					  			';
				  			}
				  			
				  	    	for($z=0;$z<$loop_no;$z++)
				  	    	{
				  	    		$last_opens_array2 = array_reverse(array_unique($last_opens_array));
					  			$last_subscriber_id = explode(':', $last_opens_array2[$z]);
					  			
					  			$q2 = 'SELECT * FROM subscribers WHERE id = '.$last_subscriber_id[0];
					  			$r2 = mysqli_query($mysqli, $q2);
					  			if ($r2 && mysqli_num_rows($r2) > 0)
					  			{
					  			    while($row = mysqli_fetch_array($r2))
					  			    {
					  					$subscriber_id = stripslashes($row['id']);
							  			$name = stripslashes($row['name']);
							  			$email = stripslashes($row['email']);
							  			$listID = stripslashes($row['list']);
							  			$timestamp = parse_date($row['timestamp'], 'short', true);
							  			$unsubscribed = stripslashes($row['unsubscribed']);
							  			$bounced = stripslashes($row['bounced']);
							  			$complaint = stripslashes($row['complaint']);
							  			if($unsubscribed==0)
							  				$unsubscribed = '<span class="label label-success">'._('Subscribed').'</span>';
							  			else if($unsubscribed==1)
							  				$unsubscribed = '<span class="label label-important">'._('Unsubscribed').'</span>';
							  			if($bounced==1)
								  			$unsubscribed = '<span class="label label-inverse">'._('Bounced').'</span>';
								  		if($complaint==1)
								  			$unsubscribed = '<span class="label label-inverse">'._('Marked as spam').'</span>';
							  			
							  			if($name=='')
							  				$name = '['._('No name').']';
							  				
							  			$q2 = 'SELECT name FROM lists WHERE id = '.$listID;
							  			$r2 = mysqli_query($mysqli, $q2);
							  			if ($r2 && mysqli_num_rows($r2) > 0)
							  			{
							  			    while($row = mysqli_fetch_array($r2))
							  			    {
							  					$list_name = stripslashes($row['name']);
							  			    }  
							  			}
					  					
					  					echo '
							  			
							  			<tr>
									      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$name.'</a></td>
									      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$email.'</a></td>
									      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$listID.'" title="">'.$list_name.'</a></td>
									      <td>'.$unsubscribed.'</td>
									    </tr>
							  			
							  			';
					  			    }  
					  			}
					  		}
				  	    }  
				  	}
				  	else
				  	{
					  	echo '
				  			
			  			<tr>
					      <td>'._('No one opened yet.').'</td>
					      <td></td>
					      <td></td>
					      <td></td>
					    </tr>
			  			
			  			';
				  	}
			  	?>
			    
			  </tbody>
			</table>
	    </div>
		<?php endif;?>
	    
	    <!-- Unsubscribed -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
		    	<h2 class="report-titles"><?php echo _('Last 10 unsubscribed');?></h2>
		    	<a href="<?php echo get_app_info('path');?>/includes/ares-reports/export-csv.php?c=<?php echo $id?>&a=unsubscribes" title="<?php echo _('Export subscribers who unsubscribed');?>" class="report-export"><i class="icon icon-download-alt"></i></a>
	    	</div>
	    </div>
	    <br/>
	    <div class="row-fluid">
	    	<table class="table table-striped table-condensed responsive">
			  <thead>
			    <tr>
			      <th><?php echo _('Name');?></th>
			      <th><?php echo _('Email');?></th>
			      <th><?php echo _('List');?></th>
			      <th><?php echo _('Status');?></th>
			      <th><?php echo _('Date');?></th>
			    </tr>
			  </thead>
			  <tbody>
			  	
			  	<?php 
				  	$q = 'SELECT * FROM subscribers WHERE unsubscribed = 1 AND last_ares = '.mysqli_real_escape_string($mysqli, $ae).' LIMIT 10';
				  	$r = mysqli_query($mysqli, $q);
				  	if ($r && mysqli_num_rows($r) > 0)
				  	{
				  	    while($row = mysqli_fetch_array($r))
				  	    {
				  	    	$subscriber_id = stripslashes($row['id']);
				  			$name = stripslashes($row['name']);
				  			$email = stripslashes($row['email']);
				  			$listID = stripslashes($row['list']);
				  			$timestamp = parse_date($row['timestamp'], 'short', true);
				  			
				  			
				  			if($name=='')
				  				$name = '['._('No name').']';
				  				
				  			$q2 = 'SELECT name FROM lists WHERE id = '.$listID;
				  			$r2 = mysqli_query($mysqli, $q2);
				  			if ($r2 && mysqli_num_rows($r2) > 0)
				  			{
				  			    while($row = mysqli_fetch_array($r2))
				  			    {
				  					$list_name = stripslashes($row['name']);
				  			    }  
				  			}
				  			
				  			echo '
				  			
				  			<tr>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$name.'</a></td>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$email.'</a></td>
						      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$listID.'" title="">'.$list_name.'</a></td>
						      <td><span class="label label-important">'._('Unsubscribed').'</span></td>
						      <td>'.$timestamp.'</td>
						    </tr>
				  			
				  			';
				  	    }  
				  	}
				  	else
				  	{
					  	echo '
				  			
			  			<tr>
					      <td>'._('No one unsubscribed from this autoresponder!').'</td>
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
	    </div>
	    
	    <!-- Bounced -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
		    	<h2 class="report-titles"><?php echo _('Last 10 bounced emails');?></h2>
		    	<a href="<?php echo get_app_info('path');?>/includes/ares-reports/export-csv.php?c=<?php echo $id?>&a=bounces" title="<?php echo _('Export subscribers who bounced');?>" class="report-export"><i class="icon icon-download-alt"></i></a>
	    	</div>
	    </div>
	    <br/>
	    <div class="row-fluid">
	    	<table class="table table-striped table-condensed responsive">
			  <thead>
			    <tr>
			      <th><?php echo _('Name');?></th>
			      <th><?php echo _('Email');?></th>
			      <th><?php echo _('List');?></th>
			      <th><?php echo _('Status');?></th>
			      <th><?php echo _('Date');?></th>
			    </tr>
			  </thead>
			  <tbody>
			  	
			  	<?php 
				  	$q = 'SELECT * FROM subscribers WHERE bounced = 1 AND last_ares = '.mysqli_real_escape_string($mysqli, $ae).' LIMIT 10';
				  	$r = mysqli_query($mysqli, $q);
				  	if ($r && mysqli_num_rows($r) > 0)
				  	{
				  	    while($row = mysqli_fetch_array($r))
				  	    {
				  	    	$subscriber_id = stripslashes($row['id']);
				  			$name = stripslashes($row['name']);
				  			$email = stripslashes($row['email']);
				  			$listID = stripslashes($row['list']);
				  			$timestamp = parse_date($row['timestamp'], 'short', true);
				  			
				  			if($name=='')
				  				$name = '['._('No name').']';
				  				
				  			$q2 = 'SELECT name FROM lists WHERE id = '.$listID;
				  			$r2 = mysqli_query($mysqli, $q2);
				  			if ($r2 && mysqli_num_rows($r2) > 0)
				  			{
				  			    while($row = mysqli_fetch_array($r2))
				  			    {
				  					$list_name = stripslashes($row['name']);
				  			    }  
				  			}
				  			
				  			echo '
				  			
				  			<tr>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$name.'</a></td>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$email.'</a></td>
						      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$listID.'" title="">'.$list_name.'</a></td>
						      <td><span class="label label-inverse">'._('Bounced').'</span></td>
						      <td>'.$timestamp.'</td>
						    </tr>
				  			
				  			';
				  	    }  
				  	}
				  	else
				  	{
					  	echo '
				  			
			  			<tr>
					      <td>'._('No emails bounced from this autoresponder!').'</td>
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
	    </div>
	    
	    <!-- Marked as spam -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
		    	<h2 class="report-titles"><?php echo _('Last 10 marked as spam');?></h2>
		    	<a href="<?php echo get_app_info('path');?>/includes/ares-reports/export-csv.php?c=<?php echo $id?>&a=complaints" title="<?php echo _('Export subscribers who marked your email as spam');?>" class="report-export"><i class="icon icon-download-alt"></i></a>
	    	</div>
	    </div>
	    <br/>
	    <div class="row-fluid">
	    	<table class="table table-striped table-condensed responsive">
			  <thead>
			    <tr>
			      <th><?php echo _('Name');?></th>
			      <th><?php echo _('Email');?></th>
			      <th><?php echo _('List');?></th>
			      <th><?php echo _('Status');?></th>
			      <th><?php echo _('Date');?></th>
			    </tr>
			  </thead>
			  <tbody>
			  	
			  	<?php 
				  	$q = 'SELECT * FROM subscribers WHERE complaint = 1 AND last_ares = '.mysqli_real_escape_string($mysqli, $ae).' LIMIT 10';
				  	$r = mysqli_query($mysqli, $q);
				  	if ($r && mysqli_num_rows($r) > 0)
				  	{
				  	    while($row = mysqli_fetch_array($r))
				  	    {
				  	    	$subscriber_id = stripslashes($row['id']);
				  			$name = stripslashes($row['name']);
				  			$email = stripslashes($row['email']);
				  			$listID = stripslashes($row['list']);
				  			$timestamp = parse_date($row['timestamp'], 'short', true);
				  			
				  			if($name=='')
				  				$name = '[No name]';
				  				
				  			$q2 = 'SELECT name FROM lists WHERE id = '.$listID;
				  			$r2 = mysqli_query($mysqli, $q2);
				  			if ($r2 && mysqli_num_rows($r2) > 0)
				  			{
				  			    while($row = mysqli_fetch_array($r2))
				  			    {
				  					$list_name = stripslashes($row['name']);
				  			    }  
				  			}
				  			
				  			echo '
				  			
				  			<tr>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$name.'</a></td>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$email.'</a></td>
						      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$listID.'" title="">'.$list_name.'</a></td>
						      <td><span class="label label-inverse">'._('Marked as spam').'</span></td>
						      <td>'.$timestamp.'</td>
						    </tr>
				  			
				  			';
				  	    }  
				  	}
				  	else
				  	{
					  	echo '
				  			
			  			<tr>
					      <td>'._('No one marked your email as spam!').'</td>
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
	    </div>
	    
	    <!-- Countries -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
	    		<h2><?php echo _('All countries');?></h2><br/>
		    	<table class="table table-striped table-condensed responsive">
				  <thead>
				    <tr>
				      <th><?php echo _('Country');?></th>
				      <th><?php echo _('Opens');?></th>
					  <?php if($opens_tracking!=2):?>
					      <?php if(!get_app_info('is_sub_user') || (get_app_info('is_sub_user') && get_app_info('lists_only')==0)):?>
						      <th><?php echo _('Export');?></th>
						      <th><?php echo _('Import');?></th>
					      <?php endif;?>
					  <?php endif;?>
				    </tr>
				  </thead>
				  <tbody>
				  	
				  	<?php 		  			
			  			if($opens_all!='')
			  			{
			  				$unique_countries = array_unique($opens_array);
			  				$unique_countries_array = array();
			  				$country_count_array = array();
			  				
				  			for($i=0;$i<count($opens_array);$i++)
				  			{
				  				if(array_key_exists($i, $unique_countries)) $ucnts = $unique_countries[$i];
				  				else $ucnts = '';
				  				
				  				$get_country = explode(':', $ucnts);
				  				if(array_key_exists(1, $get_country)) $gcty = $get_country[1];
				  				else $gcty = '';
				  				
				  				if($gcty!='')
				  				{
						  			array_push($unique_countries_array, $gcty);
						  		}
				  			}
				  			
				  			$unique_countries_array_unique = array_unique($unique_countries_array);
				  			
				  			foreach($unique_countries_array_unique as $ucau)
				  			{
				  				$no_in_country = array_keys($unique_countries_array, $ucau);
				  				array_push($country_count_array, count($no_in_country).'%'.country_code_to_country($ucau).'%'.$ucau);
				  			}
				  			
				  			natsort($country_count_array);
				  			$country_count_array = array_reverse($country_count_array);
				  			if(count($opens_array)==0)
							{
								echo '
					  			<tr>
					  				<td>'.$no_opens_yet.'</td>
					  				<td>0</td>
					  				<td></td>
					  			</tr>
					  			<script type="text/javascript">
							  		$("#countries-container").html("<span class=\'badge\'>'.$no_opens_yet.'</span>");
							  		$("#countries-container").css("margin-top", "155px");
							  		$("#countries-container").css("margin-left", "180px");
							  		$("#countries-container").css("margin-bottom", "-155px");
							  	</script>
					  			';
							}
							else
							{
					  			foreach($country_count_array as $cca)
					  			{
					  				$cc = explode('%',$cca);
					  				
						  			echo '
						  			<tr>
						  				<td>'.$cc[1].'</td>
						  				<td>'.$cc[0].'</td>';
						  				
										if($opens_tracking!=2)
										{
							  				if(!get_app_info('is_sub_user') || (get_app_info('is_sub_user') && get_app_info('lists_only')==0))
							  				{
								  				echo '<td><a href="'.get_app_info('path').'/includes/ares-reports/export-csv.php?c='.$id.'&a='.$cc[2].'" title="'._('Export a CSV of ALL subscribers from').' '.$cc[1].'"><i class="icon icon-download-alt"></i></a></td>';
								  				echo '<td><a href="#import-into-list-modal" title="'._('Import ALL subscribers who opened this campaign into a list from').' '.$cc[1].'" class="country-export-btn" data-toggle="modal" data-country="'.$cc[2].'"><i class="icon icon-plus-sign"></i></a></td>';	
								  			}
										}
						  				
						  			echo '</tr>';
					  			}
					  			
					  	?>
		  			<script type="text/javascript">
		  				var chart2;
						$(document).ready(function() {
							Highcharts.setOptions({
						        colors: ['<?php echo count($country_count_array)==0 ? '#e3e5e7' : '#579fc8';?>', '#ce5c56', '#eeca46', '#70bd6c']
						    });
							
							chart2 = new Highcharts.Chart({
								chart: {
									renderTo: 'countries-container',
									plotBackgroundColor: null,
									plotBorderWidth: null,
									plotShadow: false
								},
								title: {
									text: '<?php echo _('Top 10 countries');?>',
									style: {
										color: '#525252',
										fontWeight: 'bold',
										fontSize: '14px'
									},
									verticalAlign: 'bottom'
								},
								tooltip: {
									formatter: function() {
										return '<b>'+ this.point.name +'</b>: '+Math.round(this.percentage) +' %';
									}
								},
								plotOptions: {
									pie: {
										size: 210,
										borderWidth: 0,
										shadow: false,
										allowPointSelect: true,
										cursor: 'pointer',
										dataLabels: {
											enabled: true
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
							                color: "#797979"
							            }
							        },
									type: 'pie',
									name: 'Countries',
									data: [
										<?php 
											$ct = 0;
											if(count($country_count_array)==0)
											{
												echo '
									  			[\'No countries detected\',   100],
									  			';
											}
											else
											{
												foreach($country_count_array as $cca)
									  			{
									  				if($ct<10)
									  				{
										  				$cc = explode('%',$cca);
										  				
										  				if($ct==0)
										  				{
											  				echo '{
																name: "'.$cc[1].'",
																y: '.$cc[0].',
																sliced: true,
																selected: true
															},';
										  				}
										  				else
										  				{
												  			echo '
												  			[\''.addslashes($cc[1]).'\',   '.$cc[0].'],
												  			';
												  		}
											  		}
											  		$ct++;
										  		}
										  	}
										?>
									]
								}],
								exporting: { enabled: false }
							});
						});
		  			</script>
					  			
			  		<?php
			  		
					  		}
			  			}
					  	else
					  	{
						  	echo '
					  			
				  			<tr>
						      <td>'._('No countries detected yet.').'</td>
						      <td></td>
						    </tr>
				  			
				  			';
					  	}
				  	?>
				    
				  </tbody>
				</table>
	    	</div>
	    	
	    </div>
	    
    </div>   
</div>

<!-- Subscriber info modal -->
<div id="subscriber-info" class="modal hide fade">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h3><?php echo _('Subscriber info');?></h3>
    </div>
    <div class="modal-body">
	    <p id="subscriber-text"></p>
    </div>
    <div class="modal-footer">
      <a href="#" class="btn btn-inverse" data-dismiss="modal"><?php echo _('Close');?></a>
    </div>
  </div>
<script type="text/javascript">
	$(".subscriber-info").click(function(){
		s_id = $(this).data("id");
		$("#subscriber-text").html("<?php echo _('Fetching');?>..");
		
		$.post("<?php echo get_app_info('path');?>/includes/subscribers/subscriber-info.php", { id: s_id, app:<?php echo get_app_info('app');?> },
		  function(data) {
		      if(data)
		      {
		      	$("#subscriber-text").html(data);
		      }
		      else
		      {
		      	$("#subscriber-text").html("<?php echo _('Oops, there was an error getting the subscriber\'s info. Please try again later.');?>");
		      }
		  }
		);
	});
</script>
<!-- /Subscriber info modal -->

<!-- Import into list modal -->
<div id="import-into-list-modal" class="modal hide fade">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h3 id="import-modal-title"><?php echo _('Import into which list?');?></h3>
    </div>
    <div class="modal-body">
    	<form action="<?php echo get_app_info('path')?>/includes/ares-reports/export-csv.php" method="GET" accept-charset="utf-8" class="form-vertical" name="duplicate-form" id="import-into-list-form">
    	<div class="control-group">
            <label class="control-label" for="into-list"><?php echo _('Choose a list you\'d like to import these subscribers into');?>:</label><br/>
            <div class="controls">
              <select id="list-id" name="list-id">
              	<?php      
	              	//Get sorting preference
					$q = 'SELECT templates_lists_sorting FROM apps WHERE id = '.get_app_info('app');
					$r = mysqli_query($mysqli, $q);
					if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $templates_lists_sorting = $row['templates_lists_sorting'];
					$sortby = $templates_lists_sorting=='date' ? 'id DESC' : 'name ASC';
				
	              	//Get lists         	
	              	$q = 'SELECT id, name FROM lists WHERE app = '.get_app_info('app').' ORDER BY '.$sortby;
	              	$r = mysqli_query($mysqli, $q);
	              	if ($r && mysqli_num_rows($r) > 0)
	              	{
	              	    while($row = mysqli_fetch_array($r))
	              	    {
	              	    	$list_id = $row['id'];
	              			$list_name_dropdown = $row['name'];
	              			
	              			echo '<option value="'.$list_id.'" id="list-'.$list_id.'">'.$list_name_dropdown.'</option>';
	              	    }  
	              	}
              	?>
              </select>
              
              <input autocomplete="off" type="text" class="input-large" id="new-list" name="new-list" style="display:none;" placeholder="<?php echo _('The name of your new list');?>"/>
              
              <input type="hidden" name="create" value=""></input>
              <input type="hidden" name="a" id="a" value=""></input>
              <input type="hidden" name="c" id="c" value="<?php echo $id;?>"></input>
              <input type="hidden" name="l" id="l" value=""></input>
              <input type="hidden" name="app" id="app" value="<?php echo get_app_info('app');?>"></input>
            </div>
            
            <p style="font-size: 12px;"><a href="javascript:void(0)" style="" id="new-list-btn"><span class="icon icon-plus-sign"></span> <span id="new-list-btn-text"><?php echo _('Create a new list');?></span></a></p>
            
          </div>
          </form>
          
          <div id="please_wait" style="display: none;">
	          <?php $loader = get_app_info('dark_mode') ? 'loader-dark.gif' : 'loader-light.gif';?>
	          <p><?php echo _('Now importing. If the number of subscribers are huge, it may take a little longer.');?></p>
	          <table class="table table-condensed" style="width: 300px;">
			  <tbody>
			  	<tr>
			  		<th>List</th>
			  		<th>Progress</th>
			  	</tr>
			    <tr>
					<td id="import-list-name"></td>
					<td><span id="import-progress"><span class="badge badge-success">0</span></span></td>
			    </tr>
			  </tbody>
			</table>
          </div>
    </div>
    <div class="modal-footer">
      <a href="#" class="btn btn" data-dismiss="modal" id="cancel-btn"><?php echo _('Cancel');?></a>
      <a href="javascript:void(0)" class="btn btn-inverse" id="import-btn"><?php echo _('Import');?></a>
    </div>
    
    <script type="text/javascript">
	    $("#unopened-export-btn").click(function(){
		    $("#a").val("unopens");
	    });
	    $("#link-activity-export-btn").click(function(){
		    $("#a").val("clicks");
	    });
	    $(".link-activity-individual-export-btn").click(function(){
		    $("#a").val("recipient_clicks");
		    $("#l").val($(this).data("link_id"));
	    });
	    $("#opened-export-btn").click(function(){
		    $("#a").val("opens");
	    });
	    $(".country-export-btn").click(function(){
		    $("#a").val($(this).data("country"));
	    });
	    $("#import-btn").click(function(){
		    submit_import_list_form();
	    });
	    $("#new-list").keydown(function(e){
		    if(e.keyCode == 13)
			    submit_import_list_form();
	    });
	    $("#new-list-btn").click(function(){
		    if($("#list-id").is(":visible"))
		    {
			    //Create new list
			    $("#list-id").hide();
			    $("#new-list").show();
			    $("#new-list-btn-text").text("<?php echo _('Choose an existing list');?>");
			    $("#new-list").focus();
			}
			else
			{
				//Choose existing list
				$("#list-id").show();
			    $("#new-list").hide();
			    $("#new-list-btn-text").text("<?php echo _('Create a new list');?>");
			    $("#new-list").val("");
			}
	    });
	    
	    //Submit the form for importing of subscribers into a list
	    function submit_import_list_form()
	    {
		 	//If user wants to import into existing list
		    if($("#new-list").val()=="")
		    {
		    	$("#import-list-name").text($("#list-id option:selected").text());
		    	listid = $("#list-id").val();
		    	$("#import-into-list-form").submit();
		    }
		    //If user wants to create a new list, add a new list and display new list name in modal window
		    else
		    {
		    	$("#import-list-name").text($("#new-list").val());
		    	
		    	//Create new list
	    		url = "includes/subscribers/import-add.php";
	    		main_userID = <?php echo get_app_info('main_userID');?>;
	    		list_name = $("#new-list").val();
	    		app = <?php echo get_app_info('app');?>;
	    		var listid;
	    		
	    		$.post(url, { main_userID: main_userID, list_name: list_name, app: app, from_report: 1 },
	    		  function(data) {
	    		      if(data)
	    		      	listid = data;
	    		      else 
	    		      	alert("Sorry, unable to create list. Please try again later!");
	    		  }
	    		);	    
	    		
	    		//Check list creation AJAX request, if done, set the listid to the new list, then submit the form
	    		check_list_creation_interval = setInterval(check_list_creation, 1000);	
	    		function check_list_creation()
	    		{
		    		if(listid!='')
		    		{
			    		$("#list-id").append(new Option($("#new-list").val(), listid));
			    		$("#list-id").val(listid);
			    		$("#import-into-list-form").submit();
			    		clearInterval(check_list_creation_interval);
			    	}
		    	}
		    }
		       
		    $("#import-into-list-form").slideUp();
		    $("#please_wait").fadeIn();
		    $("#import-modal-title").text("Please wait..");
		    $("#cancel-btn").text("Close");
		    $("#import-btn").fadeOut();
		    
		    //Import progress
		    list_interval = setInterval(function(){get_list_count($("#list-id").val())}, 2000);
			
			function get_list_count(lid)
			{
				clearInterval(list_interval);
    			$.post("includes/list/progress.php", { list_id: lid, user_id: <?php echo get_app_info('main_userID');?>, from_campaign: 1 },
				  function(data) {
				      if(data)
				      {
				      	if(data.indexOf("%)") != -1)
				      		list_interval = setInterval(function(){get_list_count($("#list-id").val())}, 2000);
				      		
				      	$("#import-progress").html(data);
				      }
				      else
				      {
				      	$("#import-progress").html("'._('Error retrieving count').'");
				      }
				  }
				);
			}
	    }
    </script>
</div>
<!-- /Import into list modal -->

<?php include('includes/footer.php');?>