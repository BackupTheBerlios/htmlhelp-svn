<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$doctype = 'frameset';
	
	if($book_id = intval($_GET['book_id']))
	{
		$books = mysql_query('SELECT * FROM `book` WHERE `id`=' . $book_id);
		$book = mysql_fetch_object($books);
		$title = htmlspecialchars($book->title, ENT_NOQUOTES, $encoding);
	}
	else
		$title = 'HTML Help Books';

	include 'header.inc.php';

	// Unless the 'noxul' param is given then embed a Javascript script to
	// redirect Gecko-based browsers to the XUL-based interface
	if(!intval($_GET['noxul']))
		echo '<script type="text/javascript">if(navigator.userAgent.indexOf("Gecko") >= 0) document.location.href = "book.xul.php?book_id=' . $book_id . '";</script>';
	
	echo '<frameset rows="56,*">';
	echo '<frame src="header.php?book_id=' . $book_id . '" name="header"/>';
	echo '<frameset rows="*" cols="*,3*">';
	echo '<frame src="toc.php?book_id=' . $book_id . '" name="navigation"/>';
	echo '<frame src="page.php/' . $book_id . '/' . $book->default_path . '#' . $book->default_anchor . '" name="main"/>';
	echo '<noframes>A frames-capable web browser is required.</noframes>';
	echo '</frameset>';
	
	include 'footer.inc.php';
?>
