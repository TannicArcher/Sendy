<?php include('../_connect.php');?>
<?php include('../../includes/helpers/short.php');?>
<?php 
	//-------------------------- ERRORS -------------------------//
	$error_core = array('No data passed', 'API key not passed', 'Invalid API key');
	$error_passed = array('No brands found');
	//-----------------------------------------------------------//
	
	//--------------------------- POST --------------------------//
	//api_key
	if(isset($_POST['api_key'])) $api_key = mysqli_real_escape_string($mysqli, $_POST['api_key']);
	else $api_key = null;
	
	//-----------------------------------------------------------//
	
	//----------------------- VERIFICATION ----------------------//
	//Core data
	if($api_key==null && $list_id==null)
	{
		echo $error_core[0];
		exit;
	}
	if($api_key==null)
	{
		echo $error_core[1];
		exit;
	}
	else if(!verify_api_key($api_key))
	{
		echo $error_core[2];
		exit;
	}
	
	//-----------------------------------------------------------//
	
	//-------------------------- QUERY --------------------------//
	
	$q = 'SELECT id, app_name FROM apps';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$i = 1;
		$output = '{';
		
	    while($row = mysqli_fetch_array($r))
	    {
			$id = $row['id'];
			$app_name = $row['app_name'];
			
			$output .= '"brand'.$i.'":
				{
					"id": "'.$id.'",
					"name": "'.$app_name.'"
				},';
			
			$i++;
	    }  
		
		$output = substr($output, 0, -1);
		$output .= '}';
		
		echo $output;
	}
	else
	{
		echo $error_passed[0];
	}
	//-----------------------------------------------------------//
?>
