<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/*
* WARNING: This script probably does not support any database backend other than MySQL.
*
* USE AT YOUR OWN RISK.
*/
// Comment out or delete the following line if you are sure you would like to uninstall this.  You can comment it out by adding two / in front of it like it is at the beginning of this line.
die('You must comment this line out from the blog/uninstall.php file to continue with the uninstallation.');

// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/blog/common', 'mods/blog/setup'));

if (!$user->data['is_registered'])
{
	login_box();
}

if ($user->data['user_type'] != USER_FOUNDER)
{
	trigger_error('MUST_BE_FOUNDER');
}

if (confirm_box(true))
{
	include($phpbb_root_path . 'blog/includes/constants.' . $phpEx);
	include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
	$db_tool = new phpbb_db_tools($db);
	$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';

	// Prevent errors from stopping uninstallation
	$db->return_on_error = true;

	// uninstall the plugins
	$result = $db->sql_query('SELECT * FROM ' . BLOGS_PLUGINS_TABLE);
	while ($row = $db->sql_fetchrow($result))
	{
		include($blog_plugins_path . $row['plugin_name'] . '/uninstall.' . $phpEx);
	}

	// delete permissions
	$blog_permissions = array(
		'u_blogview',
		'u_blogpost',
		'u_blogedit',
		'u_blogdelete',
		'u_blognoapprove',
		'u_blogreport',
		'u_blogreply',
		'u_blogreplyedit',
		'u_blogreplydelete',
		'u_blogreplynoapprove',
		'u_blogbbcode',
		'u_blogsmilies',
		'u_blogimg',
		'u_blogurl',
		'u_blogflash',
		'u_blogmoderate',
		'u_blogattach',
		'u_blognolimitattach',
		'm_blogapprove',
		'm_blogedit',
		'm_bloglockedit',
		'm_blogdelete',
		'm_blogreport',
		'm_blogreplyapprove',
		'm_blogreplyedit',
		'm_blogreplylockedit',
		'm_blogreplydelete',
		'm_blogreplyreport',
		'a_blogmanage',
		'a_blogdelete',
		'a_blogreplydelete',

		'u_blog_vote',
		'u_blog_vote_change',
		'u_blog_create_poll',
		'u_blog_style',
		'u_blog_css',
	);
	foreach ($blog_permissions as $auth_option)
	{
		$db->sql_query('DELETE FROM ' . ACL_ROLES_DATA_TABLE . ' WHERE auth_option_id = (SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option = \'' . $auth_option . '\')');
		$db->sql_query('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE auth_option_id = (SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option = \'' . $auth_option . '\')');
		$db->sql_query('DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option = \'' . $auth_option . '\'');
	}

	$blog_config = array('user_blog_enable', 'user_blog_custom_profile_enable', 'user_blog_text_limit', 'user_blog_user_text_limit', 'user_blog_inform',
		'user_blog_always_show_blog_url', 'user_blog_subscription_enabled', 'user_blog_enable_zebra', 'user_blog_enable_feeds', 'user_blog_enable_plugins',
		'user_blog_seo', 'user_blog_guest_captcha', 'user_blog_user_permissions', 'user_blog_search', 'user_blog_search_type', 'user_blog_enable_ratings',
		'user_blog_min_rating', 'user_blog_max_rating', 'user_blog_enable_attachments', 'user_blog_max_attachments', 'num_blogs', 'num_blog_replies',
		'user_blog_quick_reply', 'user_blog_links_output_block', 'user_blog_message_from', 'user_blog_version');
	foreach ($blog_config as $config)
	{
		$db->sql_query('DELETE FROM ' . CONFIG_TABLE . ' WHERE config_name = \'' . $config . '\'');
	}

	// drop tables
	$blog_tables = array(BLOGS_TABLE, BLOGS_ATTACHMENT_TABLE, BLOGS_CATEGORIES_TABLE, BLOGS_IN_CATEGORIES_TABLE, BLOGS_PLUGINS_TABLE, BLOGS_POLL_OPTIONS_TABLE,
		BLOGS_POLL_VOTES_TABLE, BLOGS_RATINGS_TABLE, BLOGS_REPLY_TABLE, BLOGS_SUBSCRIPTION_TABLE, BLOGS_USERS_TABLE, BLOG_SEARCH_WORDLIST_TABLE,
		BLOG_SEARCH_WORDMATCH_TABLE);
	foreach ($blog_tables as $table)
	{
		$db->sql_query('DROP TABLE IF EXISTS ' . $table);
	}

	// remove columns
	$db_tool->sql_column_remove(USERS_TABLE, 'blog_count');
	$db_tool->sql_column_remove(EXTENSION_GROUPS_TABLE, 'allow_in_blog');

	// remove the modules
	$blog_modules = array('ACP_BLOG_SETTINGS', 'ACP_BLOG_PLUGINS', 'ACP_BLOG_SEARCH', 'ACP_BLOG_CATEGORIES', array('module_langname = \'ACP_EXTENSION_GROUPS\'', 'module_basename = \'blogs\''), 'ACP_BLOGS',
	'MCP_BLOG_REPORTED_BLOGS', 'MCP_BLOG_DISAPPROVED_BLOGS', 'MCP_BLOG_REPORTED_REPLIES', 'MCP_BLOG_DISAPPROVED_REPLIES', 'MCP_BLOG',
	'UCP_BLOG_SETTINGS', 'UCP_BLOG_TITLE_DESCRIPTION', 'UCP_BLOG_PERMISSIONS', 'UCP_BLOG');
	foreach ($blog_modules as $module)
	{
		if (!is_array($module))
		{
			$result = $db->sql_query('SELECT * FROM ' . MODULES_TABLE . ' WHERE module_langname = \'' . $module . '\'');
		}
		else
		{
			$result = $db->sql_query('SELECT * FROM ' . MODULES_TABLE . ' WHERE ' . implode(' AND ', $module));
		}
		while ($row = $db->sql_fetchrow($result))
		{
			$db->sql_query('UPDATE ' . MODULES_TABLE . ' SET left_id = left_id - 2 WHERE left_id >= ' . $row['left_id'] . ' AND module_class = \'' . $row['module_class'] . '\'');
			$db->sql_query('UPDATE ' . MODULES_TABLE . ' SET right_id = right_id - 2 WHERE right_id >= ' . $row['left_id'] . ' AND module_class = \'' . $row['module_class'] . '\'');
			$db->sql_query('DELETE FROM ' . MODULES_TABLE . ' WHERE module_id = ' . $row['module_id']);
		}
	}

	// Purge the cache and tell the user that we are finished.
	$cache->purge();

	trigger_error(sprintf($user->lang['UNINSTALL_BLOG_DB_SUCCESS'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx") . '">', '</a>'));
}
else
{
	confirm_box(false, 'UNINSTALL_BLOG_DB');
}

redirect(append_sid("{$phpbb_root_path}blog.$phpEx"));
?>