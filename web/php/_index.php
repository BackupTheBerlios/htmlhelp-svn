<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	$book_id = intval($_GET['book_id']);
	
	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	header('Content-Type: text/html; charset=utf-8');
		
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	echo '<head>';
	echo  '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	echo  '<title>Index</title>';
	echo  '<base href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/page.php/' . $book_id . '/" target="main"/>';
	echo  '<link href="../../' . $css . '" type="text/css" rel="stylesheet"/>';
	echo '</head>';
	echo '<body id="index" class="sidebar">';

	echo '<div class="menubar">';
	echo  '<a href="../../toc.php?book_id=' . $book_id . '" target="_self">Contents</a> | ';
	echo  '<a href="../../_index.php?book_id=' . $book_id . '" target="_self">Index</a> | ';
	echo  '<a href="../../search.php?book_id=' . $book_id . '" target="_self">Search</a>';
	echo '</div>';

	$query = $_GET['query'];
	echo '<form id="find" target="navigation" action="../../_index.php">';
	echo  '<input type="hidden" name="book_id" value="' . $book_id .'"/>';
	echo  '<input id="query" type="text" name="query" value="' . htmlspecialchars($query) . '"/>';
	echo  '<input id="submit" type="submit" value="Find"/>';
	echo '</form>';

	if(isset($query))
	{
		$result = mysql_query('SELECT `term`, `path`, `anchor` FROM `index_entry`,`index_link` WHERE `index_entry`.`book_id`=' . $book_id . ' AND `index_link`.`book_id`=' . $book_id . ' AND `index_link`.`no`=`index_entry`.`no`' . ($query ? ' AND LOCATE(\'' . mysql_escape_string($query) . '\', `term`)' : '') . ' ORDER BY `index_entry`.`term`');
		if(mysql_num_rows($result))
		{
			echo '<ul class="list">';
			while(list($term, $path, $anchor) = mysql_fetch_row($result))
				echo '<li><a href="' . $path . ($anchor ? '#' . $anchor : '') . '">' . htmlspecialchars($term, ENT_NOQUOTES) . '</a></li>';
			echo '</ul>';
		}
	}
	
	echo '</body>';
	echo '</html>';
?>
