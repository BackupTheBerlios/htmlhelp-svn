<?php

require_once 'lib/utf8_util.lib.php';
require_once 'lib/html_entities.lib.php';

$html_entities_tbl = array();
foreach($html_entities as $entity => $ord)
	$html_entities_tbl["&$entity;"] = unichr($ord);

// Decode HTML character entities
// It expects and produces a UTF-8 string
// Based on http://pt.php.net/manual/en/function.html-entity-decode.php
function decode_html_entities($string)
{
   // replace numeric entities
   $string = preg_replace('/&#x([0-9A-Fa-f]+);/e', 'unichr(hexdec(\'\\1\'))', $string);
   $string = preg_replace('/&#([0-9]+);/e', 'unichr(\\1)', $string);
   
   // replace literal entities
   global $html_entities_tbl;
   return strtr($string, $html_entities_tbl);
}

// Decode HTML character data into UTF-8 
function decode_html($string, $encoding = 'ISO-8859-1')
{
	$string = encode_utf8($string, $encoding);
	return decode_html_entities($string);
}

?>