<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$book_id = intval($_GET['book_id']);
	$result = mysql_query('SELECT `title` FROM `book` WHERE `id`=' . $book_id);
	list($title) = mysql_fetch_row($result);
	$title = htmlspecialchars($title, ENT_NOQUOTES);

	header('Content-Type: text/html; charset=utf-8');
		
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	echo '<title>' . $title . '</title>';
	echo '<link href="' . $css . '" type="text/css" rel="stylesheet"/>';
	echo '</head>';

	echo '<body class="header">' . $title . '</body>';
	
	echo '</html>';
?>
