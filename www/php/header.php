<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<?php
		include 'config.inc.php'; 

		$db = mysql_connect($db_server, $db_username, $db_password);
		mysql_select_db($db_database, $db);
	?>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<?php
			$books = mysql_query("SELECT * FROM books WHERE id=$book_id", $db);
			$book = mysql_fetch_object($books);

			echo '<title>' . $book->title . '</title>';
			echo '<link href="' . $css .'" type="text/css" rel="stylesheet"/>';
		?>
	</head>
	<body>
		<div class="header"><?php echo $book->title;?></div>
		<div class="menubar">
			<div class="left">
				<a href="toc.php?book_id=<?php echo $book->id;?>" target="side">Contents</a> |
				<a href="index_.php?book_id=<?php echo $book->id;?>" target="side">Index</a>
			</div>
			<div class="right">
				<a href="catalog.php" target="_parent">Catalog</a>
			</div>
			<form id="search" action="search.php" target="side">
				<input type="hidden" name="book_id" value="<?php echo $book_id; ?>" />
				<input type="text" name="query" value="<?php echo $query; ?>"/>
				<input type="submit" value="Search">
			</form>
		</div>
	</body>
</html>
