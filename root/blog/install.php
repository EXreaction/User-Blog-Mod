<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$user_blog_version = '1.0.1_dev';

// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/blog/common', 'mods/blog/setup'));

if ($user->data['user_type'] != USER_FOUNDER)
{
	trigger_error('MUST_BE_FOUNDER');
}

if (isset($config['user_blog_version']))
{
	trigger_error(sprintf($user->lang['ALREADY_INSTALLED'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx") . '">', '</a>'));
}

if (version_compare(PHP_VERSION, '5.1.0') < 0)
{
	trigger_error('UPGRADE_PHP');
}

if (confirm_box(true))
{
	// This may help...
	@set_time_limit(120);

	$error = array();

	if (!isset($table_prefix))
	{
		include($phpbb_root_path . 'config.' . $phpEx);
		unset($dbpasswd, $dbuser, $dbname);
	}

	include($phpbb_root_path . 'blog/functions.' . $phpEx);

	include($phpbb_root_path . 'includes/functions_admin.' . $phpEx); // Needed for remove_comments function for some DB types
	include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
	include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
	include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/eami.' . $phpEx);
	$auth_admin = new auth_admin();
	$db_tool = new phpbb_db_tools($db);
	$dbmd = get_available_dbms($dbms);
	$eami = new eami();
	define('IN_BLOG_INSTALL', true);

	include("{$phpbb_root_path}blog/install/tables.$phpEx");
	include("{$phpbb_root_path}blog/install/modules.$phpEx");
	include("{$phpbb_root_path}blog/install/permissions.$phpEx");
	include("{$phpbb_root_path}blog/install/config.$phpEx");
	include("{$phpbb_root_path}blog/install/data.$phpEx");

	// Purge the cache and tell the user that we are finished.
	$cache->purge();

	if (sizeof($error))
	{
		trigger_error(sprintf($user->lang['INSTALL_BLOG_DB_FAIL'], implode('<br />', $error)));
	}
	else
	{
		trigger_error(sprintf($user->lang['INSTALL_BLOG_DB_SUCCESS'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx") . '">', '</a>'));
	}
}
else
{
	confirm_box(false, 'INSTALL_BLOG_DB');
}

redirect(append_sid("{$phpbb_root_path}blog.$phpEx"));
?>