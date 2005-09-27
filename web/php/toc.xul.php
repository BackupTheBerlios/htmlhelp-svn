<?php
	require_once 'include/config.inc.php';
	require_once 'include/book.inc.php';

	$alias = $_GET['book'];
	require 'include/get_book_from_alias.inc.php';

	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';

	echo '<script src="js/toc.js"/>';

	echo '<button label="Sync" oncommand="onButtonCommand(event)"/>';
						
	echo '<tree id="tree" flex="1" seltype="single" hidecolumnpicker="true" onselect="onTocSelect(event, \'' . htmlspecialchars($alias) . '\')">';

	echo '<treecols>';
	echo '<treecol id="name" hideheader="true" primary="true" flex="1"/>';
	echo '</treecols>';

	function walk_toc_entry($title, $link, $children)
	{
		if(count($children))
			echo '<treeitem container="true">';
		else
			echo '<treeitem>';
			
		echo '<treerow>';
		echo '<treecell label="' . htmlspecialchars($title) . '" value="' . htmlspecialchars($link) . '"/>';
		echo '</treerow>';

		walk_toc_entries($children);
		
		echo '</treeitem>';
	}

	function walk_toc_entries($entries)
	{
		if(count($entries))
		{
			echo '<treechildren>';
			foreach($entries as $entry)
			{
				list($title, $link, $children) = $entry;
				walk_toc_entry($title, $link, $children);
			}
			echo '</treechildren>';
		}
	}

	$entries = $book->toc();
	walk_toc_entries($entries);
	
	echo '</tree>';
	
	echo '</window>';
?>
