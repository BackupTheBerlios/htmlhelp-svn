<?php
	include 'config.inc.php';
	include 'mysql.inc.php';
	include 'mysql_version.inc.php';

	$title = 'Search';
	include 'header.inc.php';

	echo '<body id="search" class="sidebar">';

	$book_id = intval($_GET['book_id']);
	$where = $_GET['where'];
	$query = $_GET['query'];
	
	if($query)
	{
		$book_expr = $book_id ? ' book_id=' . $book_id : '1';
		$against_expr = 'AGAINST (\'' . mysql_escape_string($query) . '\'' . (0 /*mysql_check_version('4.0.1')*/ ? ' IN BOOLEAN MODE' : '') . ')';

		if($where == 'contents')
		{
			$result = mysql_query('SELECT `book_id`, `title`, `path`, `anchor` FROM `toc_entry` WHERE ' . $book_expr . ' AND LOCATE(\'' . mysql_escape_string($query) . '\', `title`) ORDER BY `title`');
			echo '<ul>';
			while(list($book_id, $name, $path, $anchor) = mysql_fetch_row($result))
				echo '<li><a href="page.php/' . $book_id . '/' . $path . '#' . $anchor . '" target="main">' . htmlentities($name, ENT_NOQUOTES, 'UTF-8') . '</a></li>';
			echo '</ul>';
		}

		if($where == 'index')
		{
			$result = mysql_query('SELECT `index_entry`.`book_id`, `term`, `path`, `anchor` FROM `index_entry`,`index_link` WHERE ' . ($book_id ? '`index_entry`.`book_id`=' . $book_id . ' AND `index_link`.`book_id`=' . $book_id : '`index_link`.`book_id`=`index_entry`.`book_id`') . ' AND `index_link`.`no`=`index_entry`.`no` AND LOCATE(\'' . mysql_escape_string($query) . '\', `term`) ORDER BY `index_entry`.`term`') or die(mysql_error());
			echo '<ul>';
			while(list($book_id, $term, $path, $anchor) = mysql_fetch_row($result))
				echo '<li><a href="page.php/' . $book_id . '/' . $path . '#' . $anchor . '" target="main">' . $term . '</a></li>';
			echo '</ul>';
		}

		if($where == 'fulltext')
		{
			$result = mysql_query('SELECT `book_id`, `path`, `title` FROM `page` WHERE ' . $book_expr . ' AND MATCH (`title`, `body`) ' . $against_expr);
			echo '<ul>';
			while(list($book_id, $path, $title) = mysql_fetch_row($result))
				echo '<li><a href="page.php/' . $book_id . '/' . $path .'" target="main">' . htmlentities($title, ENT_NOQUOTES, 'UTF-8') . '</a></li>';
			echo '</ul>';
		}
	}

	echo '</body>';

	include 'footer.inc.php';
?>
