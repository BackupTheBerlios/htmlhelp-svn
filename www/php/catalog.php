<?php
	include 'config.inc.php';
?>
<html>
<head>
	<link href="style.css" type="text/css" rel="stylesheet" />
	<title>Catalog</title>
</head>
<body class="catalog">
	<ul>
	<?php
		$db = mysql_connect($db_server, $db_username, $db_password);
		mysql_select_db($db_database, $db);
		$books = mysql_query('SELECT id, title FROM books', $db);
		while($book = mysql_fetch_object($books))
			print ("<li><a href=\"book.php?book_id=$book->id\">$book->title</a></li>\n");
	?>
	</ul>
</body>	
