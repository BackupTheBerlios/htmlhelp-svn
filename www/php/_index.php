<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$title = 'Index';
	include 'header.inc.php';

	echo '<body id="index" class="sidebar">';

	if($book_id = intval($_GET['book_id']))
	{
		$result = mysql_query('SELECT `term`, `path`, `anchor` FROM `index_entry`,`index_link` WHERE `index_entry`.`book_id`=' . $book_id . ' AND `index_link`.`book_id`=' . $book_id . ' AND `index_link`.`no`=`index_entry`.`no` ORDER BY `index_entry`.`term`');
		echo '<ul>';
		while(list($term, $path, $anchor) = mysql_fetch_row($result))
			echo '<li><a href="page.php/' . $book_id . '/' . $path . '#' . $anchor . '" target="main">' . $term . '</a></li>';
		echo '</ul>';
	}
	
	echo '</body>';
	
	include 'footer.inc.php';
?>
