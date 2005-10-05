<?php
	require_once 'inc/config.inc.php';
	require_once 'lib/book_catalog.lib.php';

	header('Content-Type: text/html; charset=utf-8');

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	echo '<title>' . $title . '</title>';
	echo '<link href="css/default.css" type="text/css" rel="stylesheet"/>';
	echo '</head>';

	echo '<body>';
	
	echo '<div class="header">' . $title . '</div>';
	
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
		echo   '<a href="index.php?tag=' . htmlspecialchars($tag) . '">';
		echo    htmlspecialchars($tag, ENT_NOQUOTES);
		echo   '</a>';
		echo  '</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
?>
	<div id="hosting" class="sidebox">
		<a href="http://dotsrc.org/"><img src="http://dotsrc.org/images/hostedby.png"/></a>
	</div>
<?php
	
	echo '<div class="content">';
?>
