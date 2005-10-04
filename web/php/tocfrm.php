<?php

$title = 'Table of Contents';
$id = 'toc';
// TODO: Add filtering to TOC
//$search_button = 'Filter';
require 'inc/frmheader.inc.php';

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
			echo '<a href="../../tocfrm.php?book=' . htmlspecialchars($alias) . '&amp;toc_no=' . $number . '" target="_self">';
		echo htmlspecialchars($name, ENT_NOQUOTES) . '</a>';
			
		walk_children($children, $depth);
		echo '</li>';
	}

	$number = intval($_GET['toc_no']);
	$depth = 2;
	
	if($number)
	{
		list($parent_number, $title, $link) = $book->toc_entry($number);
		
		echo '<ul class="tree"><li class="collapsed"><a href="../../tocfrm.php?book=' . htmlspecialchars($alias) . '&amp;toc_no=' . $parent_number . '" target="_self">&hellip;</a>'; 
			
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
	
require 'inc/frmfooter.inc.php';
?>
