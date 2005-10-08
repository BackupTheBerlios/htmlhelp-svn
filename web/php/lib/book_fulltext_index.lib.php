<?php

require_once 'inc/mysql.inc.php';
require_once 'lib/fulltext_index.lib.php';

class Book_Fulltext_Index extends Fulltext_Index
{
	var $book_id;
	
	// lexemes of the current page
	var $page_no;
	var $page_lexemes;

	// whole index
	var $lexemes;
	
	function Book_Fulltext_Index($book_id)
	{
		global $internal_encoding;
		$this->encoding = $internal_encoding;
		
		$this->book_id = $book_id;
		
		$this->lexemes = array();
		
		$this->setup();
	}
	
	function setup()
	{
		mysql_query("DELETE FROM lexeme_link WHERE book_id = $this->book_id");
		mysql_query("DELETE FROM lexeme WHERE book_id = $this->book_id");
		mysql_query("UPDATE page SET title='' WHERE book_id = $this->book_id");
	}
	
	function set_page_no($page_no)
	{
		$this->page_no = $page_no;
	}

	function start_page()
	{
		assert(isset($this->page_no));

		$this->page_lexemes = array();
	}

	function set_title($title) 
	{
		mysql_query("
			UPDATE page
			SET title='" . mysql_escape_string($title) . "'
			WHERE book_id = $this->book_id 
				AND no = $this->page_no
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
	}

	function add_lexemes(&$lexemes)
	{
		foreach($lexemes as $lexeme)
			$this->page_lexemes[$lexeme] += 1;
	}

	function finish_page()
	{
		foreach($this->page_lexemes as $lexeme => $count)
			$this->lexemes[$lexeme][$this->page_no] = $count;

		unset($this->page_no);
		unset($this->page_lexemes);
	}

	function cleanup()
	{
		if(!count($this->lexemes))
			return;
		
		$lexeme_no = 0;
		$values = array();
		foreach($this->lexemes as $lexeme => $pages)
		{
			$values[] = '(' . $this->book_id . ',"' . mysql_escape_string($lexeme) . '",' . $lexeme_no . ')';
			$lexeme_no += 1;
		}
		mysql_query(
			"INSERT " .
			"INTO lexeme (book_id, lexeme, no) " .
			"VALUES " . implode(',', $values)
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
		
		// TODO: store lexeme positions instead of lexeme counts
		$lexeme_no = 0;
		$values = array();
		foreach($this->lexemes as $lexeme => $pages)
		{
			foreach($pages as $page_no => $count)
				$values[] = '(' . $this->book_id . ',' . $lexeme_no . ',' . $page_no . ',' . $count . ')';
			$lexeme_no += 1;
		}
		mysql_query(
			"INSERT " .
			"INTO lexeme_link (book_id, no, page_no, count) " .
			"VALUES " . implode(',', $values)
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
	}
}

?>
