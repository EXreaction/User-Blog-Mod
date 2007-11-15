<?php
/**
*
* @package phpBB3 User Blog Attachments
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB') || !defined('PLUGIN_INSTALL'))
{
	exit;
}

if (!@is_writable($phpbb_root_path . 'files/blog_mod/'))
{
	@chmod($phpbb_root_path . 'files/blog_mod/', 0777);
	if (!@is_writable($phpbb_root_path . 'files/blog_mod/'))
	{
		trigger_error('FILES_CANT_WRITE');
	}
}

// Add New Tables
switch ($dbms)
{
	case 'mysql' :
		if (version_compare($db->mysql_version, '4.1.3', '>='))
		{
			$dbms_schema = 'attach_schemas/mysql_41_schema.sql';
		}
		else
		{
			$dbms_schema = 'attach_schemas/mysql_40_schema.sql';
		}
	break;
	case 'mysqli' :
		$dbms_schema = 'attach_schemas/mysql_41_schema.sql';
	break;
	default :
		$dbms_schema = 'attach_schemas/' . $dbms . '_schema.sql';
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

// Alter Existing Tables
$db_tool->sql_column_add(BLOGS_TABLE, 'blog_attachment', array('BOOL', 0));
$db_tool->sql_column_add(BLOGS_REPLY_TABLE, 'reply_attachment', array('BOOL', 0));
$db_tool->sql_column_add(EXTENSION_GROUPS_TABLE, 'allow_in_blog', array('BOOL', 0));

// Add new permissions
$blog_permissions = array(
	'local'      => array(),
	'global'   => array(
		'u_blogattach',
		'u_blognolimitattach',
	)
);
$auth_admin->acl_add_option($blog_permissions);

// Add new config settings
set_config('user_blog_max_attachments', 3);
?>