<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$title = 'Table of contents';
	include 'header.inc.php';

	echo '<body id="toc" class="sidebar">';

	function walk_toc($book_id, $parent_number)
	{
		$result = mysql_query('SELECT `book_id`, `number`, `parent_number`, `name`, `path`, `anchor` FROM `toc` WHERE `book_id`=' . $book_id . ' AND `parent_number`=' . $parent_number . ' ORDER BY `number`');
		echo '<ul>';
		while(list($book_id, $number, $parent_number, $name, $path, $anchor) = mysql_fetch_row($result))
		{
			echo '<li>';
			echo '<a href="page.php/' . $book_id . '/' . $path . '#' . $anchor . '" target="main">' . htmlentities($name, ENT_NOQUOTES, 'UTF-8') . '</a>';
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
