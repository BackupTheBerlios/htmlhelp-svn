<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$title = 'Catalog';
	$target = '_top';
	include 'header.inc.php';
	
	echo '<body class="sidebar">';
	
	$result = mysql_query('SELECT `id`, `title` FROM `book` ORDER BY `title`');
	if(mysql_num_rows($result))
	{
		echo '<ul class="list">';
		while(list($book_id, $title) = mysql_fetch_row($result))
			echo '<li><a href="index.php?book_id=' . $book_id . '&amp;noxul=1">' . $title . '</a></li>';
		echo '</ul>';
	}

	echo '</body>';
	
	include 'footer.inc.php';
?>
