<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<?php
		include 'config.inc.php'; 

		$db = mysql_connect($db_server, $db_username, $db_password);
		mysql_select_db($db_database, $db);
	?>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<?php
			$books = mysql_query("SELECT * FROM `books` WHERE `id`=$book_id", $db);
			$book = mysql_fetch_object($books);

			echo '<title>' . $book->title . ' search</title>';
			echo '<link href="' . $css .'" type="text/css" rel="stylesheet"/>';
		?>
	</head>
	<body id="search" class="sidebar">
		<!--
		<form action="search.php" method="GET">
			<input type="hidden" name="book_id" value="<?php echo $book_id; ?>" />
			Search: <input type="text" name="query" value="<?php echo $query; ?>"/>
			<input type="submit">
		</form>
		-->
		<?php

			if($query)
			{
				echo "<ul>";
				//$entries = mysql_query("SELECT (book_id, path, title, body) FROM `pages` WHERE book_id=$book_id AND MATCH (title, body) AGAINST ('$query')", $db);
				$entries = mysql_query("SELECT book_id, path, title, body FROM `pages` WHERE book_id=$book_id AND MATCH (title, body) AGAINST ('$query' IN BOOLEAN MODE)", $db) or die("Query failed : " . mysql_error());
				while($entry = mysql_fetch_object($entries))
				{
					echo "<li>";
					echo "<a href=\"page.php/$book_id/$entry->path\" target=\"main\">$entry->title</a>";
					echo "</li>";
				}
				echo "</ul>";
			}
		?>
	</body>
