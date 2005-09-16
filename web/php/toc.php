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
	echo  '<title>Table of contents</title>';
	echo  '<base href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/page.php/' . htmlspecialchars($alias) . '/" target="main"/>';
	echo  '<link href="../../' . $css . '" type="text/css" rel="stylesheet"/>';
	echo '</head>';
	echo '<body id="toc" class="sideframe">';

	echo '<div class="menubar">';
	echo  '<a href="../../toc.php?book=' . htmlspecialchars($alias) . '" target="_self">Contents</a> | ';
	echo  '<a href="../../_index.php?book=' . htmlspecialchars($alias) . '" target="_self">Index</a> | ';
	echo  '<a href="../../search.php?book=' . htmlspecialchars($alias) . '" target="_self">Search</a>';
	echo '</div>';

	function walk_children($entries, $depth)
	{
		if(count($entries) && $depth)
		{
			echo '<ul class="tree">';
			foreach($entries as $number => $entry)
			{
				list($name, $link, $children) = $entry;
				walk_toc($number, $name, $link, $children, $depth - 1);
			}
			echo '</ul>';
		}
	}
	
	function walk_toc($number, $name, $link, $children, $depth)
	{
		global $book, $alias;
		
		$has_children = count($children);

		if($has_children)
		{
			if($depth)
				echo '<li class="expanded">';
			else
				echo '<li class="collapsed">';
		}
		else
			echo '<li class="single">';
		
		if($depth || !$has_children)
			echo '<a href="' . $link . '">';
		else
			echo '<a href="../../toc.php?book=' . htmlspecialchars($alias) . '&amp;toc_no=' . $number . '" target="_self">';
		echo htmlspecialchars($name, ENT_NOQUOTES) . '</a>';
			
		walk_children($children, $depth);
		echo '</li>';
	}

	$number = intval($_GET['toc_no']);
	$depth = 2;
	
	if($number)
	{
		list($parent_number, $title, $link) = $book->toc_entry($number);
		
		echo '<ul class="tree"><li class="collapsed"><a href="../../toc.php?book=' . htmlspecialchars($alias) . '&amp;toc_no=' . $parent_number . '" target="_self">&hellip;</a>'; 
			
		echo '<ul class="tree">';
		echo '<li class="expanded">';
		if($link)
			echo '<a href="' . htmlspecialchars($link) . '">' . htmlspecialchars($title, ENT_NOQUOTES) . '</a>';
		else
			echo htmlspecialchars($title, ENT_NOQUOTES);
		
		walk_children($book->toc($number, $depth), $depth - 1);
		echo '</li>';
		echo '</ul>';
		
		echo '</li></ul>';
		
	}
	else
		walk_children($book->toc(0, $depth + 1), $depth);
	
	echo '</body>';
	echo '</html>';
?>
