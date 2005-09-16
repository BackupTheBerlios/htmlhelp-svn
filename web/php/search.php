<?php
	require_once 'config.inc.php';
	require_once 'book.inc.php';

	$alias = $_GET['book'];
	require 'get_book_from_alias.inc.php';
	
	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-Type: text/html; charset=utf-8');
		
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	echo '<head>';
	echo  '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	echo  '<title>Search</title>';
	echo  '<base href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/page.php/' . htmlspecialchars($alias) . '/" target="main"/>';
	echo  '<link href="../../' . $css . '" type="text/css" rel="stylesheet"/>';
	echo '</head>';
	echo '<body id="search" class="sideframe">';

	echo '<div class="menubar">';
	echo  '<a href="../../toc.php?book=' . htmlspecialchars($alias) . '" target="_self">Contents</a> | ';
	echo  '<a href="../../_index.php?book=' . htmlspecialchars($alias) . '" target="_self">Index</a> | ';
	echo  '<a href="../../search.php?book=' . htmlspecialchars($alias) . '" target="_self">Search</a>';
	echo '</div>';

	$query = $_GET['query'];
	echo '<form id="find" target="navigation" action="../../search.php">';
	echo  '<input type="hidden" name="book" value="' . htmlspecialchars($alias) .'"/>';
	echo  '<input id="query" type="text" name="query" value="' . htmlspecialchars($query) . '"/>';
	echo  '<input id="submit" type="submit" value="Find"/>';
	echo '</form>';

	if($query)
	{
		$entries = $book->search($query);
		if(count($entries))
		{
			echo '<ul class="list">';
			foreach($entries as $entry)
			{
				list($title, $path) = $entry;
				echo '<li><a href="' . $path .'">' . htmlspecialchars($title, ENT_NOQUOTES) . '</a></li>';
			}
			echo '</ul>';
		}
	}

	echo '</body>';
	echo '</html>';
?>
