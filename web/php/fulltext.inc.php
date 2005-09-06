<?php

require_once 'mimetypes.inc.php';

class Fulltext_Index
{
	function store_title($page, $title) {}

	function store_lexemes($page, $lexemes) {}

	// Indexer Factory Method
	function indexer($path, $page)
	{
		$content_type = guess_type($path);
		if($content_type == "text/plain")
			return new Fulltext_TextIndexer($this, $page);
		elseif($content_type == "text/html")
			return new Fulltext_HtmlIndexer($this, $page);
		return NULL;
	}

	function index($path, $page, $content)
	{
		$indexer = $this->indexer($path, $page);
		if(!is_null($indexer))
			$indexer->feed($content);
	}
}

class Fulltext_SimpleIndex extends Fulltext_Index
{
	var $titles;
	var $lexemes;

	function Fulltext_SimpleIndex()
	{
		$this->titles = array();
		$this->lexemes = array();
	}

	function store_title($page, $title) 
	{
		$this->titles[$page] = $title;
	}

	function store_lexemes($page, $lexemes)
	{
		foreach($lexemes as $lexeme)
		{
			if(!isset($this->lexemes[$lexeme]))
				$this->lexemes[$lexeme] = array();
			$this->lexemes[$lexeme][$page] += 1;
		}
	}
}

class Fulltext_Indexer
{
	var $index;
	var $page;

	function Fulltext_Indexer($index, $page)
	{
		$this->index = $index;
		$this->page = $page;
	}

	function feed_title($title)
	{
		$this->index->store_title($this->page, $this->normalize($title));
	}

	function feed_body($body)
	{
		$this->index->store_lexemes($this->page, $this->tokenize($body));
	}

	function feed($content) {}

	// Normalize whitespace
	function normalize($str)
	{
		return implode(' ', preg_split('/\s/', $str, -1, PREG_SPLIT_NO_EMPTY));
	}

	function tokenize($str)
	{
		// TODO: Improve this like http://svn.apache.org/repos/asf/lucene/java/trunk/src/java/org/apache/lucene/analysis/standard/StandardTokenizer.jj
		// TODO: This may be better in a separate class
		$lexemes = preg_split('/\s/', $str, -1, PREG_SPLIT_NO_EMPTY);
		return $lexemes;
	}
}

class Fulltext_TextIndexer extends Fulltext_Indexer
{
	function feed($content)
	{
		$this->feed_body($content);
	}
}

class Fulltext_HtmlIndexer extends Fulltext_Indexer
{
	function feed($content)
	{
		$title = $this->extract_title($content);
		if($title)
			$this->feed_title($title);

		$body = $this->extract_body($content);
		if($body)
			$this->feed_body($body);
	}

	function extract_encoding($html)
	{
		// FIXME: determine HTML encoding
		return "ISO8859-1";
	}

	function extract_title($html)
	{
		if(preg_match("/<title(?:\s.*?)?>(.*?)<\/title\s*>/is", $html, $matches))
			return html_entity_decode($matches[1]);
		else
			return NULL;
	}

	function extract_body($html)
	{
		if(preg_match("/<body(?:\s.*?)?>(.*?)<\/body\s*>/is", $html, $matches))
			return html_entity_decode(preg_replace("/<.*?>/s", "", $matches[1]));
		else
			return NULL;
	}
}

?>
