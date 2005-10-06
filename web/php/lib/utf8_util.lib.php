<?php

// Return a Unicode string of one character with ordinal. 
// Based from comments in http://pt.php.net/manual/en/function.chr.php
// and description on http://en.wikipedia.org/wiki/UTF-8
function unichr($ord)
{
	if($ord < 0x80)
		return chr($ord);
	elseif($ord < 0x0800)
		return 
			chr(0xc0 ^ (($ord >> 6) & 0x1f)) .
			chr(0x80 ^ ($ord & 0x3f));
	elseif($ord < 0x010000)
		return
			chr(0xe0 ^ (($ord >> 12) & 0x0f)) .
			chr(0x80 ^ (($ord >> 6) & 0x3f)) .
			chr(0x80 ^ ($ord & 0x3f));
	elseif($ord < 0x110000)
		return
			chr(0xf0 ^ (($ord >> 18) & 0x07)) .
			chr(0x80 ^ (($ord >> 12) & 0x3f)) .
			chr(0x80 ^ (($ord >> 6) & 0x3f)) .
			chr(0x80 ^ ($ord & 0x3f));
	else
		return FALSE;
}

function encode_utf8($string, $encoding='ISO-8859-1')
{
	if(function_exists('iconv'))
		return iconv($encoding, "UTF-8", $string);
	elseif(preg_match('/^ISO-?8859-(1|15)$/i', $encoding))
		// fallback for ISO-8859-1/15 encodings
		return utf8_encode($string);
	else
		// replace higher ASCII code by question mark
		return preg_replace('/[\x80-\xff]/', '?', $string);
}

// Based from comments http://pt.php.net/manual/en/function.utf8-decode.php
function is_valid_utf8($string)
{
	return preg_match('/^(?:[\x00-\x7f]|[\xc0-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xf7][\x80-\xbf]{3}|[\xf8-\xfb][\x80-\xbf]{4}|[\xfc-\xfd][\x80-\xbf]{5})*$/', $string);
}

// Based on the whitespace definition in 
// http://www.unicode.org/Public/4.1.0/ucd/PropList.txt
function explode_utf8($string)
{
	return preg_split('/[\x{0009}-\x{000D}\x{0020}\x{0085}\x{00A0}\x{1680}\x{180E}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}
]/u', $string, -1, PREG_SPLIT_NO_EMPTY);
}

?>