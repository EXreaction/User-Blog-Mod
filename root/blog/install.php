<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

if (isset($config['user_blog_version']))
{
	trigger_error(sprintf($user->lang['ALREADY_INSTALLED'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx") . '">', '</a>'));
}

if (version_compare(PHP_VERSION, '5.1.0') < 0)
{
	trigger_error('You are running an unsupported PHP version. Please upgrade to PHP 5.1.0 or higher.');
}

if (confirm_box(true))
{
	$error = array();

	if (!isset($table_prefix))
	{
		include($phpbb_root_path . 'config.' . $phpEx);
		unset($dbpasswd, $dbuser, $dbname);
	}

	include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/db_tools.' . $phpEx);
	include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/eami.' . $phpEx);
	$auth_admin = new auth_admin();
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

	if (count($error))
	{
		trigger_error(sprintf($user->lang['INSTALL_BLOG_DB_FAIL'], implode('<br/>', $error)));
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