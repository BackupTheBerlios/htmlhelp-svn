<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$title = 'Table of contents';
	include 'header.inc.php';

	echo '<body id="toc" class="sidebar">';

	function walk_toc($book_id, $parent_number)
	{
		echo '<ul>';
		$entries = mysql_query(sprintf('SELECT * FROM `toc` WHERE `book_id`=%d AND `parent_number`=%d ORDER BY number', $book_id, $parent_number));
		while($entry = mysql_fetch_object($entries))
		{
			echo '<li>';
			echo '<a href="page.php/' . $book_id . '/' . $entry->path . '#' . $entry->anchor . '" target="main">' . htmlentities($entry->name, ENT_NOQUOTES, 'UTF-8') . '</a>';
			walk_toc($book_id, $entry->number);
			echo "</li>";
		}
		echo '</ul>';
	}

	if($book_id = intval($_GET['book_id']))
		walk_toc($book_id, 0);
	
	echo '</body>';
	
	include 'footer.inc.php';
?>
