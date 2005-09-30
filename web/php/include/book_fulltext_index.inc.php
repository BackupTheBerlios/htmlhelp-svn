<?php

require_once 'include/mysql.inc.php';
require_once 'include/fulltext_index.inc.php';
require_once 'include/util.inc.php';

class Book_Fulltext_Index extends Fulltext_Index
{
	var $book_id;
	var $page_no;

	var $last_lexeme_no;

	// The lexemes of the current page
	var $page_lexemes;

	function Book_Fulltext_Index($book_id)
	{
		$this->book_id = $book_id;
		$this->last_lexeme_no = 0;

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

		mysql_query("
			CREATE TEMPORARY TABLE temp_lexeme (
				lexeme varchar(31) NOT NULL
			) TYPE=HEAP"
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
	}

	function set_title($title) 
	{
		if(!is_valid_utf8($title))
		{
			echo "<p>book_id#$this->book_id:page_no#$this->page_no: invalid UTF-8 in lexeme \"" . htmlspecialchars($title, ENT_NOQUOTES, 'utf-8') . "\"</p>";
			return;
		}

		mysql_query("
			UPDATE page
			SET title='" . mysql_escape_string($title) . "'
			WHERE book_id = $this->book_id 
				AND no = $this->page_no
		");
	}

	function add_lexemes(&$lexemes)
	{
		if(!count($lexemes))
			return;
		
		$values = array();
		foreach($lexemes as $lexeme)
			$values[] = '("' . mysql_escape_string($lexeme) . '")';		
		mysql_query(
			"INSERT INTO temp_lexeme " .
			"(lexeme) " .
			"VALUES " . implode(',', $values)
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
	}

	function finish_page()
	{
		mysql_query(
			"ALTER " .
			"TABLE temp_lexeme " .
			"ADD INDEX lexeme (lexeme(7))"
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		
		// eliminate stop words from index
		mysql_query(
			"DELETE temp_lexeme " .
			"FROM stop_word " .
			"LEFT JOIN temp_lexeme USING(lexeme)"
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());

		mysql_query(
			"SELECT @lexeme_no := IFNULL(MAX(lexeme.no), 0) " .
			"FROM lexeme " .
			"WHERE book_id=$this->book_id" 
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");

		mysql_query(
			"INSERT " .
			"INTO lexeme (book_id, no, lexeme) " .
			"SELECT $this->book_id, @lexeme_no := (@lexeme_no + 1), temp_lexeme.lexeme " .
			"FROM temp_lexeme " .
			"LEFT JOIN lexeme ON lexeme.book_id = $this->book_id AND temp_lexeme.lexeme = lexeme.lexeme " .
			"WHERE lexeme.no IS NULL " .
			"GROUP BY temp_lexeme.lexeme"
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
		
		// TODO: store lexeme positions instead of lexeme counts
		mysql_query(
			"INSERT " .
			"INTO lexeme_link (book_id, no, page_no, count) " .
			"SELECT $this->book_id, no, $this->page_no, COUNT(*) " .
			"FROM temp_lexeme " .
			"LEFT JOIN lexeme ON temp_lexeme.lexeme = lexeme.lexeme " .
			"WHERE lexeme.book_id = $this->book_id " .
			"GROUP BY temp_lexeme.lexeme"
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");

		mysql_query(
			"DROP TEMPORARY TABLE temp_lexeme" 
		) or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());

		unset($this->page_no);
	}

	function cleanup()
	{
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
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		
		mysql_query("
			DROP TEMPORARY TABLE delete_lexeme 
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
	}
}

?>
