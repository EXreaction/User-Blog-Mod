<?php
/**
*
* @package phpBB3 User Blog Attachments
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB') || !defined('PLUGIN_UNINSTALL'))
{
	exit;
}

$sql_array = array();

// Delete the attachment table
$sql_array[] = 'DROP TABLE ' . BLOGS_ATTACHMENT_TABLE;

// remove the columns we added
$db_tool->sql_column_remove(BLOGS_TABLE, 'blog_attachment');
$db_tool->sql_column_remove(BLOGS_REPLY_TABLE, 'reply_attachment');
$db_tool->sql_column_remove(EXTENSION_GROUPS_TABLE, 'allow_in_blog');

// remove the auth options
$sql_array[] = 'DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option = \'u_blogattach\'';
$sql_array[] = 'DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option = \'u_blognolimitattach\'';

// remove the config settings
$sql_array[] = 'DELETE FROM ' . CONFIG_TABLE . ' WHERE config_name = \'user_blog_max_attachments\'';

foreach ($sql_array as $sql)
{
	$db->sql_query($sql);
}
?>