<?php
	require_once 'config.inc.php';
	require_once 'book.inc.php';

	$catalog = new Book_Catalog();
	$alias = $_GET['book'];
	$book = $catalog->get_book_from_alias($alias);
	
	$doctype = 'frameset';
	$title = htmlspecialchars($book->title(), ENT_NOQUOTES);
	require_once 'header.inc.php';

	// Unless the 'noxul' param is given then embed a Javascript script to
	// redirect Gecko-based browsers to the XUL-based interface
	if(!intval($_GET['noxul']))
		echo '<script type="text/javascript">if(navigator.userAgent.indexOf("Gecko") >= 0) document.location.href = "book.xul.php?book=' . htmlspecialchars($alias) . '";</script>';
	
	echo '<frameset rows="56,*">';
	echo '<frame src="header.php?book=' . htmlspecialchars($alias) . '" name="header"/>';
	echo '<frameset rows="*" cols="*,3*">';
	echo '<frame src="toc.php?book=' . htmlspecialchars($alias) . '" name="navigation"/>';
	echo '<frame src="page.php/' . htmlspecialchars($alias) . '/' . $book->default_link() . '" name="main"/>';
	echo '<noframes>A frames-capable web browser is required.</noframes>';
	echo '</frameset>';
	
	require_once 'footer.inc.php';
?>
