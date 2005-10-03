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
	echo '<body id="' . $id . '">';

	echo '<div class="header">';
	$menuitems = array(
		'toc' => 'Contents',
		'index' => 'Index',
		'search' => 'Search',
	);
	echo '<ul class="menu">';
	foreach($menuitems as $menu_id => $menu_title)
	{
		echo '<li>';
		echo  '<a' . ($menu_id == $id ? ' class="current"' : ' href="../../' . $menu_id . 'frm.php?book=' . htmlspecialchars($alias) . '" target="_self"') . '>';
		echo   htmlspecialchars($menu_title, ENT_NOQUOTES);
		echo  '</a>';
		echo '</li>';
	}
	echo '</ul>';

	if($id != 'toc') {
		$query = $_GET['query'];
		echo '<form id="find" target="navigation" action="../../searchfrm.php">';
		echo  '<div>';
		echo   '<input type="hidden" name="book" value="' . htmlspecialchars($alias) .'"/>';
		echo   '<input id="query" type="text" name="query" value="' . htmlspecialchars($query) . '"/>';
		echo   '<input id="submit" type="submit" value="Search"/>';
		echo  '</div>';
		echo '</form>';
	}
	echo '</div>';

	echo '<div class="results">';
?>
