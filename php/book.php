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
<frameset cols="256,*">
	<frameset rows="32,*">
		<frame src="menu.php?book_id=<?php echo $book->id;?>" name="menu">
		<frame src="toc.php?book_id=<?php echo $book->id;?>" name="navigation">
	</frameset>
	<frame src="page.php?book_id=<?php echo $book->id;?>&path=<?php echo $book->default_path;?>" name="main">
</frameset>
</html>
