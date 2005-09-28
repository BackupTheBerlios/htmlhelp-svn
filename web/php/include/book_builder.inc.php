<?php

require_once 'include/book_book.inc.php';

class BookBuilder extends Book
{
	var $commited;
	
	var $last_page_no;
	var $last_toc_entry_no;
	var $last_index_entry_no;
	
	// Constructor
	function BookBuilder()
	{
		mysql_query("INSERT INTO book () values ()");
		$id = mysql_insert_id();

		$this->commited = FALSE;
		register_shutdown_function(array(&$this, "_BookBuilder"));
		
		$this->Book($id);
		
		$this->last_page_no = 0;	
		$this->last_toc_entry_no = 0;	
		$this->last_index_entry_no = 0;	
	}
	
	// Destructor
	function _BookBuilder()
	{
		if(!$this-commited)
		{
			// revert all changes
			$this->delete();
		}
	}

	function set_title($title)
	{
		mysql_query(
			'UPDATE book ' .
			'SET title = "' . mysql_escape_string($title) . '" ' .
			'WHERE id = ' . $this->id 
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
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

	// Add a book page.
	//		
	// All pages should be added before any link can be added, as links 
	// are internally stored as a page reference plus an anchor.
	function add_page($path, $content)
	{
		$this->last_page_no += 1;
		mysql_query(
			'INSERT ' .
			'INTO page ' . 
			'(book_id, no, path, content) ' . 
			'VALUES (' . 
				$this->id . ', ' .
				$this->last_page_no . ', ' .
				'"' . mysql_escape_string($path) . '", ' .
				'"' . mysql_escape_string($content) . '")' 
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
	}
	
	// Split a link into a page reference plus anchor pair
	function _split_link($link)
	{
		$pos = strpos($link, '#');
		if($pos === FALSE)
		{
			$path = $link;
			$anchor = '';
		}
		else
		{
			$path = substr($link, 0, $pos);
			$anchor = substr($link, $pos + 1);
		}
		
		$result = mysql_query(
			'SELECT no ' .
			'FROM page ' .
			'WHERE book_id = ' . $this->id . ' ' .
				'AND path = "' . mysql_escape_string($path) . '"'
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		if(mysql_num_rows($result))
			list($page_no) = mysql_fetch_row($result);
		else
			$page_no = 0;
		
		return array($page_no, $anchor);
	}
	
	function set_default_link($link)
	{
		list($page_no, $anchor) = $this->_split_link($link);

		mysql_query(
			'UPDATE book ' .
			'SET page_no = ' . $page_no . ', ' .
				'anchor = "' . mysql_escape_string($anchor) . '" ' .
			'WHERE id = ' . $this->id 
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
	}
	
	function add_toc_entry($title, $link, $parent_no = 0)
	{
		list($page_no, $anchor) = $this->_split_link($link);

		$this->last_toc_entry_no += 1;
		mysql_query(
			'INSERT ' .
			'INTO toc_entry ' .
			'(book_id, parent_no, no, title, page_no, anchor)' .
			'VALUES (' . 
				$this->id . ', ' .
				$parent_no . ', ' .
				$this->last_toc_entry_no . ', ' .
				'"' . mysql_escape_string($title) . '", ' .
				$page_no . ', ' .
				'"' . mysql_escape_string($anchor) . '")'
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		
		return $this->last_toc_entry_no;
	}
	
	function add_index_entry($term, &$links)
	{
		$result = mysql_query(
			'SELECT no ' .
			'FROM index_entry ' .
			'WHERE book_id = ' . $this->id . ' ' .
				'AND term = "' . mysql_escape_string($term) . '"'
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		if(mysql_num_rows($result))
			list($index_entry_no) = mysql_fetch_row($result);
		else
		{
			$this->last_index_entry_no += 1;
			mysql_query(
				'INSERT ' .
				'INTO index_entry ' .
				'(book_id, no, term)' .
				'VALUES (' . 
					$this->id . ', ' .
					$this->last_index_entry_no . ', ' .
					'"' . mysql_escape_string($term) . '")' 
			) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
			$index_entry_no = $this->last_index_entry_no;
		}

		$values = array();
		foreach($links as $link)
		{
			// FIXME: do this with a single query
			list($page_no, $anchor) = $this->_split_link($link);
			
			$values[] = '(' . 
				$this-> id . ', ' . 
				$index_entry_no . ', ' . 
				$page_no . ', ' .
				'"' . mysql_escape_string($anchor) . '"' .
			')';
		}
	
		mysql_query(
			'REPLACE ' .
			'INTO index_link ' .
			'(book_id, no, page_no, anchor) ' .
			'VALUES ' . implode(', ', $values) 
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
	}
	
	function commit()
	{
		$this->committed = TRUE;
		
		// TODO: re-indexate book here
	}
}

?>
