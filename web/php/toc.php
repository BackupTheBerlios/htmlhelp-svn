<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$book_id = intval($_GET['book_id']);
	
	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-Type: text/html; charset=utf-8');
		
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	echo '<head>';
	echo  '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	echo  '<title>Table of contents</title>';
	echo  '<base href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/page.php/' . $book_id . '/" target="main"/>';
	echo  '<link href="../../' . $css . '" type="text/css" rel="stylesheet"/>';
	echo '</head>';
	echo '<body id="toc" class="sidebar">';

	echo '<div class="menubar">';
	echo  '<a href="../../toc.php?book_id=' . $book_id . '" target="_self">Contents</a> | ';
	echo  '<a href="../../_index.php?book_id=' . $book_id . '" target="_self">Index</a> | ';
	echo  '<a href="../../search.php?book_id=' . $book_id . '" target="_self">Search</a>';
	echo '</div>';

	function query_toc($parent_number)
	{
		global $book_id;

		return mysql_query('SELECT `no`, `title`, `path`, `anchor` FROM `toc_entry` WHERE `book_id`=' . $book_id . ' AND `parent_no`=' . $parent_number . ' ORDER BY `no`');

	}
	
	function walk_children($result, $depth)
	{
		global $book_id;
		
		if(mysql_num_rows($result) && $depth)
		{
			echo '<ul class="tree">';
			while(list($number, $name, $path, $anchor) = mysql_fetch_row($result))
				walk_toc($number, $name, $path, $anchor, $depth - 1);
			echo '</ul>';
		}
	}
	
	function walk_toc($number, $name, $path, $anchor, $depth)
	{
		global $book_id;
		
		$result = query_toc($number);
		$has_children = mysql_num_rows($result);

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
			echo '<a href="' . $path . ($anchor ? '#' . $anchor : '') . '">';
		else
			echo '<a href="../../toc.php?book_id=' . $book_id . '&amp;toc_no=' . $number . '" target="_self">';
		echo htmlspecialchars($name, ENT_NOQUOTES) . '</a>';
			
		walk_children($result, $depth);
		echo '</li>';
	}

	$number = intval($_GET['toc_no']);
	$depth = 2;
	
	if($number)
	{
		$result = mysql_query('SELECT `parent_no`, `title`, `path`, `anchor` FROM `toc_entry` WHERE `book_id`=' . $book_id . ' AND `no`=' . $number . ' ORDER BY `no`');
		list($parent_number, $name, $path, $anchor) = mysql_fetch_row($result);
		
		echo '<ul class="tree"><li class="collapsed"><a href="../../toc.php?book_id=' . $book_id . '&amp;toc_no=' . $parent_number . '" target="_self">&hellip;</a>'; 
			
		echo '<ul class="tree">';
		echo '<li class="expanded">';
		if($path)
			echo '<a href="' . $path . ($anchor ? '#' . $anchor : '') . '">' . htmlspecialchars($name, ENT_NOQUOTES) . '</a>';
		else
			echo htmlspecialchars($name, ENT_NOQUOTES);
		
		walk_children(query_toc($number), $depth - 1);
		echo '</li>';
		echo '</ul>';
		
		echo '</li></ul>';
		
	}
	else
		walk_children(query_toc(0), $depth);
	
	echo '</body>';
	echo '</html>';
?>
