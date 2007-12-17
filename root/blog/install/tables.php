<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB') || !defined('IN_BLOG_INSTALL'))
{
	exit;
}

/*
* Add New Tables ----------------------------------------------------------------------------------
*/
switch ($dbms)
{
	case 'mysql' :
		if (version_compare($db->mysql_version, '4.1.3', '>='))
		{
			$dbms_schema = 'schemas/mysql_41_schema.sql';
		}
		else
		{
			$dbms_schema = 'schemas/mysql_40_schema.sql';
		}
	break;
	case 'mysqli' :
		$dbms_schema = 'schemas/mysql_41_schema.sql';
	break;
	default :
		$dbms_schema = 'schemas/' . $dbms . '_schema.sql';
}

if (!file_exists($phpbb_root_path . 'blog/install/' . $dbms_schema))
{
	trigger_error('SCHEMA_NOT_EXIST');
}

$remove_remarks = $dbmd[$dbms]['COMMENTS'];
$delimiter = $dbmd[$dbms]['DELIM'];

$sql_query = @file_get_contents($phpbb_root_path . 'blog/install/' . $dbms_schema);

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

/*
* Alter Existing Tables -----------------------------------------------------------------------
*/
$db_tool->sql_column_add(USERS_TABLE, 'blog_count', array('UINT', 0));
?>