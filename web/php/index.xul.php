<?php
	
	include 'config.inc.php';

	// For link backward compatability
	if($book_id = intval($_GET['book_id']))
		header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/book.xul.php?book_id=' . $book_id);
	else
		header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/');
?>
