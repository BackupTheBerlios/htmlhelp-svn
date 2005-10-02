<?php
	$title = 'HTML Help Books';
	
	require 'inc/header.inc.php';
	
	if($tag = $_GET['tag'])
		$books = $catalog->enumerate_books_by_tag($tag);
	else	
		$books = $catalog->enumerate_books();	
	if(count($books))
	{
		echo '<ul class="list">';
		foreach($books as $book_alias => $book_title)
		{
			echo '<li>';
			echo  '<a ';
			echo    'href="book.php?book=' . htmlspecialchars($book_alias) . '" ';
			echo    'onclick="return openBook(\'' . htmlspecialchars($book_alias) . '\');" ';
			echo    'target="_blank">';
			echo   htmlspecialchars($book_title, ENT_NOQUOTES);
			echo  '</a>';
			echo '</li>';
		}
		echo '</ul>';
	}
	else
		echo '<p>No book found.</p>';

	require 'inc/footer.inc.php';
?>
