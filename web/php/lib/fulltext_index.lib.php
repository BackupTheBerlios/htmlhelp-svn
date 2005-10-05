<?php

require_once 'lib/mimetypes.lib.php';

// Index interface
class Fulltext_Index
{

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

// Based on Lucene's standard tokenizer
// http://svn.apache.org/repos/asf/lucene/java/trunk/src/java/org/apache/lucene/analysis/standard/StandardTokenizer.jj
// NOTE: order *does* matter: first match is chosen
// NOTE: branches '|' should be inside subpatterns '(?: ... )'
$letter = '\x{0041}-\x{005a}\x{0061}-\x{007a}\x{00c0}-\x{00d6}\x{00d8}-\x{00f6}\x{00f8}-\x{00ff}\x{0100}-\x{1fff}';
$digit = '\x{0030}-\x{0039}\x{0660}-\x{0669}\x{06f0}-\x{06f9}\x{0966}-\x{096f}\x{09e6}-\x{09ef}\x{0a66}-\x{0a6f}\x{0ae6}-\x{0aef}\x{0b66}-\x{0b6f}\x{0be7}-\x{0bef}\x{0c66}-\x{0c6f}\x{0ce6}-\x{0cef}\x{0d66}-\x{0d6f}\x{0e50}-\x{0e59}\x{0ed0}-\x{0ed9}\x{1040}-\x{1049}';
$cjk = '\x{3040}-\x{318f}\x{3300}-\x{337f}\x{3400}-\x{3d2d}\x{4e00}-\x{9fff}\x{f900}-\x{faff}';
$token_patterns = array(
	// acronyms
	"[$letter]\.(?:[$letter]\.)+",

	// email addresses (according to http://www.developer.com/lang/php/article.php/3290141)
	'[_a-zA-Z0-9-]+(?:\.[_a-zA-Z0-9-]+)*@[_a-zA-Z0-9-]+(?:\.[_a-zA-Z0-9-]+)*\.[a-zA-Z]{2,4}',

	// company names
	"[$letter]+[&@][$letter]+",

	// internal apostrophes
	"[$letter]+(?:\'[$letter]+)+",

	// floating point numbers
	'(?:\d+|\d+[.,]\d*|\d*[.,]\d+)[eE][-+]?\d+',

	// versions and ip numbers
	'[a-zA-Z]*\d+[a-zA-Z]*(?:\.[a-zA-Z]*\d+[a-zA-Z]*)+',

	// dates
	'\d+-\d+-\d+|\d+\/\d+\/\d+',

	// decimal numbers
	'\d*[.,]\d+',

	// paths?

	// identifiers
	'[_a-zA-Z][_a-zA-Z0-9]+',

	// basic word: a sequence of letters and digits
	"[$letter$digit]{2,}",

	// Chinese, Japanese, and Korean ideographs
	"[$cjk]",
	
	// integers
	'\d+',	
);

$tokens_pattern = '/' . implode('|',  $token_patterns) . '/u';

// Extracts title and lexemes from documents
class Fulltext_Indexer
{
	var $index;

	function Fulltext_Indexer(&$index)
	{
		$this->index = & $index;
	}

	function feed_title(&$title)
	{
		$this->index->set_title($this->normalize($title));
	}

	function feed_body(&$body_part)
	{
		$tokens = & $this->tokenize($body_part);
		$this->index->add_lexemes($tokens);
	}

	function feed(&$content) {}

	// Normalize whitespace. Expects and produces a UTF-8 string
	function normalize($string)
	{
		return implode(' ', preg_split('/[\s\pZ]+/u', $string, -1, PREG_SPLIT_NO_EMPTY));
	}

	function tokenize(&$string)
	{
		global $tokens_pattern;

		preg_match_all($tokens_pattern, $string, $matches, PREG_PATTERN_ORDER);
		$tokens = & $matches[0];
		return $tokens;
	}
}

// Indexes plain-text documents
class Fulltext_TextIndexer extends Fulltext_Indexer
{
	function feed(&$content)
	{
		$this->feed_body($content);
	}
}

// Indexes HTML documents
class Fulltext_HtmlIndexer extends Fulltext_Indexer
{
	function feed(&$content)
	{
		$encoding = $this->extract_encoding($content);

		$title = $this->extract_title($content, $encoding);
		if($title)
			$this->feed_title($title);

		$body_parts = & $this->extract_body_parts($content, $encoding);
		foreach($body_parts as $body_part)
			$this->feed_body($body_part);
	}

	function extract_encoding(&$html, $default_encoding='iso8859-1')
	{
		// extract encoding from XML header
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
		// extract encoding from HTML META declaration, per
		// http://www.w3.org/TR/html401/charset.html#h-5.2.2
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

	// Decode HTML text into UTF-8
	function decode(&$text, $encoding="iso8859-1")
	{
		if(function_exists('iconv'))
			$decoded_text = iconv($encoding, "utf-8", $text);
		elseif(preg_match('/^iso-?8859-(1|15)$/i', $encoding))
			// fallback for ISO-8859-1/15 encodings
			$decoded_text = utf8_encode($text);
		else
			// replace higher ASCII code by question mark
			$decoded_text = preg_replace('/[\x80-\xff]/', '?', $text);

		// decode HTML entities
		return html_entity_decode($decoded_text, ENT_COMPAT, 'utf-8');
	}

	function extract_title(&$html, $encoding=NULL)
	{
		if(!isset($encoding))
			$encoding = Fulltext_HtmlIndexer::extract_encoding($html);

		if(preg_match(
				// body start tag
				'/<TITLE(?:\s+[^>]*)?>' . 
				// body text
				'(.*?)' . 
				// body end tag
				'<\/TITLE\s*>/is', $html, $matches))
			return Fulltext_HtmlIndexer::decode($matches[1], $encoding);
		else
			return NULL;
	}

	function extract_body_parts(&$html, $encoding=NULL)
	{
		if(!isset($encoding))
			$encoding = Fulltext_HtmlIndexer::extract_encoding($html);

		$parts = preg_split(
				// everything till body start tag
				'/^.*<BODY(?:\s+[^>]*)?>' . 
				// everything after body end tag
				'|<\/BODY\s*>.*$' .
				// any tag
				'|<[^>]*>/is', $html, -1, PREG_SPLIT_NO_EMPTY);
				
		$result = array();
		foreach($parts as $part)
			$result[] = Fulltext_HtmlIndexer::decode($part, $encoding);
		return $result;
	}

	function extract_body(&$html, $encoding=NULL)
	{
		return implode('', Fulltext_HtmlIndexer::extract_body_parts($html, $encoding));
	}
}

?>
