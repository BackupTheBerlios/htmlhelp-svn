<?php

require_once 'inc/config.inc.php';

require_once 'lib/book_catalog.lib.php';

require 'inc/admin_auth.inc.php';

require 'inc/header.inc.php';

$catalog = new BookCatalog();

$action = $_POST['action'];
if(isset($action))
{
	echo '<div class="content result">';

	// disable memory and time limits, necessary for some administration tasks
	ini_set('memory_limit','-1');
	if(!intval(ini_get('safe_mode')))
		set_time_limit(0);

	echo "<pre>\n";
	$start_time = time();
	switch($action)
	{
		case 'import_books':
			$files = $_POST['files'];
			if(isset($files))
				foreach($files as $file)
				{
					echo "Importing " . htmlspecialchars($file, ENT_NOQUOTES) . "\n";
					ob_flush();
					flush();
					$catalog->import_book($admin_directory . "/" .$file);
				}
			break;
		
		case 'upload_book':
			$file = $_FILES['file']['tmp_name'];
			if(isset($file) and is_uploaded_file($file))
			{
				echo "Importing " . htmlspecialchars($_FILES["file"]["name"], ENT_NOQUOTES) . "\n";
				ob_flush();
				flush();
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
					echo "Indexing " . htmlspecialchars($title, ENT_NOQUOTES) . "\n";
					ob_flush();
					flush();
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
					echo "Deleting " . htmlspecialchars($title, ENT_NOQUOTES) . "\n";
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
					echo "Setting " . htmlspecialchars($title, ENT_NOQUOTES) . " metadata...\n";
					$book->set_metadata($name, $value);
				}
			break;	
	}

	$finish_time = time();
	$ellapsed_time = $finish_time - $start_time;
	if($ellapsed_time)
		echo "Ellapsed time: $ellapsed_time sec\n";

	echo "</pre>";
	echo '</div>';
}

?>

	<div class="content">

		<h2>Books</h2>

<?php 
	if($admin_directory) { 
?>
		<h3>Import</h3>
		<form action="admin.php" method="post">
			<p>
				<select name="files[]" multiple="multiple" size="20">
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

<?php
	} 
?>
		<h3>Upload</h3>
		<form enctype="multipart/form-data" action="admin.php" method="post">
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

		<h3>Edit</h3>
		
		<form action="admin.php" method="post">
			<p>
				<select name="books[]" multiple="multiple" size="20">
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
	</div>
<?php
require_once 'inc/footer.inc.php';
?>