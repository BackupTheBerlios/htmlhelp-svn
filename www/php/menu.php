<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	if($book_id = intval($_GET['book_id']))
	{
		$books = mysql_query('SELECT * FROM `books` WHERE `id`=' . $book_id);
		$book = mysql_fetch_object($books);
		$title = htmlentities($book->title, ENT_NOQUOTES, 'UTF-8');
	}
	else
		$title = 'HTML Help Books';

	include 'header.inc.php';

	echo '<body>';
	
	echo '<div class="search">';
	echo '<form action="search.php" target="navigation">';
	
	#echo '<input type="hidden" name="book_id" value="' . $book_id .'"/>';
	$books = mysql_query('SELECT `id`, `title` FROM `books`');
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
	echo '</div>';

	echo '<div class="header">' . $title . '</div>';
	echo '<div class="menubar">';
	echo '<a href="books.php" target="navigation">Books</a>';
	if($book_id)
	{
		echo '| <a href="toc.php?book_id=' . $book_id . '" target="navigation">Contents</a>';
		echo '| <a href="_index.php?book_id=' . $book_id . '" target="navigation">Index</a>';
	}
	echo '</div>';
	echo '</body>';
	
	include 'footer.inc.php';
?>
