<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$title = 'Index';
	include 'header.inc.php';

	echo '<body id="index" class="sidebar">';

	if($book_id = intval($_GET['book_id']))
	{
		$result = mysql_query('SELECT `id`, `book_id`, `term` FROM `index` WHERE `book_id`=' . $book_id . ' ORDER BY `term`');
		echo '<ul>';
		while(list($index_id, $book_id, $term) = mysql_fetch_row($result))
		{
			list($index_id, $path, $anchor) = mysql_fetch_row(mysql_query('SELECT `index_id`, `path`, `anchor` FROM `index_links` WHERE `index_id`=' . $index_id));
			
			echo '<li><a href="page.php/' . $book_id . '/' . $path . '#' . $anchor . '" target="main">' . $term . '</a></li>';
		}
		echo '</ul>';
	}
	
	echo '</body>';
	
	include 'footer.inc.php';
?>
