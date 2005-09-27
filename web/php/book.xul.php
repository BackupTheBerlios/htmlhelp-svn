<?php
	
	require_once 'include/config.inc.php';
	require_once 'include/book.inc.php';

	$alias = $_GET['book'];
	require 'include/get_book_from_alias.inc.php'; 
	
	// Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-type: application/vnd.mozilla.xul+xml');

	$title = htmlspecialchars($book->title());
	
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<?xml-stylesheet href="css/xul.css" type="text/css"?>';

	echo '
<!-- 
  You need a Gecko-based browser for viewing this page.
-->
';
	
	echo '<window id="wnd" xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul" title="' . $title . '" width="640" height="420" persist="width height screenX screenY sizemode">';
	echo '<script type="text/javascript">document.title = "' . $title . '";</script>';
	
	echo '<script src="js/book.js"/>';
	
	echo '<hbox flex="1">';
		
	// Navigation panels
	echo '<tabbox flex="1">';
	echo  '<tabs>';
	echo   '<tab label="Contents"/>';
	echo   '<tab label="Index"/>';
	echo   '<tab label="Search"/>';
	echo  '</tabs>';
	echo  '<tabpanels flex="1">';
	echo   '<iframe id="toc" src="toc.xul.php?book=' . htmlspecialchars($alias) .'" flex="1"/>';
	echo   '<iframe id="index" src="_index.xul.php?book=' . htmlspecialchars($alias) .'" flex="1"/>';
	echo   '<iframe id="search" src="search.xul.php?book=' . htmlspecialchars($alias) .'" flex="1"/>';
	echo  '</tabpanels>';
	echo '</tabbox>';
		
	// Splitter
	echo '<splitter collapse="before"><grippy/></splitter>';

	echo '<vbox flex="3">';
	
	echo '<toolbar>';
	echo   '<toolbarbutton id="back-button" label="Back" oncommand="goBack(event);"/>';
	echo   '<toolbarbutton id="forward-button" label="Forward" oncommand="goForward(event);"/>';
	echo   '<toolbarbutton id="home-button" label="Home" oncommand="goHome(event, \'' . htmlspecialchars($alias) . '\');"/>';
	echo   '<spacer flex="1" />';
	echo   '<toolbarbutton id="print-button" label="Print" oncommand="print();"/>';
	echo '</toolbar>';
	
	// Browser window
	echo '<browser name="content" type="content-primary" src="page.php/' . htmlspecialchars($alias) . '/" flex="1"/>';

	echo '</vbox>';

	echo '</hbox>';
	
	echo '</window>';
?>
