<?php
	include 'config.inc.php';

	$db = mysql_connect($db_server, $db_username, $db_password);
	mysql_select_db($db_database, $db);

	$books = mysql_query("SELECT * FROM books WHERE id=$book_id", $db);
	$book = mysql_fetch_object($books);
?>
<html>
<head>
	<title><?php echo $book->title;?></title>
</head>
<frameset cols="256,*">\n'
	<frame src="toc.php?book_id=<?php echo $book->id;?>" name="navigation">
	<frame src="page.php?book_id=<?php echo $book->id;?>&link=<?php echo $book->default_link;?>" name="main">
</frameset>
