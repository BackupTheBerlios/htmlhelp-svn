<?php
	require_once 'inc/config.inc.php';

	$alias = $_GET['book'];
	require 'inc/get_book_from_alias.inc.php';
	
	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';

	echo '<script src="js/search.js"/>';

	$query = $_GET['query'];	
	
	echo '<textbox id="query" type="autocomplete" value="' . htmlspecialchars($query) . '" onkeypress="onQueryKeypress(event, \'' . htmlspecialchars($alias) . '\')"/>';

	echo '<listbox seltype="single" flex="1" onselect="onSearchSelect(event, \'' . htmlspecialchars($alias) . '\')">';
	if($query)
	{
		$entries = $book->search($query);
		foreach($entries as $entry)
		{
			list($title, $path) = $entry;
			echo '<listitem label="' . htmlspecialchars($title) . '" value="' . $path .'"/>';
		}
	}
	echo '</listbox>';

	echo '</window>';
?>
