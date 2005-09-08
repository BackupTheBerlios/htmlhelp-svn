<?php

require_once 'fulltext.inc.php';
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
			" \n\t " => "",
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
			"" => array(),
			"a word" => array("a", "word"),
			"an A.B.C. acronym" => array("an", "A.B.C.", "acronym"),
			"a strange_email.address@somewhere1.com email" => array("a", "strange_email.address@somewhere1.com", "email"),
			"package-1.2.3.4a.ext" => array("package", "-", "1.2.3.4a", ".", "ext"),
			"some .1+2.-3.456789E-123 numbers" => array("some", ".1", "+", "2.", "-", "3.456789E-123", "numbers"),
			"ip 196.168.0.1 number" => array("ip", "196.168.0.1", "number"),
			"some 1234-12-23 07/06/00 dates" => array("some", "1234-12-23", "07/06/00", "dates"),
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
