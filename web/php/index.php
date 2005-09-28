<?php
	require_once 'include/config.inc.php';
	require_once 'include/book_catalog.inc.php';

	header('Content-Type: text/html; charset=utf-8');

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	echo '<title>HTML Help Books</title>';
	echo '<link href="css/default.css" type="text/css" rel="stylesheet"/>';
	echo '</head>';

	echo '<body>';
	
	echo '<script src="js/index.js"></script>';

	echo '<div class="header">HTML Help Books</div>';
	
	$catalog = new BookCatalog();

	echo '<div id="tags" class="sidebox">';
	echo '<span class="title">Tags</span>';
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

	echo '</body>';
	
	echo '</html>';
?>
