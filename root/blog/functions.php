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

// Make sure that if this file is accidently included more than once we don't get errors
if (!defined('BLOG_FUNCTIONS_INCLUDED'))
{
	define('BLOG_FUNCTIONS_INCLUDED', true);

	include($phpbb_root_path . 'blog/data/constants.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_categories.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_misc.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_permissions.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_rate.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_sql.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_subscription.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_url.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_view.' . $phpEx);

	/**
	* Setup the blog plugin system
	*/
	function setup_blog_plugins()
	{
		global $blog_plugins, $blog_plugins_path, $phpbb_root_path, $phpEx;

		if (!class_exists('blog_plugins'))
		{
			include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);
		}

		$blog_plugins = new blog_plugins();
		$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';
		$blog_plugins->load_plugins();
	}

	/**
	* Setup the blog search system
	*/
	function setup_blog_search()
	{
		global $config, $user, $phpbb_root_path, $phpEx;

		if (file_exists($phpbb_root_path . 'blog/search/' . $config['user_blog_search_type'] . '.' . $phpEx))
		{
			include($phpbb_root_path . 'blog/search/' . $config['user_blog_search_type'] . '.' . $phpEx);
			$class = 'blog_' . $config['user_blog_search_type'];
			return new $class();
		}
		else
		{
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
		global $cache, $auth, $user, $db, $blog_plugins;

		$blog_plugins->plugin_do('function_handle_blog_cache');

		if ($mode == 'blog' || (strpos($mode, 'blog') !== false))
		{
			$cache->destroy('sql', BLOGS_TABLE);

			if ($user_id === false)
			{
				$sql = 'SELECT user_id FROM ' . USERS_TABLE;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$cache->destroy('_blog_archive' . $row['user_id']);
					$cache->destroy('_blog_settings_' . $row['user_id']);
					$cache->destroy('_blog_subscription' . $row['user_id']);
				}
			}
			else
			{
				$cache->destroy('_blog_categories');
				$cache->destroy("_blog_archive{$user_id}");
				$cache->destroy('_blog_settings_' . $user_id);
				$cache->destroy("_blog_subscription{$user_id}");
				$cache->destroy("_blog_rating_{$user_id}");
			}
		}

		switch ($mode)
		{
			case 'new_blog' :
				if ($auth->acl_get('u_blognoapprove'))
				{
					$cache->destroy('all_blog_ids');
				}
				else
				{
					$cache->destroy('all_unapproved_blog_ids');
				}
			break;
			case 'approve_blog' :
				$cache->destroy('all_blog_ids');
			break;
			case 'delete_blog' :
				$cache->destroy('all_blog_ids');
			break;
			case 'blog' :
				$cache->destroy('all_blog_ids');
			break;
			case 'subscription' :
				$cache->destroy("_blog_subscription{$user_id}");
			break;
			case 'plugins' :
				$cache->destroy('_blog_plugins');
			default :
				$blog_plugins->plugin_do_arg('function_handle_blog_cache_mode', $mode);
		}
	}

	/**
	* Blog Meta Refresh (the normal one does not work with the SEO Url's)
	*/
	function blog_meta_refresh($time, $url, $instant = false)
	{
		global $config, $template, $user, $user_settings;

		if ($instant || (isset($user_settings[$user->data['user_id']]['instant_redirect']) && $user_settings[$user->data['user_id']]['instant_redirect']))
		{
			$time = 0;
			header('Location: ' . str_replace('&amp;', '&', $url));
		}

		if ($config['user_blog_seo'])
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '" />')
			);
		}
		else
		{
			meta_refresh($time, $url);
		}
	}
}
?>