<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$book_id = intval($_GET['book_id']);
	$result = mysql_query('SELECT `title` FROM `book` WHERE `id`=' . $book_id);
	list($title) = mysql_fetch_row($result);
	$title = htmlspecialchars($title, ENT_NOQUOTES);

	include 'header.inc.php';

	echo '<body class="header">' . $title . '</body>';
	
	include 'footer.inc.php';
?>
