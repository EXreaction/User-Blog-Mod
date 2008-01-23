<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Setup the blog search system
*/
function setup_blog_search()
{
	global $config, $phpbb_root_path, $phpEx, $template;

	if (file_exists($phpbb_root_path . 'blog/search/' . $config['user_blog_search_type'] . '.' . $phpEx))
	{
		include($phpbb_root_path . 'blog/search/' . $config['user_blog_search_type'] . '.' . $phpEx);
		$class = 'blog_' . $config['user_blog_search_type'];
		return new $class();
	}
	else
	{
		$template->set_template();
		trigger_error('BLOG_SEARCH_BACKEND_NOT_EXIST');
	}
}

/**
* Handles updates to the cache
*
* @param string $mode
* @param int $user_id
*/
function handle_blog_cache($mode, $user_id = 0)
{
	global $cache;

	$temp = compact('mode', 'user_id');
	blog_plugins::plugin_do_arg('function_handle_blog_cache', $temp);

	if (!$mode && $user_id)
	{
		$cache->destroy("_blog_settings_{$user_id}");
		$cache->destroy("_blog_subscription_{$user_id}");
		$cache->destroy("_blog_rating_{$user_id}");
	}

	switch ($mode)
	{
/*			Not currently used
		case 'new_blog' :
		case 'approve_blog' :
		case 'report_blog' :
		case 'delete_blog' :
		case 'undelete_blog' :
		case 'new_reply' :
		case 'approve_reply' :
		case 'report_reply' :
		case 'delete_reply' :
		case 'undelete_reply' :
*/
		case 'plugins' :
			$cache->destroy('_blog_plugins');
		break;
		case 'extensions' :
			$cache->destroy('_blog_extensions');
		break;
		case 'categories' :
			$cache->destroy('_blog_categories');
		break;
		default :
			blog_plugins::plugin_do_arg('function_handle_blog_cache_mode', $mode);
	}
}

/**
* Blog Meta Refresh (the normal one does not work with the SEO Url's)
*/
function blog_meta_refresh($time, $url)
{
	global $template, $user, $user_settings;

	if ($time == 0 || (isset($user_settings[$user->data['user_id']]['instant_redirect']) && $user_settings[$user->data['user_id']]['instant_redirect']))
	{
		$time = 0;
		header('Location: ' . str_replace('&amp;', '&', $url));
	}

	$template->assign_vars(array(
		'META' => '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '" />')
	);
}
?>