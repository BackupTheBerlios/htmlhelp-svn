<?php
	include 'config.inc.php';
	include 'book.inc.php';

	$title = $book->title . ' index';

	include 'header.inc.php';
?>
	<body id="index" class="sidebar">
		<?php
			function walk_index($parent_id)
			{
				global $db, $book_id;

				echo '<ul>';
				$entries = mysql_query("SELECT * FROM `index` WHERE `book_id`=$book_id AND `parent_id`=$parent_id ORDER BY `term`", $db);
				while($entry = mysql_fetch_object($entries))
				{

					$links = mysql_query("SELECT * FROM `index_links` WHERE `index_id`=$entry->id", $db);
					$link = mysql_fetch_object($links);
					
					echo '<li>';
					echo '<a href="page.php/' . $book_id . '/' . $link->path . '#' . $link->anchor . '" target="main">' . $entry->term . '</a>';
					walk_index($entry->id);
					echo '</li>';
				}
				echo '</ul>';
			}

			walk_index(0);
		?>
	</body>
<?php
	include 'footer.inc.php';
?>
