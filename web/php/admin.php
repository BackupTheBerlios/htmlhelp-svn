<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

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
		echo "$admin_mysql -h $db_server -u $db_username -pXXXXXXXX $db_database < $file\n";
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
			$file = $admin_directory . '/' . $_POST['file'];
			mysql_import($file);
		}
		
		if($action == 'upload')
		{
			$file = $_FILES['file']['tmp_name'];
			if(is_uploaded_file($file))
				mysql_import($file);
		}
		
		if($action == 'delete')
		{
			$book_id = intval($_POST['book_id']);
			mysql_query('DELETE FROM `book` WHERE `id`=' . $book_id);
			mysql_query('DELETE FROM `toc_entry` WHERE `book_id`=' . $book_id);
			mysql_query('DELETE FROM `index_entry` WHERE `book_id`=' . $book_id);
			mysql_query('DELETE FROM `index_link` WHERE `book_id`=' . $book_id);
			mysql_query('DELETE FROM `page` WHERE `book_id`=' . $book_id);
			mysql_query('DELETE FROM `metadata` WHERE `book_id`=' . $book_id);
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
			echo '<select name="file">';
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
		echo '<select name="book_id">';
		echo '<option value="0">All</option>';
		$result = mysql_query('SELECT `id`, `title` FROM `book` ORDER BY `title`');
		while(list($book_id, $title) = mysql_fetch_row($result))
			echo '<option value="' . $book_id . '">' . $title . '</option>';
		echo '</select>';
		echo '<input type="submit" value="Delete">';
		echo '</form>';
		echo '</p>';
	}
	echo '</div>';
	
	echo '</body>';
	echo '</html>';
?>
