<?php
	header('Content-Type: text/html; charset=' . $encoding);
		
	echo '<?xml version="1.0" encoding="' . $encoding . '"?>';
	if($doctype == 'strict')
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	else if($doctype == 'frameset')
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
	else
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=' . $encoding . '"/>';
	if($title)
		echo '<title>' . $title . '</title>';
	if($css)
		echo '<link href="' . $css . '" type="text/css" rel="stylesheet"/>';
	if($target)
		echo '<base target="' . $target . '"/>';
	echo '</head>';
?>