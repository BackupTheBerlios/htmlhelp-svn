<?php
	include 'config.inc.php';
	include 'book.inc.php';

	$title = $book->title . ' contents';

	include 'header.inc.php';
?>
	<body id="toc" class="sidebar">
		<?php
			function walk_toc($parent_number)
			{
				global $book;

				echo '<ul>';
				$entries = mysql_query(sprintf('SELECT * FROM `toc` WHERE `book_id`=%d AND `parent_number`=%d ORDER BY number', $book->id, $parent_number));
				while($entry = mysql_fetch_object($entries))
				{
					echo '<li>';
					echo '<a href="page.php/' . $book->id . '/' . $entry->path . '#' . $entry->anchor . '" target="main">' . htmlentities($entry->name, ENT_NOQUOTES, 'UTF-8') . '</a>';
					walk_toc($entry->number);
					echo "</li>";
				}
				echo '</ul>';
			}

			walk_toc(0);
		?>
	</body>
<?php
	include 'footer.inc.php';
?>
