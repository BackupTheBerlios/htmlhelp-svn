<?php
	include 'config.inc.php';

	// For this to work with the CGI version of PHP4, the "cgi.fix_pathinfo=1"
	// option in php.ini must be set.

	$book_id = '';
	$path = $PATH_INFO;
	while(!$book_id and $path)
		list($book_id, $path) = explode('/', $path, 2);
	
	$db = mysql_connect($db_server, $db_username, $db_password);
	mysql_select_db($db_database, $db);

	$pages = mysql_query(sprintf('SELECT `book_id`, `path`, `content` FROM `pages` WHERE `book_id`=%d AND `path`="%s"', $book_id, mysql_escape_string($path)), $db);
	$page = mysql_fetch_object($pages);
	$content = $page->content;
	
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

	echo $content;
?>
