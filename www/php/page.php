<?php
	include 'config.inc.php';

	$db = mysql_connect($db_server, $db_username, $db_password);
	mysql_select_db($db_database, $db);

	$pages = mysql_query("SELECT `book_id`, `path`, `content` FROM `pages` WHERE `book_id`=$book_id AND `path`='$path'", $db);
	$page = mysql_fetch_object($pages);
	$content = $page->content;
	
	function _mime_content_type($path)
	{
		if(substr($path, strlen($path) - 5) == '.html')
			return 'text/html';

		if(substr($path, strlen($path) - 4) == '.htm')
			return 'text/html';
			
		return 'application/octet-stream';
	}	
	
	$content_type = _mime_content_type($path);
	
	if($content_type == 'text/html') {
		function href_replace_callback($matches) {
			global $book_id;
			
			return $matches[1] . '="page.php?book_id=' . $book_id . '&path=' . $matches[2] . '"';
		}
		
		$content = preg_replace_callback('/(href|src)="([^"]*)"/', "href_replace_callback", $page->content);
	}
	
	header('Content-Type: ' . $content_type);
	echo $content;
?>
