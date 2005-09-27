<?php
	require_once 'include/config.inc.php';
	require_once 'include/book.inc.php';

	// XXX: for link backward compatability
	if($book = $_GET['book'])
	{
		header(
			'Location: http://' . 
			$_SERVER['HTTP_HOST'] . 
			dirname($_SERVER['REQUEST_URI']) . 
			'/book.php?book=' . $book);
		exit;
	}
	
	$title = 'HTML Help Books';
	require 'include/header.inc.php';

	echo '<body>';
	
	echo '<script src="js/index.js"></script>';

	echo '<div class="header">HTML Help Books</div>';
	
	$catalog = new Book_Catalog();

	echo '<div id="tags" class="sidebar">';
	echo '<p><strong>Tags</strong></p>';
	echo '<table>';
	$tags = $catalog->count_tags();
	foreach($tags as $tag => $count)
	{
		echo '<tr>';
		echo  '<td>' . $count . '</td>';
		echo  '<td>';
		echo   '<a href="?tag=' . htmlspecialchars($tag) . '">';
		echo    htmlspecialchars($tag, ENT_NOQUOTES);
		echo   '</a>';
		echo  '</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
	
	echo '<div class="content">';
	if($tag = $_GET['tag'])
		$books = $catalog->enumerate_books_by_tag($tag);
	else	
		$books = $catalog->enumerate_books();	
	if(count($books))
	{
		echo '<ul class="list">';
		foreach($books as $title => $book)
		{
			$alias = $book->alias();
			echo '<li>';
			echo  '<a ';
			echo    'href="book.php?book=' . htmlspecialchars($alias) . '" ';
			echo    'onclick="return openBook(\'' . htmlspecialchars($alias) . '\');" ';
			echo    'target="_blank">';
			echo   htmlspecialchars($title, ENT_NOQUOTES);
			echo  '</a>';
			echo '</li>';
		}
		echo '</ul>';
	}
	else
		echo '<p>No book found.</p>';
	echo '</div>';

	echo '<div>';
	require 'include/frontpage.inc.php';
	echo '</div>';
	
	echo '</body>';
	
	require 'include/footer.inc.php';
?>
