<?php
	include 'config.inc.php';
	include 'mysql.inc.php';
	include 'mysql_version.inc.php';

	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';

	echo '<script src="search.js"/>';
	
	$book_id = intval($_GET['book_id']);
	$query = $_GET['query'];
	$boolean_mode = intval($_GET['boolean_mode']) && mysql_check_version('4.0.1');
	
	echo '<textbox id="query" type="autocomplete" value="' . htmlspecialchars($query) . '" onkeypress="onQueryKeypress(event, ' . $book_id . ')"/>';
	
	echo '<listbox seltype="single" flex="1" onselect="onSearchSelect(event, ' . $book_id . ')">';
	$result = mysql_query('SELECT `path`, `title` FROM `page` WHERE book_id=' . $book_id . ' AND MATCH (`title`, `body`) AGAINST (\'' . mysql_escape_string($query) . '\'' . (boolean_mode ? ' IN BOOLEAN MODE' : '') . ')');
	while(list($path, $title) = mysql_fetch_row($result))
		echo '<listitem label="' . htmlspecialchars($title) . '" value="' . $path .'"/>';
	echo '</listbox>';

	echo '</window>';
?>
