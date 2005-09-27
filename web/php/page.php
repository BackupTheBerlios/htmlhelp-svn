<?php
	require_once 'include/config.inc.php';
	require_once 'include/book.inc.php';
	require_once 'include/mimetypes.inc.php';

	// For this to work with the CGI version of PHP4, the "cgi.fix_pathinfo=1"
	// option in php.ini must be set.

	$alias = '';
	$path = $_SERVER['PATH_INFO'];
	while(!$alias and $path)
		list($alias, $path) = explode('/', $path, 2);
	require 'include/get_book_from_alias.inc.php';

	// If the 'path' param is not given then redirect to the book's front page.
	if(!$path)
	{
		header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . htmlspecialchars($alias) . '/' . $book->default_link());
		exit;
	}

	if(list($compressed, $content) = $book->page($path))
	{
		$content_type = guess_type($path);
		$accept_compressed = strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip') !== false;
		
		if($compressed)
		{
			if($accept_compressed)
			{
				header('Content-Encoding: gzip');
				header('Content-Type: ' . $content_type);
				header('Vary: Accept-Encoding');

				echo $content;
			}
			else
			{
				# FIXME: verify header here
				$content = gzinflate(substr($content, 10, -4));
				
				header('Content-Type: ' . $content_type);
				header('Content-Length: ' . strlen($content));
				header('Vary: Accept-Encoding');
				
				echo $content;
			}
		}
		else
		{
			header('Content-Type: ' . $content_type);
			header('Content-Length: ' . strlen($content));
			
			echo $content;
		}
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
