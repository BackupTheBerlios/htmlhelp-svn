<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	header("Content-type: text/xml");

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';

	echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:hh="http://htmlhelp.berlios.de/rdf#">';
	
	/*
	$result = mysql_query('SELECT `no`, `title`, `path`, `anchor` FROM `toc_entry` WHERE `book_id`=' . $book_id);
	while(list($number, $name, $path, $anchor) = mysql_fetch_row($result))
	{
		echo '<rdf:Description rdf:ID="' . $number . '">';
		echo '<hh:name>' . htmlspecialchars($name, ENT_NOQUOTES, $encoding) . '</hh:name>';
		echo '<hh:link>page.php/' . $book_id . '/' . $path . ($anchor ? '#' . $anchor : '') . '</hh:link>';
		walk_toc($book_id, $number);
		echo '</rdf:Description>';
	}
	*/

	function walk_toc($book_id, $parent_number)
	{
		$result = mysql_query('SELECT `book_id`, `parent_no`, `no`, `title`, `path`, `anchor` FROM `toc_entry` WHERE `book_id`=' . $book_id . ' AND `parent_no`=' . $parent_number . ' ORDER BY `no`');
		if(mysql_num_rows($result))
		{
			if(!$parent_number)
				echo '<rdf:Seq rdf:about="urn:root">';
			else
				echo '<rdf:Seq rdf:about="#' . $parent_number .'">';
			while(list($book_id, $parent_number, $number, $name, $path, $anchor) = mysql_fetch_row($result))
			{
				echo '<rdf:li>';
				echo '<rdf:Description rdf:ID="' . $number .'">';
				echo '<hh:name>' . htmlspecialchars($name, ENT_NOQUOTES, $encoding) . '</hh:name>';
				echo '<hh:link>page.php/' . $book_id . '/' . $path . ($anchor ? '#' . $anchor : '') . '</hh:link>';
				echo '</rdf:Description>';
				walk_toc($book_id, $number);
				echo '</rdf:li>';
			}
			echo '</rdf:Seq>';
		}
	}

	if($book_id = intval($_GET['book_id']))
		walk_toc($book_id, 0);
	
	echo '</rdf:RDF>';
?>
