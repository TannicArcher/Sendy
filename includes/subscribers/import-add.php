<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php

/********************************/
$userID = get_app_info('main_userID');
$new_list_name = mysqli_real_escape_string($mysqli, $_POST['list_name']);
$app = (int)$_POST['app'];
$opt_in = isset($_POST['opt_in']) && is_numeric($_POST['opt_in']) ? mysqli_real_escape_string($mysqli, (int)$_POST['opt_in']) : 0;
$from_report = isset($_POST['from_report']) && is_numeric($_POST['from_report']) ? mysqli_real_escape_string($mysqli, (int)$_POST['from_report']) : '';
/********************************/

//add new list
$q = 'INSERT INTO lists (app, userID, name, opt_in) VALUES ('.$app.', '.$userID.', "'.$new_list_name.'", '.$opt_in.')';
$r = mysqli_query($mysqli, $q);
if ($r)
{
    $listID = mysqli_insert_id($mysqli);
}

//If list is created from a report, return the list ID
if($from_report)
{
	echo $listID;
}
//Otherwise, go to the CSV import page after creating this list
else
{
	//return
	header("Location: ".get_app_info('path').'/update-list?i='.$app.'&l='.$listID);
}
?>
