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

	function mysql_import($file)
	{
		// FIXME: do not rely on the mysql executable

		global $admin_mysql, $db_server, $db_username, $db_password, $db_database;
		
		echo '<p>Importing ' . $file . '...</p>';
		echo '<pre>';
		$handle = popen("$admin_mysql -h $db_server -u $db_username -p$db_password -e \"source $file\" $db_database", 'r');
		do {
			$data = fread($handle, 8192);
			if(!strlen($data))
				break;
			echo htmlspecialchars($data);
		} while (true);
		echo '</pre>';
		echo '<p>Exit code: ' . pclose($handle) . '</p>';
	}
	
	echo '<div>';
	if($authenticated)
	{
		if($action == 'import')
		{
			$files = $_POST['files'];
			foreach($files as $file)
				mysql_import($admin_directory . '/' .$file);
		}
		
		if($action == 'upload')
		{
			$file = $_FILES['file']['tmp_name'];
			if(is_uploaded_file($file))
				mysql_import($file);
		}
		
		if($action == 'delete')
		{
			$books = $_POST['books'];
			foreach($books as $book)
			{
				$book = new Book($book);
				$book->delete();
			}
		}
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
			while(false !== ($entry = $dir->read()))
				if(substr($entry, -strlen($ext)) == $ext)
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
		$entries = book_catalog();	
		foreach($entries as $book => $title)
			echo '<option value="' . $book . '">' . $title . '</option>';
		echo '</select>';
		echo '<input type="submit" value="Delete">';
		echo '</form>';
		echo '</p>';
	}
	echo '</div>';
	
	echo '</body>';
	echo '</html>';
?>
