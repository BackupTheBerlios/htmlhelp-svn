<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$title = 'Index';
	include 'header.inc.php';

	echo '<body id="index" class="sidebar">';

	function walk_index($book_id, $parent_id)
	{
		$result = mysql_query('SELECT `book_id`, `id`, `parent_id`, `term` FROM `index` WHERE `book_id`=' . $book_id . ' AND `parent_id`=' . $parent_id . ' ORDER BY `term`');
		echo '<ul>';
		while(list($book_id, $id, $parent_id, $term) = mysql_fetch_row($result))
		{
			list($index_id, $path, $anchor) = mysql_fetch_row(mysql_query('SELECT `index_id`, `path`, `anchor` FROM `index_links` WHERE `index_id`=' . $id));
			
			echo '<li>';
			echo '<a href="page.php/' . $book_id . '/' . $path . '#' . $anchor . '" target="main">' . $term . '</a>';
			walk_index($book_id, $id);
			echo '</li>';
		}
		echo '</ul>';
	}
	
	if($book_id = intval($_GET['book_id']))
		walk_index($book_id, 0);
	
	echo '</body>';
	
	include 'footer.inc.php';
?>
