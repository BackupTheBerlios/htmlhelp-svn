<?php
	include 'config.inc.php';

	$title = 'Catalog';

	include 'header.inc.php';
?>
	<body class="sidebar">
		<?php
			mysql_connect($db_server, $db_username, $db_password);
			mysql_select_db($db_database);
			$books = mysql_query('SELECT `id`, `title` FROM `books`');
			echo '<ul>';
			while($book = mysql_fetch_object($books))
				echo '<li><a href="index.php?book_id=' . $book->id . '" target="_top">' . $book->title . '</a></li>';
			echo '</ul>';
		?>
	</body>	
<?php
	include 'footer.inc.php';
?>
