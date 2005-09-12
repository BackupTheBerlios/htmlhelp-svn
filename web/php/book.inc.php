<?php

	require_once 'mysql.inc.php';
	require_once 'mysql_util.inc.php';
	require_once 'search.inc.php';
	require_once 'fulltext.inc.php';

	class Book_Catalog
	{
		// Import a book into the bookshelf
		function import_book($filename)
		{
			mysql_import_dump($filename);
			
			$this->update_aliases();
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
					SELECT value AS alias, book_id
					FROM metadata
					WHERE name = 'name'
					GROUP BY book_id
				UNION
					SELECT GROUP_CONCAT(value ORDER BY name SEPARATOR '_') AS alias, book_id
					FROM metadata
					WHERE name in ('name', 'version')
					GROUP BY book_id
					HAVING COUNT(DISTINCT name) = 2
				UNION
					SELECT GROUP_CONCAT(value ORDER BY name SEPARATOR '_') AS alias, book_id
					FROM metadata
					WHERE name in ('name', 'version', 'language')
					GROUP BY book_id
					HAVING COUNT(DISTINCT name) = 3
EOSQL
			);
		}
		
		function enumerate_ids()
		{
			$result = mysql_query("
				SELECT id, title 
				FROM book 
				ORDER BY title
			");
			$entries = array();
			while(list($book_id, $book_title) = mysql_fetch_row($result))
				$entries[$book_id] = $book_title;
			return $entries;
		}
		
		function enumerate_aliases()
		{
			# FIXME: deal with books with multiple versions
			$result = mysql_query("
				SELECT metadata.value, title 
				FROM book 
					LEFT JOIN metadata ON metadata.book_id = book.id
				WHERE metadata.name = 'name' 
				ORDER BY title
			");
			$entries = array();
			while(list($book_alias, $book_title) = mysql_fetch_row($result))
				$entries[$book_alias] = $book_title;
			return $entries;
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

		function Book_Fulltext_Index($book_id)
		{
			$this->book_id = $book_id;
			$this->last_lexeme_no = 0;

			// import the stop words
			$result = mysql_query("SELECT COUNT(*) FROM stop_word");
			list($count) = mysql_fetch_row($result);
			if(!$count)
			{				
				# NOTE: from http://en.wikipedia.org/wiki/Stop_words
				$handle = fopen('misc/stop_words.txt', 'rt');
				while(!feof($handle))
				{
					$word = trim(fgets($handle, 4096));
					// TODO: use a single insert
					mysql_query("
						INSERT
						INTO stop_word
						VALUES ('" . mysql_escape_string($word) . "')
					") or die(mysql_error());
				}
				fclose($handle);
			}
			
			mysql_query("DELETE FROM lexeme_link WHERE book_id = $this->book_id");
			mysql_query("DELETE FROM lexeme WHERE book_id = $this->book_id");
			mysql_query("UPDATE page SET title='' WHERE book_id = $this->book_id");
		}

		function cleanup()
		{
			// drop lexemes which start with a digit and appear only once
			mysql_query(<<<EOSQL
				CREATE TEMPORARY TABLE delete_lexeme
					SELECT lexeme 
						FRO lexeme
							LEFT JOIN lexeme_link ON lexeme.no = lexeme_link.lexeme_no
						WHERE lexeme.book_id = $this->book_id
							AND lexeme >= '0' AND lexeme < 'A'
							AND lexeme_link.book_id = $this->book_id
						GROUP BY lexeme.book_id, lexeme
						HAVING SUM(count) = 1
EOSQL
			);
			mysql_query(<<<EOSQL
				DELETE lexeme, lexeme_link
					FROM lexeme, delete_lexeme
						LEFT JOIN lexeme_link ON lexeme.no = lexeme_link.no
					WHERE lexeme.book_id = $this->book_id
						AND lexeme.lexeme IN (delete_lexeme.lexeme)
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

			mysql_query("
				UPDATE page
				SET title='" . mysql_escape_string($title) . "'
				WHERE book_id = $this->book_id 
					AND no = $this->page_no
			");
		}

		function add_lexemes($lexemes)
		{
			// TODO: store lexeme positions instead of lexeme counts
				
			foreach($lexemes as $lexeme)
			{
				$result = mysql_query(
					"SELECT COUNT(*) " .
					"FROM stop_word " .
					"WHERE lexeme = '" . mysql_escape_string($lexeme) . "'");
				list($count) = mysql_fetch_row($result);
				if($count)
					continue;

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
				") or print(__FILE__ . ':' . __LINE__ . ': ' .  htmlspecialchars($lexeme, ENT_NOQUOTES, 'utf-8') . ':' . ord($lexeme) . ':' . mysql_error() . "\n");
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

	class Book extends Searchable
	{
		var $id;

		function Book($id)
		{

			$this->id = $id;
		}
		
		function alias()
		{
			$result = mysql_query("
				SELECT alias
				FROM book_alias
				WHERE book_id = $this->id
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
			mysql_query("DELETE FROM book_tag WHERE book_id = $this->id");
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
