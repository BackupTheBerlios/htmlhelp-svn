<?php
	require_once 'config.inc.php';
	require_once 'book.inc.php';

	$authenticated = 0;
	$password = $_COOKIE['Password'];
	if(isset($password))
	{
		if($password == $admin_password)
			$authenticated = 1;
		else
			setcookie("Password", "", time() - 3600);
	}
	
	$action = $_POST['action'];
	if($action == 'login')
	{
		$password = $_POST['password'];
		if($password == $admin_password)
		{
			$authenticated = 1;
			setcookie('Password', $password);
		}
	}
	
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	echo '<title>Administration</title>';
	echo '<link href="' . $css . '" type="text/css" rel="stylesheet"/>';
	echo '</head>';
	echo '<body>';
	
	echo '<div class="header">Administration</div>';

	$catalog = new Book_Catalog();
	
	echo '<div>';
	if($authenticated)
	{
		set_time_limit(0);
		$start_time = time();

		if($action == 'import')
		{
			$files = $_POST['files'];
			foreach($files as $file)
				mysql_import_dump($admin_directory . '/' .$file);
		}
		
		if($action == 'upload')
		{
			$file = $_FILES['file']['tmp_name'];
			if(is_uploaded_file($file))
				mysql_import($file);
		}
		
		if($action == 'delete')
		{
			$book_ids = $_POST['books'];
			foreach($book_ids as $book_id)
			{
				$book = new Book($book_id);
				$book->delete();
			}
		}
		
		if($action == 'index')
		{
			$book_ids = $_POST['books'];
			foreach($book_ids as $book_id)
			{
				$book = new Book($book_id);
				$book->index_fulltext();
			}
		}

		$finish_time = time();
		$ellapsed_time = $finish_time - $start_time;
		echo "<p>Ellapsed time: $ellapsed_time sec</p>\n";
	}
	echo '</div>';
	
	echo '<div>';
	if(!$authenticated)
	{
		echo '<form action="admin.php" method="post">';
		echo '<input type="hidden" name="action" value="login"/>';
		echo '<input type="password" name="password"/>';
		echo '<input type="submit" value="Login">';
		echo '</form>';
	}
	else
	{
		if($admin_directory)
		{
			echo '<p>';
			echo '<form action="admin.php" method="post">';
			echo '<input type="hidden" name="action" value="import"/>';
			echo '<select name="files[]" multiple="yes">';
			$dir = dir($admin_directory);
			$ext = '.sql';
			$entries = array();
			while(false !== ($entry = $dir->read()))
				if(substr($entry, -strlen($ext)) == $ext)
					$entries[] = $entry;
			natcasesort($entries);
			foreach($entries as $entry)
				echo '<option value="' . $entry . '">' . substr($entry, 0, -strlen($ext)) . '</option>';
			echo '</select>';
			echo '<input type="submit" value="Import">';
			echo '</form>';
			echo '</p>';
		}
		
		echo '<p>';
		echo '<form enctype="multipart/form-data" action="admin.php" method="POST">';
		echo '<input type="hidden" name="action" value="upload"/>';
		$MAX_FILE_SIZE = ini_get('upload_max_filesize');
		if(substr($MAX_FILE_SIZE, -1) == 'M')
			$MAX_FILE_SIZE = intval(substr($MAX_FILE_SIZE, 0, -1))*1024*1024;
		if($MAX_FILE_SIZE)
			echo '<input type="hidden" name="MAX_FILE_SIZE" value="' . $MAX_FILE_SIZE . '">';
		echo '<input type="file" name="file">';
		echo '<input type="submit" value="Upload">';
		echo '</form>';
		echo '</p>';

		echo '<p>';
		echo '<form action="admin.php" method="post">';
		echo '<input type="hidden" name="action" value="delete"/>';
		echo '<select name="books[]" multiple="yes">';
		$entries = $catalog->enumerate_ids();	
		foreach($entries as $book_id => $title)
			echo '<option value="' . $book_id . '">' . $title . '</option>';
		echo '</select>';
		echo '<input type="submit" value="Delete">';
		echo '</form>';
		echo '</p>';

		echo '<p>';
		echo '<form action="admin.php" method="post">';
		echo '<input type="hidden" name="action" value="index"/>';
		echo '<select name="books[]" multiple="yes">';
		$entries = $catalog->enumerate_ids();	
		foreach($entries as $book_id => $title)
			echo '<option value="' . $book_id . '">' . $title . '</option>';
		echo '</select>';
		echo '<input type="submit" value="Index">';
		echo '</form>';
		echo '</p>';
	}
	echo '</div>';
	
	echo '</body>';
	echo '</html>';
?>
