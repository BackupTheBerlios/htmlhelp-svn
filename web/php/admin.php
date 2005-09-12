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

	header('Content-Type: text/html; charset=utf-8');

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
	
	echo '<div class="sidebar">';
	echo  '<ul>';
	echo   '<li><a href="?what=books">Books</a></li>';
	echo   '<li><a href="?what=tags">Tags</a></li>';
	echo  '<ul>';
	echo '</div>';

	echo '<div class="content">';

	$catalog = new Book_Catalog();
	
	echo '<div class="result">';
	if(!$authenticated && $action != 'login')
		$action = NULL;
	
	set_time_limit(0);
	$start_time = time();
	switch($action)
	{
		case 'login':
			if($authenticated)
				echo '<p>Logged in successfully</p>';
			else
				echo '<p>Could not login.</p>';
			break;
			
		case 'add':
			$files = $_POST['files'];
			if(isset($files))
				foreach($files as $file)
				{
					echo '<p>importing ' . htmlspecialchars($file) . '...</p>';
					$catalog->import_book($admin_directory . '/' .$file);
				}
			$file = $_FILES['file']['tmp_name'];
			if(isset($file) and is_uploaded_file($file))
			{
				echo '<p>importing ' . htmlspecialchars($_FILES['file']['name']) . '...</p>';
				$catalog->import_book($file);
			}
			break;
		
		case 'edit':
			break;
		
		case 'index':
			$book_ids = $_POST['books'];
			if(isset($book_ids))
				foreach($book_ids as $book_id)
				{
					$book = new Book($book_id);
					$title = $book->title();
					echo '<p>Indexing ' . htmlspecialchars($title) . '...</p>';
					$book->index_fulltext();
				}
			break;
		
		case 'delete';
			$book_ids = $_POST['books'];
			if(isset($book_ids))
				foreach($book_ids as $book_id)
				{
					$book = new Book($book_id);
					$title = $book->title();
					echo '<p>Deleting ' . htmlspecialchars($title) . '...</p>';
					$book->delete();
				}
			break;
		
		case 'tag';
			$book_ids = $_POST['books'];
			$tags = $_POST['tags'];
			if(isset($book_ids) and isset($tags))
				foreach($book_ids as $book_id)
				{
					$book = new Book($book_id);
					$book->tag($tags);
				}
			break;
			
		case 'untag';
			$book_ids = $_POST['books'];
			$tags = $_POST['tags'];
			if(isset($book_ids) and isset($tags))
				foreach($book_ids as $book_id)
				{
					$book = new Book($book_id);
					$book->untag($tags);
				}
			break;
	}
	$finish_time = time();
	$ellapsed_time = $finish_time - $start_time;
	if($ellapsed_time)
		echo "<p>Ellapsed time: $ellapsed_time sec</p>\n";
	echo '</div>';
	
	echo '<div>';
	if($authenticated)
		$what = $_GET['what'];
	else
		$what = 'login';
	switch($what)
	{
		case 'login':
				echo '<div>';
				echo '<form action="admin.php" method="POST">';
				echo '<p>';
				echo '<input type="hidden" name="action" value="login"/>';
				echo '<input type="password" name="password"/>';
				echo '<br/>';
				echo '<input type="submit" value="Login">';
				echo '</p>';
				echo '</div>';
				break;
		
		case 'books':
			if($admin_directory)
			{
				echo '<form action="admin.php?what=books" method="POST">';
				echo '<p>';
				echo '<input type="hidden" name="action" value="add">';
				echo '<select name="files[]" multiple="yes" size="10">';
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
				echo '<br/>';
				echo '<input type="submit" value="Import"/>';
				echo '</p>';
				echo '</form>';
			}
			
			echo '<form enctype="multipart/form-data" action="admin.php" method="POST">';
			echo '<p>';
			echo '<input type="hidden" name="action?what=books" value="add">';
			$MAX_FILE_SIZE = ini_get('upload_max_filesize');
			if(substr($MAX_FILE_SIZE, -1) == 'M')
				$MAX_FILE_SIZE = intval(substr($MAX_FILE_SIZE, 0, -1))*1024*1024;
			if($MAX_FILE_SIZE)
				echo '<input type="hidden" name="MAX_FILE_SIZE" value="' . $MAX_FILE_SIZE . '"/>';
			echo '<input type="file" name="file"/>';
				echo '<br/>';
			echo '<input type="submit" value="Upload"/>';
			echo '</p>';
			echo '</form>';

			echo '<hr/>';

			/*
			echo '<form action="admin.php" method="GET">';
			echo '<p>';
			echo '<input type="hidden" name="what" value="books">';
			echo '<select name="book">';
			$entries = $catalog->enumerate_books();	
			foreach($entries as $title => $book)
				echo '<option value="' . $book->id . '">' . $title . '</option>';
			echo '</select>';
			echo '<br/>';
			echo '<input type="submit" value="Edit">';
			echo '</p>';
			echo '</form>';

			echo '<hr/>';
			*/

			echo '<form action="admin.php?what=books" method="POST">';
			echo '<p>';
			echo '<input type="hidden" name="action" value="index">';
			echo '<select name="books[]" multiple="yes" size="20">';
			$entries = $catalog->enumerate_books();	
			foreach($entries as $title => $book)
				echo '<option value="' . $book->id . '">' . $title . '</option>';
			echo '</select>';
			echo '<select name="tags[]" multiple="yes" size="20">';
			$tags = $catalog->enumerate_tags();
			foreach($tags as $tag)
				echo '<option value="' . htmlspecialchars($tag) . '">' . htmlspecialchars($tag, ENT_NOQUOTES) . '</option>';
			echo '</select>';			
			echo '<br/>';
			echo '<button type="submit" name="action" value="index">Index</button>';
			echo '<br/>';
			echo '<button type="submit" name="action" value="delete">Delete</button>';
			echo '<br/>';
			echo '<button type="submit" name="action" value="tag">Tag</button>';
			echo '<br/>';
			echo '<button type="submit" name="action" value="untag">Untag</button>';
			echo '</p>';
			echo '</form>';
			break;

		case 'book':			
			$book_id = intval($_GET['book']);
			$book = new Book($book_id);
			echo '<form action="admin.php?what=book" method="POST">';
			echo '<input type="hidden" name="action" value="edit">';
			echo '<input type="hidden" name="book" value="' . $book_id . '"/>';
			echo '<p>' . htmlspecialchars($book->title()) . '</p>';
			echo '<p>';
			echo '<select name="tags[]" multiple="yes">';
			echo '</select>';
			echo '<br/>';
			echo '<input type="submit" value="Edit">';
			echo '</p>';
			echo '</form>';
			break;
	}
	echo '</div>';

	echo '</div>';

	echo '</body>';
	echo '</html>';
?>
