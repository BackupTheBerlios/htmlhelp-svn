<?php
	include 'config.inc.php';
	include 'book.inc.php';

	$title = $book->title;

	include 'header.inc.php';
?>
	<body id="search" class="sidebar">
		<?php

			if($query)
			{
				echo "<ul>";
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
<?php
	include 'footer.inc.php';
?>
