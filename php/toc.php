<?php
	include 'config.inc.php';

	$db = mysql_connect($db_server, $db_username, $db_password);
	mysql_select_db($db_database, $db);

	$books = mysql_query("SELECT * FROM books WHERE id=$book_id", $db);
	$book = mysql_fetch_object($books);
?>
<html>
<head>
	<title><?php echo $book->title;?> table of contents</title>
</head>
<body>
	<?php
		function walk_toc($parent_id)
		{
			global $db, $book_id;

			echo "<ul>";
			$entries = mysql_query("SELECT * FROM toc WHERE book_id=$book_id AND parent_id=$parent_id ORDER BY number", $db);
			while($entry = mysql_fetch_object($entries))
			{
				echo "</li>";
				echo "<a href=\"page.php?book_id=$book_id&link=$entry->link\" target=\"main\">$entry->name</a>";
				walk_toc($entry->id);
				echo "</li>";
			}
			echo "</ul>";
		}

		walk_toc(0);
	?>
</body>
