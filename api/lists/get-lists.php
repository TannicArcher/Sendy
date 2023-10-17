<?php include('../_connect.php');?>
<?php include('../../includes/helpers/short.php');?>
<?php 
	//-------------------------- ERRORS -------------------------//
	$error_core = array('No data passed', 'API key not passed', 'Invalid API key');
	$error_passed = array('Brand ID not passed', 'Brand does not exist', 'No lists found');
	//-----------------------------------------------------------//
	
	//--------------------------- POST --------------------------//
	//api_key
	if(isset($_POST['api_key'])) $api_key = mysqli_real_escape_string($mysqli, $_POST['api_key']);
	else $api_key = null;
	
	//brand_id
	if(isset($_POST['brand_id'])) $brand_id = $_POST['brand_id'];
	else $brand_id = null;
	
	//include_hidden
	if(isset($_POST['include_hidden'])) $include_hidden = $_POST['include_hidden'];
	else $include_hidden = 'no';
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
	
	//Passed data
	if($brand_id==null)
	{
		echo $error_passed[0];
		exit;
	}
	else
	{
		$q = 'SELECT id FROM apps WHERE id = '.$brand_id;
		$r = mysqli_query($mysqli, $q);
		if (mysqli_num_rows($r) == 0) 
		{
			echo $error_passed[1]; 
			exit;
		}
	}
	//-----------------------------------------------------------//
	
	//-------------------------- QUERY --------------------------//
	
	//Get sorting preference
	$q = 'SELECT templates_lists_sorting FROM apps WHERE id = '.$brand_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) 
	{
		while($row = mysqli_fetch_array($r)) 
		{
			$templates_lists_sorting = $row['templates_lists_sorting'];
		}
	}
	
	$sortby = $templates_lists_sorting=='date' ? 'id DESC' : 'name ASC';
	
	$show_hidden = $include_hidden=='yes' || $include_hidden=='true' || $include_hidden==true ? ' OR hide = 1' : '';
	
	//Get lists
	$q = 'SELECT id, name FROM lists WHERE app = '.$brand_id.' AND (hide = 0 '.$show_hidden.') ORDER BY '.$sortby;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    $i = 1;
		$output = '{';
		
		while($row = mysqli_fetch_array($r))
		{
			$id = $row['id'];
			$name = $row['name'];
			
			$output .= '"list'.$i.'":
				{
					"id": "'.encrypt_val($id).'",
					"name": "'.$name.'"
				},';
			
			$i++;
		}  
		
		$output = substr($output, 0, -1);
		$output .= '}';
		
		echo $output;
	}
	else
	{
		echo $error_passed[2];
	}
	//-----------------------------------------------------------//
?>