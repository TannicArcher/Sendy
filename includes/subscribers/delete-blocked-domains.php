<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 

/********************************/
$app = isset($_POST['app']) && is_numeric($_POST['app']) ? $_POST['app'] : exit;

//Check if sub user is trying to download CSVs from other brands
if(get_app_info('is_sub_user')) 
{
	if(get_app_info('app')!=get_app_info('restricted_to_app'))
	{
		echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/blacklist-blocked-domains?i='.get_app_info('restricted_to_app').'"</script>';
		exit;
	}
}

//Delete all email addresses from Suppression list
$q = 'DELETE FROM blocked_domains WHERE app = '.$app;
$r = mysqli_query($mysqli, $q);
if ($r)
{
	echo true;
}
else
{
	echo false;
	error_log("[Unable to delete all domains in blocked_domains]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
	exit;
}
 
?>