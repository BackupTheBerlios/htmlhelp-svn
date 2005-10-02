<?php

require_once 'inc/mysql.inc.php';
require_once 'lib/book.lib.php';
require_once 'lib/book_builder.lib.php';
require_once 'lib/devhelp.lib.php';

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
		if(!$book->metadata('name') and preg_match(
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

		$this->update_aliases();
				
		// attemp to tag new book based on the title
		$book_ids = array($book->id);
		$title = $book->title();
		preg_match_all('/[\pL\pN]+/u', $title, $matches, PREG_PATTERN_ORDER);
		$tags = & $matches[0];
		$this->tag_books($book_ids, $tags);
	}

	// Update book aliases cache
	//
	// It should be called whenever the book metadata table is changed 		
	function update_aliases()
	{
		// a book can be identified by its 'name', 'name_version', and 
		// 'name_version_language'
		mysql_query(<<<EOSQL
			REPLACE INTO book_alias
			SELECT book.id AS book_id, book.id
				FROM book
			UNION
				SELECT value AS alias, book_id
				FROM metadata
				WHERE name = 'name'
			UNION
				SELECT GROUP_CONCAT(value ORDER BY name SEPARATOR '-') AS alias, book_id
				FROM metadata
				WHERE name in ('name', 'version')
				GROUP BY book_id
				HAVING COUNT(DISTINCT name) = 2
			UNION
				SELECT GROUP_CONCAT(value ORDER BY name SEPARATOR '-') AS alias, book_id
				FROM metadata
				WHERE name in ('name', 'version', 'language')
					GROUP BY book_id
					HAVING COUNT(DISTINCT name) = 3
EOSQL
		);
	}
	
	function enumerate_tags()
	{
		$tags = array();
		$result = mysql_query("
			SELECT tag
			FROM tag
			ORDER BY tag ASC
		");
		while(list($tag) = mysql_fetch_row($result))
			$tags[] = $tag;			
		return $tags;
	}
	
	function count_tags()
	{
		$tags = array();
		$result = mysql_query("
			SELECT tag, COUNT(DISTINCT book_id) AS count
			FROM tag
				LEFT JOIN book_tag ON tag.id = tag_id
				LEFT JOIN book_alias ON book_name = alias
			GROUP BY tag.id
			-- HAVING count > 0
			ORDER BY count DESC, tag ASC
		");
		while(list($tag, $count) = mysql_fetch_row($result))
			$tags[$tag] = $count;			
		return $tags;
	}

	function _get_book_alias($book_id)
	{
		$result = mysql_query("
			SELECT alias
			FROM book_alias
			WHERE book_id = $book_id
				AND alias != $book_id
			ORDER BY LENGTH(alias) ASC
		");
		if(mysql_num_rows($result))
		{
			list($alias) = mysql_fetch_row($result);
			return $alias;
		}
		
		// fallback to the book numeric ID
		return strval($this->id);
	}

	// Internal function which enumerates books by a SQL query
	//
	// The query should return a (book_id, book_title) column table. 
	function _enumerate_books_by_query($query)
	{
		$books = array();
		$result = mysql_query($query);
		if($result)
			while(list($book_id, $book_title) = mysql_fetch_row($result))
			{
				$book_alias = $this->_get_book_alias($book_id);
				$books[$book_alias] = $book_title;
			}
		return $books;
	}
	
	function enumerate_books()
	{
		return $this->_enumerate_books_by_query("
			SELECT id, title 
			FROM book 
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
		");
		while(list($book_id, $book_title) = mysql_fetch_row($result))
			$books[$book_id] = $book_title;
		return $books;
	}	
	
	function enumerate_books_by_tag($tag)
	{
		return $this->_enumerate_books_by_query("
			SELECT book.id, book.title
			FROM book
				LEFT JOIN book_alias ON book_id = book.id
				LEFT JOIN book_tag ON book_name = alias 
				LEFT JOIN tag ON tag.id = tag_id
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
			FROM book_alias 
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
			
		$values = array();
		foreach($tags as $tag)
			$values[] = "'" . mysql_escape_string($tag) . "'";			
		mysql_query("
			REPLACE 
			INTO tag 
			(tag)
			VALUES (" . implode(',', $values) . ")
		");
	}
	
	function delete_tags($tags)
	{
		if(!count($tags))
			return;
			
		$values = array();
		foreach($tags as $tag)
			$values[] = "'" . mysql_escape_string($tag) . "'";			
		mysql_query("
			DELETE 
			FROM tag 
			WHERE tag in (" . implode(',', $values) . ")
		");
	}
	
	// Tag the books with the given tags
	function tag_books(&$book_ids, &$tags)
	{
		if(!count($book_ids) || !count($tags))
			return;
			
		$values = array();
		foreach($tags as $tag)
			$values[] = "'" . mysql_escape_string($tag) . "'";			
		foreach($book_ids as $book_id)
		{
			mysql_query(
				"REPLACE " .
					"INTO book_tag (tag_id, book_name) " .
				"SELECT tag.id, value " .
					"FROM tag, metadata " .
					"WHERE book_id = " . intval($book_id) . " " .
						"AND name = 'name' " .
						"AND tag IN (" . implode(',', $values) . ")"
			) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		}
	}
	
	// Untag the books with the given tags
	function untag_books(&$book_ids, &$tags)
	{
		if(!count($book_ids) || !count($tags))
			return;
			
		$values = array();
		foreach($tags as $tag)
			$values[] = "'" . mysql_escape_string($tag) . "'";
		foreach($book_ids as $book_id)
		{
			mysql_query(
				"DELETE book_tag " .
				"FROM book_alias " .
					"LEFT JOIN book_tag ON book_name = alias " .
					"LEFT JOIN tag ON tag.id = tag_id " .
				"WHERE book_id = " . intval($book_id) . " " .
					"AND tag IN (" . implode(',', $values) . ")"
			) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		}
	}
}

?>
