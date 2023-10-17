<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php
	//Imported file
	$file = $_FILES['html-file']['tmp_name'];
	$file_name = $_FILES['html-file']['name'];
	
	//Get brand info
	$aid = isset($_POST['aid']) && is_numeric($_POST['aid']) ? $_POST['aid'] : exit;
	$q = 'SELECT app_name, from_name, from_email, reply_to, query_string, opens_tracking, links_tracking FROM apps WHERE id = '.$aid;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		while($row = mysqli_fetch_array($r))
		{
			$app_name = $row['app_name'];
			$from_name = $row['from_name'];
			$from_email = $row['from_email'];
			$reply_to = $row['reply_to'];
			$query_string = $row['query_string'];
			$opens_tracking = $row['opens_tracking'];
			$links_tracking = $row['links_tracking'];
		}  
	}
	
	//Check filetype
	$extension_explode = explode('.', $file_name);
	$extension = $extension_explode[count($extension_explode)-1];
	$allowed = array("html", "htm");
	if(in_array($extension, $allowed)) //if file is an image, allow upload
	{
		$html_file_content = file_get_contents($file);
		
		if(!$html_file_content)
		{
			//Unable to read HTML file error
			echo "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"/><link rel=\"Shortcut Icon\" type=\"image/ico\" href=\"/img/favicon.png\"><title>"._('Unable to read HTML file')."</title></head><style type=\"text/css\">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #f2f2f2;width: 300px;height: 120px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;line-height: 18px;font-size: 12px;padding: 0 30px;}h2{font-weight: normal;text-align: center;font-size: 20px;}a{color: #000;}a:hover{text-decoration: none;}</style><body><div id=\"wrapper\"><p><h2>"._('Unable to read HTML file')."</h2></p><p>"._('Unable to read content of file using file_get_contents.')."</p></div></body></html>";
		}
		else
		{
			//Create new campaign
			$q = 'INSERT INTO campaigns (userID, app, from_name, from_email, reply_to, query_string, title, html_text, wysiwyg, opens_tracking, links_tracking) VALUES ('.get_app_info('main_userID').', '.$aid.', "'.$from_name.'", "'.$from_email.'", "'.$reply_to.'", "'.$query_string.'", "Untitled", "'.addslashes($html_file_content).'", 1, '.$opens_tracking.', '.$links_tracking.')';
			
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
				$inserted_id = mysqli_insert_id($mysqli);
				
				//Redirect to editing page
				header("Location: ".get_app_info('path')."/edit?i=".$aid."&c=$inserted_id");
			}
			else 
				show_error(_('Failed to create campaign with imported HTML file'), '<p>'.mysqli_error($mysqli).'</p>', true);
		}
	}
	else 
	{
		//Incorrect file type error
		echo "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"/><link rel=\"Shortcut Icon\" type=\"image/ico\" href=\"/img/favicon.png\"><title>"._('Incorrect file type')."</title></head><style type=\"text/css\">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #f2f2f2;width: 300px;height: 100px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;line-height: 18px;font-size: 12px;padding: 0 30px;}h2{font-weight: normal;text-align: center;font-size: 20px;}a{color: #000;}a:hover{text-decoration: none;}</style><body><div id=\"wrapper\"><p><h2>"._('Incorrect file type')."</h2></p><p>"._('Only .html and .htm files are allowed.')."</p></div></body></html>";
		exit;
	}
?>