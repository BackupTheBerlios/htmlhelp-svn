<?php
	include 'config.inc.php';

	$title = 'Book Catalog';

	include 'header.inc.php';
?>
	<body>
		<div class="header">Book Catalog</div>

		<div id="catalog">
			<?php
				mysql_connect($db_server, $db_username, $db_password);
				mysql_select_db($db_database);
				$books = mysql_query('SELECT `id`, `title` FROM `books`');
				echo '<ul>';
				while($book = mysql_fetch_object($books))
					echo '<li><a href="book.php?book_id=' . $book->id . '">' . $book->title . '</a></li>';
				echo '</ul>';
			?>
		</div>
	</body>	
<?php
	include 'footer.inc.php';
?>
