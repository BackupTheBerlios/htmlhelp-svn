<?php
	include 'config.inc.php'; 

	mysql_connect($db_server, $db_username, $db_password);
	mysql_select_db($db_database);
	$books = mysql_query(sprintf('SELECT * FROM `books` WHERE `id`=%d', $book_id));
	$book = mysql_fetch_object($books);
?>
