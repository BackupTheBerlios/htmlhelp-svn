<?php
	
	include 'config.inc.php';
	include 'mysql.inc.php';

	// Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul" title="' . $title . '">';

	echo '<script src="books.js"/>';
	
	echo '<listbox seltype="single" flex="1" onselect="onBookSelect(event)">';
	
	$result = mysql_query('SELECT `id`, `title` FROM `book` ORDER BY `title`');
	while(list($book_id, $title) = mysql_fetch_row($result))
		echo '<listitem label="' . htmlspecialchars($title) . '" value="' . $book_id . '"/>';
				
	echo '</listbox>';
	
	echo '</window>';

?>
