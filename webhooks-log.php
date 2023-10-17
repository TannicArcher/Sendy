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
    	?>
		
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
			  						  			
			  			echo '
		  				<tr id="'.$id.'">
			  			  <td><span class="label label-info">'.$section_label.'</span></td>
			  			  
						</tr>
					    ';
			  	    }  
			  	}
			  	else
			  	{
				  	echo '
				  		<tr>
					      <td>'._('There is nothing in the log.').'</td>
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
		
    </div>   
</div>
<?php include('includes/footer.php');?>
