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

if (!defined('BLOGS_TABLE'))
{
	if (!isset($phpbb_root_path) || !isset($phpEx))
	{
		global $phpbb_root_path, $phpEx;
	}

	if (!isset($table_prefix))
	{
		include($phpbb_root_path . 'config.' . $phpEx);
		unset($dbpasswd);
		unset($dbuser);
		unset($dbname);
	}

	define('BLOGS_TABLE',				$table_prefix . 'blogs');
	define('BLOGS_REPLY_TABLE',			$table_prefix . 'blogs_reply');
	define('BLOGS_SUBSCRIPTION_TABLE',	$table_prefix . 'blogs_subscription');
	define('BLOGS_PLUGINS_TABLE',		$table_prefix . 'blogs_plugins');
	define('BLOGS_USERS_TABLE',			$table_prefix . 'blogs_users');

	define('BLOG_SEARCH_WORDLIST_TABLE',	$table_prefix . 'blog_search_wordlist');
	define('BLOG_SEARCH_WORDMATCH_TABLE',	$table_prefix . 'blog_search_wordmatch');
	//define('BLOG_SEARCH_RESULTS_TABLE',		$table_prefix . 'blog_search_results');

	define('BLOGS_CATEGORIES_TABLE',	$table_prefix . 'blogs_categories');
	define('BLOGS_IN_CATEGORIES_TABLE',	$table_prefix . 'blogs_in_categories');
}
?>