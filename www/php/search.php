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
		$against_expr = 'AGAINST (\'' . mysql_escape_string($query) . '\'' . (mysql_check_version('4.0.1') ? ' IN BOOLEAN MODE' : '') . ')';

		if($where == 'contents')
		{
			#$result = mysql_query('SELECT `book_id`, `name`, `path`, `anchor` FROM `toc` WHERE ' . $book_expr . ' AND MATCH (`name`) ' . $against_expr);
			$result = mysql_query('SELECT `book_id`, `name`, `path`, `anchor` FROM `toc` WHERE ' . $book_expr . ' AND LOCATE(\'' . mysql_escape_string($query) . '\', `name`) ORDER BY `name`');
			echo '<ul>';
			while(list($book_id, $name, $path, $anchor) = mysql_fetch_row($result))
				echo '<li><a href="page.php/' . $book_id . '/' . $path . '#' . $anchor . '" target="main">' . htmlentities($name, ENT_NOQUOTES, 'UTF-8') . '</a></li>';
			echo '</ul>';
		}

		if($where == 'index')
		{
			#$result = mysql_query('SELECT `book_id`, `term`, `path`, `anchor` FROM `index`  LEFT JOIN `index_links` ON `id`=`index_id` WHERE ' . $book_expr . ' AND MATCH (`term`) ' . $against_expr);
			$result = mysql_query('SELECT `book_id`, `term`, `path`, `anchor` FROM `index`  LEFT JOIN `index_links` ON `id`=`index_id` WHERE ' . $book_expr . ' AND LOCATE(\'' . mysql_escape_string($query) . '\', `term`) ORDER BY `term`');
			echo '<ul>';
			while(list($book_id, $term, $path, $anchor) = mysql_fetch_row($result))
			{
				echo '<li><a href="page.php/' . $book_id . '/' . $path . '#' . $anchor . '" target="main">' . $term . '</a></li>';
			}
			echo '</ul>';
		}

		if($where == 'fulltext')
		{
			$result = mysql_query('SELECT `book_id`, `path`, `title`, `body` FROM `pages` WHERE ' . $book_expr . ' AND MATCH (`title`, `body`) ' . $against_expr);
			echo '<ul>';
			while(list($book_id, $path, $title, $body) = mysql_fetch_row($result))
				echo '<li><a href="page.php/' . $book_id . '/' . $path .'" target="main">' . htmlentities($title, ENT_NOQUOTES, 'UTF-8') . '</a></li>';
			echo '</ul>';
		}
	}

	echo '</body>';

	include 'footer.inc.php';
?>
