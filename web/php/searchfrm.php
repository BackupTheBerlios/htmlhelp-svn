<?php

$title = 'Search';
$id = 'search';
require 'inc/frmheader.inc.php';

	$query = $_GET['query'];
	echo '<form id="find" target="navigation" action="../../searchfrm.php">';
	echo  '<input type="hidden" name="book" value="' . htmlspecialchars($alias) .'"/>';
	echo  '<input id="query" type="text" name="query" value="' . htmlspecialchars($query) . '"/>';
	echo  '<input id="submit" type="submit" value="Find"/>';
	echo '</form>';

	if($query)
	{
		$entries = $book->search($query);
		if(count($entries))
		{
			echo '<ul class="list">';
			foreach($entries as $entry)
			{
				list($title, $path) = $entry;
				echo '<li><a href="' . $path .'">' . htmlspecialchars($title, ENT_NOQUOTES) . '</a></li>';
			}
			echo '</ul>';
		}
	}

require 'inc/frmfooter.inc.php';
?>
