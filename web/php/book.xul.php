<?php
	
	include 'config.inc.php';
	include 'mysql.inc.php';

	// Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';
	echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

	echo '
<!-- 
  You need a Gecko-based browser for viewing this page.
-->
';

	$book_id = intval($_GET['book_id']);
	$books = mysql_query('SELECT * FROM `book` WHERE `id`=' . $book_id);
	$book = mysql_fetch_object($books);
	$title = htmlspecialchars($book->title);

	echo '<window id="wnd" xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul" title="' . $title . '" width="640" height="420" persist="width height screenX screenY sizemode">';
	echo '<script type="text/javascript">document.title = "' . $title . '";</script>';
	
	echo '<script src="book.js"/>';
	
	echo '<hbox flex="1">';
		
	// Navigation panels
	echo '<tabbox flex="1">';
	echo  '<tabs>';
	echo   '<tab label="Contents"/>';
	echo   '<tab label="Index"/>';
	echo   '<tab label="Search"/>';
	echo  '</tabs>';
	echo  '<tabpanels flex="1">';
	echo   '<iframe id="toc" src="toc.xul.php?book_id=' . $book_id .'" flex="1"/>';
	echo   '<iframe id="index" src="_index.xul.php?book_id=' . $book_id .'" flex="1"/>';
	echo   '<iframe id="search" src="search.xul.php?book_id=' . $book_id .'" flex="1"/>';
	echo  '</tabpanels>';
	echo '</tabbox>';
		
	// Splitter
	echo '<splitter collapse="before"><grippy/></splitter>';

	echo '<vbox flex="3">';
	
	echo '<hbox>';
	echo  '<button label="Back" oncommand="goBack(event);"/>';
	echo  '<button label="Forward" oncommand="goForward(event);"/>';
	echo  '<button label="Home" oncommand="goHome(event, ' . $book_id . ');"/>';
	/*
	echo  '<spacer flex="1" />';
	echo  '<button label="Sync TOC"/>';
	*/
	echo '</hbox>';
	
	// Browser window
	echo '<browser name="content" type="content-primary" src="' . ($book_id ? 'page.php/' . $book_id . '/' : 'about.php') .' " flex="1"/>';

	echo '</vbox>';

	echo '</hbox>';
	
	echo '</window>';

?>
