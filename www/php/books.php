<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$title = 'Catalog';

	include 'header.inc.php';
	
	echo '<body class="sidebar">';
	
	$books = mysql_query('SELECT `id`, `title` FROM `book` ORDER BY `title`');
	echo '<ul>';
	while($book = mysql_fetch_object($books))
		echo '<li><a href="index.php?book_id=' . $book->id . '" target="_top">' . $book->title . '</a></li>';
	echo '</ul>';

	echo '</body>';
	
	include 'footer.inc.php';
?>
