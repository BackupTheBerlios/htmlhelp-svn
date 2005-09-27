<?php
	require_once 'include/config.inc.php';
	require_once 'include/book.inc.php';

	$alias = $_GET['book'];
	require 'include/get_book_from_alias.inc.php'; 
	
	$doctype = 'frameset';
	$title = htmlspecialchars($book->title(), ENT_NOQUOTES);
	require 'include/header.inc.php';

	// Unless the 'noxul' param is given then embed a Javascript script to
	// redirect Gecko-based browsers to the XUL-based interface
	if(!intval($_GET['noxul']))
		echo '<script type="text/javascript">if(navigator.userAgent.indexOf("Gecko") >= 0) document.location.href = "book.xul.php?book=' . htmlspecialchars($alias) . '";</script>';
	
	echo '<frameset rows="56,*" frameborder="no" border="1">';
	echo '<frame src="header.php?book=' . htmlspecialchars($alias) . '" name="header" scrolling="no" noresize="true"/>';
	echo '<frameset rows="*" cols="*,3*" frameborder="yes" border="1">';
	echo '<frame src="toc.php?book=' . htmlspecialchars($alias) . '" name="navigation"/>';
	echo '<frame src="page.php/' . htmlspecialchars($alias) . '/' . $book->default_link() . '" name="main"/>';
	echo '<noframes>A frames-capable web browser is required.</noframes>';
	echo '</frameset>';
	
	require 'include/footer.inc.php';
?>
