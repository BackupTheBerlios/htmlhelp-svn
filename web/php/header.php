<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	if($book_id = intval($_GET['book_id']))
	{
		$books = mysql_query('SELECT * FROM `book` WHERE `id`=' . $book_id);
		$book = mysql_fetch_object($books);
		$title = htmlspecialchars($book->title, ENT_NOQUOTES, $encoding);
	}
	else
		$title = 'HTML Help Books';

	include 'header.inc.php';

	echo '<body class="header">' . $title . '</body>';
	
	include 'footer.inc.php';
?>
