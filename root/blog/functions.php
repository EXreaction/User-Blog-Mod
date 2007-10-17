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

// Make sure that if this file is accidently included more than once we don't get errors
if (!defined('BLOG_FUNCTIONS_INCLUDED'))
{
	define('BLOG_FUNCTIONS_INCLUDED', true);

	// Include the constants.php file
	if (!isset($phpbb_root_path) || !isset($phpEx))
	{
		global $phpbb_root_path, $phpEx;
	}
	include($phpbb_root_path . 'blog/data/constants.' . $phpEx);

	/**
	* URL handler
	*/
	function blog_url($user_id, $blog_id = false, $reply_id = false, $url_data = array(), $extra_data = array())
	{
		global $config, $phpbb_root_path, $phpEx, $user;
		global $blog_data, $user_data;

		if ($config['user_blog_seo'])
		{
			if ($user_id != false && !empty($user_data))
			{
				if (!array_key_exists($user_id, $user_data->user))
				{
					$user_data->get_user_data($user_id);
				}
				$username = utf8_clean_string($user_data->user[$user_id]['username']);
			}
			else if ($user_id != false && isset($extra_data['username']))
			{
				$username = utf8_clean_string($extra_data['username']);
			}
			else
			{
				$username = 'user';
			}

			$start = ((isset($url_data['start'])) ? '_s-' . $url_data['start'] : '');

			if (isset($url_data['page']))
			{
				if (isset($url_data['mode']))
				{
					$return = "{$phpbb_root_path}blog/{$url_data['page']}/m-{$url_data['mode']}" . (($blog_id) ? "_b-$blog_id" : '') . (($reply_id) ? "_r-$reply_id" : '') . '.html';
				}
				else
				{
					$return = "{$phpbb_root_path}blog/{$url_data['page']}/index.html";
				}
			}
			else if (isset($url_data['mode']))
			{
				$return = "{$phpbb_root_path}blog/{$username}/{$url_data['mode']}{$start}.html";
			}
			else if ($reply_id !== false)
			{
				$return = "{$phpbb_root_path}blog/{$username}/r-" . $reply_id . $start . '.html' . '#r' . $reply_id;
			}
			else if ($blog_id !== false)
			{
				if (!empty($blog_data) && array_key_exists($blog_id, $blog_data->blog))
				{
					$return = "{$phpbb_root_path}blog/{$username}/" . utf8_clean_string($blog_data->blog[$blog_id]['blog_subject']) . '_b-' . $blog_id . $start . '.html';
				}
				else if (array_key_exists('blog_subject', $extra_data))
				{
					$return = "{$phpbb_root_path}blog/{$username}/" . utf8_clean_string($extra_data['blog_subject']) . '_b-' . $blog_id . $start . '.html';
				}
				else
				{
					$return = "{$phpbb_root_path}blog/{$username}/b-" . $blog_id . $start . '.html';
				}
			}
			else if ($user_id !== false)
			{
				if ($start != '')
				{
					$return = "{$phpbb_root_path}blog/{$username}/u-" . $user_id . $start . '.html';
				}
				else
				{
					$return = "{$phpbb_root_path}blog/{$username}/index.html";
				}
			}
			else
			{
				$return = "{$phpbb_root_path}blog/index.html";
			}

			if (isset($return))
			{
				return $return;
			}
		}

		$extras = '';
		if (count($url_data))
		{
			foreach ($url_data as $name => $var)
			{
				// Do not add the blog/reply/user id to the url string, they got added already
				if ($name == 'b' || $name == 'u' || $name == 'r')
				{
					continue;
				}

				$extras .= '&amp;' . $name . '=' . $var;
			}

			$extras = substr($extras, 5);
		}

		$extras .= (($user_id) ? '&amp;u=' . $user_id : '');
		$extras .= (($blog_id) ? '&amp;b=' . $blog_id : '');
		$extras .= (($reply_id) ? '&amp;r=' . $reply_id . '#r' . $reply_id: '');
		$url = $phpbb_root_path . 'blog.' . $phpEx;
		return append_sid($url, $extras);
	}

	/**
	* generates the basic URL's used by this mod
	*/
	function generate_blog_urls()
	{
		global $phpbb_root_path, $phpEx, $config, $user;
		global $blog_id, $reply_id, $user_id, $start;
		global $blog_data, $reply_data, $user_data, $user_founder, $blog_urls, $blog_plugins;

		$self_data = array();
		foreach ($_GET as $name => $var)
		{
			$self_data[$name] = $var;
		}

		$blog_urls = array(
			'main'				=> blog_url(false),
			'self'				=> blog_url($user_id, $blog_id, $reply_id, $self_data),
			'self_print'		=> blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('view' => 'print'))),
			'subscribe'			=> ($config['user_blog_subscription_enabled'] && ($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != $user_id && $user->data['user_id'] != ANONYMOUS) ? blog_url($user_id, $blog_id, false, array('page' => 'subscribe')) : '',
			'unsubscribe'		=> ($config['user_blog_subscription_enabled'] && ($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != $user_id && $user->data['user_id'] != ANONYMOUS) ? blog_url($user_id, $blog_id, false, array('page' => 'unsubscribe')) : '',

			'add_blog'			=> blog_url(false, false, false, array('page' => 'blog', 'mode' => 'add')),
			'add_reply'			=> ($blog_id) ? blog_url($user_id, $blog_id, false, array('page' => 'reply', 'mode' => 'add')) : '',

			'view_blog'			=> ($blog_id != 0) ? blog_url($user_id, $blog_id) : '',
			'view_reply'		=> ($reply_id != 0) ? blog_url($user_id, $blog_id, $reply_id) : '',
			'view_user'			=> ($user_id != 0) ? blog_url($user_id) : false,
			'view_user_deleted'	=> ($user_id != 0) ? blog_url($user_id, false, false, array('mode' => 'deleted')) : false,
			'view_user_self'	=> blog_url($user->data['user_id']),
		);

		if (isset($self_data['start']))
		{
			unset($self_data['start']);
		}
		$blog_urls['self_minus_start'] = blog_url($user_id, $blog_id, $reply_id, $self_data);
		$blog_urls['start_zero'] = blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('start' => '*start*')));

		$blog_plugins->plugin_do_arg('function_generate_blog_urls', $blog_urls);
	}

	/**
	* Create the breadcrumbs
	*
	* @param string $crumb_lang The last language option in the breadcrumbs
	*/
	function generate_blog_breadcrumbs($crumb_lang = '')
	{
		global $template, $user;
		global $page, $username, $blog_id, $reply_id;
		global $blog_data, $reply_data, $user_data, $user_founder, $blog_urls;

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'		=> $user->lang['USER_BLOGS'],
			'U_VIEW_FORUM'		=> $blog_urls['main'],
		));

		if ($username != '')
		{
			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'		=> sprintf($user->lang['USERNAMES_BLOGS'], $username),
				'U_VIEW_FORUM'		=> $blog_urls['view_user'],
			));

			if ($blog_id != 0)
			{
				$template->assign_block_vars('navlinks', array(
					'FORUM_NAME'		=> censor_text($blog_data->blog[$blog_id]['blog_subject']),
					'U_VIEW_FORUM'		=> $blog_urls['view_blog'],
				));

				if ($reply_id != 0 && $page == 'reply')
				{
					$template->assign_block_vars('navlinks', array(
						'FORUM_NAME'		=> (censor_text($reply_data->reply[$reply_id]['reply_subject']) != '') ? censor_text($reply_data->reply[$reply_id]['reply_subject']) : $user->lang['UNTITLED_REPLY'],
						'U_VIEW_FORUM'		=> $blog_urls['view_reply'],
					));
				}
			}
		}

		if ($crumb_lang != '')
		{
			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'		=> $crumb_lang,
				'U_VIEW_FORUM'		=> $blog_urls['self'],
			));
		}
	}

	/**
	* Gets Zebra (friend/foe)  info
	*
	* Just grabs the foe info right now.  No reason to grab the friend info ATM.
	*
	* @param int|bool $uid The user_id we will grab the zebra data for.  If this is false we will use $user->data['user_id']
	*/
	function get_zebra_info($uid = false)
	{
		global $config, $user, $db;
		global $foe_list;

		if ($config['user_blog_enable_zebra'] && $user->data['user_id'] != ANONYMOUS)
		{
			$uid = ($uid !== false) ? $uid : $user->data['user_id'];

			$sql = 'SELECT zebra_id FROM ' . ZEBRA_TABLE . '
				WHERE user_id = \'' . $uid . '\'
					AND foe = \'1\'';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$foe_list[] = $row['zebra_id'];
			}
			$db->sql_freeresult($result);
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
		global $cache, $auth, $user, $db, $blog_plugins, $user_founder;

		$blog_plugins->plugin_do('function_handle_blog_cache');

		if (strpos($mode, 'blog'))
		{
			$cache->destroy('sql', BLOGS_TABLE);
			if ($user_id === false)
			{
				$sql = 'SELECT user_id FROM ' . USERS_TABLE;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$cache->destroy('_blog_archive' . $row['user_id']);
					$cache->destroy('_blog_subscription' . $row['user_id']);
				}
			}
			else
			{
				$cache->destroy("_blog_archive{$user_id}");
				$cache->destroy("_blog_subscription{$user_id}");
			}
		}

		switch ($mode)
		{
			case 'new_blog' :
				if ($auth->acl_get('u_blognoapprove') || $user_founder)
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
				$cache->destroy('all_unapproved_blog_ids');
			break;
			case 'delete_blog' :
				$cache->destroy('all_blog_ids');
				$cache->destroy('all_deleted_blog_ids');
			break;
			case 'blog' :
				$cache->destroy('all_blog_ids');
				$cache->destroy('all_unapproved_blog_ids');
				$cache->destroy('all_deleted_blog_ids');
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
	* Get subscription info
	*
	* Grabs subscription info from the DB if not already in the cache and finds out if the user is subscribed to the blog/user.
	*
	* @param int|bool $blog_id The blog_id to check, set to false if we are checking a user_id.
	* @param int|bool $user_id The user_id to check, set to false if we are checking a blog_id.
	*
	* @return Returns true if the user is subscribed to the blog or user, false if not.
	*/
	function get_subscription_info($blog_id, $user_id = false)
	{
		global $db, $user, $cache, $config, $blog_plugins;

		if (!$config['user_blog_subscription_enabled'])
		{
			return false;
		}

		// attempt to get the data from the cache
		$subscription_data = $cache->get('_blog_subscription' . $user->data['user_id']);

		// grab data from the db if it isn't cached
		if ($subscription_data === false)
		{
			$sql = 'SELECT * FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
					WHERE sub_user_id = \'' . $user->data['user_id'] . '\'';
			$result = $db->sql_query($sql);
			$subscription_data = $db->sql_fetchrowset($result);
			$cache->put('_blog_subscription' . $user->data['user_id'], $subscription_data);
		}

		if (count($subscription_data))
		{
			$blog_plugins->plugin_do('function_get_subscription_info');

			if ($user_id !== false)
			{
				foreach ($subscription_data as $row)
				{
					if ($row['user_id'] == $user_id)
					{
						unset($subscription_data);
						return true;
					}
				}
			}
			else if ($blog_id !== false)
			{
				foreach ($subscription_data as $row)
				{
					if ($row['blog_id'] == $blog_id)
					{
						unset($subscription_data);
						return true;
					}
				}
			}
		}

		unset($subscription_data);
		return false;
	}

	/**
	* Add the links in the custom profile fields to view the users' blog
	*
	* @param int $user_id The users id.
	* @param string $block The name of the custom profile block we insert it into
	* @param mixed $user_data Extra data on the user.  If blog_count is supplied in $user_data we can skip 1 sql query (if $grab_from_db is true)
	* @param bool $grab_from_db If it is true we will run the query to find out how many blogs the user has if the data isn't supplied in $user_data, otherwise we won't and just display the link alone.
	* @param bool $force_output is if you would like to force the output of the links for the single requested section
	*/
	function add_blog_links($user_id, $block, $user_data = false, $grab_from_db = false, $force_output = false)
	{
		global $db, $template, $user, $phpbb_root_path, $phpEx, $config;

		// check if the User Blog Mod is enabled, and if the user is anonymous
		if (!$config['user_blog_enable'] || $user_id == ANONYMOUS)
		{
			return;
		}

		if (!isset($user->lang['BLOG']))
		{
			$user->add_lang('mods/blog');
		}

		// if they are not an anon user, and they blog_count row isn't set grab that data from the db.
		if ($user_id > 1 && (!isset($user_data['blog_count']) || !isset($user_data['username'])) && $grab_from_db)
		{
			$sql = 'SELECT username, blog_count FROM ' . USERS_TABLE . ' WHERE user_id = \'' . intval($user_id) . '\'';
			$result = $db->sql_query($sql);
			$user_data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}
		else if (!isset($user_data['blog_count']))
		{
			$user_data['blog_count'] = -1;
		}
		
		if ($user_data['blog_count'] > 0 || (($config['user_blog_always_show_blog_url'] || $force_output) && $user_data['blog_count'] >= 0))
		{
			$template->assign_block_vars($block, array(
				'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
				'PROFILE_FIELD_VALUE'		=> '<a href="' . blog_url($user_id, false, false, array(), array('username' => $user_data['username'])) . '">' . $user->lang['VIEW_BLOGS'] . ' (' .$user_data['blog_count'] . ')</a>',
			));
		}
		else if (!$grab_from_db && $user_data['blog_count'] == -1)
		{
			$template->assign_block_vars($block, array(
				'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
				'PROFILE_FIELD_VALUE'		=> '<a href="' . blog_url($user_id, false, false, array(), array('username' => $user_data['username'])) . '">' . $user->lang['VIEW_BLOGS'] . '</a>',
			));
		}
	}

	/**
	* Pagination routine, generates page number sequence
	* tpl_prefix is for using different pagination blocks at one page
	*/
	function generate_blog_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = false, $tpl_prefix = '')
	{
		global $config, $template, $user;

		if (strpos($base_url, '#'))
		{
			$base_url = substr($base_url, 0, strpos($base_url, '#'));
		}

		if ($config['user_blog_seo'])
		{
			// Make sure $per_page is a valid value
			$per_page = ($per_page <= 0) ? 1 : $per_page;

			$seperator = '<span class="page-sep">' . $user->lang['COMMA_SEPARATOR'] . '</span>';
			$total_pages = ceil($num_items / $per_page);

			if ($total_pages == 1 || !$num_items)
			{
				return false;
			}

			$on_page = floor($start_item / $per_page) + 1;
			$page_string = ($on_page == 1) ? '<strong>1</strong>' : '<a href="' . str_replace('*start*', '0', $base_url) . '">1</a>';

			if ($total_pages > 5)
			{
				$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
				$end_cnt = max(min($total_pages, $on_page + 4), 6);

				$page_string .= ($start_cnt > 1) ? ' ... ' : $seperator;

				for ($i = $start_cnt + 1; $i < $end_cnt; $i++)
				{
					$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . str_replace('*start*', (($i - 1) * $per_page), $base_url) . '">' . $i . '</a>';
					if ($i < $end_cnt - 1)
					{
						$page_string .= $seperator;
					}
				}

				$page_string .= ($end_cnt < $total_pages) ? ' ... ' : $seperator;
			}
			else
			{
				$page_string .= $seperator;

				for ($i = 2; $i < $total_pages; $i++)
				{
					$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . str_replace('*start*', (($i - 1) * $per_page), $base_url) . '">' . $i . '</a>';
					if ($i < $total_pages)
					{
						$page_string .= $seperator;
					}
				}
			}

			$page_string .= ($on_page == $total_pages) ? '<strong>' . $total_pages . '</strong>' : '<a href="' . str_replace('*start*', (($i - 1) * $per_page), $base_url) . '">' . $total_pages . '</a>';

			if ($add_prevnext_text)
			{
				if ($on_page != 1) 
				{
					$page_string = '<a href="' . str_replace('*start*', (($on_page - 2) * $per_page), $base_url) . '">' . $user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
				}

				if ($on_page != $total_pages)
				{
					$page_string .= '&nbsp;&nbsp;<a href="' . str_replace('*start*', ($on_page * $per_page), $base_url) . '">' . $user->lang['NEXT'] . '</a>';
				}
			}

			$template->assign_vars(array(
				$tpl_prefix . 'BASE_URL'	=> $base_url,
				$tpl_prefix . 'PER_PAGE'	=> $per_page,

				$tpl_prefix . 'PREVIOUS_PAGE'	=> ($on_page == 1) ? '' : str_replace('*start*', (($on_page - 2) * $per_page), $base_url),
				$tpl_prefix . 'NEXT_PAGE'		=> ($on_page == $total_pages) ? '' : str_replace('*start*', ($on_page  * $per_page), $base_url),
				$tpl_prefix . 'TOTAL_PAGES'		=> $total_pages)
			);

			return $page_string;
		}
		else
		{
			return generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text, $tpl_prefix);
		}
	}

	/**
	* Blog Meta Refresh (the normal one does not work with the SEO Url's
	*/
	function blog_meta_refresh($time, $url)
	{
		global $config, $template;

		if ($config['user_blog_seo'])
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="' . $time . ';url=' . str_replace('&', '&amp;', $url) . '" />')
			);
		}
		else
		{
			meta_refresh($time, $url);
		}
	}
}
?>