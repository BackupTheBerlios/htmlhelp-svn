<?php
	include 'config.inc.php';
	include 'book.inc.php';

	// For link backward compatability
	if($book_id = intval($_GET['book_id']))
	{
		header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/book.php?book_id=' . $book_id);
		exit;
	}
	
	// Unless the 'noxul' param is given then embed a Javascript script to
	$title = 'HTML Help Books';
	include 'header.inc.php';

	echo '<body>';
	
	echo '<script src="index.js"></script>';

	echo '<div class="header">HTML Help Books</div>';
	
	echo '<div>';
	
	$entries = book_catalog();	
	if(count($entries))
	{
		echo '<ul class="list">';
		foreach($entries as $book_id => $title)
			echo '<li><a href="book.php?book_id=' . $book_id . '" onclick="return openBook(' . $book_id . ');" target="_blank">' . $title . '</a></li>';
		echo '</ul>';
	}

	echo '</div>';

	echo '<div>';
	include 'frontpage.inc.php';
	echo '</div>';
	
	echo '</body>';
	
	include 'footer.inc.php';
?>
