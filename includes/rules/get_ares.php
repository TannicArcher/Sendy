<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$list = isset($_POST['list']) && is_numeric($_POST['list']) ? mysqli_real_escape_string($mysqli, (int)$_POST['list']) : 0;
	
	//Get autoresponders
	$q = 'SELECT id, name FROM ares WHERE list = '.$list.' ORDER BY type ASC';
	$r = mysqli_query($mysqli, $q);
	$data = '<li class="dropdown-header">'._('Autoresponders').'</li>
			 <li class="divider"></li>
	';
	if (mysqli_num_rows($r) > 0) 
	{
		while($row = mysqli_fetch_array($r))
	    {
			$id = $row['id'];
			$name = $row['name'];
			$data .= '<li><a href="javascript:void(0)" class="ares" data-ares-id="'.$id.'">'.$name.'</a></li>';
	    }
	}
	else	 $data .= '<li><a href="javascript:void(0)" class="ares" data-ares-id="">'._('No autoresponders found').'</a></li>';
	
	echo $data;
?>