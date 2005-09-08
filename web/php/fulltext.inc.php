<?php

require_once 'mimetypes.inc.php';

// Index interface
class Fulltext_Index
{

	// Set page title (should be overriden by derived classes)
	function set_title($title) {}

	// Add lexemes in order (should be overriden by derived classes)
	function add_lexemes($lexemes) {}

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
	function index_page($path, $content)
	{
		$indexer = $this->indexer_factory($path);
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

	function set_title($page, $title) 
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

// Extracts title and lexemes from documents
class Fulltext_Indexer
{
	var $index;

	function Fulltext_Indexer($index)
	{
		$this->index = $index;
	}

	function feed_title($title)
	{
		$this->index->set_title($this->normalize($title));
	}

	function feed_body($body)
	{
		$this->index->add_lexemes($this->tokenize($body));
	}

	function feed($content) {}

	// Normalize whitespace
	function normalize($str)
	{
		return implode(' ', preg_split('/\s/', $str, -1, PREG_SPLIT_NO_EMPTY));
	}

	function tokenize($str)
	{
		// NOTE: Based on http://svn.apache.org/repos/asf/lucene/java/trunk/src/java/org/apache/lucene/analysis/standard/StandardTokenizer.jj
		$token_patterns = array(
			// basic word: a sequence of letters and digits
			"[\w\d]+", 

			// acronyms
			"\w\.(\w\.)+",

			// company names
			"\w+(&|@)\w+",

			// email addresses
			"[\w\d][-_.\w\d]*@[\w\d]+([-.][\w\d]+)+",

			// hostnames
			//"\w[\w\d]*(\.\w[\w\d]*)+",

			// identifiers
			"[_\w][_\w\d]*",

			// floating point
			"(\d+\.\d*|\d*\.\d+)([eE][-+]?\d+)?",

			// dates, versions, ip numbers
			"[\d\w]*\d[\d\w]*([-.,\/_][\d\w]*\d[\d\w]*)+",

			// paths
			//"((\.|\.\.|[^\s:]+)\/)+[^\s:]+",
			
			// character
		);

		$string = $str;
		$tokens = array();
		while(strlen($string))
		{
			if(preg_match("/^\s+/", $string, $matches))
			{
				// remove whitespace
				$string = substr($string, strlen($matches[0]));
			}
			else
			{
				$longest_token = 1;

				foreach($token_patterns as $token_pattern)
					if(preg_match('/^(' . $token_pattern . ')/', $string, $matches))
						if(strlen($matches[0]) > $longest_token)
							$longest_token = strlen($matches[0]);

				$tokens[] = substr($string, 0, $longest_token);
				$string = substr($string, $longest_token);
			}
		}

		return $tokens;
	}
}

// Indexes plain-text documents
class Fulltext_TextIndexer extends Fulltext_Indexer
{
	function feed($content)
	{
		$this->feed_body($content);
	}
}

// Indexes HTML documents
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

	function extract_encoding($html, $default_encoding='iso8859-1')
	{
		// FIXME: determine and use HTML encoding
		if(preg_match(
				'/^<\?xml' . 
					// other attributes
					'(?:\s+[-a-zA-Z0-9._:]+\s*=\s*(?:"[^"]*"|\'[^\']*\'))*?' . 
					// encoding attribute
					'\s+encoding\s*=\s*(?:"([^"]*)"|\'([^\']*)\')' .
					// other attributes
					'(?:\s+[-a-zA-Z0-9._:]+\s*=\s*(?:"[^"]*"|\'[^\']*\'))*?' . 
				'\s*\?>/', $html, $matches))
		{
			$encoding = $matches[1] . $matches[2];
			return trim($encoding);
		}
		// per http://www.w3.org/TR/html401/charset.html#h-5.2.2
		elseif(preg_match(
				'/<META' .
					// other attributes
					'(?:\s+[-a-zA-Z0-9._:]+\s*=\s*(?:[-a-zA-Z0-9._:]+|"[^"]*"|\'[^\']*\'))*?' . 
					// http-equiv attribute
					'\s+http-equiv\s*=\s*(?:Content-Type|"Content-Type"|\'Content-Type\')' .
					// other attributes
					'(?:\s+[-a-zA-Z0-9._:]+\s*=\s*(?:[-a-zA-Z0-9._:]+|"[^"]*"|\'[^\']*\'))*?' .
					// content attribute
					'\s+content\s*=\s*(?:([-a-zA-Z0-9._:]+)|"([^"]*)"|\'([^\']*)\')' .
					// other attributes
					'(?:\s+[-a-zA-Z0-9._:]+\s*=\s*(?:[-a-zA-Z0-9._:]+|"[^"]*"|\'[^\']*\'))*?' .
				'\s*>/i', $html, $matches))
		{
			$http_equiv = html_entity_decode($matches[1] . $matches[2] . $matches[3]);
			if(preg_match('/^text\/html;\s*charset=(?:([-a-zA-Z0-9._:]+)|"([^"]*)"|\'([^\']*)\')/', $http_equiv, $matches))
			{
				$encoding = $matches[1] . $matches[2] . $matches[3];
				return $encoding;
			}
		}
		return $default_encoding;
	}

	function extract_title($html)
	{
		if(preg_match(
				// body start tag
				'/<TITLE(?:\s+[^>]*)?>' . 
				// body text
				'(.*?)' . 
				// body end tag
				'<\/TITLE\s*>/is', $html, $matches))
			return html_entity_decode($matches[1]);
		else
			return NULL;
	}

	function extract_body($html)
	{
		if(preg_match(
				// body start tag
				'/<BODY(?:\s+[^>]*)?>' . 
				// body text
				'(.*?)' . 
				// body end tag
				'<\/BODY\s*>/is', $html, $matches))
			return html_entity_decode(preg_replace('/<[^>]*>/', '', $matches[1]));
		else
			return NULL;
	}
}

?>
