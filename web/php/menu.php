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

	echo '<body class="menubar">';

	echo '<a href="books.php">Books</a>';
	if($book_id)
	{
		echo '| <a href="toc.php?book_id=' . $book_id . '">Contents</a>';
		echo '| <a href="_index.php?book_id=' . $book_id . '">Index</a>';
	}
	echo '| <a href="search.php?book_id=' . $book_id . '">Search</a>';

	echo '</body>';
	
	include 'footer.inc.php';
?>
