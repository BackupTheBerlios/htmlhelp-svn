<?php
	
	include 'config.inc.php';
	include 'book.inc.php';

	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';

	echo '<script src="_index.js"/>';
	
	$book = new Book($_GET['book_id']);
	$query = $_GET['query'];
	
	echo '<textbox id="query" type="autocomplete" value="' . htmlspecialchars($query) . '" onkeypress="onQueryKeypress(event, ' . $book->id . ')"/>';
	
	echo '<listbox seltype="single" flex="1" onselect="onIndexSelect(event, ' . $book->id . ')">';
	if(isset($query))
	{
		$entries = $book->index($query);
		foreach($entries as $entry)
		{
			list($term, $link) = $entry;
			echo '<listitem label="' . htmlspecialchars($term) . '" value="' . $link . '"/>';
		}
	}
	echo '</listbox>';

	echo '</window>';
?>
