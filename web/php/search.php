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
	
	echo '<form class="search" target="navigation" action="_index.php">';
	echo  '<input type="hidden" name="book_id" value="' . $book_id .'"/>';
	echo  '<input type="text" name="query" value=""/>';
	echo  '<input type="submit" value="Find">';
	echo '</form>';

	if($query)
	{
		$book_expr = $book_id ? ' book_id=' . $book_id : '1';
		$against_expr = 'AGAINST (\'' . mysql_escape_string($query) . '\'' . (0 /*mysql_check_version('4.0.1')*/ ? ' IN BOOLEAN MODE' : '') . ')';

		$result = mysql_query('SELECT `book_id`, `path`, `title` FROM `page` WHERE ' . $book_expr . ' AND MATCH (`title`, `body`) ' . $against_expr);
		if(mysql_num_rows($result))
		{
			echo '<ul>';
			while(list($book_id, $path, $title) = mysql_fetch_row($result))
				echo '<li><a href="page.php/' . $book_id . '/' . $path .'">' . htmlspecialchars($title, ENT_NOQUOTES, $encoding) . '</a></li>';
			echo '</ul>';
		}
	}

	echo '</body>';

	include 'footer.inc.php';
?>
