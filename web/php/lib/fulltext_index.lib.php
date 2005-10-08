<?php

require_once 'lib/mimetypes.lib.php';
require_once 'lib/fulltext_indexer.lib.php';

// Index interface
class Fulltext_Index
{
	// Internal encoding
	var $encoding = 'UTF-8';

	// Set page title (should be overriden by derived classes)
	function set_title(&$title) {}

	// Add lexemes in order (should be overriden by derived classes)
	function add_lexemes(&$lexemes) {}

	// Begin a new page
	function new_page() {}

	// Finished with the page
	function finish_page() {}

	// Fulltext_Indexer Factory Method
	function indexer_factory($path)
	{
		$content_type = guess_type($path);
		if($content_type == "text/plain")
			return new Fulltext_TextIndexer($this);
		elseif($content_type == "text/html")
			return new Fulltext_HtmlIndexer($this);
		return NULL;
	}

	// Index a page
	function index_page($path, &$content)
	{
		$indexer = & $this->indexer_factory($path);
		$this->start_page();
		if(!is_null($indexer))
			$indexer->feed($content);
		$this->finish_page();
	}
}

// Simple in-memory index
class Fulltext_SimpleIndex extends Fulltext_Index
{
	var $titles;
	var $lexemes;

	function Fulltext_SimpleIndex()
	{
		$this->titles = array();
		$this->lexemes = array();
	}

	function set_title($title) 
	{
		$this->titles[$this->page] = $title;
	}

	function add_lexemes(&$lexemes)
	{
		foreach($lexemes as $lexeme)
		{
			if(!isset($this->lexemes[$lexeme]))
				$this->lexemes[$lexeme] = array();
			$this->lexemes[$lexeme][$this->page] += 1;
		}
	}
}

?>
