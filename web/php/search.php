<?php
	include 'config.inc.php';
	include 'mysql.inc.php';
	include 'mysql_version.inc.php';

	# Enable HTTP compression
	ob_start("ob_gzhandler");
	
	$title = 'Search';
	$target = 'main';
	include 'header.inc.php';

	echo '<body id="search" class="sidebar">';

	$book_id = intval($_GET['book_id']);
	$query = $_GET['query'];
	$boolean_mode = intval($_GET['boolean_mode']) && mysql_check_version('4.0.1');
	
	echo '<form id="find" target="navigation" action="search.php">';
	echo  '<input type="hidden" name="book_id" value="' . $book_id .'"/>';
	echo  '<input id="query" type="text" name="query" value="' . htmlspecialchars($query) . '"/>';
	echo  '<input id="submit" type="submit" value="Find">';
	echo '</form>';

	if($query)
	{
		$result = mysql_query('SELECT `book_id`, `path`, `title` FROM `page` WHERE ' . ($book_id ? ' book_id=' . $book_id : '1') . ' AND MATCH (`title`, `body`) AGAINST (\'' . mysql_escape_string($query) . '\'' . ($boolean_mode ? ' IN BOOLEAN MODE' : '') . ')');
		if(mysql_num_rows($result))
		{
			echo '<ul class="list">';
			while(list($book_id, $path, $title) = mysql_fetch_row($result))
				echo '<li><a href="page.php/' . $book_id . '/' . $path .'">' . htmlspecialchars($title, ENT_NOQUOTES, $encoding) . '</a></li>';
			echo '</ul>';
		}
	}

	echo '</body>';

	include 'footer.inc.php';
?>
