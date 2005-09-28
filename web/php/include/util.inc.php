<?php

// Creates a directory with a unique name at the specified with the specified 
// prefix.
//
// Returns directory name on success, false otherwise
//
// Taken from comments in http://pt.php.net/manual/en/function.tempnam.php
function tmpdir($path, $prefix)
{
       // Use PHP's tmpfile function to create a temporary
       // directory name. Delete the file and keep the name.
       $tempname = tempnam($path,$prefix);
       if (!$tempname)
               return false;

       if (!unlink($tempname))
               return false;

       // Create the temporary directory and returns its name.
       if (mkdir($tempname))
               return $tempname;

       return false;
}

// Taken from comments http://pt.php.net/manual/en/function.utf8-decode.php
function is_valid_utf8($string)
{
	if(preg_match('/^([\x00-\x7f]|[\xc0-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xf7][\x80-\xbf]{3}|[\xf8-\xfb][\x80-\xbf]{4}|[\xfc-\xfd][\x80-\xbf]{5})*$/', $string))
		return TRUE;
	else
		return FALSE;
}

?>
