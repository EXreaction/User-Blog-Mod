<?php
/**
*
* @package phpBB3 User Blog Tags
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if ($dbms == 'mysql' || $dbms == 'mysqli')
{
	if ($dbms == 'mysqli' || (isset($db->mysql_version) && version_compare($db->mysql_version, '4.1.3', '>=')) || (isset($db->sql_server_version) && version_compare($db->sql_server_version, '4.1.3', '>=')))
	{
		$dbms_schema = 'mysql_41_schema.sql';
	}
	else
	{
		$dbms_schema = 'mysql_40_schema.sql';
	}
}
else
{
	$dbms_schema = $dbms . '_schema.sql';
}

if (!file_exists($blog_plugins_path . 'tags/schemas/' . $dbms_schema))
{
	trigger_error('SCHEMA_NOT_EXIST');
}

$remove_remarks = $dbmd[$dbms]['COMMENTS'];
$delimiter = $dbmd[$dbms]['DELIM'];

$sql_query = @file_get_contents($blog_plugins_path . 'tags/schemas/' . $dbms_schema);

$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

$remove_remarks($sql_query);

$sql_query = split_sql_file($sql_query, $delimiter);

foreach ($sql_query as $sql)
{
	if (!$db->sql_query($sql))
	{
		$error[] = $db->sql_error();
	}
}
unset($sql_query);

$db_tool->sql_column_add(BLOGS_TABLE, 'blog_tags', array('MTEXT_UNI', ''));

?>