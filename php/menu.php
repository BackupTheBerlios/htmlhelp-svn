<?php
	include 'config.inc.php';

	$db = mysql_connect($db_server, $db_username, $db_password);
	mysql_select_db($db_database, $db);

	$books = mysql_query("SELECT * FROM books WHERE id=$book_id", $db);
	$book = mysql_fetch_object($books);
?>
<html>
<head>
	<link href="style.css" type="text/css" rel="stylesheet" />
	<title><?php echo $book->title;?></title>
</head>
<body class="menu">
	<a href="toc.php?book_id=<?php echo $book->id;?>" target="navigation">Contents</a>
	<a href="index_.php?book_id=<?php echo $book->id;?>" target="navigation">Index</a>
	<a href="search.php?book_id=<?php echo $book->id;?>" target="navigation">Search</a>
</body>
