<?php
	include 'config.inc.php';
	include 'mysql.inc.php';
	include 'mysql_version.inc.php';

	$title = 'Search';
	include 'header.inc.php';

	echo '<body id="search" class="sidebar">';

	$book_id = intval($_GET['book_id']);
	$where = $_GET['where'];
	$query = mysql_escape_string($_GET['query']);
	
	if($query)
	{
		echo '<ul>';
		$entries = mysql_query('SELECT book_id, path, title, body FROM `pages` WHERE book_id=' . $book_id . ' AND MATCH (title, body) AGAINST ("' . $query . '"' . (mysql_check_version('4.0.1') ? ' IN BOOLEAN MODE' : '') . ')') or die("Query failed: " . mysql_error());
		while($entry = mysql_fetch_object($entries))
		{
			echo '<li>';
			echo '<a href="page.php/' . $entry->book_id . '/' . $entry->path .'" target="main">' . $entry->title . '</a>';
			echo '</li>';
		}
		echo '</ul>';
	}

	echo '</body>';

	include 'footer.inc.php';
?>
