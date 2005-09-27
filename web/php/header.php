<?php
	require_once 'include/config.inc.php';
	require_once 'include/book.inc.php';

	$alias = $_GET['book'];
	require 'include/get_book_from_alias.inc.php'; 	
	$title = htmlspecialchars($book->title(), ENT_NOQUOTES);

	header('Content-Type: text/html; charset=utf-8');

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	echo '<title>' . $title . '</title>';
	echo '<link href="css/html.css" type="text/css" rel="stylesheet"/>';
	echo '</head>';

	echo '<body class="header">' . $title . '</body>';
	
	echo '</html>';
?>
