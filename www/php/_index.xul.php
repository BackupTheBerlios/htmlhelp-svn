<?php
	
	include 'config.inc.php';
	include 'mysql.inc.php';

	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';

	echo '<listbox flex="1">';
	if($book_id = intval($_GET['book_id']))
	{
		$result = mysql_query('SELECT `term`, `path`, `anchor` FROM `index_entry`,`index_link` WHERE `index_entry`.`book_id`=' . $book_id . ' AND `index_link`.`book_id`=' . $book_id . ' AND `index_link`.`no`=`index_entry`.`no` ORDER BY `index_entry`.`term`');
		while(list($term, $path, $anchor) = mysql_fetch_row($result))
		{
			echo '<listitem label="' . htmlspecialchars($term) . '" value="' . $book_id . '/' . $path . ($anchor ? '#' . $anchor : '') . '"/>';
		}
	}
	echo '</listbox>';

	echo '</window>';
?>
