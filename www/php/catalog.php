<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<?php
		include 'config.inc.php'; 

		$db = mysql_connect($db_server, $db_username, $db_password);
		mysql_select_db($db_database, $db);
	?>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Book Catalog</title>
		<?php	echo '<link href="' . $css . '" type="text/css" rel="stylesheet"/>'; ?>
	</head>
	<body>
		<div class="header">Book Catalog</div>

		<div id="catalog">
			<?php
				$books = mysql_query('SELECT `id`, `title` FROM `books`', $db);
				echo '<ul>';
				while($book = mysql_fetch_object($books))
					echo '<li><a href="book.php?book_id=' . $book->id . '">' . $book->title . '</a></li>';
				echo '</ul>';
			?>
		</div>
	</body>	
</html>
