<script type="text/javascript">
	$(document).ready(function() {
		CKEDITOR.replace( 'thankyou_message', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php?app=<?php echo get_app_info('app');?>',
			height: '350px',
			extraPlugins: 'codemirror,dragresize'
			<?php if($dark_mode):?>
			,skin: 'moono-dark'
			<?php else:?>
			,uiColor: '#FFFFFF'
			<?php endif;?>
			
		});
		CKEDITOR.replace( 'goodbye_message', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php?app=<?php echo get_app_info('app');?>',
			height: '350px',
			extraPlugins: 'codemirror'
			<?php if($dark_mode):?>
			,skin: 'moono-dark'
			<?php else:?>
			,uiColor: '#FFFFFF'
			<?php endif;?>
		});
		CKEDITOR.replace( 'confirmation_email', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php?app=<?php echo get_app_info('app');?>',
			height: '350px',
			extraPlugins: 'codemirror'
			<?php if($dark_mode):?>
			,skin: 'moono-dark'
			<?php else:?>
			,uiColor: '#FFFFFF'
			<?php endif;?>
		});
	});
</script>