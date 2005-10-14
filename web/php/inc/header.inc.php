<?php
	require_once 'inc/config.inc.php';

	header('Content-Type: text/html; charset=' . $internal_encoding);

	$title = 'HTML Help Books';

	echo '<?xml version="1.0" encoding="' . $internal_encoding . '"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=' . $internal_encoding . '"/>';
	echo '<title>' . $title . '</title>';
	echo '<link href="css/default.css" type="text/css" rel="stylesheet"/>';
	echo '</head>';

	echo '<body>';
	
	echo '<div class="header">' . $title . '</div>';
	
	require_once 'lib/book_catalog.lib.php';

	$catalog = new BookCatalog();

	echo '<div id="tags" class="sidebox">';
	echo '<span class="title">Tags</span>';
	echo '<table>';
	$tags = $catalog->count_tags();
	foreach($tags as $tag => $tag_count)
	{
		echo '<tr>';
		echo  '<td>' . $tag_count . '</td>';
		echo  '<td>';
		echo   '<a href="books.php?tag=' . htmlspecialchars($tag, ENT_QUOTES) . '">';
		echo    htmlspecialchars($tag, ENT_NOQUOTES);
		echo   '</a>';
		echo  '</td>';
		echo '</tr>';
	}
		echo '<tr>';
		echo  '<td></td>';
		echo  '<td>';
		echo   '<a href="books.php">';
		echo    'all';
		echo   '</a>';
		echo  '</td>';
		echo '</tr>';
	echo '</table>';
	echo '</div>';
?>
	<div id="hosting" class="sidebox">
		<p><a href="http://dotsrc.org/"><img src="http://dotsrc.org/images/hostedby.png" alt="dotsrc.org logo"/></a></p>
		<p><a href="http://htmlhelp.berlios.de/"><img src="http://developer.berlios.de/bslogo.php?group_id=918" width="124" height="32" alt="BerliOS Developer Logo"/></a></p>
	</div>
