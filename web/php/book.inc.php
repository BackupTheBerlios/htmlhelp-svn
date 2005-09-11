<?php

	require_once 'mysql.inc.php';
	require_once 'search.inc.php';
	require_once 'fulltext.inc.php';

	function book_catalog()
	{
		# FIXME: deal with books with multiple versions
		$result = mysql_query('SELECT `metadata`.`value`, `title` FROM `book`, `metadata` WHERE `book`.`id`=`metadata`.`book_id` AND `metadata`.`name`=\'name\' ORDER BY `title`');
		$entries = array();
		while(list($book_alias, $book_title) = mysql_fetch_row($result))
			$entries[$book_alias] = $book_title;
		return $entries;
	}

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

		var $stop_words;

		function Book_Fulltext_Index($book_id)
		{
			$this->book_id = $book_id;
			$this->last_lexeme_no = 0;

			# NOTE: from http://en.wikipedia.org/wiki/Stop_words
			$this->stop_words = array();
			$handle = fopen("stop_words.txt", "rt");
			while(!feof($handle))
			{
				 $word = trim(fgets($handle, 4096));
				 $this->stop_words[$word] = true;
			}
			fclose($handle);
			
			mysql_query('DELETE FROM `lexeme_link` WHERE `book_id`=' . $this->book_id);
			mysql_query('DELETE FROM `lexeme` WHERE `book_id`=' . $this->book_id);
			mysql_query('UPDATE `page` SET `title`="" WHERE `book_id`=' . $this->book_id);
		}

		function cleanup()
		{
			// drop lexemes which start with a digit and appear only once
			mysql_query(<<<EOSQL
				CREATE TEMPORARY TABLE delete_lexeme
					SELECT string
						FROM lexeme
							LEFT JOIN lexeme_link ON lexeme.no = lexeme_link.lexeme_no
						WHERE lexeme.book_id = $this->book_id
							AND string >= '0' AND string < 'A'
							AND lexeme_link.book_id = $this->book_id
						GROUP BY lexeme.book_id, string
						HAVING SUM(count) = 1
EOSQL
			);
			mysql_query(<<<EOSQL
				DELETE lexeme, lexeme_link
					FROM lexeme, delete_lexeme
						LEFT JOIN lexeme_link ON lexeme.no = lexeme_link.lexeme_no
					WHERE lexeme.book_id = $this->book_id
						AND lexeme.string IN (delete_lexeme.string)
						AND lexeme_link.book_id = $this->book_id
EOSQL
			);
			mysql_query(<<<EOSQL
				DROP TEMPORARY TABLE delete_lexeme 
EOSQL
			);
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

			mysql_query('
				UPDATE `page`
				SET `title`="' . mysql_escape_string($title) . '"
				WHERE `book_id`=' . $this->book_id . ' AND `no`=' . $this->page_no . '
			');
		}

		function add_lexemes($lexemes)
		{
			// TODO: add stop words lists somewhere to reduce table size
			// TODO: store lexeme positions instead of lexeme counts
				
			foreach($lexemes as $lexeme)
			{
				if(!is_valid_utf8($lexeme))
					continue;

				if($this->stop_words[mb_strtolower($lexeme, 'utf-8')])
					continue;

				$this->page_lexemes[$lexeme] += 1;
			}
		}

		function finish_page()
		{
			foreach($this->page_lexemes as $lexeme => $count)
			{
				// get this lexeme number
				$result = mysql_query(
					"SELECT `no`
				 	FROM `lexeme` 
					WHERE `book_id`=$this->book_id AND `string`='" . mysql_escape_string($lexeme) . "'"
				) or print(__FILE__ . ':' . __LINE__ . ': ' .  htmlspecialchars($lexeme, ENT_NOQUOTES, 'utf-8') . ':' . ord($lexeme) . ':' . mysql_error() . "\n");
				if(mysql_num_rows($result))
					list($lexeme_no) = mysql_fetch_row($result);
				else
				{
					$this->last_lexeme_no += 1;
					$lexeme_no = $this->last_lexeme_no;
					mysql_query(
						"INSERT INTO `lexeme` 
						(book_id, no, string) VALUES 
						($this->book_id, $lexeme_no, '" . mysql_escape_string($lexeme) . "')"
					);
				}

				mysql_query(
					"INSERT INTO `lexeme_link` 
					(book_id, lexeme_no, page_no, count) VALUES 
					($this->book_id, $lexeme_no, $this->page_no, $count)"
				);
			}
			
			unset($this->page_lexemes);
			unset($this->page_no);
		}
	}

	class Book extends Searchable
	{
		var $alias;
		var $id;

		function Book($alias)
		{
			$result = mysql_query('
				SELECT `book_id` 
				FROM `metadata` 
				WHERE `name`=\'name\' AND `value`=\'' . mysql_escape_string($alias) . '\'
			');
			list($id) = mysql_fetch_row($result);
			
			$this->alias = $alias;
			$this->id = $id;
		}

		function title()
		{
			$result = mysql_query('SELECT `title` FROM `book` WHERE `id`=' . $this->id);
			list($title) = mysql_fetch_row($result);
			return $title;
		}

		function default_link()
		{
			$result = mysql_query('
				SELECT CONCAT_WS("#", `path`, NULLIF(`anchor`, "")) 
				FROM `book`, `page` 
				WHERE `id`=' . $this->id . ' AND `book_id`=' . $this->id . ' AND `page`.`no`=`page_no`
			');
			list($link) = mysql_fetch_row($result);
			return $link;
		}

		function toc($parent_number = 0, $depth = -1)
		{
			// FIXME: do this non-recursively
			$entries = array();
			if($depth)
			{
				$result = mysql_query('
					SELECT `toc_entry`.`no`, `toc_entry`.`title`, CONCAT_WS("#", `path`, NULLIF(`anchor`, "")) 
					FROM `toc_entry`, `page` 
					WHERE `toc_entry`.`book_id`=' . $this->id . ' AND `parent_no`=' . $parent_number . ' AND `page`.`book_id`=' . $this->id . ' AND `page`.`no` = `page_no` 
					ORDER BY `toc_entry`.`no`
				');
				while(list($number, $title, $link) = mysql_fetch_row($result))
					$entries[$number] = array($title, $link, $this->toc($number, $depth - 1));
			}
			return $entries;
		}

		function toc_entry($number)
		{
			$result = mysql_query('
				SELECT `parent_no`, `toc_entry`.`title`, CONCAT_WS("#", `path`, NULLIF(`anchor`, "")) 
				FROM `toc_entry`, `page` 
				WHERE `toc_entry`.`book_id`=' . $this->id . ' AND `toc_entry`.`no`=' . $number . ' AND `page`.`book_id`=' . $this->id . ' AND `page`.`no` = `page_no`
			');
			list($parent_number, $title, $link) = mysql_fetch_row($result);
			return array($parent_number, $title, $link);
		}

		function index($query = '')
		{
			$result = mysql_query('
				SELECT `term`, CONCAT_WS("#", `path`, NULLIF(`anchor`, ""))
				FROM `index_entry`, `index_link`, `page` 
				WHERE `index_entry`.`book_id`=' . $this->id . ' AND `index_link`.`book_id`=' . $this->id . ' AND `page`.`book_id`=' . $this->id . ' AND `index_link`.`no`=`index_entry`.`no` AND `page`.`no`=`page_no`' . ($query ? ' AND LOCATE(\'' . mysql_escape_string($query) . '\', `term`)' : '') . ' 
				ORDER BY `index_entry`.`no`
			');
			$entries = array();
			while(list($term, $link) = mysql_fetch_row($result))
				$entries[] = array($term, $link);
			return $entries;
		}

		function search_lexeme($lexeme)
		{
			$result = mysql_query('
				SELECT `path`, `title` 
				FROM `lexeme`, `lexeme_link`, `page` 
				WHERE `lexeme`.`book_id`=' . $this->id . ' AND `lexeme`.`string`=\'' . mysql_escape_string($lexeme) . '\' AND `lexeme_link`.`book_id`=' . $this->id . ' AND `lexeme_no`=`lexeme`.`no` AND `page`.`book_id`=' . $this->id . ' AND `page`.`no` = `page_no`
				ORDER BY `count` DESC
			');
			$entries = array();
			while(list($path, $title) = mysql_fetch_row($result))
				$entries[] = array($title, $path);
			return new Search_Result($entries);
		}

		function metadata()
		{
			$result = mysql_query('
				SELECT `name`, `value` 
				FROM `metadata` 
				WHERE book_id=' . $this->id .'
			');
			$entries = array();
			while(list($name, $value) = mysql_fetch_row($result))
				$entries[$name] = $value;
			return $entries;
		}

		function page($path)
		{
			$result = mysql_query('
				SELECT `compressed`, `content` 
				FROM `page` 
				WHERE `book_id`=' . $this->id . ' AND `path`=\'' . mysql_escape_string($path) . '\'
			');
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
			mysql_query('DELETE FROM `book` WHERE `id`=' . $this->id);
			mysql_query('DELETE FROM `metadata` WHERE `book_id`=' . $this->id);
			mysql_query('DELETE FROM `toc_entry` WHERE `book_id`=' . $this->id);
			mysql_query('DELETE FROM `index_entry` WHERE `book_id`=' . $this->id);
			mysql_query('DELETE FROM `index_link` WHERE `book_id`=' . $this->id);
			mysql_query('DELETE FROM `page` WHERE `book_id`=' . $this->id);
			mysql_query('DELETE FROM `lexeme` WHERE `book_id`=' . $this->id);
			mysql_query('DELETE FROM `lexeme_link` WHERE `book_id`=' . $this->id);
		}

		function index_fulltext()
		{
			$index = new Book_Fulltext_Index($this->id);

			$result = mysql_query('
				SELECT `no`, `path`, `compressed`, `content` 
				FROM `page` 
				WHERE book_id=' . $this->id .'
			');
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
