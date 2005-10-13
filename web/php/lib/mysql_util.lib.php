<?php

// Get the MySQL server version
function mysql_version()
{
	list($version) = mysql_fetch_row(mysql_query('SELECT VERSION()'));
	return $version;
}

function explode_version($version)
{
	if($pos = strpos($version, '-'))
		$version = substr($version, 0, $pos);
	return explode('.', $version);
}

// Checks for a specified MySQL server version
function mysql_check_version($version)
{
	$version = explode_version($version);
	$mysql_version = explode_version(mysql_version());
	
	foreach($version as $index => $version_number)
	{
		if(!$version_number)
			continue;
		
		if($index >= count($mysql_version))
			return FALSE;

		$mysql_version_number = $mysql_version[$index];
		
		if($version_number > $mysql_version_number)
			return FALSE;

		if($version_number < $mysql_version_number)
			return TRUE;
	}

	return TRUE;
}

// Import a MySQL dump
function mysql_import_dump($filename, $ignoreerrors = FALSE)
{
	// based on example from http://pt.php.net/mysql_query
	
	$handle = fopen($filename, 'r');
	$query = '';
	$query_line_no = $line_no = 1;
	while(!feof($handle))
	{
		$buffer = fgets($handle);
		$query .= $buffer;
		if($buffer{strlen($buffer) - 1} == "\n")
			$line_no += 1;
		if(preg_match('/;\s*\n$/', $buffer))
		{
			$query = substr($query, 0, strrpos($query, ';'));
			$result = mysql_query($query);
			if(!$result && !$ignoreerrors) 
				die($filename . ':' . $query_line_no . ':' . mysql_error());
			$query = '';
			$query_line_no = $line_no;
		}
	}
}

// Escape a array into comma separated values
function mysql_escape_array(&$values)
{
	$escaped_values = array();
	foreach($values as $value)
	{
		if(is_int($value) || is_float($value))
			$escaped_values[] = $value;
		elseif(is_string($value))
			$escaped_values[] = '"' . mysql_escape_string($value) . '"';
		elseif(is_array($value))
			$escaped_values[] = '(' . mysql_escape_array($value) . ')';
		else
			die("Don't know how to escape '$value'");
	}
	return implode(',', $escaped_values);
}

function mysql_fetch_rows($result)
{
	$rows = array();
	while($row = mysql_fetch_row($result))
		$rows[] = $row;
	return $rows;
}

function mysql_fetch_fields($result)
{
	$fields = array();
	while(list($field) = mysql_fetch_row($result))
		$fields[] = $field;
	return $fields;
}

function mysql_write_html_result($result)
{
	echo '<table>';	
	$rows = array();
	while($row = mysql_fetch_assoc($result))
	{
		echo '<tr>';
		foreach($row as $field)
		{
			echo '<td>';
			echo htmlspecialchars($field, ENT_NOQUOTES);
			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</table>';	
}

?>
