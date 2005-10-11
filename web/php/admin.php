<?php

require_once 'inc/config.inc.php';
require_once 'lib/book_catalog.lib.php';

// authentication must be done before any actual output
$authenticated = FALSE;
$password = $_COOKIE['Password'];
if(isset($password))
{
	if($password == $admin_password)
		$authenticated = TRUE;
	else
		setcookie("Password", "", time() - 3600);
}

$action = $_POST['action'];
if($action == 'login')
{
	$password = $_POST['password'];
	if($password == $admin_password)
	{
		$authenticated = TRUE;
		setcookie('Password', $password);
	}
}
else
	if(!authenticated)
		$action = NULL;

header('Content-Type: text/html; charset=' . $internal_encoding);

echo '<?xml version="1.0" encoding="' . $internal_encoding . '"?>';	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=' . $internal_encoding . '"/>
		<title>Administration</title>
		<link href="css/default.css" type="text/css" rel="stylesheet"/>
	</head>
	<body>
		<div class="header">Administration</div>
		<div class="sidebox">
			<ul>
				<li><a href="#tags">Tags</a></li>
				<li><a href="#books">Books</a></li>
			<ul>
		</div>

<?php

$catalog = new BookCatalog();

if(isset($action))
{
	echo '<div class="content result">';

	// disable memory and time limits, necessary for some administration tasks
	ini_set('memory_limit','-1');
	if(!intval(ini_get('safe_mode')))
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
			
		case 'import_books':
			$files = $_POST['files'];
			if(isset($files))
				foreach($files as $file)
				{
					echo '<p>importing ' . htmlspecialchars($file, ENT_NOQUOTES) . '...</p>';
					$catalog->import_book($admin_directory . '/' .$file);
				}
			break;
		
		case 'upload_book':
			$file = $_FILES['file']['tmp_name'];
			if(isset($file) and is_uploaded_file($file))
			{
				echo '<p>Importing ' . htmlspecialchars($_FILES['file']['name'], ENT_NOQUOTES) . '...</p>';
				$catalog->import_book($file);
			}
			break;
		
		case 'index_books':
			$book_ids = $_POST['books'];
			if(isset($book_ids))
				foreach($book_ids as $book_id)
				{
					$book = $catalog->get_book_by_id($book_id);
					$title = $book->title();
					echo '<p>Indexing ' . htmlspecialchars($title, ENT_NOQUOTES) . '...</p>';
					$book->index_fulltext();
				}
			break;
		
		case 'delete_books';
			$book_ids = $_POST['books'];
			if(isset($book_ids))
				foreach($book_ids as $book_id)
				{
					$book = $catalog->get_book_by_id($book_id);
					$title = $book->title();
					echo '<p>Deleting ' . htmlspecialchars($title, ENT_NOQUOTES) . '...</p>';
					$book->delete();
				}
			break;
			
		case 'set_book_metadata':
			$book_ids = $_POST['books'];
			$name = $_POST['name'];
			$value = $_POST['value'];
			if(isset($book_ids) && isset($name) && isset($value))
				foreach($book_ids as $book_id)
				{
					$book = $catalog->get_book_by_id($book_id);
					$title = $book->title();
					echo '<p>Setting ' . htmlspecialchars($title, ENT_NOQUOTES) . ' metadata...</p>';
					$book->set_metadata($name, $value);
				}
			break;	
		
		case 'add_tag':
			$tag = $_POST['tag'];
			if(isset($tag))
			{
				$tags = array($tag);
				$catalog->add_tags($tags);
				echo '<p>Tag \'' . htmlspecialchars($tag, ENT_NOQUOTES) . '\' added.</p>';
			}
			break;
			
		case 'delete_tags':
			$tags = $_POST['tags'];
			if(isset($tags))
			{
				$catalog->delete_tags($tags);
				echo '<p>Tags deleted.</p>';
			}
			break;			
			
		case 'tag_books';
			$book_ids = $_POST['books'];
			$tags = $_POST['tags'];
			if(isset($book_ids) and isset($tags))
				$catalog->tag_books($book_ids, $tags);
			break;
			
		case 'untag_books';
			$book_ids = $_POST['books'];
			$tags = $_POST['tags'];
			if(isset($book_ids) and isset($tags))
				$catalog->untag_books($book_ids, $tags);
			break;
	}

	$finish_time = time();
	$ellapsed_time = $finish_time - $start_time;
	if($ellapsed_time)
		echo "<p>Ellapsed time: $ellapsed_time sec</p>\n";

	echo '</div>';
}

