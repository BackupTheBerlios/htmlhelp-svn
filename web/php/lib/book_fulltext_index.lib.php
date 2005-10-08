<?php

require_once 'inc/mysql.inc.php';
require_once 'lib/fulltext_index.lib.php';

class Book_Fulltext_Index extends Fulltext_Index
{
	var $book_id;
	var $page_no;

	var $last_lexeme_no;

	// The lexemes of the current page
	var $page_lexemes;

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
		$this->last_lexeme_no = 0;

		mysql_query("DELETE FROM lexeme_link WHERE book_id = $this->book_id");
		mysql_query("DELETE FROM lexeme WHERE book_id = $this->book_id");
		mysql_query("UPDATE page SET title='' WHERE book_id = $this->book_id");
		
		// eliminate stop words from index
		$result = mysql_query(
			"SELECT lexeme " .
			"FROM stop_word"
		) or print(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		$this->stop_words = array();
		while(list($stop_word) = mysql_fetch_row($result))
			$this->stop_words[$stop_word] = TRUE;
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
		");
	}

	function add_lexemes(&$lexemes)
	{
		foreach($lexemes as $lexeme)
		{
			if($this->stop_words[$lexeme] !== TRUE)
				$this->page_lexemes[$lexeme] += 1;
		}
	}

	function finish_page()
	{
		foreach($this->page_lexemes as $lexeme => $count)
		{
			/*if(!isset($this->lexemes[$lexeme]))
				$this->lexemes[$lexeme] = array();*/
			$this->lexemes[$lexeme][$this->page_no] = $count;
		}

		unset($this->page_no);
		unset($this->page_lexemes);
	}

	function cleanup()
	{
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
		
		// drop lexemes which start with a digit and appear only once
		mysql_query("
			CREATE TEMPORARY TABLE delete_lexeme
			SELECT lexeme 
			FROM lexeme
				LEFT JOIN lexeme_link ON lexeme.no = lexeme_link.no
			WHERE lexeme.book_id = $this->book_id
				AND lexeme >= '0' AND lexeme < 'A'
				AND lexeme_link.book_id = $this->book_id
			GROUP BY lexeme.book_id, lexeme
			HAVING SUM(count) = 1
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		mysql_query("
			DELETE lexeme, lexeme_link
			FROM delete_lexeme
				LEFT JOIN lexeme ON lexeme.book_id = $this->book_id AND lexeme.lexeme = delete_lexeme.lexeme
				LEFT JOIN lexeme_link ON lexeme_link.book_id = $this->book_id AND lexeme.no = lexeme_link.no
		") or print(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		mysql_query("
			DROP /*!40000 TEMPORARY */  TABLE delete_lexeme 
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
	}
}

?>
