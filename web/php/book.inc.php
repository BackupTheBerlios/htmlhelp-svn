<?php

	require_once 'mysql.inc.php';
	require_once 'search.inc.php';

	function book_catalog()
	{
		$result = mysql_query('SELECT `alias`, `title` FROM `book` ORDER BY `title`');
		$entries = array();
		while(list($book_alias, $book_title) = mysql_fetch_row($result))
			$entries[$book_alias] = $book_title;
		return $entries;
	}

	class Book extends Searchable
	{
		var $alias;
		var $id;

		function Book($alias)
		{
			$result = mysql_query('SELECT `id` FROM `book` WHERE `alias`=\'' . mysql_escape_string($alias) . '\'');
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
			return new SearchResult($entries);
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
	}

?>
