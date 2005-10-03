<?php

$title = 'Index';
$id = 'index';
require 'inc/frmheader.inc.php';

	$query = $_GET['query'];
	echo '<form id="find" target="navigation" action="../../indexfrm.php">';
	echo  '<input type="hidden" name="book" value="' . htmlspecialchars($alias) .'"/>';
	echo  '<input id="query" type="text" name="query" value="' . htmlspecialchars($query) . '"/>';
	echo  '<input id="submit" type="submit" value="Find"/>';
	echo '</form>';

	if(isset($query))
	{
		$entries = $book->index($query);
		if(count($entries))
		{
			echo '<ul class="list">';
			foreach($entries as $entry)
			{
				list($term, $path) = $entry;
				echo '<li><a href="' . $path .'">' . htmlspecialchars($term, ENT_NOQUOTES) . '</a></li>';
			}
			echo '</ul>';
		}
	}
	
require 'inc/frmfooter.inc.php';
?>
