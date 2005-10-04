<?php

$title = 'Index';
$id = 'index';
require 'inc/frmheader.inc.php';

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