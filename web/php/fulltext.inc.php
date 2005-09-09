<?php

require_once 'mimetypes.inc.php';

mb_internal_encoding('utf-8');

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

	// Normalize whitespace. Expects and produces a UTF-8 string
	function normalize($string)
	{
		return implode(' ', preg_split('/[\s\pZ]+/u', $string, -1, PREG_SPLIT_NO_EMPTY));
	}

	function tokenize($string)
	{
		// NOTE: Based on http://svn.apache.org/repos/asf/lucene/java/trunk/src/java/org/apache/lucene/analysis/standard/StandardTokenizer.jj
		$token_patterns = array(
			// basic word: a sequence of letters and digits
			"[\pL\pN]{2,}", 

			// acronyms
			"\p{Lu}\.(\p{Lu}\.)+",

			// company names
			"\w+(&|@)\w+",

			// email addresses (according to http://www.developer.com/lang/php/article.php/3290141)
			"[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*\.[a-zA-Z]{2,4}\b",

			// urls
			"[a-z]+:\/\/[^\s]+",

			// identifiers
			"[_a-zA-Z][_a-zA-Z0-9]+",

			// numbers
			"\d+|\d*[.,]\d+",
			"(\d+|\d+[.,]\d*|\d*[.,]\d+)[eE][-+]?\d+",

			// dates
			"\d+-\d+-\d+|\d+\/\d+\/\d+",

			// versions and ip numbers
			"\w*\d\w*(\.\w*\d\w*)+",

			// paths
			#"\.{0,2}(\/(\.{0,2}|[_a-zA-Z0-9-]+))+",
		);

		/**/
		$tokens = array();
		while(strlen($string))
		{
			if(preg_match("/^[\s\pZ\pP\pS]+/u", $string, $matches))
			{
				// remove whitespace
				$string = substr($string, strlen($matches[0]));
			}
			else
			{
				$longest_token = 0;

				foreach($token_patterns as $token_pattern)
					if(preg_match('/^(' . $token_pattern . ')/u', $string, $matches))
						if(strlen($matches[0]) > $longest_token)
							$longest_token = strlen($matches[0]);

				if($longest_token)
				{
					$tokens[] = substr($string, 0, $longest_token);
					$string = substr($string, $longest_token);
				}
				else
				{
					// no token found; advance one character
					$character_length = strlen(mb_substr($string, $offset, 1));
					if($character_length)
						$string = substr($string, $character_length);
					else
						$string = substr($string, 1);
				}
			}
		}
		/**/

		/*
		$length = strlen($string);
		$offset = 0;
		$tokens = array();
		while($offset < $length)
		{
			$last_offset = $offset;

			if(preg_match("/\G\d[\s\pZ]+/u", $string, $matches, NULL, $offset))
			{
				// remove whitespace
				assert(strlen($matches[0]));
				$offset += strlen($matches[0]);
			}
			else
			{
				$longest_token = 0;

				// TODO: dot not use substrings and assert tokens are separated by whitespace or separators
				foreach($token_patterns as $token_pattern)
					if(preg_match('/\G\d(' . $token_pattern . ')(?=[^\pL\pN]|$)/u', $string, $matches, NULL, $offset))
						if(strlen($matches[0]) > $longest_token)
							$longest_token = strlen($matches[0]);

				if($longest_token)
				{
					$tokens[] = substr($string, $offset, $longest_token);
					$offset += $longest_token;
				}
				else
				{
					// no token found; advance one character
					$character_length = strlen(mb_substr($string, $offset, 1));
					if($character_length)
						$offset += $character_length;
					else
						$offset += 1;
				}
			}

			assert($offset != $last_offset);
		}
		*/

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
		$encoding = $this->extract_encoding($content);

		$title = $this->extract_title($content, $encoding);
		if($title)
			$this->feed_title($title);

		$body_parts = $this->extract_body_parts($content, $encoding);
		foreach($body_parts as $body_part)
			$this->feed_body($body_part);
	}

	function extract_encoding($html, $default_encoding='iso8859-1')
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
	function decode($text, $encoding="iso8859-1")
	{
		if(function_exists('iconv'))
			$decoded_text = iconv($encoding, "utf-8", $text);
		elseif(preg_match('/^iso-?8859-(1|15)$/i', $encoding))
			// fallback for ISO-8859-1/15 encodings
			$decoded_text = utf8_encode($encoding, "utf-8", $text);
		else
			// replace higher ASCII code by question mark
			$decoded_text = preg_replace('/[\x80-\xff]/', '?', $text);

		// decode HTML entities
		return html_entity_decode($decoded_text, ENT_COMPAT, 'utf-8');
	}

	function extract_title($html, $encoding=NULL)
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

	function extract_body_parts($html, $encoding=NULL)
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

	function extract_body($html, $encoding=NULL)
	{
		return implode('', Fulltext_HtmlIndexer::extract_body_parts($html, $encoding));
	}
}

?>
