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

	$target = "navigation";
	include 'header.inc.php';

	echo '<body>';
	
	echo '<div class="header">' . $title . '</div>';
	echo '<div class="menubar">';
	echo '<span class="left">';
	echo '<a href="books.php">Books</a>';
	if($book_id)
	{
		echo '| <a href="toc.php?book_id=' . $book_id . '">Contents</a>';
		echo '| <a href="_index.php?book_id=' . $book_id . '">Index</a>';
	}
	echo '| <a href="search.php?book_id=' . $book_id . '">Search</a>';
	echo '</span>';
	/*
	echo '<span class="right">';
	echo '<form class="search" action="search.php">';
	
	//echo '<input type="hidden" name="book_id" value="' . $book_id .'"/>';
	$books = mysql_query('SELECT `id`, `title` FROM `book`');
	echo '<select name="book_id">';
	echo '<option value="0">All</option>';
	while($book = mysql_fetch_object($books))
		echo '<option value="' . $book->id . '"' . ($book->id == $book_id ? ' selected="1"' : '') . '>' . $book->title . '</option>';
	echo '</select>';	

	echo '<select name="where">';
	echo '<option value="contents">Contents</option>';
	echo '<option value="index" selected="1">Index</option>';
	echo '<option value="fulltext">Full-text</option>';
	echo '</select>';

	echo '<input type="text" name="query" value=""/>';
	echo '<input type="submit" value="Search">';
	
	echo '</form>';
	echo '</span>';
	*/
	echo '</div>';
	echo '</body>';
	
	include 'footer.inc.php';
?>
