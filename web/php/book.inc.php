<?php

	include 'mysql.inc.php';

	function make_link($path, $anchor)
	{
		if($anchor)
			return $path . '#' . $anchor;
		else
			return $path;
	}
	
	function book_catalog()
	{
		$result = mysql_query('SELECT `id`, `title` FROM `book` ORDER BY `title`');
		$entries = array();
		while(list($book_id, $title) = mysql_fetch_row($result))
			$entries[$book_id] = $title;
		return $entries;
	}
	
	class Book
	{
		var $id;

		function Book($id)
		{
			$this->id = intval($id);
		}

		function title()
		{
			$result = mysql_query('SELECT `title` FROM `book` WHERE `id`=' . $this->id);
			list($title) = mysql_fetch_row($result);
			return $title;
		}

		function default_link()
		{
			$result = mysql_query('SELECT `default_path`, `default_anchor` FROM `book` WHERE `id`=' . $this->id);
			list($default_path, $default_anchor) = mysql_fetch_row($result);
			return make_link($default_path, $default_anchor);
		}

		function toc($parent_number = 0, $depth = -1)
		{
			// FIXME: do this non-recursively
			$entries = array();
			if($depth)
			{
				$result = mysql_query('SELECT `no`, `title`, `path`, `anchor` FROM `toc_entry` WHERE `book_id`=' . $this->id . ' AND `parent_no`=' . $parent_number . ' ORDER BY `no`');
				while(list($number, $title, $path, $anchor) = mysql_fetch_row($result))
					$entries[$number] = array($title, make_link($path, $anchor), $this->toc($number, $depth - 1));
			}
			return $entries;
		}

		function toc_entry($number)
		{
			$result = mysql_query('SELECT `parent_no`, `title`, `path`, `anchor` FROM `toc_entry` WHERE `book_id`=' . $this->id . ' AND `no`=' . $number);
			list($parent_number, $title, $path, $anchor) = mysql_fetch_row($result);
			return array($parent_number, $title, make_link($path, $anchor));
		}

		function index($query = '')
		{
			$result = mysql_query('SELECT `term`, `path`, `anchor` FROM `index_entry`,`index_link` WHERE `index_entry`.`book_id`=' . $this->id . ' AND `index_link`.`book_id`=' . $this->id . ' AND `index_link`.`no`=`index_entry`.`no`' . ($query ? ' AND LOCATE(\'' . mysql_escape_string($query) . '\', `term`)' : '') . ' ORDER BY `index_entry`.`term`');
			$entries = array();
			while(list($term, $path, $anchor) = mysql_fetch_row($result))
				$entries[] = array($term, make_link($path, $anchor));
			return $entries;
		}

		function search($query)
		{
			$result = mysql_query('SELECT `path`, `title` FROM `page` WHERE book_id=' . $this->id . ' AND MATCH (`title`, `body`) AGAINST (\'' . mysql_escape_string($query) . '\'' . ($boolean_mode ? ' IN BOOLEAN MODE' : '') . ')');
			$entries = array();
			while(list($path, $title) = mysql_fetch_row($result))
				$entries[] = array($title, $path);
			return $entries;
		}

		function metadata()
		{
			$result = mysql_query('SELECT `name`, `value` FROM `metadata` WHERE book_id=' . $this->id);
			$entries = array();
			while(list($name, $value) = mysql_fetch_row($result))
				$entries[$name] = $value;
			return $entries;
		}

		function page($path)
		{
			$result = mysql_query('SELECT `compressed`, `content` FROM `page` WHERE `book_id`=' . $this->id . ' AND `path`=\'' . mysql_escape_string($path) . '\'');
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
			mysql_query('DELETE FROM `toc_entry` WHERE `book_id`=' . $this->id);
			mysql_query('DELETE FROM `index_entry` WHERE `book_id`=' . $this->id);
			mysql_query('DELETE FROM `index_link` WHERE `book_id`=' . $this->id);
			mysql_query('DELETE FROM `page` WHERE `book_id`=' . $this->id);
			mysql_query('DELETE FROM `metadata` WHERE `book_id`=' . $this->id);
		}
	}

?>
