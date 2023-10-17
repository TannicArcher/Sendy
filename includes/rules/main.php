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
	function get_data($val, $table, $id)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM '.$table.' WHERE id = '.$id;
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
	function totals($app)
	//------------------------------------------------------//
	{
		global $mysqli;
		global $privileges;
			
		$q = 'SELECT id FROM rules WHERE brand = '.$app.' '.$privileges;
		$r = mysqli_query($mysqli, $q);
		if ($r) return mysqli_num_rows($r);
	}
	
	//------------------------------------------------------//
	function get_lists($app)
	//------------------------------------------------------//
	{
		global $mysqli;
			
		//Get sorting preference
		$q = 'SELECT templates_lists_sorting FROM apps WHERE id = '.$app;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $templates_lists_sorting = $row['templates_lists_sorting'];
		$sortby = $templates_lists_sorting=='date' ? 'id DESC' : 'name ASC';
				
		//Get lists
		$q = 'SELECT id, name FROM lists WHERE app = '.$app.' ORDER BY '.$sortby;
		$r = mysqli_query($mysqli, $q);
		$data = '<li class="dropdown-header">'._('Lists').'</li>
				 <li class="divider"></li>';
		if (mysqli_num_rows($r) > 0) 
		{
			while($row = mysqli_fetch_array($r))
		    {
				$id = $row['id'];
				$name = $row['name'];
				$data .= '<li><a href="javascript:void(0)" class="list" data-list-id="'.$id.'">'.$name.'</a></li>';
		    }
		}
		else
		{
			$data .= '<li><a href="javascript:void(0)" class="list" data-list-id="">'._('No lists found').'</a></li>';
		}
		return $data;
	}
	
	//------------------------------------------------------//
	function pagination($limit, $type='rules')
	//------------------------------------------------------//
	{		
		global $p;
		
		$curpage = $p;
		
		$next_page_num = 0;
		$prev_page_num = 0;
		
		if($type=='rules') $total_rules = totals(get_app_info('app'));
		$total_pages = @ceil($total_rules/$limit);
		
		if($total_rules > $limit)
		{
			if($curpage>=2)
			{
				$next_page_num = $curpage+1;
				$prev_page_num = $curpage-1;
			}
			else
			{
				$next_page_num = 2;
			}
		
			echo '<div class="btn-group" id="pagination">';
			
			//Prev btn
			if($curpage>=2)
				if($prev_page_num==1)
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/'.$type.'?i='.get_app_info('app').'\'"><span class="icon icon icon-arrow-left"></span></button>';
				else
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/'.$type.'?i='.get_app_info('app').'&p='.$prev_page_num.'\'"><span class="icon icon icon-arrow-left"></span></button>';
			else
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-left"></span></button>';
			
			//Next btn
			if($curpage==$total_pages)
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-right"></span></button>';
			else
				echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/'.$type.'?i='.get_app_info('app').'&p='.$next_page_num.'\'"><span class="icon icon icon-arrow-right"></span></button>';
					
			echo '</div>';
		}
	}
?>