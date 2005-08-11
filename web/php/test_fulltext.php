<?php

require_once 'fulltext.inc.php';
require_once 'PHPUnit.php';

class IndexTest extends PHPUnit_TestCase
{
	var $index;

	function setUp()
	{
		$this->index = new Index();
	}

	function tearDown()
	{
		unset($this->index);
	}

	function testIndexer()
	{
		$testcases = array(
			'abc.txt' => 'TextIndexer',
			'abc.htm' => 'HtmlIndexer',
			'abc.html' => 'HtmlIndexer',
		);
		foreach($testcases as $path => $class)
			$this->assertTrue(is_a($this->index->indexer($path), $class), $class);
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
			$this->assertEquals($result, TextIndexer::normalize($string));
	}
}

class HtmlIndexerTest extends PHPUnit_TestCase
{
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
			$this->assertEquals($title, HtmlIndexer::extract_title($html));
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
			$this->assertEquals($body, HtmlIndexer::extract_body($html));
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
