<hr>
	
	      <footer>
	      	<!-- Check if sub user -->
			<?php if(!get_app_info('is_sub_user')):?>
	        <p class="footer-left">
	        	&copy; <?php echo date("Y",time())?> <a href="https://sendy.co" title="" target="_blank">Sendy</a> | <a href="https://sendy.co/troubleshooting" target="_blank">Troubleshooting</a> | <a href="https://sendy.co/forum/" target="_blank">Support forum</a> | Version <?php echo get_app_info('version');?> 
	        	<?php 
		        	if(get_app_info('version_latest') > get_app_info('version')):
	        	?>
		        <a href="https://sendy.co/get-updated?l=<?php echo get_app_info('license');?>" target="_blank" style="text-decoration:none;"><span class="label label-info">new version: <?php echo get_app_info('version_latest');?> available</span></a>
		        <?php endif;?>
	        </p>
	        <?php else:?>
	        <p>&copy; <?php echo date("Y",time())?> <?php echo get_app_info('company');?></p>
	        <?php endif;?>
	        
	        <!-- Appearance toggle (Light/Dark) -->
		        <p class="footer-right">
			        <div id="footer-mode" class="btn-group" data-toggle="buttons-radio">
					  <a href="javascript:void(0)" title="<?php echo _('Light');?>" class="btn" id="light_btn_small"><i class="icon icon-sun"></i></a>
					  <a href="javascript:void(0)" title="<?php echo _('Dark');?>" class="btn" id="dark_btn_small"><i class="icon icon-moon"></i></a>
					</div>
					<input type="hidden" name="dark_mode" id="dark_mode" value="<?php echo get_app_info('dark_mode');?>">
					
					<script type="text/javascript">
					$(document).ready(function() {	
						//Init buttons					
						<?php if(get_app_info('dark_mode')):?>
							$("#dark_btn_small").button('toggle');
							$("#dark_mode").val("1");
						<?php else:?>
							$("#light_btn_small").button('toggle');
							$("#dark_mode").val("0");
						<?php endif;?>
						
						//btn click events
						$("#dark_btn_small").click(function(){
							change_appearance(1);
						});
						$("#light_btn_small").click(function(){
							change_appearance(0);
						});
						
						//keyboard shortcut Ctrl+g
						$(document).keydown(function (e){
						    if(e.ctrlKey && e.keyCode == 71){
						        <?php if(get_app_info('dark_mode')):?>
									change_appearance(0);
								<?php else:?>
									change_appearance(1);
								<?php endif;?>
						    }
						})
						
/*
						//Auto change appearance mode based on system preference
						//User system using dark mode
						if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)
						{
							if($("#dark_mode").val()==0)
								change_appearance(1);
						}
						//User system using light mode
						else
						{
							if($("#dark_mode").val()==1)
								change_appearance(0);
						}
*/
						
						//Change the appearance
						function change_appearance(mode)
						{
							$("#dark_mode").val(mode);
							url = "<?php echo get_app_info('path');?>/includes/helpers/appearance.php";
							dark_mode = $("#dark_mode").val();
							
							$.post(url, { dark_mode: dark_mode },
							  function(data) {
							      if(data)
							      	location.reload();
							      else
							      	alert("Unable to change the appearance, please try again later.");
							  }
							);
						}
					});
					</script>
		        </p>
	        <!-- Appearance toggle (Light/Dark) -->
	        
	      </footer>
	    </div>
	</body>
</html>