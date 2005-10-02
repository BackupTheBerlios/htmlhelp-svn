<?php
	
	require_once 'inc/config.inc.php';

	$alias = $_GET['book'];
	require_once 'lib/get_book_from_alias.lib.php';
	
	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';

	echo '<script src="js/_index.js"/>';
	
	$query = $_GET['query'];
	
	echo '<textbox id="query" type="autocomplete" value="' . htmlspecialchars($query) . '" onkeypress="onQueryKeypress(event, \'' . htmlspecialchars($alias) . '\')"/>';
	
	echo '<listbox seltype="single" flex="1" onselect="onIndexSelect(event, \'' . htmlspecialchars($alias) . '\')">';
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
