<?php
	
	include 'config.inc.php';
	include 'mysql.inc.php';

	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';

	echo '<script src="_index.js"/>';
	
	$book_id = intval($_GET['book_id']);
	$query = $_GET['query'];
	
	echo '<textbox id="query" type="autocomplete" value="' . htmlspecialchars($query) . '" onkeypress="onQueryKeypress(event, ' . $book_id . ')"/>';
	
	echo '<listbox seltype="single" flex="1" onselect="onIndexSelect(event, ' . $book_id . ')">';
	$result = mysql_query('SELECT `term`, `path`, `anchor` FROM `index_entry`,`index_link` WHERE `index_entry`.`book_id`=' . $book_id . ' AND `index_link`.`book_id`=' . $book_id . ' AND `index_link`.`no`=`index_entry`.`no`' . ($query ? ' AND LOCATE(\'' . mysql_escape_string($query) . '\', `term`)' : '') . ' ORDER BY `index_entry`.`term`');
	while(list($term, $path, $anchor) = mysql_fetch_row($result))
	{
		echo '<listitem label="' . htmlspecialchars($term) . '" value="' . $path . ($anchor ? '#' . $anchor : '') . '"/>';
	}
	echo '</listbox>';

	echo '</window>';
?>
