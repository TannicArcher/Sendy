<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$app = isset($_GET['i']) && is_numeric($_GET['i']) ? mysqli_real_escape_string($mysqli, (int)$_GET['i']) : exit;	
	$list_id = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, (int)$_GET['l']) : exit;	
	$userID = get_app_info('main_userID');
	
	//Get CSV file name
	$csv_file = $userID.'-'.$list_id.'.csv';
	
	//Delete CSV file
	if(!unlink('../../uploads/csvs/'.$csv_file))
	{
		show_error(_('Unable to delete CSV file'), '<p>'._('Unable to delete CSV file').' ('.$csv_file.')</p><p>Please ensure the /uploads/csvs/ folder is writable</p>', true);
		error_log("[Unable to delete $csv_file] Please ensure the /uploads/csvs/ folder is writable");
	}
	
	//Reset all necessary columns for this list to '0'
	$q = 'UPDATE lists SET prev_count=0, currently_processing=0, total_records=0 WHERE id = '.$list_id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		//Redirect to 'All lists' page
		header("Location: ".get_app_info('path')."/list?i=$app"); 
	}
	else
	{
		show_error(_('Unable to update the \'lists\' table to stop the import'), '<p>'.mysqli_error($mysqli).'</p>', true);
		error_log("[Unable to update the \'lists\' table to stop the import]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
		exit;
	}
?>