<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	$title = 'Index';
	$target = 'main';
	include 'header.inc.php';

	$book_id = intval($_GET['book_id']);
	$query = $_GET['query'];
	
	echo '<body id="index" class="sidebar">';

	echo '<form class="search" target="navigation" action="_index.php">';
	echo  '<input type="hidden" name="book_id" value="' . $book_id .'"/>';
	echo  '<input type="text" name="query" value=""/>';
	echo  '<input type="submit" value="Find">';
	echo '</form>';

	$result = mysql_query('SELECT `term`, `path`, `anchor` FROM `index_entry`,`index_link` WHERE `index_entry`.`book_id`=' . $book_id . ' AND `index_link`.`book_id`=' . $book_id . ' AND `index_link`.`no`=`index_entry`.`no`' . ($query ? ' AND LOCATE(\'' . mysql_escape_string($query) . '\', `term`)' : '') . ' ORDER BY `index_entry`.`term`');
	if(mysql_num_rows($result))
	{
		echo '<ul>';
		while(list($term, $path, $anchor) = mysql_fetch_row($result))
			echo '<li><a href="page.php/' . $book_id . '/' . $path . ($anchor ? '#' . $anchor : '') . '">' . $term . '</a></li>';
		echo '</ul>';
	}
	
	echo '</body>';
	
	include 'footer.inc.php';
?>
