<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$edit = isset($_GET['edit']) ? $_GET['edit'] : '';
	$template_id = isset($_GET['t']) && is_numeric($_GET['t']) ? mysqli_real_escape_string($mysqli, (int)$_GET['t']) : 0;
	$save_only = isset($_POST['save-only']) ? mysqli_real_escape_string($mysqli, $_POST['save-only']) : 0;	
	$template_name = addslashes(mysqli_real_escape_string($mysqli, $_POST['template_name']));
	$html = trim($_POST['html'])=='<html><head></head><body></body></html>' ? '' : addslashes($_POST['html']);
	$plain = addslashes($_POST['plain']);
	$redirect = $save_only ? get_app_info('path').'/edit-template?i='.get_app_info('app').'&t='.$template_id : get_app_info('path').'/templates?i='.get_app_info('app');
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	if($edit)
	{
		$q = 'UPDATE template SET template_name="'.$template_name.'", html_text="'.$html.'", plain_text="'.$plain.'" WHERE id='.$template_id;
		$r = mysqli_query($mysqli, $q);
		if ($r) 
		{
			if($save_only) header('Location: '.get_app_info('path').'/edit-template?i='.get_app_info('app').'&t='.$template_id); 
			else header('Location: ' .get_app_info('path').'/templates?i='.get_app_info('app'));
		}
		else show_error(_('Unable to create template'), '<p>'.mysqli_error($mysqli).'</p>');	
	}
	else
	{	
		//Insert into campaigns
		$q = 'INSERT INTO template (userID, app, template_name, html_text, plain_text) VALUES ('.get_app_info('main_userID').', '.get_app_info('app').', "'.$template_name.'", "'.$html.'", "'.$plain.'")';
		$r = mysqli_query($mysqli, $q);
		if ($r) 
		{
			$template_id = mysqli_insert_id($mysqli);
			
			if($save_only) header('Location: '.get_app_info('path').'/edit-template?i='.get_app_info('app').'&t='.$template_id); 
			else header('Location: ' .get_app_info('path').'/templates?i='.get_app_info('app'));
		}
		else show_error(_('Unable to create template'), '<p>'.mysqli_error($mysqli).'</p>');	
	}
?>