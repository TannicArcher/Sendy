<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$list = isset($_POST['list']) && is_numeric($_POST['list']) ? mysqli_real_escape_string($mysqli, (int)$_POST['list']) : 0;
	$type = isset($_POST['type']) ? mysqli_real_escape_string($mysqli, $_POST['type']) : '';
	
	$data = '
		<li><code>trigger</code> '.$type.'</li>
		<li><code>name</code> '._('Name of the user').'</li>
		<li><code>email</code> '._('User\'s email address').'</li>
		<li><code>list_id</code> '._('The encrypted list ID').'</li>
		<li><code>list_name</code> '._('The name of the list being subscribed to').'</li>
		<li><code>list_url</code> '._('URL of the list being subscribed to').'</li>
		<li><code>gravatar</code> '._('User\'s Gravatar image URL').'</li>
	';
				
	//Get custom fields
	$q = 'SELECT custom_fields FROM lists WHERE id = '.$list;
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) > 0) 
	{
		while($row = mysqli_fetch_array($r))
			$custom_fields = $row['custom_fields'];
		
		//Populate custom fields (if available)
		if($custom_fields!='')
		{			
			//get custom fields list and format it for db insert
			$custom_fields_array = explode('%s%', $custom_fields);
			foreach($custom_fields_array as $cf)
			{
				$cf_array = explode(':', $cf);
				$data .= '<li><code>'.$cf_array[0].'</code> The value of this custom field</li>';
			}
		}
	}
	else
	{
		error_log("[Unable to get custom_fields from lists]".mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
	}
	echo $data;
?>