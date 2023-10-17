<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	/********************************/
	$l = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, (int)$_GET['l']) : '';
	$filename = 'sample.csv';
	$cf_headers = '';
	$cf_data = '';
	$data = '';
	/********************************/
	
	//Get custom fields data
	$q = 'SELECT custom_fields FROM lists WHERE id = '.$l;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	  while($row = mysqli_fetch_array($r))
	  {
		  $custom_field = $row['custom_fields'];
	  }
	  if($custom_field!='')
	  {
		  $custom_field_array = explode('%s%', $custom_field);
		  foreach($custom_field_array as $cf)
		  {
				$cf_array = explode(':', $cf);
			    $cf_headers .= ',"'.$cf_array[0].'"';
				$cf_data .= ',';
		  }
	  }
	}
	
	$first_line = '"'._('Name').'","'._('Email').'"'.$cf_headers."\n";
	
	$data .= 'Philip Morris,pmorris@gmail.com'.$cf_data."\n";
	$data .= 'Jane Webster,jwebster@gmail.com'.$cf_data;
	$data = $first_line.str_replace("\r" , "" , $data);
	
	header("Content-type: application/csv");
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Pragma: no-cache");
	header("Expires: 0");
	print "$data";
?>