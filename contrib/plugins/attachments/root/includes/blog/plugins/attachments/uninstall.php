<?php
/**
*
* @package phpBB3 User Blog Attachments
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

$sql_array = array();
$sql_array[] = 'DROP TABLE ' . BLOGS_ATTACHMENT_TABLE;

$sql_array[] = 'ALTER TABLE ' . BLOGS_TABLE . ' DROP blog_attachment';
$sql_array[] = 'ALTER TABLE ' . BLOGS_REPLY_TABLE . ' DROP reply_attachment';
$sql_array[] = 'ALTER TABLE ' . EXTENSION_GROUPS_TABLE . " DROP allow_in_blog";

$sql_array[] = 'DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option = \'u_blogattach\'';
$sql_array[] = 'DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option = \'u_blognolimitattach\'';

$sql_array[] = 'DELETE FROM ' . CONFIG_TABLE . ' WHERE config_name = \'user_blog_max_attachments\'';

foreach ($sql_array as $sql)
{
	$db->sql_query($sql);
}
?>