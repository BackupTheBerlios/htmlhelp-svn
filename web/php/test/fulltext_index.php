<?php

require_once 'lib/fulltext_index.lib.php';
require_once 'PHPUnit.php';

class IndexTest extends PHPUnit_TestCase
{
	var $index;

	function setUp()
	{
		$this->index = new Fulltext_Index();
	}

	function tearDown()
	{
		unset($this->index);
	}

	function testIndexer()
	{
		$testcases = array(
			'abc.txt' => 'Fulltext_TextIndexer',
			'abc.htm' => 'Fulltext_HtmlIndexer',
			'abc.html' => 'Fulltext_HtmlIndexer',
		);
		foreach($testcases as $path => $class)
			$this->assertTrue(is_a($this->index->indexer_factory($path), $class), $class);
	}
}

class IndexerTest extends PHPUnit_TestCase
{
	function testNormalize()
	{
		$testcases = array(
			"" => "",

			// white space
			" \t\r\n" => "",
			
			// U+00A0 NO-BREAK SPACE
			"\xC2\xA0" => "",

			"  a house" => "a house",
			" a   house " => "a house",
			"a  house" => "a house",
		);
		foreach($testcases as $string => $result)
			$this->assertEquals($result, Fulltext_TextIndexer::normalize($string));
	}

	function testTokenize()
	{
		$testcases = array(
			// empty
			"" => array(),

			// words
			"pre post" => array("pre", "post"),
			"pre/post" => array("pre", "post"),
			"pre,post" => array("pre", "post"),
			"pre. post" => array("pre", "post"),
			" pre  post " => array("pre", "post"),

			// acronyms
			"pre A.B. post" => array("pre", "A.B.", "post"),
			"pre C.D.E., post" => array("pre", "C.D.E.", "post"),
			"pre X.Y.Z.. post" => array("pre", "X.Y.Z.", "post"),

			// emails
			"pre simple@email.com post" => array("pre", "simple@email.com", "post"), 
			"pre strange_email.address@somewhere1.com post" => array("pre", "strange_email.address@somewhere1.com", "post"), 

			// versions
			"package-1.2.3.4a.ext" => array("package", "1.2.3.4a", "ext"),

			// ip numbers
			"pre 127.0.0.1 post" => array("pre", "127.0.0.1", "post"),
			"pre 196.168.0.1. post" => array("pre", "196.168.0.1", "post"),

			// numbers
			"pre 1 post" => array("pre", "1", "post"),
			"pre 1, 2, 3 post" => array("pre", "1", "2", "3", "post"),
			"pre .2 post" => array("pre", ".2", "post"),
			"pre 3.456789E-123 post" => array("pre", "3.456789E-123", "post"),
			"pre 1+2 post" => array("pre", "1", "2", "post"),
			"pre 3*4 post" => array("pre", "3", "4", "post"),
			"pre 5/6 post" => array("pre", "5", "6", "post"),
			"pre 0x1234 post" => array("pre", "0x1234", "post"),
			"pre 10101010b post" => array("pre", "10101010b", "post"),

			// dates
			"pre 1234-12-23 post" => array("pre", "1234-12-23", "post"),
			"pre 07/06/00 post" => array("pre", "07/06/00", "post"),

			// Latin-1 characters
			"Eagle \xC3\x80guia" => array("Eagle", "\xC3\x80guia"),
		);
		foreach($testcases as $string => $result)
			$this->assertEquals($result, Fulltext_TextIndexer::Tokenize($string));
	}
}

class HtmlIndexerTest extends PHPUnit_TestCase
{
	function testExtractEncoding()
	{
		$testcases = array(
			"" => NULL,

			// Based from http://search.cpan.org/~bjoern/HTML-Encoding-0.52/lib/HTML/Encoding.pm
			"<?xml version='1.0'>"                    => NULL,
			"<?xml version='1.0' encoding='utf-8'?>"  => 'utf-8',
			"<?xml encoding='utf-8'?>"                => 'utf-8',
			"<?xml encoding=\"utf-8\"?>"              => 'utf-8',
			"<?xml foo='bar' encoding='utf-8'?>"      => 'utf-8',
			"<?xml encoding='a' encoding='b'?>"       => 'a',
			"<?xml-stylesheet encoding='utf-8'?>"     => NULL,
			" <?xml encoding='utf-8'?>"               => NULL,
			"<?xml encoding = 'utf-8'?>"              => 'utf-8',
			"<?xml version='1.0' encoding=utf-8?>"    => NULL,
			"<?xml x='encoding=\"a\"' encoding='b'?>" => 'b',

			'<META http-equiv="Content-Type" content="text/html">'                           => NULL,
			'<META http-equiv="Content-Type" content="text/html,text/plain;charset=utf-8">'  => NULL,
			'<META http-equiv="Content-Type" content="text/html;charset=">'                  => NULL,
			'<META http-equiv="Content-Type" id="test" content="text/html;charset=utf-8">'   => 'utf-8',
			'<META http-equiv="Content-Type" content="text/html;charset=\'utf-8\'">'         => 'utf-8',
			'<META http-equiv="Content-Type" content=\'text/html;charset="UTF-8"\'>'         => 'UTF-8',
			'<META http-equiv="Content-Type" content="text/html;charset=&quot;UTF-8&quot;">' => 'UTF-8',

		);
		foreach($testcases as $html => $title)
			$this->assertEquals($title, Fulltext_HtmlIndexer::extract_encoding($html, NULL), $html);
	}

	function testDecode()
	{
		$testcases = array(
			// Basic HTML entities
			'&amp;' => '&',
			'&lt;' => '<',
			'&gt;' => '>',
			'&quot;' => '"',

			// Latin capital letter A with grave (U+00C0)
			"&Agrave;" => "\xC3\x80",
			"&#192;" => "\xC3\x80",
			"&#xC0;" => "\xC3\x80",
			"\xC0" => "\xC3\x80",
		);
		foreach($testcases as $html => $title)
			$this->assertEquals($title, Fulltext_HtmlIndexer::decode($html));
	}

	function testExtractTitle()
	{
		$testcases = array(
			"before<title></title>after" => "",
			"<title>Simple</title>" => "Simple",
			"<Title>Ignore case</TITLE>" => "Ignore case",
			"<title id=\"id123\">Attributes</title>" => "Attributes",
			"<title id=\"id123\">Html entities: &amp;&lt;&gt;</title >" => "Html entities: &<>",
			"<title\n\n>\nNewlines\n</title\n\n>" => "\nNewlines\n",
		);
		foreach($testcases as $html => $title)
			$this->assertEquals($title, Fulltext_HtmlIndexer::extract_title($html));
	}

	function testExtractBody()
	{
		$testcases = array(
			"before<body></body>after" => "",
			"<body>Simple</body>" => "Simple",
			"<Body>Ignore case</BODY>" => "Ignore case",
			"<body id=\"id123\">Attributes</body>" => "Attributes",
			"<body id=\"id123\">Html entities: &amp;&lt;&gt;</body >" => "Html entities: &<>",
			"<body><b class=\"bold\">Tags</b></body\n\n>" => "Tags",
			"<body\n\n>\nNewlines\n</body\n\n>" => "\nNewlines\n",
		);
		foreach($testcases as $html => $body)
			$this->assertEquals($body, Fulltext_HtmlIndexer::extract_body($html));
	}
}

$cases = array(
	'IndexTest',
	'IndexerTest',
	'HtmlIndexerTest',
);
foreach($cases as $case)
	echo PHPUnit::run(new PHPUnit_TestSuite($case))->toString();

?>