?>

		<div class="content">
		
			<?php if(!$authenticated) { ?>

			<h1 name="login">Login</h1>
			<form action="admin.php" method="POST">
				<p>
					<input type="hidden" name="action" value="login"/>
					<input type="password" name="password"/>
					<br/>
					<input type="submit" value="Login">
				</p>
			</form>
			
			<?php } else { ?>
				
			<h1 id="tags">Tags</h1>
			
			<form action="admin.php" method="POST">
				<p>
					<input type="text" name="tag"/>
					<button type="submit" name="action" value="add_tag">Add tag</button>
				<p>
			</form>
			<form action="admin.php" method="POST">
				<p>
					<select name="tags[]" multiple="yes" size="20">
						<?php
						$tags = $catalog->enumerate_tags();
						foreach($tags as $tag)
							echo '<option value="' . htmlspecialchars($tag, ENT_QUOTES) . '">' . htmlspecialchars($tag, ENT_NOQUOTES) . '</option>';
						?>
					</select>
					<select name="books[]" multiple="yes" size="20">
						<?php
						$books = $catalog->enumerate_book_ids();	
						foreach($books as $book_id => $book_title)
							echo '<option value="' . $book_id . '">' . htmlspecialchars($book_title, ENT_NOQUOTES) . '</option>';
						?>
					</select>
					<br/>
					<button type="submit" name="action" value="delete_tags">Delete tags</button>
					<button type="submit" name="action" value="tag_books">Tag books</button>
					<button type="submit" name="action" value="untag_books">Untag books</button>
					<br/>
				</p>
			</form>

			<h1 id="books">Books</h1>

			<?php if($admin_directory) { ?>
			<h2>Import</h2>
			<form action="admin.php" method="POST">
				<p>
					<select name="files[]" multiple="yes" size="20">
						<?php
						$dir = dir($admin_directory);
						$ext = '.tgz';
						$entries = array();
						while(false !== ($entry = $dir->read()))
							if(substr($entry, -strlen($ext)) == $ext)
								$entries[] = $entry;
						natcasesort($entries);
						foreach($entries as $entry)
							echo '<option value="' . $entry . '">' . substr($entry, 0, -strlen($ext)) . '</option>';
						?>
					</select>
					<br/>
					<button type="submit" name="action" value="import_books">Import books</button>
				</p>
			</form>
			<?php } ?>

			<h2>Upload</h2>
			<form enctype="multipart/form-data" action="admin.php" method="POST">
				<p>
					<?php
					$MAX_FILE_SIZE = ini_get('upload_max_filesize');
					if(substr($MAX_FILE_SIZE, -1) == 'M')
						$MAX_FILE_SIZE = intval(substr($MAX_FILE_SIZE, 0, -1))*1024*1024;
					if($MAX_FILE_SIZE)
						echo '<input type="hidden" name="MAX_FILE_SIZE" value="' . $MAX_FILE_SIZE . '"/>';
					?>
					<input type="file" name="file"/>
					<button type="submit" name="action" value="upload_book">Upload book</button>
				</p>
			</form>

			<h2>Edit</h2>
			
			<form action="admin.php" method="POST">
				<p>
					<select name="books[]" multiple="yes" size="20">
						<?php
						$books = $catalog->enumerate_book_ids();	
						foreach($books as $book_id => $book_title)
							echo '<option value="' . $book_id . '">' . htmlspecialchars($book_title, ENT_NOQUOTES) . '</option>';
						?>
					</select>
					<br/>
					<button type="submit" name="action" value="delete_books">Delete books</button>
					<button type="submit" name="action" value="index_books">Index books</button>
				</p>
				<p>
					<select name="name">
						<option value="name">Name</option>
						<option value="version">Version</option>
						<option value="language">Language</option>
						<option value="date">Date</option>
					</select>
					<input type="text" name="value"/>
					<button type="submit" name="action" value="set_book_metadata">Set book metadata</button>
				</p>
			</form>

			<?php } ?>

		</div>
	</body>
</html>
