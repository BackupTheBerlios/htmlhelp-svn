<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	// For link backward compatability
	if($book_id = intval($_GET['book_id']))
	{
		header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/book.php?book_id=' . $book_id);
		exit;
	}
	
	$noxul = intval($_GET['noxul']);

	$title = 'HTML Help Books';
	include 'header.inc.php';

	echo '<body>';
	
	echo '<div class="header">HTML Help Books</div>';
	
	echo '<div>';
	
	$result = mysql_query('SELECT `id`, `title` FROM `book` ORDER BY `title`');
	if(mysql_num_rows($result))
	{
		echo '<ul class="list">';
		while(list($book_id, $title) = mysql_fetch_row($result))
			echo '<li><a href="book.php?book_id=' . $book_id . ($noxul ? '&amp;noxul=1' : '') . '">' . $title . '</a></li>';
		echo '</ul>';
	}

	echo '</div>';

	echo '</body>';
	
	include 'footer.inc.php';
?>
