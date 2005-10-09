<?php

require_once 'inc/mysql.inc.php';
require_once 'lib/book_fulltext_index.lib.php';
require_once 'lib/fulltext_search.lib.php';
require_once 'lib/util.lib.php';

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
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
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
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		list($link) = mysql_fetch_row($result);
		return $link;
	}

	function get_toc_entries($parent_number = 0)
	{
		$entries = array();
		$result = mysql_query("
			SELECT 
				toc_entry.no, 
				toc_entry.title, 
				CONCAT_WS('#', page.path, NULLIF(toc_entry.anchor, '')) AS link,
				COUNT(toc_child_entry.no) AS nchildren
			FROM toc_entry
		 		LEFT JOIN page 
		 		ON page.book_id = $this->id
		 			AND page.no = toc_entry.page_no
		 		LEFT JOIN toc_entry AS toc_child_entry
		 		ON toc_child_entry.book_id = $this->id
		 			AND toc_child_entry.parent_no = toc_entry.no
			WHERE toc_entry.book_id = $this->id
				AND toc_entry.parent_no = $parent_number
			GROUP BY toc_entry.no
			ORDER BY toc_entry.no
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		while(list($number, $title, $link, $nchildren) = mysql_fetch_row($result))
			$entries[$number] = array($title, $link, $nchildren);
		return $entries;
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
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
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
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
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
			") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
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
			") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
			$entries = array();
			while(list($name, $value) = mysql_fetch_row($result))
				$entries[$name] = $value;
			return $entries;
		}
	}

	function set_metadata($name, $value)
	{
		mysql_query(
			'REPLACE ' .
			'INTO metadata ' . 
			'(book_id, name, value) ' . 
			'VALUES (' . 
				$this->id . ', ' .
				'"' . mysql_escape_string($name) . '", ' .
				'"' . mysql_escape_string($value) . '")' 
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
	}

	function page($path, $allow_compressed = FALSE)
	{
		$result = mysql_query("
			SELECT compressed, content 
			FROM page 
			WHERE book_id = $this->id AND path = '" . mysql_escape_string($path) . "'
		");
		if(mysql_num_rows($result))
			list($compressed, $content) = mysql_fetch_row($result);
		else
			return NULL;
		
		if($allow_compressed)
			return array($compressed, $content);
		else
			if($compressed)
				return gzdecode($content);
			else
				return $content;
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
		$index->index();
	}
}

?>
