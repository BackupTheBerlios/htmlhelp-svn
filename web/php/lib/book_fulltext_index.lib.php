<?php

require_once 'inc/mysql.inc.php';
require_once 'lib/mysql_util.lib.php';
require_once 'lib/fulltext_index.lib.php';
require_once 'lib/fulltext_indexer.lib.php';

class Book_Fulltext_Index extends Fulltext_Index
{
	var $book_id;
	
	// current page
	var $page_no;

	// whole index
	var $lexemes;
	
	function Book_Fulltext_Index($book_id)
	{
		global $internal_encoding;		
		parent::Fulltext_Index($internal_encoding);
		
		$this->book_id = $book_id;
	}
	
	function enumerate_items()
	{
		$result = mysql_query("
			SELECT no
			FROM page 
			WHERE book_id = $this->book_id
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
		return mysql_fetch_fields($result);
	}
	
	function index_item($page_no)
	{
		$result = mysql_query("
			SELECT path, compressed, content
			FROM page 
			WHERE book_id = $this->book_id
			AND no = $page_no
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
		list($path, $compressed, $content) = mysql_fetch_row($result);
		
		if($compressed)
			$content = gzdecode($content);
		
		if(($indexer = & Fulltext_Indexer_factory($path, $this)) !== NULL)
			$indexer->feed($content);
	}
	
	function handle_start()
	{
		parent::handle_start();
		
		mysql_query("DELETE FROM lexeme_link WHERE book_id = $this->book_id");
		mysql_query("DELETE FROM lexeme WHERE book_id = $this->book_id");
		mysql_query("UPDATE page SET title='' WHERE book_id = $this->book_id");

		$this->lexemes = array();
	}
	
	function handle_item_start($page_no)
	{
		$this->page_no = $page_no;
	}
	
	function handle_item_title($title) 
	{
		mysql_query("
			UPDATE page
			SET title='" . mysql_escape_string($title) . "'
			WHERE book_id = $this->book_id 
				AND no = $this->page_no
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
	}
	
	function handle_item_lexemes(&$lexemes)
	{
		foreach($lexemes as $lexeme)
			$this->lexemes[$lexeme][$this->page_no] += 1;
	}

	function handle_item_end()
	{
		$this->page_no = NULL;
	}

	function handle_end()
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
		
		$this->lexemes = NULL;
	}
	
	function search_lexeme($lexeme)
	{
		$result = mysql_query("
			SELECT path, title 
			FROM lexeme
				LEFT JOIN lexeme_link ON lexeme_link.no = lexeme.no 
				LEFT JOIN page ON page.no = lexeme_link.page_no 
			WHERE lexeme='" . mysql_escape_string($lexeme) . "'
				AND lexeme.book_id = $this->book_id 
				AND lexeme_link.book_id = $this->book_id 
				AND page.book_id = $this->book_id
			ORDER BY count DESC
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
		$entries = array();
		while(list($path, $title) = mysql_fetch_row($result))
			$entries[] = array($title, $path);
		return new Fulltext_SearchResult($entries);
	}
}

?>
