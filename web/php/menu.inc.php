<?php
	echo '<div class="menubar">';

	echo '<a href="toc.php?book_id=' . $book_id . '" target="_self">Contents</a>';
	echo '| <a href="_index.php?book_id=' . $book_id . '" target="_self">Index</a>';
	echo '| <a href="search.php?book_id=' . $book_id . '" target="_self">Search</a>';

	echo '</div>';
?>
