<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$title = 'Table of contents';
	$target = 'main';
	include 'header.inc.php';

	echo '<body id="toc" class="sidebar">';

	function walk_toc($book_id, $parent_number)
	{
		$result = mysql_query('SELECT `book_id`, `parent_no`, `no`, `title`, `path`, `anchor` FROM `toc_entry` WHERE `book_id`=' . $book_id . ' AND `parent_no`=' . $parent_number . ' ORDER BY `no`');
		echo '<ul>';
		while(list($book_id, $parent_number, $number, $name, $path, $anchor) = mysql_fetch_row($result))
		{
			echo '<li>';
			echo '<a href="page.php/' . $book_id . '/' . $path . ($anchor ? '#' . $anchor : '') . '">' . htmlentities($name, ENT_NOQUOTES, 'UTF-8') . '</a>';
			walk_toc($book_id, $number);
			echo '</li>';
		}
		echo '</ul>';
	}

	if($book_id = intval($_GET['book_id']))
		walk_toc($book_id, 0);
	
	echo '</body>';
	
	include 'footer.inc.php';
?>
