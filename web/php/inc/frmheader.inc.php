<?php
	require_once 'inc/config.inc.php';

	$alias = $_GET['book'];
	require 'inc/get_book_from_alias.inc.php';
	
	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-Type: text/html; charset=utf-8');
		
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	echo '<head>';
	echo  '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	echo  '<title>' . $title . '</title>';
	echo  '<base href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/page.php/' . htmlspecialchars($alias) . '/" target="main"/>';
	echo  '<link href="../../css/html.css" type="text/css" rel="stylesheet"/>';
	echo '</head>';
	echo '<body id="' . $id . '" class="sideframe">';

	echo '<div class="menubar">';
	echo  '<a href="../../tocfrm.php?book=' . htmlspecialchars($alias) . '" target="_self">Contents</a> | ';
	echo  '<a href="../../indexfrm.php?book=' . htmlspecialchars($alias) . '" target="_self">Index</a> | ';
	echo  '<a href="../../searchfrm.php?book=' . htmlspecialchars($alias) . '" target="_self">Search</a>';
	echo '</div>';
?>
