<?php
	
	include 'config.inc.php';

	// For link backward compatability
	if($book = intval($_GET['book']))
		header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/book.xul.php?book=' . $book);
	else
		header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/');
?>
