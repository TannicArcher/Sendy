<?php 
	//--------------------------------------------------------------//
	function update_subscriber_custom_field($userID, $custom_field_name, $custom_field_value)
	//--------------------------------------------------------------//
	{
		global $mysqli;
		
		$q = 'SELECT lists.id, lists.custom_fields, subscribers.custom_fields as custom_values FROM lists, subscribers WHERE (lists.id = subscribers.list) AND subscribers.id = '.$userID;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$listID = $row['id'];
				$custom_fields = $row['custom_fields'];
				$custom_values = $row['custom_values'];
		    }  
		    
		    $custom_fields_value = '';
		    $custom_fields_array = explode('%s%', $custom_fields);
		    $custom_values_array = explode('%s%', $custom_values);
		    $custom_fields_count = count($custom_fields_array);
		    
		    //Get custom field position
			foreach($custom_fields_array as $key => $cf)
			{
				$cf_array = explode(':', $cf);
				
				//if custom field format is Text and it's the custom field that we want to update
				if($cf_array[1]=='Text' && $cf_array[0]==$custom_field_name)
				{
					$cf_position = $key;
					break;
				}
			}
			
		    if($cf_position!=0 || $cf_position!='')
		    {
			    //Update custom field value
			    for($i=0;$i<$custom_fields_count;$i++)
			    {		    
				    if($i==$cf_position)
					    $custom_fields_value .= $custom_field_value;
				    else
					    $custom_fields_value .= $custom_values_array[$i];
					    
				    $custom_fields_value .= '%s%';
			    }
			    
			    $custom_fields_value = substr($custom_fields_value, 0, -3);
			    
			    //Update subscriber's custom field column
			    $q = 'UPDATE subscribers SET custom_fields = "'.$custom_fields_value.'" WHERE id = '.$userID;
			    $r = mysqli_query($mysqli, $q);
			    if(!$r) error_log(mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
			}
			else error_log('[Custom field does not exist] '.mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
		}
		else error_log(mysqli_error($mysqli).': in '.__FILE__.' on line '.__LINE__);
	}
?>