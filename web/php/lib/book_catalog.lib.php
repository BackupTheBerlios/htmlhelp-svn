<?php

require_once 'inc/mysql.inc.php';
require_once 'lib/book.lib.php';
require_once 'lib/book_builder.lib.php';
require_once 'lib/devhelp.lib.php';
require_once 'lib/mysql_util.lib.php';

class BookCatalog
{
	// Import a book into the catalog (database)
	function import_book($filename)
	{
		$book = & new BookBuilder();
		$reader = & new DevhelpReader($filename);
		$reader->read($book);
		
		// attempt to get book name and version from filename
		// FIXME: deal with uploads too
		// TODO: detect language too
		$name = $book->metadata('name');
		if(!$name and preg_match(
			'/^(.*?)(?:-([0-9]+(?:\.[0-9]+[a-z]?)*))\.tgz$/', 
			basename($filename), 
			$matches)
		)
		{
			$name = $matches[1];
			$version = $matches[2];
			
			if($name)
				$book->set_metadata('name', $name);
			
			if($version)
				$book->set_metadata('version', $version);
		}

		// attemp to tag new book based on the title
		$book_ids = array($book->id);
		$title = $book->title();
		preg_match_all('/\w+/', $title, $matches, PREG_PATTERN_ORDER);
		$tags = & $matches[0];
		$this->tag_books($book_ids, $tags);
	}

	function enumerate_tags()
	{
		$result = mysql_query("
			SELECT tag
			FROM tag
			ORDER BY tag ASC
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		$tags = mysql_fetch_fields($result);
		return $tags;
	}
	
	function count_tags()
	{
		$tags = array();
		$result = mysql_query("
			SELECT tag, COUNT(DISTINCT alias.book_id) AS count
			FROM tag
				LEFT JOIN alias_tag ON tag_id = tag.id
				LEFT JOIN alias ON alias.id = alias_id
			GROUP BY tag.id
			HAVING count > 0
			ORDER BY count DESC, tag ASC
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		while(list($tag, $count) = mysql_fetch_row($result))
			$tags[$tag] = $count;			
		return $tags;
	}

	// Internal function which enumerates books by a SQL query
	//
	// The query should return a (book_id, book_title) column table. 
	function _enumerate_books_by_query($query)
	{
		$books = array();
		$result = mysql_query($query);
		if($result)
			while(list($book_alias, $book_title) = mysql_fetch_row($result))
				$books[$book_alias] = $book_title;
		return $books;
	}
	
	function enumerate_books()
	{
		return $this->_enumerate_books_by_query("
			SELECT alias, title 
			FROM alias
				INNER JOIN book ON book.id = book_id 
			ORDER BY title
		");
	}
	
	// IDs should be used only administrative purposes
	function enumerate_book_ids()
	{
		$books = array();
		$result = mysql_query("
			SELECT id, title 
			FROM book 
			ORDER BY title
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		while(list($book_id, $book_title) = mysql_fetch_row($result))
			$books[$book_id] = $book_title;
		return $books;
	}	
	
	function enumerate_books_by_tag($tag)
	{
		return $this->_enumerate_books_by_query("
			SELECT alias, title
			FROM tag
				LEFT JOIN alias_tag ON tag_id = tag.id
				LEFT JOIN alias ON alias.id = alias_id
				INNER JOIN book ON book.id = book_id
			WHERE tag.tag = '" . mysql_escape_string($tag) . "'
			ORDER BY book.title ASC
		");
	}
	
	function get_book_by_id($book_id)
	{
		return new Book(intval($book_id));
	}
	
	function get_book_from_alias($alias)
	{
		// search alias cache
		$result = mysql_query("
			SELECT book_id 
			FROM alias 
			WHERE alias='" . mysql_escape_string($alias) . "'
		");
		if(mysql_num_rows($result))
		{
			list($id) = mysql_fetch_row($result);
			return new Book($id);
		}
		
		// fallback to numeric book ID
		if(is_numeric($alias))
		{
			$id = intval($alias);
			return new Book($id);
		}
		
		return NULL;
	}
	
	function add_tags($tags)
	{
		if(!count($tags))
			return;
			
		mysql_query("
			INSERT IGNORE 
			INTO tag (tag)
			VALUES (" . mysql_escape_array($tags) . ")
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
	}
	
	function delete_tags(&$tags)
	{
		if(!count($tags))
			return;
			
		mysql_query("
			DELETE 
			FROM tag 
			WHERE tag in (" . mysql_escape_array($tags) . ")
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
	}
	
	// Tag the books with the given tags
	function tag_books(&$book_ids, &$tags)
	{
		if(!count($book_ids) || !count($tags))
			return;
		
		$result = mysql_query(
			"SELECT id " .
			"FROM tag " .
			"WHERE tag in (" . mysql_escape_array($tags) . ")"
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		$tag_ids = mysql_fetch_fields($result);
		
		if(!count($tag_ids))
			return;

		$values = array();
		foreach($tag_ids as $tag_id)
			foreach($book_ids as $book_id)
				$values[] = '(' . intval($tag_id) . ',' . intval($book_id) . ')';
		mysql_query(
			"REPLACE " .
			"INTO alias_tag (tag_id, book_id) " .
			"VALUES " . implode(',', $values)
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
	}
	
	// Untag the books with the given tags
	function untag_books(&$book_ids, &$tags)
	{
		if(!count($book_ids) || !count($tags))
			return;

		$result = mysql_query(
			"SELECT id " .
			"FROM tag " .
			"WHERE tag in (" . mysql_escape_array($tags) . ")"
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		$tag_ids = mysql_fetch_fields($result);

		if(!count($tag_ids))
			return;

		mysql_query(
			"DELETE " .
			"FROM alias_tag " .
			"WHERE " .
				"tag_id IN (" . mysql_escape_array($tag_ids) . ") AND " .
				"book_id IN (" . mysql_escape_array($book_ids) . ")"
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
	}
}

?>
