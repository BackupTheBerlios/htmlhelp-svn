<?php
	include 'config.inc.php'; 

	$db = mysql_connect($db_server, $db_username, $db_password);
	mysql_select_db($db_database, $db);
	$books = mysql_query(sprintf('SELECT * FROM `books` WHERE `id`=%d', $book_id), $db);
	$book = mysql_fetch_object($books);
?>
