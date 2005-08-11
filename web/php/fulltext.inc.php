<?php

require_once 'mimetypes.inc.php';

class Index
{
	function store_title($page, $title) {}

	function store_lexemes($page, $lexemes) {}

	// Indexer Factory Method
	function indexer($page)
	{
		$content_type = guess_type($page);
		if($content_type == "text/plain")
			return new TextIndexer($this, $page);
		elseif($content_type == "text/html")
			return new HtmlIndexer($this, $page);
		return NULL;
	}

	function index_($page, $content)
	{
		$indexer = $this->indexer($page);
		if(!is_null($indexer))
			$indexer->feed($content);
	}
}

class Indexer
{
	var $index;
	var $page;

	function Indexer($index, $page)
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
		$lexemes = preg_split('/\s/', $s, -1, PREG_SPLIT_NO_EMPTY);
		return $lexemes;
	}
}

class TextIndexer extends Indexer
{
	function feed($content)
	{
		$this->feed_body($content);
	}
}

class HtmlIndexer extends Indexer
{
	function feed($content)
	{
		$title = $this->extract_title($html);
		if($title)
			$this->feed_title($title);

		$body = $this->extract_body($html);
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
