<?php
	include 'config.inc.php';

	$db = mysql_connect($db_server, $db_username, $db_password);
	mysql_select_db($db_database, $db);

	$books = mysql_query("SELECT * FROM books WHERE id=$book_id", $db);
	$book = mysql_fetch_object($books);
?>
<html>
<head>
	<link href="style.css" type="text/css" rel="stylesheet" />
	<title><?php echo $book->title;?> search</title>
</head>
<body class="index">
	<form action="search.php" method="GET">
		<input type="hidden" name="book_id" value="<?php echo $book_id; ?>" />
		Search: <input type="text" name="query" value="<?php echo $query; ?>"/>
    <input type="submit">
  </form>

	<?php

		if($query)
		{
			echo "<ul>";
			//$entries = mysql_query("SELECT (book_id, path, title, body) FROM `pages` WHERE book_id=$book_id AND MATCH (title, body) AGAINST ('$query')", $db);
			$entries = mysql_query("SELECT book_id, path, title, body FROM `pages` WHERE book_id=$book_id AND MATCH (title, body) AGAINST ('$query' IN BOOLEAN MODE)", $db) or die("Query failed : " . mysql_error());
			while($entry = mysql_fetch_object($entries))
			{
				echo "<li>";
				echo "<a href=\"page.php?book_id=$book_id&path=$entry->path\" target=\"main\">$entry->title</a>";
				echo "</li>";
			}
			echo "</ul>";
		}
	?>
</body>
