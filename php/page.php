<?php
	include 'config.inc.php';

	$db = mysql_connect($db_server, $db_username, $db_password);
	mysql_select_db($db_database, $db);

	$pages = mysql_query("SELECT * FROM pages WHERE book_id=$book_id AND path='$link'", $db);
	$page = mysql_fetch_object($pages);

	function href_replace_callback($matches) {
		global $book_id;
		
		return "href=\"page.php?book_id=$book_id&link=$matches[1]\"";
	}
		
	echo preg_replace_callback('/href="([^"]*)"/', "href_replace_callback", $page->content);
?>
