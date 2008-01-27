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
	'plugin_version'	=> '0.7.4',
);
$sql = 'INSERT INTO ' . BLOGS_PLUGINS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

// Add default permissions for Roles
$role_data = array(
	'ROLE_ADMIN_FULL'		=> array('a_blogmanage', 'a_blogdelete', 'a_blogreplydelete'),
	'ROLE_MOD_FULL'			=> array('m_blogapprove', 'm_blogedit', 'm_bloglockedit', 'm_blogdelete', 'm_blogreport', 'm_blogreplyapprove', 'm_blogreplyedit', 'm_blogreplylockedit', 'm_blogreplydelete', 'm_blogreplyreport'),
	'ROLE_MOD_STANDARD'		=> array('m_blogapprove', 'm_blogedit', 'm_bloglockedit', 'm_blogdelete', 'm_blogreport', 'm_blogreplyapprove', 'm_blogreplyedit', 'm_blogreplylockedit', 'm_blogreplydelete', 'm_blogreplyreport'),
	'ROLE_MOD_QUEUE'		=> array('m_blogapprove', 'm_blogedit', 'm_bloglockedit', 'm_blogreplyapprove', 'm_blogreplyedit', 'm_blogreplylockedit'),
	'ROLE_MOD_SIMPLE'		=> array('m_blogedit', 'm_bloglockedit', 'm_blogdelete', 'm_blogreplyedit', 'm_blogreplylockedit', 'm_blogreplydelete'),
	'ROLE_USER_FULL'		=> array('u_blog_style', 'u_blog_vote', 'u_blog_vote_change', 'u_blog_create_poll', 'u_blogattach', 'u_blognolimitattach', 'u_blogview', 'u_blogpost', 'u_blogedit', 'u_blogdelete', 'u_blognoapprove', 'u_blogreport', 'u_blogreply', 'u_blogreplyedit', 'u_blogreplydelete', 'u_blogreplynoapprove', 'u_blogbbcode', 'u_blogsmilies', 'u_blogimg', 'u_blogurl', 'u_blogflash', 'u_blogmoderate'),
	'ROLE_USER_STANDARD'	=> array('u_blog_style', 'u_blog_vote', 'u_blog_vote_change', 'u_blog_create_poll', 'u_blogattach', 'u_blogview', 'u_blogpost', 'u_blogedit', 'u_blogdelete', 'u_blognoapprove', 'u_blogreport', 'u_blogreply', 'u_blogreplyedit', 'u_blogreplydelete', 'u_blogreplynoapprove', 'u_blogbbcode', 'u_blogsmilies', 'u_blogimg', 'u_blogurl', 'u_blogmoderate'),
	'ROLE_USER_LIMITED'		=> array('u_blog_vote', 'u_blog_vote_change', 'u_blogview', 'u_blogpost', 'u_blogedit', 'u_blogreport', 'u_blogreply', 'u_blogreplyedit', 'u_blogbbcode', 'u_blogsmilies', 'u_blogimg', 'u_blogurl'),
	'ROLE_USER_NOPM'		=> array('u_blog_style', 'u_blog_vote', 'u_blog_vote_change', 'u_blogview', 'u_blogpost', 'u_blogedit', 'u_blogreport', 'u_blogreply', 'u_blogreplyedit', 'u_blogbbcode', 'u_blogsmilies', 'u_blogimg', 'u_blogurl'),
	'ROLE_USER_NOAVATAR'	=> array('u_blog_style', 'u_blog_vote', 'u_blog_vote_change', 'u_blogview', 'u_blogpost', 'u_blogedit', 'u_blogreport', 'u_blogreply', 'u_blogreplyedit', 'u_blogbbcode', 'u_blogsmilies', 'u_blogimg', 'u_blogurl'),
);

foreach ($role_data as $role => $options)
{
	$sql = 'SELECT role_id FROM ' . ACL_ROLES_TABLE . " WHERE role_name = '{$role}'";
	$db->sql_query($sql);
	$role_id = $db->sql_fetchfield('role_id');

	if ($role_id)
	{
		$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . ' WHERE ' . $db->sql_in_set('auth_option', $options);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_ary = array(
				'role_id'			=> $role_id,
				'auth_option_id'	=> $row['auth_option_id'],
				'auth_setting'		=> 1,
			);
			$sql = 'INSERT IGNORE INTO ' . ACL_ROLES_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}
	}

	$role_id = false;
}

// Add the first blog
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

$message_parser = new parse_message();
$blog_search = setup_blog_search();

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