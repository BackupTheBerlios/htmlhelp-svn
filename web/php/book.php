<?php
	include 'config.inc.php';
	include 'book.inc.php';

	$book = new Book($_GET['book_id']);
	
	$doctype = 'frameset';
	$title = htmlspecialchars($book->title(), ENT_NOQUOTES);
	include 'header.inc.php';

	// Unless the 'noxul' param is given then embed a Javascript script to
	// redirect Gecko-based browsers to the XUL-based interface
	if(!intval($_GET['noxul']))
		echo '<script type="text/javascript">if(navigator.userAgent.indexOf("Gecko") >= 0) document.location.href = "book.xul.php?book_id=' . $book->id . '";</script>';
	
	echo '<frameset rows="56,*">';
	echo '<frame src="header.php?book_id=' . $book->id . '" name="header"/>';
	echo '<frameset rows="*" cols="*,3*">';
	echo '<frame src="toc.php?book_id=' . $book->id . '" name="navigation"/>';
	echo '<frame src="page.php/' . $book->id . '/' . $book->default_link() . '" name="main"/>';
	echo '<noframes>A frames-capable web browser is required.</noframes>';
	echo '</frameset>';
	
	include 'footer.inc.php';
?>
