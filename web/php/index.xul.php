<?php
	
	require_once 'config.inc.php';

	// XXX: For link backward compatability
	if($book = $_GET['book'])
		header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/book.xul.php?book=' . $book);
	else
		header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/');
?>
