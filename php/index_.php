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
	<title><?php echo $book->title;?> table of contents</title>
</head>
<body class="index">
	<?php
		function walk_index($parent_id)
		{
			global $db, $book_id;

			echo "<ul>";
			$entries = mysql_query("SELECT * FROM `index` WHERE book_id=$book_id AND parent_id=$parent_id ORDER BY term", $db);
			while($entry = mysql_fetch_object($entries))
			{

				$links = mysql_query("SELECT * FROM `index_links` WHERE index_id=$entry->id", $db);
				$link = mysql_fetch_object($links);
				
				echo "<li>";
				echo "<a href=\"page.php?book_id=$book_id&path=$link->path#$link->anchor\" target=\"main\">$entry->term</a>";
				walk_index($entry->id);
				echo "</li>";
			}
			echo "</ul>";
		}

		walk_index(0);
	?>
</body>
