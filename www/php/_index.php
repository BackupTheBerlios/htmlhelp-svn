<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$title = 'Index';
	include 'header.inc.php';

	echo '<body id="index" class="sidebar">';

	function walk_index($book_id, $parent_id)
	{
		echo '<ul>';
		$entries = mysql_query('SELECT * FROM `index` WHERE `book_id`=' . $book_id . ' AND `parent_id`=' . $parent_id . ' ORDER BY `term`');
		while($entry = mysql_fetch_object($entries))
		{

			$links = mysql_query('SELECT * FROM `index_links` WHERE `index_id`=' . $entry->id);
			$link = mysql_fetch_object($links);
			
			echo '<li>';
			echo '<a href="page.php/' . $book_id . '/' . $link->path . '#' . $link->anchor . '" target="main">' . $entry->term . '</a>';
			walk_index($book_id, $entry->id);
			echo '</li>';
		}
		echo '</ul>';
	}

	if($book_id = intval($_GET['book_id']))
		walk_index($book_id, 0);
	
	echo '</body>';
	
	include 'footer.inc.php';
?>
