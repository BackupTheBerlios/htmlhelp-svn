<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	if($book_id = intval($_GET['book_id']))
	{
		$books = mysql_query('SELECT * FROM `book` WHERE `id`=' . $book_id);
		$book = mysql_fetch_object($books);
		$title = htmlentities($book->title, ENT_NOQUOTES, 'UTF-8');
	}
	else
		$title = 'HTML Help Books';

	include 'header.inc.php';

	echo '<frameset rows="80,*">';
	echo '<frame src="menu.php?book_id=' . $book_id . '" name="menu"/>';
	echo '<frameset rows="*" cols="*,3*">';
	if($book_id)
	{
		echo '<frame src="toc.php?book_id=' . $book_id . '" name="navigation"/>';
		echo '<frame src="page.php/' . $book_id . '/' . $book->default_path . '#' . $book->default_anchor . '" name="main"/>';
	}
	else
	{
		echo '<frame src="books.php" name="navigation"/>';
		echo '<frame src="about.php" name="main"/>';
	}
	echo '</frameset>';
	echo '</frameset>';
	
	include 'footer.inc.php';
?>
