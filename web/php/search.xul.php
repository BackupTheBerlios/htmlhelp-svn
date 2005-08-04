<?php
	include 'config.inc.php';
	include 'book.inc.php';
	include 'search.inc.php';

	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';

	echo '<script src="search.js"/>';
	
	$book = new Book($_GET['book']);
	$query = $_GET['query'];
	
	echo '<textbox id="query" type="autocomplete" value="' . htmlspecialchars($query) . '" onkeypress="onQueryKeypress(event, \'' . htmlspecialchars($book->alias) . '\')"/>';
	
	echo '<listbox seltype="single" flex="1" onselect="onSearchSelect(event, \'' . htmlspecialchars($book->alias) . '\')">';
	if($query)
	{
		$search = parse_search($query);
		$entries = $search->apply($book);
		foreach($entries as $entry)
		{
			list($title, $path) = $entry;
			echo '<listitem label="' . htmlspecialchars($title) . '" value="' . $path .'"/>';
		}
	}
	echo '</listbox>';

	echo '</window>';
?>
