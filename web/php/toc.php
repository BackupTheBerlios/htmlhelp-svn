<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	$title = 'Table of contents';
	$target = 'main';
	include 'header.inc.php';

	echo '<body id="toc" class="sidebar">';

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
			echo '<ul>';
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
			echo '<a href="page.php/' . $book_id . '/' . $path . ($anchor ? '#' . $anchor : '') . '">';
		else
			echo '<a target="_self" href="toc.php?book_id=' . $book_id . '&toc_no=' . $number . '">';
		echo htmlspecialchars($name, ENT_NOQUOTES, $encoding) . '</a>';
			
		walk_children($result, $depth);
		echo '</li>';
	}

	$book_id = intval($_GET['book_id']);
	$number = intval($_GET['toc_no']);
	$depth = 2;
	
	if($book_id)
	{
		if($number)
		{
			$result = mysql_query('SELECT `parent_no`, `title`, `path`, `anchor` FROM `toc_entry` WHERE `book_id`=' . $book_id . ' AND `no`=' . $number . ' ORDER BY `no`');
			list($parent_number, $name, $path, $anchor) = mysql_fetch_row($result);
			
			echo '<ul><li class="collapsed"><a target="_self" href="toc.php?book_id=' . $book_id . '&toc_no=' . $parent_number . '">&hellip;</a>'; 
				
			echo '<ul>';
			echo '<li class="expanded">';
			echo '<a href="page.php/' . $book_id . '/' . $path . ($anchor ? '#' . $anchor : '') . '">' . htmlspecialchars($name, ENT_NOQUOTES, $encoding) . '</a>';
			walk_children(query_toc($number), $depth - 1);
			echo '</li>';
			echo '</ul>';
			
			echo '</li></ul>';
			
		}
		else
			walk_children(query_toc(0), $depth);
	}
	
	echo '</body>';
	
	include 'footer.inc.php';
?>
