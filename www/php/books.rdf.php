<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	header("Content-type: text/xml");

	echo '<?xml version="1.0" encoding="UTF-8"?' . '>';

	echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:hh="http://htmlhelp.berlios.de/rdf#">';
	
	echo '<rdf:Seq rdf:about="urn:root">';
	$result = mysql_query('SELECT `id`, `title` FROM `book`');
	if(mysql_num_rows($result))
	{
		while(list($book_id, $title) = mysql_fetch_row($result))
		{
			echo '<rdf:li>';
			echo '<rdf:Description hh:book_id="' . $book_id . '">';
			echo '<hh:title>' . $title . '</hh:title>';
			echo '</rdf:Description>';
			echo '</rdf:li>';
		}
	}
	echo '</rdf:Seq>';
	
	echo '</rdf:RDF>';
?>
