<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	header("Content-type: text/xml");

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';

	echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:hh="http://htmlhelp.berlios.de/rdf#">';
	
	echo '<rdf:Seq rdf:about="urn:root">';
	if($book_id = intval($_GET['book_id']))
	{
		$result = mysql_query('SELECT `term`, `path`, `anchor` FROM `index_entry`,`index_link` WHERE `index_entry`.`book_id`=' . $book_id . ' AND `index_link`.`book_id`=' . $book_id . ' AND `index_link`.`no`=`index_entry`.`no` ORDER BY `index_entry`.`term`');
		while(list($term, $path, $anchor) = mysql_fetch_row($result))
		{
			echo '<rdf:li>';
			echo '<rdf:Description hh:href="page.php/' . $book_id . '/' . $path . ($anchor ? '#' . $anchor : '') . '">';
			echo '<hh:term>'. $term . '</hh:term>';
			echo '</rdf:Description>';
			echo '</rdf:li>';
		}
	}
	echo '</rdf:Seq>';

	echo '</rdf:RDF>';
?>
