<?php

$db_version_major = 1;
$db_version_minor = 1;

mysql_connect($db_server, $db_username, $db_password);
mysql_select_db($db_database);
mysql_query("SET NAMES '$internal_encoding'");

$result = mysql_query('SELECT major, minor FROM version');
if($result && mysql_num_rows($result))
	list($major, $minor) = mysql_fetch_row($result);
else
	list($major, $minor) = array(0, 0);

if($major == $db_version_major && $minor == $db_version_minor)
		return;

require_once('lib/mysql_util.lib.php');

mysql_import_dump('sql/htmlhelp.sql');
mysql_import_dump('sql/tags.sql');

mysql_query(
	"INSERT " .
	"INTO version (major, minor) " .
	"VALUES ($db_version_major, $db_version_minor)"
);

?>