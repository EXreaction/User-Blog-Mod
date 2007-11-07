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
* Insert Some Data into the new tables ------------------------------------------------------------
*/

// Install the Archive Plugin
$sql_ary = array(
	'plugin_name'		=> 'archive',
	'plugin_enabled'	=> 1,
	'plugin_version'	=> '0.7.0',
);
$sql = 'INSERT INTO ' . BLOGS_PLUGINS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

// Add the first blog
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
include($phpbb_root_path . 'blog/search/fulltext_native.' . $phpEx);

$message_parser = new parse_message();
$blog_search = new blog_fulltext_native();

$message_parser->message = $user->lang['WELCOME_MESSAGE'];
$message_parser->parse(true, true, true);

$sql_data = array(
	'user_id' 					=> $user->data['user_id'],
	'user_ip'					=> $user->data['user_ip'],
	'blog_time'					=> time(),
	'blog_subject'				=> $user->lang['WELCOME_SUBJECT'],
	'blog_text'					=> $message_parser->message,
	'blog_checksum'				=> md5($message_parser->message),
	'blog_approved' 			=> 1,
	'enable_bbcode' 			=> 1,
	'enable_smilies'			=> 1,
	'enable_magic_url'			=> 1,
	'bbcode_bitfield'			=> $message_parser->bbcode_bitfield,
	'bbcode_uid'				=> $message_parser->bbcode_uid,
	'blog_edit_reason'			=> '',
);
$sql = 'INSERT INTO ' . BLOGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);
$db->sql_query($sql);
$blog_id = $db->sql_nextid();

$blog_search->index('add', $blog_id, 0, $message_parser->message, $user->lang['WELCOME_SUBJECT'], $user->data['user_id']);

$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = blog_count + 1 WHERE user_id = \'' . $user->data['user_id'] . '\'';
$db->sql_query($sql);
?>