<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

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
		list($default_path) = mysql_fetch_row($result);
		header('Location: ' . $default_path);
		exit;
	}

	$result = mysql_query('SELECT `content` FROM `page` WHERE `book_id`=' . $book_id . ' AND `path`=\'' . mysql_escape_string($path) . '\'');
	list($content) = mysql_fetch_row($result);
	
	// Determine the MIME content-type from the file extension.
	// FIXME: This is very incomplete - we should rely on the PHP library for this.
	function _mime_content_type($path)
	{
		$ext = strrchr($path, '.');
		
		if($ext == '.html' or $ext == '.htm')
			return 'text/html';

		if($ext == '.css')
			return 'text/css';

		return 'application/octet-stream';
	}	
	
	$content_type = _mime_content_type($path);
	
	header('Content-Type: ' . $content_type);
	header('Content-Length: ' . strlen($content));

	echo $content;
?>