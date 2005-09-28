<?php

require_once 'include/mysql.inc.php';
require_once 'include/book_fulltext_index.inc.php';
require_once 'include/fulltext_search.inc.php';

class Book extends Searchable
{
	var $id;

	function Book($id)
	{
		$this->id = $id;
	}
	
	function title()
	{
		$result = mysql_query("
			SELECT title 
			FROM book 
			WHERE id = $this->id
		");
		list($title) = mysql_fetch_row($result);
		return $title;
	}

	function default_link()
	{
		$result = mysql_query("
			SELECT CONCAT_WS('#', path, NULLIF(anchor, '')) 
			FROM book
				LEFT JOIN page ON page.no = page_no
			WHERE id = $this->id 
				AND book_id = $this->id
		");
		list($link) = mysql_fetch_row($result);
		return $link;
	}

	function toc($parent_number = 0, $depth = -1)
	{
		// FIXME: do this non-recursively
		$entries = array();
		if($depth)
		{
			$result = mysql_query("
				SELECT toc_entry.no, toc_entry.title, CONCAT_WS('#', path, NULLIF(anchor, '')) 
				FROM toc_entry
			 		LEFT JOIN page ON page.no = page_no
				WHERE toc_entry.book_id = $this->id 
					AND parent_no = $parent_number 
					AND page.book_id =  $this->id
				ORDER BY toc_entry.no
			");
			while(list($number, $title, $link) = mysql_fetch_row($result))
				$entries[$number] = array($title, $link, $this->toc($number, $depth - 1));
		}
		return $entries;
	}

	function toc_entry($number)
	{
		$result = mysql_query("
			SELECT parent_no, toc_entry.title, CONCAT_WS('#', path, NULLIF(anchor, '')) 
			FROM toc_entry
				LEFT JOIN page ON page.no = page_no
			WHERE toc_entry.book_id = $this->id 
				AND toc_entry.no = $number 
				AND page.book_id = $this->id
		");
		list($parent_number, $title, $link) = mysql_fetch_row($result);
		return array($parent_number, $title, $link);
	}

	function index($query = '')
	{
		$result = mysql_query("
			SELECT term, CONCAT_WS('#', path, NULLIF(anchor, ''))
			FROM index_entry
				LEFT JOIN index_link ON index_link.no = index_entry.no
				LEFT JOIN page ON page.no = index_link.page_no
			WHERE index_entry.book_id = $this->id
				AND index_link.book_id = $this->id 
				AND page.book_id = $this->id 
				" . ($query ? "AND LOCATE('" . mysql_escape_string($query) . "', term)" : "")  . "
			ORDER BY index_entry.no
		");
		$entries = array();
		while(list($term, $link) = mysql_fetch_row($result))
			$entries[] = array($term, $link);
		return $entries;
	}

	function search_lexeme($lexeme)
	{
		$result = mysql_query("
			SELECT path, title 
			FROM lexeme
				LEFT JOIN lexeme_link ON lexeme_link.no = lexeme.no 
				LEFT JOIN page ON page.no = lexeme_link.page_no 
			WHERE lexeme='" . mysql_escape_string($lexeme) . "'
				AND lexeme.book_id = $this->id 
				AND lexeme_link.book_id = $this->id 
				AND page.book_id = $this->id
			ORDER BY count DESC
		");
		$entries = array();
		while(list($path, $title) = mysql_fetch_row($result))
			$entries[] = array($title, $path);
		return new Search_Result($entries);
	}

	function metadata($name = NULL)
	{
		if(isset($name))
		{
			$result = mysql_query("
				SELECT value 
				FROM metadata 
				WHERE book_id = $this->id
			");
			if(mysql_num_rows($result))
			{
				list($value) = mysql_fetch_row($result);
				return $value;
			}
			else
				return NULL;
		}
		else
		{
			$result = mysql_query("
				SELECT name, value 
				FROM metadata 
				WHERE book_id = $this->id
			");
			$entries = array();
			while(list($name, $value) = mysql_fetch_row($result))
				$entries[$name] = $value;
			return $entries;
		}
	}

	function page($path)
	{
		$result = mysql_query("
			SELECT compressed, content 
			FROM page 
			WHERE book_id = $this->id AND path = '" . mysql_escape_string($path) . "'
		");
		if(mysql_num_rows($result))
		{
			list($compressed, $content) = mysql_fetch_row($result);
			return array($compressed, $content);
		}
		else
			return NULL;
	}
	
	function delete()
	{
		mysql_query("DELETE FROM book WHERE id = $this->id");
		mysql_query("DELETE FROM book_alias WHERE book_id = $this->id");
		mysql_query("DELETE FROM metadata WHERE book_id = $this->id");
		mysql_query("DELETE FROM toc_entry WHERE book_id = $this->id");
		mysql_query("DELETE FROM index_entry WHERE book_id = $this->id");
		mysql_query("DELETE FROM index_link WHERE book_id = $this->id");
		mysql_query("DELETE FROM page WHERE book_id = $this->id");
		mysql_query("DELETE FROM lexeme WHERE book_id = $this->id");
		mysql_query("DELETE FROM lexeme_link WHERE book_id = $this->id");
	}

	function index_fulltext()
	{
		$index = new Book_Fulltext_Index($this->id);

		$result = mysql_query("
			SELECT no, path, compressed, content
			FROM page 
			WHERE book_id = $this->id
		");
		while(list($page_no, $path, $compressed, $content) = mysql_fetch_row($result))
		{
			if($compressed)
				$content = gzinflate(substr($content, 10, -4));
			$index->set_page_no($page_no);
			$indexer = $index->index_page($path, $content);
		}

		$index->cleanup();
	}
}

?>
