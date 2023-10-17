<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/list/main.php');?>
<?php include('includes/helpers/short.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/list?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
		else if(get_app_info('campaigns_only')==1 && get_app_info('templates_only')==1 && get_app_info('lists_only')==1 && get_app_info('reports_only')==1)
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/logout"</script>';
			exit;
		}
		else if(get_app_info('lists_only')==1)
		{
			go_to_next_allowed_section();
		}
	}
?>
<link href="<?php echo get_app_info('path');?>/css<?php echo get_app_info('dark_mode') ? '/dark' : '';?>/tablesorter.css?30" rel="stylesheet">
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/tablesorter/jquery.tablesorter.widgets.min.js"></script>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('table').tablesorter({
			widgets        : ['saveSort'],
			usNumberFormat : true,
			sortReset      : true,
			sortRestart    : true,
			headers: { 0: { sorter: false}, 8: {sorter: false}, 9: {sorter: false}, 10: {sorter: false} }	
		});
		$("#new-list-btn, #add-one-btn").click(function(){
			$("#list-form").slideDown("fast");
			$("#list_name").focus();
			$("#list-form").validate({
				rules: {
					list_name: {
						required: true	
					}
				},
				messages: {
					list_name: "<?php echo addslashes(_('List name is required'));?>"
				}
			});
		});
		$("#close-add-list-btn").click(function(){
			$("#list-form").slideUp("fast");
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
    	<h2><?php echo _('Subscriber lists');?></h2><br/>
    	
    	<div style="clear:both;">
	    	<button class="btn" id="new-list-btn"><i class="icon-plus-sign"></i> <?php echo _('Add a new list');?></button>
	    	
	    	<form class="form-search" action="<?php echo get_app_info('path');?>/search-all-lists" method="GET" style="float:right;">
	    		<input type="hidden" name="i" value="<?php echo get_app_info('app');?>">
				<input type="text" class="input-medium search-query" name="s" style="width: 200px;">
				<button type="submit" class="btn"><i class="icon-search"></i> <?php echo _('Search all lists');?></button>
			</form>
		</div>
		
		<form action="<?php echo get_app_info('path')?>/includes/subscribers/import-add.php" method="POST" accept-charset="utf-8" class="form-vertical well" enctype="multipart/form-data" id="list-form">
	    	
	    	<label class="control-label" for="list_name"><?php echo _('List name');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="list_name" name="list_name" placeholder="<?php echo _('The name of your new list');?>">
	            </div>
				<div class="btn-group" data-toggle="buttons-radio" style="margin-top: 5px;">
				  <a href="javascript:void(0)" title="" class="btn" id="single"><i class="icon icon-angle-right"></i> <?php echo _('Single Opt-In');?></a>
				  <a href="javascript:void(0)" title="" class="btn" id="double"><i class="icon icon-double-angle-right"></i> <?php echo _('Double Opt-In');?></a>
				</div>
				<script type="text/javascript">
					$(document).ready(function() {
						$("#single").button('toggle');
						$("#opt_in").val("0");						
						$("#single").click(function(){
							$("#opt_in").val("0");
						});
						$("#double").click(function(){
							$("#opt_in").val("1");
						});
					});
				</script>
	        </div>
	        
			<input type="hidden" name="opt_in" id="opt_in" value="">
	        <input type="hidden" name="app" value="<?php echo get_app_info('app');?>">
			
			<br/>
	        
	        <button type="submit" class="btn btn-inverse"><i class="icon icon-plus"></i> <?php echo _('Add');?></button> 
	        <a href="javascript:void(0)" id="close-add-list-btn"><span class="icon icon-remove-sign"></span> <?php echo _('Cancel');?></a>
	    </form>
		
		<br/>
		
		<?php $has_gdpr_subscribers = has_gdpr_subscribers();?>
    	
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('ID');?></th>
		      <th><?php echo _('List');?></th>
		      <th><?php echo _('Active');?></th>
		      <?php if($has_gdpr_subscribers):?>
		      <th><?php echo _('GDPR');?></th>
		      <?php endif;?>
		      <th title="Segments"><?php echo _('Segs');?></th>
		      <th title="Autoresponders"><?php echo _('ARs');?></th>
		      <th><?php echo _('Unsubscribed');?></th>
		      <th><?php echo _('Bounced');?></th>
		      <th><?php echo _('Hide');?></th>
		      <th><?php echo _('Edit');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	
		  	<!-- Auto select encrypted listID -->
		  	<script type="text/javascript">
		  		$(document).ready(function() {
					$(".encrypted-list-id").mouseover(function(){
						$(this).selectText();
					});
				});
			</script>
			
			<?php 
				//Get sorting preference and whether to show hidden lists
				$q = 'SELECT templates_lists_sorting, hide_lists FROM apps WHERE id = '.get_app_info('app');
				$r = mysqli_query($mysqli, $q);
				if ($r && mysqli_num_rows($r) > 0) 
				{
					while($row = mysqli_fetch_array($r)) 
					{
						$templates_lists_sorting = $row['templates_lists_sorting'];
						$hide_lists = $row['hide_lists'];
					}
				}
				$sortby = $templates_lists_sorting=='date' ? 'id DESC' : 'name ASC';
				$show_hidden_lists = $hide_lists == 0 ? '' : 'AND hide = 0';
			?>
		  	
		  	<?php 
			  	$limit = get_app_data('campaign_report_rows');
				$total_lists = totals(get_app_info('app'));
				$total_pages = ceil($total_lists/$limit);
				$p = isset($_GET['p']) ? $_GET['p'] : null;
				$offset = $p!=null ? ($p-1) * $limit : 0;
			  	
			  	$q = 'SELECT id, name, hide FROM lists WHERE app = '.get_app_info('app').' AND userID = '.get_app_info('main_userID').' '.$show_hidden_lists.' ORDER BY '.$sortby.' LIMIT '.$offset.','.$limit;
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = $row['id'];
			  			$name = stripslashes($row['name']);
			  			$hidden = stripslashes($row['hide']);
			  			$subscribers_count = get_subscribers_count($id);
			  			$unsubscribers_count = get_unsubscribers_count($id);
			  			$bounces_count = get_bounced_count($id);
			  			if(strlen(encrypt_val($id))>5) $listid = substr(encrypt_val($id), 0, 5).'..';
			  			else $listid = encrypt_val($id);
			  			
			  			$us_percentage = get_unsubscribers_percentage($subscribers_count, $unsubscribers_count);
			  			
			  			$b_percentage = get_bounced_percentage($bounces_count, $subscribers_count);
			  			
			  			$no_of_segs = get_segment_count($id);
			  			$seg_label = $no_of_segs>0 ? 'label-info' : 'label';
			  			$seg_count = $no_of_segs>0 ? '<a href="'.get_app_info('path').'/segments-list?i='.get_app_info('app').'&l='.$id.'" style="color:white;" title="'._('View segments for this list').'">'.$no_of_segs.'</a>' : '<a href="'.get_app_info('path').'/segments-list?i='.get_app_info('app').'&l='.$id.'" title="'._('Create segments for this list').'">'.$no_of_segs.'</a>';
			  			
			  			$no_of_ars = get_ar_count($id);
			  			$ar_label = $no_of_ars>0 ? 'label-info' : 'label';
			  			$ar_count = $no_of_ars>0 ? '<a href="'.get_app_info('path').'/autoresponders-list?i='.get_app_info('app').'&l='.$id.'" style="color:white;" title="'._('View autoresponders for this list').'">'.$no_of_ars.'</a>' : '<a href="'.get_app_info('path').'/autoresponders-list?i='.get_app_info('app').'&l='.$id.'" title="'._('Create autoresponders for this list').'">'.$no_of_ars.'</a>';
			  			
						$is_hidden = $hidden ? '.5' : '1';
						$icon_eye = $hidden ? 'icon-eye-open' : 'icon-eye-close';
						$hide_unhide_title = $hidden ? _('Unhide this list') : _('Hide this list');
						$to_hide = $hidden ? 0 : 1;
						  
			  			echo '
			  			
			  			<tr id="'.$id.'" style="opacity: '.$is_hidden.'">
			  			  <td><span class="label" id="list'.$id.'">'.$listid.'</span><span class="label encrypted-list-id" id="list'.$id.'-encrypted" style="display:none;">'.encrypt_val($id).'</span></td>
					      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$id.'" title="">'.$name.'</a></td>
					      <td id="progress'.$id.'"><span class="badge badge-success">'.$subscribers_count.'</span></td>';
					      
					    if($has_gdpr_subscribers)
					    {
						    $gdpr_count = get_gdpr_count($id);
						    $gdpr_percentage = get_gdpr_percentage($id, $gdpr_count);
						    echo '<td><span class="label label-warning">'.$gdpr_percentage.'%</span> '.$gdpr_count.'</td>';
						}
					      
					    echo '
					      <td><span class="label '.$seg_label.'">'.$seg_count.'</span></td>
					      <td><span class="label '.$ar_label.'">'.$ar_count.'</span></td>
					      <td><span class="label">'.$us_percentage.'%</span> '.$unsubscribers_count.'</td>
					      <td><span class="label">'.$b_percentage.'%</span> '.$bounces_count.'</td>
					      <td><a href="includes/list/hide-list.php" id="hide-btn-'.$id.'" title="'.$hide_unhide_title.'" data-l="'.$id.'" data-hide="'.$to_hide.'"><i class="icon '.$icon_eye.'"></i></a></td>
					      <td><a href="edit-list?i='.get_app_info('app').'&l='.$id.'" title="'._('List settings').'"><i class="icon icon-pencil"></i></a></td>
					      <td><a href="#delete-list" title="'._('Delete').' '.$name.'" id="delete-btn-'.$id.'" data-toggle="modal"><span class="icon icon-trash"></span></a></td>
					      <script type="text/javascript">
						  	$("#hide-btn-'.$id.'").click(function(e){
								e.preventDefault(); 
								$.post($(this).attr("href"), {l:$(this).data("l"), hide:$(this).attr("data-hide")},
									function(data) 
									{
										if(data==1) 
										{
											$("#'.$id.'").css("opacity", "0.5");
											$("#hide-btn-'.$id.'").html("<i class=\"icon icon-eye-open\"></i>");
											$("#hide-btn-'.$id.'").attr("title", "'._('Unhide this list').'");
											$("#hide-btn-'.$id.'").attr("data-hide", 0);
											to_hide = 0;
										}
										else 
										{
											$("#'.$id.'").css("opacity", "1");
											$("#hide-btn-'.$id.'").html("<i class=\"icon icon-eye-close\"></i>");
											$("#hide-btn-'.$id.'").attr("title", "'._('Hide this list').'");
											$("#hide-btn-'.$id.'").attr("data-hide", 1);
											to_hide = 1;
										}
									}
								);
							});
					        $("#delete-btn-'.$id.'").click(function(e){
								e.preventDefault(); 
								$("#delete-list-btn").attr("data-id", '.$id.');
								$("#list-to-delete").text("'.$name.'");
								$("#delete-text").val("");
							});
							$("#list'.$id.'").mouseover(function(){
								$("#list'.$id.'-encrypted").show();
								$(this).hide();
							});
							$("#list'.$id.'-encrypted").mouseout(function(){
								$(this).hide();
								$("#list'.$id.'").show();
							});
							</script>
					    </tr>
			  			';
			  	    }  
			  	}
			  	else
			  	{
				  	echo '
				  		<tr>
				  			<td>'._('No list yet.').' <a href="javascript:void(0)" title="" id="add-one-btn" style="text-decoration: underline;">'._('Add one').'</a>!</td>
				  			<td></td>
				  			<td></td>
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
		
		<!-- Delete -->
		<div id="delete-list" class="modal hide fade">
		  <div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		    <h3><?php echo _('Delete list');?></h3>
		  </div>
		  <div class="modal-body">
		    <p><?php echo _('All subscribers, custom fields, autoresponders and segments in this list will be permanently deleted. Confirm delete <span id="list-to-delete" style="font-weight:bold;"></span>?');?></p>
		  </div>
		  <div class="modal-footer">
			<?php if(get_app_info('strict_delete')):?>
			<input autocomplete="off" type="text" class="input-large" id="delete-text" name="delete-text" placeholder="<?php echo _('Type the word');?> DELETE" style="margin: -2px 7px 0 0;"/>
			<?php endif;?>
			
		    <a href="javascript:void(0)" id="delete-list-btn" data-id="" class="btn btn-primary"><?php echo _('Delete');?></a>
		  </div>
		</div>
		
		<script type="text/javascript">
			$("#delete-list-btn").click(function(e){
				e.preventDefault(); 
				
				<?php if(get_app_info('strict_delete')):?>
				if($("#delete-text").val()=='DELETE'){
				<?php endif;?>
				
					$.post("includes/list/delete.php", { list_id: $(this).attr("data-id") },
					  function(data) {
					      if(data)
					      {
					        $("#delete-list").modal('hide');
					        $("#"+$("#delete-list-btn").attr("data-id")).fadeOut(); 
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
		
		<?php pagination($limit); ?>	
    </div>   
</div>
<?php include('includes/footer.php');?>
