<?php
	require_once 'config.inc.php';
	require_once 'book.inc.php';

	// For link backward compatability
	if($book = intval($_GET['book']))
	{
		header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/book.php?book=' . $book);
		exit;
	}
	
	// Unless the 'noxul' param is given then embed a Javascript script to
	$title = 'HTML Help Books';
	require_once 'header.inc.php';

	echo '<body>';
	
	echo '<script src="js/index.js"></script>';

	echo '<div class="header">HTML Help Books</div>';
	
	$catalog = new Book_Catalog();

	echo '<div id="tags">';	
	$tags = $catalog->enumerate_tags();
	echo '<ul class="list">';
	foreach($tags as $tag => $count)
		echo '<li><a href="?tag=' . htmlspecialchars($tag) . '">' . htmlspecialchars($tag, ENT_NOQUOTES) . '</a> (' . $count . ')</li>';
	echo '</ul>';	
	echo '</div>';
	
	echo '<div>';
	if($tag = $_GET['tag'])
		$books = $catalog->enumerate_books_by_tag($tag);
	else	
		$books = $catalog->enumerate_books();	
	if(count($books))
	{
		echo '<ul class="list">';
		foreach($books as $title => $book)
			echo '<li><a href="book.php?book=' . htmlspecialchars($book->alias()) . '" onclick="return openBook(\'' . $alias . '\');" target="_blank">' . htmlspecialchars($title, ENT_NOQUOTES) . '</a></li>';
		echo '</ul>';
	}
	else
		echo '<p>No book found.</p>';
	echo '</div>';

	echo '<div>';
	require_once 'frontpage.inc.php';
	echo '</div>';
	
	echo '</body>';
	
	require_once 'footer.inc.php';
?>
