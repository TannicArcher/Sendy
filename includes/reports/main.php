<?php 
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//------------------------------------------------------//
	function get_app_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;		
		$q = 'SELECT '.$val.' FROM apps WHERE id = "'.get_app_info('app').'" AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row[$val];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_saved_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM campaigns WHERE id = "'.mysqli_real_escape_string($mysqli, $_GET['c']).'" AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$value = $row[$val]=='' ? '' : stripslashes($row[$val]);
		    	
		    	//if title
		    	if($val == 'title')
		    	{
			    	//tags for subject
					preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $value, $matches_var, PREG_PATTERN_ORDER);
					preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $value, $matches_val, PREG_PATTERN_ORDER);
					preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $value, $matches_all, PREG_PATTERN_ORDER);
					preg_match_all('/\[([^\]]+),\s*fallback=/i', $value, $matches_var, PREG_PATTERN_ORDER);
					preg_match_all('/,\s*fallback=([^\]]*)\]/i', $value, $matches_val, PREG_PATTERN_ORDER);
					preg_match_all('/(\[[^\]]+,\s*fallback=[^\]]*\])/i', $value, $matches_all, PREG_PATTERN_ORDER);
					$matches_var = $matches_var[1];
					$matches_val = $matches_val[1];
					$matches_all = $matches_all[1];
					for($i=0;$i<count($matches_var);$i++)
					{		
						$field = $matches_var[$i];
						$fallback = $matches_val[$i];
						$tag = $matches_all[$i];
						//for each match, replace tag with fallback
						$value = str_replace($tag, $fallback, $value);
					}
					$value = str_replace('[Name]', get_saved_data('from_name'), $value);
					$value = str_replace('[Email]', get_saved_data('from_email'), $value);
					
					//convert date
					date_default_timezone_set(get_app_info('timezone'));
					$sent = get_saved_data('sent');
					$today = $sent;
					$currentdaynumber = date('d', $today);
					$currentday = date('l', $today);
					$currentmonthnumber = date('m', $today);
					$currentmonth = date('F', $today);
					$currentyear = date('Y', $today);
					$unconverted_date = array('[currentdaynumber]', '[currentday]', '[currentmonthnumber]', '[currentmonth]', '[currentyear]');
					$converted_date = array($currentdaynumber, $currentday, $currentmonthnumber, $currentmonth, $currentyear);
					$value = str_replace($unconverted_date, $converted_date, $value);
		    	}
				
				return $value;
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_click_percentage($cid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$clicks_join = '';
		$clicks_array = array();
		$clicks_unique = 0;
		
		$q = 'SELECT * FROM links WHERE campaign_id = '.$cid;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
		    	$id = stripslashes($row['id']);
				$link = stripslashes($row['link']);
				$clicks = $row['clicks']=='' ? '' : stripslashes($row['clicks']);
				if($clicks!='')
					$clicks_join .= $clicks.',';				
		    }  
		}
		
		$clicks_array = explode(',', $clicks_join);
		$clicks_unique = count(array_unique($clicks_array));
		
		return $clicks_unique-1;
	}
	
	//------------------------------------------------------//
	function get_unsubscribes()
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT last_campaign FROM subscribers WHERE last_campaign = '.mysqli_real_escape_string($mysqli, $_GET['c']).' AND unsubscribed = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    return mysqli_num_rows($r); 
		}
		else
		{
			return 0;
		}
	}
	
	//------------------------------------------------------//
	function get_bounced($soft='', $c=0)
	//------------------------------------------------------//
	{
		global $mysqli;
		$campaign_id = isset($_GET['c']) ? mysqli_real_escape_string($mysqli, $_GET['c']) : $c;
		$campaign_id = is_numeric($campaign_id) ? $campaign_id : 0;
		
		if($soft=='soft') $q = 'SELECT last_campaign FROM subscribers WHERE last_campaign = '.mysqli_real_escape_string($mysqli, $_GET['c']).' AND bounce_soft = 1';
		else $q = 'SELECT last_campaign FROM subscribers WHERE last_campaign = '.$campaign_id.' AND bounced = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    return mysqli_num_rows($r); 
		}
		else
		{
			return 0;
		}
	}
	
	//------------------------------------------------------//
	function get_complaints()
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT last_campaign FROM subscribers WHERE last_campaign = '.mysqli_real_escape_string($mysqli, $_GET['c']).' AND complaint = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    return mysqli_num_rows($r); 
		}
		else
		{
			return 0;
		}
	}
	
	//------------------------------------------------------//
	function get_lists()
	//------------------------------------------------------//
	{
		global $mysqli;
		$name_array = array();
		
		$q = 'SELECT to_send_lists, segs FROM campaigns WHERE id = '.mysqli_real_escape_string($mysqli, $_GET['c']);
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) 
		{
			$to_send_lists = $row['to_send_lists'];
			$segs = $row['segs'];
		}
		
		if($to_send_lists!='')
		{
			$q2 = 'SELECT name FROM lists WHERE id IN ('.$to_send_lists.')';
			$r2 = mysqli_query($mysqli, $q2);
			if ($r2)
			{
			    while($row = mysqli_fetch_array($r2))
			    {
					$name = stripslashes($row['name']);
					array_push($name_array, '<span class="label">List: '.$name.'</span>');
			    }  
			}
		}
		
		if($segs!='')
		{
			$q3 = 'SELECT name FROM seg WHERE id IN ('.$segs.')';
			$r3 = mysqli_query($mysqli, $q3);
			if ($r3)
			{
			    while($row = mysqli_fetch_array($r3))
			    {
					$name = stripslashes($row['name']);
					array_push($name_array, '<span class="label">Segment: '.$name.'</span>');
			    }  
			}
		}
		
		$list_names = implode(' ', $name_array);
		
		if($list_names!='')
			return $list_names;
		else return 'No data';
	}
	
	//------------------------------------------------------//
	function get_excluded_lists()
	//------------------------------------------------------//
	{
		global $mysqli;
		$name_array = array();
		
		$q = 'SELECT lists_excl, segs_excl FROM campaigns WHERE id = '.mysqli_real_escape_string($mysqli, $_GET['c']);
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) 
		{
			$lists_excl = $row['lists_excl'];
			$segs_excl = $row['segs_excl'];
		}
		
		if($lists_excl!='')
		{
			$q2 = 'SELECT name FROM lists WHERE id IN ('.$lists_excl.')';
			$r2 = mysqli_query($mysqli, $q2);
			if ($r2)
			{
			    while($row = mysqli_fetch_array($r2))
			    {
					$name = stripslashes($row['name']);
					array_push($name_array, '<span class="label">List: '.$name.'</span>');
			    }  
			}
		}
		
		if($segs_excl!='')
		{
			$q3 = 'SELECT name FROM seg WHERE id IN ('.$segs_excl.')';
			$r3 = mysqli_query($mysqli, $q3);
			if ($r3)
			{
			    while($row = mysqli_fetch_array($r3))
			    {
					$name = stripslashes($row['name']);
					array_push($name_array, '<span class="label">Segment: '.$name.'</span>');
			    }  
			}
		}
		
		$list_names = implode(' ', $name_array);
		
		if($list_names!='')
			return $list_names;
		else return 'No data';
	}
	
	//------------------------------------------------------//
	function is_last_campaign($app, $cid)
	//------------------------------------------------------//
	{
		global $mysqli;		
		$q = 'SELECT id FROM campaigns where app = '.$app.' AND sent != "" ORDER BY sent DESC LIMIT 1';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$id = $row['id'];
		    }  
		}
		if($cid==$id) return true;
	}
	
	//------------------------------------------------------//
	function get_last_campaign_data($app, $data)
	//------------------------------------------------------//
	{
		global $mysqli;
		
		if($data=='title')
		{
			if(get_last_campaign_data($app, 'label')!='')
				$data = 'label';
		}
		
		$q = 'SELECT '.$data.' FROM campaigns WHERE sent != ""  AND app = '.$app.' ORDER BY id DESC LIMIT 1';
		$r = mysqli_query($mysqli, $q);
		if (mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) return $row[$data];
		else 
		{
			if($data=='title' || $data=='label')
				return '-';
			else
				return 0;
		}
	}
	
	//------------------------------------------------------//
	function last_campaign_title($app)
	//------------------------------------------------------//
	{
		global $mysqli;
		
		$q = 'SELECT id FROM campaigns WHERE sent != ""  AND app = '.$app.' ORDER BY id DESC LIMIT 1';
		$r = mysqli_query($mysqli, $q);
		if (mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) return $row['id'];
		else return 0;
	}
	
	//------------------------------------------------------//
	function get_open_rate($cid, $number=false)
	//------------------------------------------------------//
	{
		global $mysqli;
		
		$q = 'SELECT opens, recipients FROM campaigns WHERE id = '.$cid;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$opens = $row['opens'];
				$recipients = $row['recipients'];
				
				if($opens=='')
	  			{
		  			if($number)
		  				return 0;
		  			else
		  				return ' '._('No opens yet');
	  			}
	  			else
	  			{
		  			$opens_array = explode(',', $opens);
		  			$opens_array2 = array();
		  			foreach($opens_array as $oa)
		  			{
			  			$oa = $oa.',';
			  			$oa = delete_between(':', ',', $oa);
			  			array_push($opens_array2, $oa);
		  			}
		  			$opens_unique = count(array_unique($opens_array2));
		  			$percentage_opened = round($opens_unique/($recipients-get_bounced($cid)) * 100, 2);
		  			
		  			if($number)
		  				return $percentage_opened;
		  			else
			  			return $percentage_opened.'% '._('opened');
		  		}
		    }  
		}
		else
		{
			return ' '._('No campaigns yet');
		}
	}
?>