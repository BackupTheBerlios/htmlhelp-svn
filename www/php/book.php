<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
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
<frameset rows="32,*">
	<frame src="header.php?book_id=<?php echo $book->id;?>" name="header"/>
	<frameset cols="256,*">
		<frame src="toc.php?book_id=<?php echo $book->id;?>" name="side"/>
		<frame src="page.php?book_id=<?php echo $book->id;?>&path=<?php echo $book->default_path;?>" name="main"/>
	</frameset>
</frameset>
</html>
