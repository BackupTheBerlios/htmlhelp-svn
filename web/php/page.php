<?php
	include 'config.inc.php';
	include 'mysql.inc.php';
	include 'mimetypes.inc.php';

	// For this to work with the CGI version of PHP4, the "cgi.fix_pathinfo=1"
	// option in php.ini must be set.

	$book_id = '';
	$path = $_SERVER['PATH_INFO'];
	while(!$book_id and $path)
		list($book_id, $path) = explode('/', $path, 2);
	$book_id = intval($book_id);

	// If the 'path' param is not given then redirect to the book's front page.
	if(!$path)
	{
		$result = mysql_query('SELECT `default_path` FROM `book` WHERE `id`=' . $book_id);
		if(mysql_num_rows($result))
		{
			list($default_path) = mysql_fetch_row($result);
			header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $book_id . '/' . $default_path);
			exit;
		}
	}

	$result = mysql_query('SELECT `content` FROM `page` WHERE `book_id`=' . $book_id . ' AND `path`=\'' . mysql_escape_string($path) . '\'');
	if(mysql_num_rows($result))
	{
		list($content) = mysql_fetch_row($result);
		
		$content_type = mime_content_type($path);
		
		header('Content-Type: ' . $content_type);
		header('Content-Length: ' . strlen($content));

		echo $content;
	}
	else
	{
		header("Status: 404 Not Found");
		echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">';
		echo '<html><head>';
		echo '<title>404 Not Found</title>';
		echo '</head><body>';
		echo '<h1>Not Found</h1>';
		echo '<p>The requested page was not found.</p>';
		echo '</body></html>';
	}
?>
