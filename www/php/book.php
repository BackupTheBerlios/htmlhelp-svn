<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<?php
		include 'config.inc.php'; 

		$db = mysql_connect($db_server, $db_username, $db_password);
		mysql_select_db($db_database, $db);
	?>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<?php
			$books = mysql_query("SELECT * FROM `books` WHERE `id`=$book_id", $db);
			$book = mysql_fetch_object($books);

			echo '<title>' . $book->title . '</title>';
			echo '<link href="' . $css .'" type="text/css" rel="stylesheet"/>';
		?>
	</head>
	<frameset rows="80,*">
		<frame src="header.php?book_id=<?php echo $book->id;?>" name="header" frameborder="0" scrolling="no" noresize="noresize"/>
		<frameset rows="*" cols="256,*">
			<frame src="toc.php?book_id=<?php echo $book->id;?>" name="side" frameborder="0"/>
			<frame src="page.php/<?php echo $book->id . '/' . $book->default_path . '#' . $book->default_anchor;?>" name="main" frameborder="0"/>
		</frameset>
	</frameset>
</html>
