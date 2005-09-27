<?php

require_once 'include/mysql.inc.php';
require_once 'include/fulltext_index.inc.php';

function is_valid_utf8($string)
{
	// taken from http://pt.php.net/manual/en/function.utf8-decode.php
	if(preg_match('/^([\x00-\x7f]|[\xc0-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xf7][\x80-\xbf]{3}|[\xf8-\xfb][\x80-\xbf]{4}|[\xfc-\xfd][\x80-\xbf]{5})*$/', $string))
		return TRUE;
	else
		return FALSE;
}

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
			FROM lexeme, delete_lexeme
				LEFT JOIN lexeme_link ON lexeme.no = lexeme_link.no
			WHERE lexeme.book_id = $this->book_id
				AND lexeme.lexeme IN (delete_lexeme.lexeme)
				AND lexeme_link.book_id = $this->book_id
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		mysql_query("
			DROP TEMPORARY TABLE delete_lexeme 
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
		
		// eliminate stop words from index
		$result = mysql_query("
			DELETE lexeme, lexeme_link
			FROM stop_word
				LEFT JOIN lexeme ON lexeme.lexeme = stop_word.lexeme
				LEFT JOIN lexeme_link ON lexeme_link.no = lexeme.no
			WHERE lexeme.book_id = $this->book_id
				AND lexeme_link.book_id = $this->book_id
		") or die(__FILE__ . ':' . __LINE__ . ':' . mysql_error());
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
		// TODO: store lexeme positions instead of lexeme counts
		foreach($lexemes as $lexeme)
		{
			$this->page_lexemes[$lexeme] += 1;
		}
	}

	function finish_page()
	{
		foreach($this->page_lexemes as $lexeme => $count)
		{
			// get this lexeme number
			$result = mysql_query("
				SELECT no
			 	FROM lexeme
				WHERE book_id=$this->book_id AND lexeme='" . mysql_escape_string($lexeme) . "'
			") or print(__FILE__ . ':' . __LINE__ . ':' . mysql_error() . "\n");
			if(mysql_num_rows($result))
				list($lexeme_no) = mysql_fetch_row($result);
			else
			{
				$this->last_lexeme_no += 1;
				$lexeme_no = $this->last_lexeme_no;
				mysql_query(
					"INSERT INTO lexeme 
					(book_id, no, lexeme) VALUES 
					($this->book_id, $lexeme_no, '" . mysql_escape_string($lexeme) . "')"
				);
			}

			mysql_query(
				"INSERT INTO lexeme_link 
				(book_id, no, page_no, count) VALUES 
				($this->book_id, $lexeme_no, $this->page_no, $count)"
			);
		}

		unset($this->page_lexemes);
		unset($this->page_no);
	}
}

?>
