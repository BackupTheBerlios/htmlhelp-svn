<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';

	echo '<script src="toc.js"/>';

	$book_id = intval($_GET['book_id']);
	
	echo '<button label="Sync" oncommand="onButtonCommand(event)"/>';
						
	echo '<tree id="tree" flex="1" seltype="single" hidecolumnpicker="true" onselect="onTocSelect(event, ' . $book_id . ')">';

	echo '<treecols>';
	echo '<treecol id="name" hideheader="true" primary="true" flex="1"/>';
	echo '</treecols>';

	function walk_toc($book_id, $number, $title, $path, $anchor)
	{
		$result = mysql_query('SELECT `no`, `title`, `path`, `anchor` FROM `toc_entry` WHERE `book_id`=' . $book_id . ' AND `parent_no`=' . $number . ' ORDER BY `no`');
		
		if(mysql_num_rows($result))
			echo '<treeitem container="true">';
		else
			echo '<treeitem>';
			
		echo '<treerow>';
		echo '<treecell label="' . htmlspecialchars($title) . '" value = "' . $path . ($anchor ? '#' . $anchor : '') . '"/>';
		echo '</treerow>';

		if(mysql_num_rows($result))
		{
			echo '<treechildren>';
			while(list($number, $title, $path, $anchor) = mysql_fetch_row($result))
				walk_toc($book_id, $number, $title, $path, $anchor);
			echo '</treechildren>';
		}
		
		echo '</treeitem>';
	}

	$result = mysql_query('SELECT `no`, `title`, `path`, `anchor` FROM `toc_entry` WHERE `book_id`=' . $book_id . ' AND `parent_no`=0 ORDER BY `no`');
	if(mysql_num_rows($result))
	{
		echo '<treechildren>';
		while(list($number, $title, $path, $anchor) = mysql_fetch_row($result))
			walk_toc($book_id, $number, $title, $path, $anchor);
		echo '</treechildren>';
	}
	
	echo '</tree>';
	
	echo '</window>';
?>
