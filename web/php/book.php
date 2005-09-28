<?php
	require_once 'include/config.inc.php';

	$alias = $_GET['book'];
	require 'include/get_book_from_alias.inc.php'; 
	
	$title = htmlspecialchars($book->title(), ENT_NOQUOTES);
	header('Content-Type: text/html; charset=utf-8');
		
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
	
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	echo '<title>' . $title . '</title>';
	echo '<link href="css/html.css" type="text/css" rel="stylesheet"/>';
	echo '</head>';

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

	echo '</html>';
?>
