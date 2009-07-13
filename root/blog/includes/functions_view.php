<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: functions_view.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Get Attachment Data
*
* Grabs attachment data for blogs and replies.
*
* @param int|array $blog_ids An array of blog_ids to look up
* @param int|array|bool $reply_ids An array of reply_ids to look up
*/
function get_attachment_data($blog_ids, $reply_ids = false)
{
	global $auth, $config, $db;

	if (!$config['user_blog_enable_attachments'] || !$auth->acl_get('u_download'))
	{
		return;
	}

	if (!is_array($blog_ids))
	{
		$blog_ids = array($blog_ids);
	}

	if (!is_array($reply_ids) && $reply_ids !== false)
	{
		$reply_ids = array($reply_ids);
	}

	$temp = compact('blog_ids', 'reply_ids');
	blog_plugins::plugin_do_ref('function_get_attachment_data', $temp);
	extract($temp);

	$reply_sql = ($reply_ids !== false) ? ' OR ' . $db->sql_in_set('reply_id', $reply_ids) : '';

	$sql = 'SELECT * FROM ' . BLOGS_ATTACHMENT_TABLE . '
		WHERE ' . $db->sql_in_set('blog_id', $blog_ids) .
			$reply_sql . '
				ORDER BY attach_id DESC';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['reply_id'] != 0)
		{
			blog_data::$reply[$row['reply_id']]['attachment_data'][] = $row;
		}
		else if ($row['blog_id'] != 0)
		{
			blog_data::$blog[$row['blog_id']]['attachment_data'][] = $row;
		}
	}
	$db->sql_freeresult($result);
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
	global $db, $user, $cache, $config;

	if (!$config['user_blog_subscription_enabled'])
	{
		return false;
	}

	// attempt to get the data from the cache
	$subscription_data = $cache->get('_blog_subscription_' . $user->data['user_id']);

	// grab data from the db if it isn't cached
	if ($subscription_data === false)
	{
		$sql = 'SELECT * FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
				WHERE sub_user_id = ' . $user->data['user_id'];
		$result = $db->sql_query($sql);
		$subscription_data = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		$cache->put('_blog_subscription_' . $user->data['user_id'], $subscription_data);
	}

	if (sizeof($subscription_data))
	{
		blog_plugins::plugin_do_arg('function_get_subscription_info', $subscription_data);

		if ($user_id)
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
		else if ($blog_id)
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
* Gets Zebra (friend/foe)  info
*
* @param int|bool $uid The user_id we will grab the zebra data for.  If this is false we will use $user->data['user_id']
*/
function get_zebra_info($user_ids, $reverse_lookup = false)
{
	global $config, $db, $zebra_list, $reverse_zebra_list;

	if (!isset($config['user_blog_enable_zebra']) || !$config['user_blog_enable_zebra'])
	{
		return;
	}

	blog_plugins::plugin_do('function_get_zebra_info', compact('user_ids', 'reverse_lookup'));

	$to_query = array();

	if (!is_array($user_ids))
	{
		$user_ids = array($user_ids);
	}

	if (!$reverse_lookup)
	{
		foreach ($user_ids as $user_id)
		{
			if (!is_array($zebra_list) || ($user_id && !array_key_exists($user_id, $zebra_list)))
			{
				$to_query[] = $user_id;
			}
		}

		if (!sizeof($to_query))
		{
			return;
		}
	}
	else
	{
		foreach ($user_ids as $user_id)
		{
			if (!is_array($reverse_zebra_list) || !array_key_exists($user_id, $reverse_zebra_list))
			{
				$to_query[] = $user_id;
			}
		}

		if (!sizeof($to_query))
		{
			return;
		}
	}

	$sql = 'SELECT * FROM ' . ZEBRA_TABLE . '
		WHERE ' . $db->sql_in_set((($reverse_lookup) ? 'zebra_id' : 'user_id'), $to_query);
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		if ($reverse_lookup)
		{
			if ($row['foe'])
			{
				$reverse_zebra_list[$row['zebra_id']]['foe'][] = $row['user_id'];
				$zebra_list[$row['user_id']]['foe'][] = $row['zebra_id'];
			}
			else if ($row['friend'])
			{
				$reverse_zebra_list[$row['zebra_id']]['friend'][] = $row['user_id'];
				$zebra_list[$row['user_id']]['friend'][] = $row['zebra_id'];
			}
		}
		else
		{
			if ($row['foe'])
			{
				$zebra_list[$row['user_id']]['foe'][] = $row['zebra_id'];
			}
			else if ($row['friend'])
			{
				$zebra_list[$row['user_id']]['friend'][] = $row['zebra_id'];
			}
		}
	}
	$db->sql_freeresult($result);
}

/**
* Pagination routine, generates page number sequence
* tpl_prefix is for using different pagination blocks at one page
*/
function generate_blog_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = false, $tpl_prefix = '')
{
	global $config, $template, $user;

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

	$page_string .= ($on_page == $total_pages) ? '<strong>' . $total_pages . '</strong>' : '<a href="' . str_replace('*start*', (($total_pages - 1) * $per_page), $base_url) . '">' . $total_pages . '</a>';

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

/**
* Add the links in the custom profile fields to view the users' blog
*
* @param int $user_id The users id.
* @param string $block The name of the custom profile block we insert it into
* @param mixed $user_data Extra data on the user.  If blog_count is supplied in $user_data we can skip 1 sql query (if $grab_from_db is true)
* @param bool $grab_from_db If it is true we will run the query to find out how many blogs the user has if the data isn't supplied in $user_data, otherwise we won't and just display the link alone.
* @param bool $force_output is if you would like to force the output of the links for the single requested section
* @param bool $return Set to true to return an array with the data in it instead of outputting it
*/
function add_blog_links($user_id, $block, $user_data = false, $grab_from_db = false, $force_output = false, $return = false)
{
	global $db, $template, $user, $phpbb_root_path, $phpEx, $config;
	global $reverse_zebra_list, $user_settings;

	if ($user_id == ANONYMOUS)
	{
		return;
	}

	// If the $user_settings are set we check to make sure they have permission first
	if (isset($user_settings[$user_id]))
	{
		$allowed = true;
		if ($user->data['user_id'] == ANONYMOUS)
		{
			if ($user_settings[$user_id]['perm_guest'] == 0)
			{
				$allowed = false;
			}
		}
		else
		{
			if ($config['user_blog_enable_zebra'])
			{
				if (isset($reverse_zebra_list[$user->data['user_id']]['foe']) && in_array($user_id, $reverse_zebra_list[$user->data['user_id']]['foe']))
				{
					if ($user_settings[$user_id]['perm_foe'] == 0)
					{
						$allowed = false;
					}
				}
				else if (isset($reverse_zebra_list[$user->data['user_id']]['friend']) && in_array($user_id, $reverse_zebra_list[$user->data['user_id']]['friend']))
				{
					if ($user_settings[$user_id]['perm_friend'] == 0)
					{
						$allowed = false;
					}
				}
				else
				{
					if ($user_settings[$user_id]['perm_registered'] == 0)
					{
						$allowed = false;
					}
				}
			}
			else if ($user_settings[$user_id]['perm_registered'] == 0)
			{
				$allowed = false;
			}
		}

		// If they are not allowed we only show them the link with a 0 count for blogs if it is always on
		if (!$allowed)
		{
			if ($config['user_blog_always_show_blog_url'] || $force_output)
			{
				$url = ((isset($user_data['username'])) ? blog_url($user_id, false, false, array('c' => '*skip*'), array('username' => $user_data['username'])) : blog_url($user_id, false, false, array('c' => '*skip*')));
				$data = array(
					'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
					'PROFILE_FIELD_VALUE'		=> '<a href="' . $url . '">' . $user->lang['VIEW_BLOG'] . ' (0)</a>',
					'S_FIELD_VT'				=> true, // For compatibility with the Select viewable Custom Profiles Mod
					'S_FIELD_VP'				=> true, // For compatibility with the Select viewable Custom Profiles Mod
				);
				if ($return)
				{
					return $data;
				}

				if ($config['user_blog_links_output_block'])
				{
					$template->assign_block_vars($block, $data);
				}
				if (!substr($block, 0, strpos($block, '.')))
				{
					$template->assign_vars(array('PROFILE_BLOG' => $data['PROFILE_FIELD_VALUE'], 'U_VIEW_BLOG' => $url, 'BLOG_COUNT' => 0));
				}
				else
				{
					$template->alter_block_array(substr($block, 0, strpos($block, '.')), array('PROFILE_BLOG' => $data['PROFILE_FIELD_VALUE'], 'U_VIEW_BLOG' => $url, 'BLOG_COUNT' => 0), true, 'change');
				}
			}
			return;
		}
	}

	// if the blog_count or username isn't set, grab that data from the db.
	if (!isset($user_data['blog_count']) || !isset($user_data['username']))
	{
		$user_data['blog_count'] = -1;
		if ($grab_from_db)
		{
			$sql = 'SELECT username, blog_count FROM ' . USERS_TABLE . ' WHERE user_id = ' . intval($user_id);
			$result = $db->sql_query($sql);
			$user_data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}
	}

	if ($user_data['blog_count'] < 1 && !($config['user_blog_always_show_blog_url'] || $force_output))
	{
		return;
	}

	$url = ((isset($user_data['username'])) ? blog_url($user_id, false, false, array('c' => '*skip*'), array('username' => $user_data['username'])) : blog_url($user_id, false, false, array('c' => '*skip*')));
	$data = array(
		'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
		'PROFILE_FIELD_VALUE'		=> '<a href="' . $url . '">' . $user->lang['VIEW_BLOG'] . (($user_data['blog_count'] != -1) ? ' (' . $user_data['blog_count'] . ')</a>' : '</a>'),
		'S_FIELD_VT'				=> true, // For compatibility with the Select viewable Custom Profiles Mod
		'S_FIELD_VP'				=> true, // For compatibility with the Select viewable Custom Profiles Mod
	);
	if ($return)
	{
		return $data;
	}

	if ($config['user_blog_links_output_block'])
	{
		$template->assign_block_vars($block, $data);
	}
	if (!substr($block, 0, strpos($block, '.')))
	{
		$template->assign_vars(array('PROFILE_BLOG' => $data['PROFILE_FIELD_VALUE'], 'U_VIEW_BLOG' => $url, 'BLOG_COUNT' => (($user_data['blog_count'] != -1) ? $user_data['blog_count'] : '')));
	}
	else
	{
		$template->alter_block_array(substr($block, 0, strpos($block, '.')), array('PROFILE_BLOG' => $data['PROFILE_FIELD_VALUE'], 'U_VIEW_BLOG' => $url, 'BLOG_COUNT' => (($user_data['blog_count'] != -1) ? $user_data['blog_count'] : '')), true, 'change');
	}
}

/**
* Create the breadcrumbs
*
* @param string $crumb_lang The last language option in the breadcrumbs
* @param string $crumb_url The last url option in the breadcrumbs (this will be set to the current URL if this is blank)
*/
function generate_blog_breadcrumbs($crumb_lang = '', $crumb_url = '')
{
	global $template, $user;
	global $page, $username, $blog_id, $reply_id;
	global $blog_urls, $category_id;

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'		=> $user->lang['BLOGS'],
		'U_VIEW_FORUM'		=> $blog_urls['main'],
	));

	if ($category_id || $username)
	{
		if ($category_id)
		{
			$category_nav = get_category_branch($category_id, 'parents', 'descending');
			foreach ($category_nav as $row)
			{
				$template->assign_block_vars('navlinks', array(
					'FORUM_NAME'		=> $row['category_name'],
					'U_VIEW_FORUM'		=> blog_url(false, false, false, array('page' => $row['category_name'], 'c' => $row['category_id'])),
				));
			}
		}
		else if ($username != '')
		{
			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'		=> ($user->data['username'] == $username) ? $user->lang['MY_BLOG'] : sprintf($user->lang['USERNAMES_BLOGS'], $username),
				'U_VIEW_FORUM'		=> $blog_urls['view_user'],
			));
		}

		if ($blog_id != 0)
		{
			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'		=> censor_text(blog_data::$blog[$blog_id]['blog_subject']),
				'U_VIEW_FORUM'		=> $blog_urls['view_blog'],
			));

			if ($reply_id != 0 && $page == 'reply')
			{
				$c_text = censor_text(blog_data::$reply[$reply_id]['reply_subject']);

				if ($c_text)
				{
					$template->assign_block_vars('navlinks', array(
						'FORUM_NAME'		=> $c_text,
						'U_VIEW_FORUM'		=> $blog_urls['view_reply'],
					));
				}
			}
		}
	}

	if ($crumb_lang != '')
	{
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'		=> $crumb_lang,
			'U_VIEW_FORUM'		=> ($crumb_url) ? $crumb_url : $blog_urls['self'],
		));
	}
}

/**
* Generates the left side menu
*
* @param int $user_id If we are building it for a certain user, send the uid here
*/
function generate_menu($user_id = false)
{
	global $auth, $config, $db, $template, $blog_data, $user, $phpbb_root_path, $phpEx;

	$extra = $user_menu_extra = '';
	$stats = ($user_id) ? array() : array(
		$user->lang['TOTAL_NUMBER_OF_BLOGS'] => $config['num_blogs'],
		$user->lang['TOTAL_NUMBER_OF_REPLIES'] => $config['num_blog_replies'],
	);

	$links = array();
	if (!$user_id)
	{
		if ($auth->acl_get('u_blogpost'))
		{
			$links[] = array(
				'URL'		=> blog_url($user->data['user_id']),
				'NAME'		=> $user->lang['MY_BLOG'],
				'IMG'		=> 'icon_mini_profile.gif',
				'CLASS'		=> 'icon-ucp',
				'auth'		=> ($auth->acl_get('u_blogpost')) ? true : false,
			);

			if ($user->data['is_registered'])
			{
				$links[] = array(
					'URL'		=>append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=blog'),
					'NAME'		=> $user->lang['BLOG_CONTROL_PANEL'],
					'IMG'		=> 'icon_mini_register.gif',
					'CLASS'		=> 'icon-register',
				);
			}
		}

		if (sizeof($links))
		{
			$links[] = array('URL' => 'spacer', 'NAME' => 'spacer');
		}

		$links[] = array(
			'URL'		=> blog_url(false, false, false, array('mode' => 'recent_blogs')),
			'NAME'		=> $user->lang['RECENT_BLOGS'],
			'IMG'		=> 'icon_mini_groups.gif',
			'CLASS'		=> 'icon-bump',
		);
		$links[] = array(
			'URL'		=> blog_url(false, false, false, array('mode' => 'random_blogs')),
			'NAME'		=> $user->lang['RANDOM_BLOGS'],
			'IMG'		=> 'icon_mini_message.gif',
			'CLASS'		=> 'icon-bookmark',
		);
		$links[] = array(
			'URL'		=> blog_url(false, false, false, array('mode' => 'popular_blogs')),
			'NAME'		=> $user->lang['POPULAR_BLOGS'],
			'IMG'		=> 'icon_mini_members.gif',
			'CLASS'		=> 'icon-members',
		);
	}

	$temp = compact('user_id', 'user_menu_extra', 'extra', 'stats', 'links');
	blog_plugins::plugin_do_ref('function_generate_menu', $temp);
	extract($temp);

	if ($user_id)
	{
		$userdata = $blog_data->handle_user_data($user_id);
		$template->assign_vars($userdata);

		foreach ($userdata['custom_fields'] as $fields)
		{
			$template->assign_block_vars('custom_fields', $fields);
		}

		$template->assign_vars(array(
			'S_USER_BLOG_MENU'	=> true,
			'USER_MENU_EXTRA'	=> $user_menu_extra,
		));
	}
	else
	{

		$template->assign_vars(array(
			'S_MAIN_BLOG_MENU'		=> true,
			'MENU_EXTRA'			=> $extra,
		));
	}

	if (sizeof($links))
	{
		$template->assign_vars(array('S_BLOG_LINKS' => true));

		foreach ($links as $data)
		{
			if (!isset($data['auth']) || $data['auth'])
			{
				$template->assign_block_vars('left_blog_links', $data);
			}
		}
	}

	if (sizeof($stats))
	{
		$template->assign_vars(array('S_BLOG_STATS' => true));

		foreach ($stats as $name => $value)
		{
			$template->assign_block_vars('stats', array(
				'NAME'		=> $name,
				'VALUE'		=> $value,
			));
		}
	}

	if ($config['user_blog_search'] && !$user->data['is_bot'])
	{
		$template->assign_vars(array(
			'S_DISPLAY_BLOG_SEARCH'	=> true,
			'U_BLOG_SEARCH'			=> blog_url(false, false, false, array('page' => 'search')),
		));
	}
}

/**
* BBCode-safe truncating of text
*
* From: http://www.phpbb.com/community/viewtopic.php?f=71&t=670335
* Slightly modified to trim at either the first found end line or space
*
* @param string $text Text containing BBCode tags to be truncated
* @param string $uid BBCode uid
* @param int $max_length Text length limit
* @param string $bitfield BBCode bitfield (optional)
* @param bool $enable_bbcode Whether BBCode is enabled (true by default)
* @return string
*/
function trim_text($text, $uid, $max_length, $bitfield = '', $enable_bbcode = true)
{
	// If there is any custom BBCode that can have space in its argument, turn this on,
	// but else I suggest turning this off as it adds one additional (cache) SQL query
	$check_custom_bbcodes = true;

	if ($enable_bbcode && $check_custom_bbcodes)
	{
		global $db;
		static $custom_bbcodes = array();

		// Get all custom bbcodes
		if (empty($custom_bbcodes))
		{
			$sql = 'SELECT bbcode_id, bbcode_tag
			FROM ' . BBCODES_TABLE;
			$result = $db->sql_query($sql, 108000);

			while ($row = $db->sql_fetchrow($result))
			{
				// There can be problems only with tags having an argument
				if (substr($row['bbcode_tag'], -1, 1) == '=')
				{
					$custom_bbcodes[$row['bbcode_id']] = array('[' . $row['bbcode_tag'], ':' . $uid . ']');
				}
			}
			$db->sql_freeresult($result);
		}
	}

	// First truncate the text
	if (utf8_strlen($text) > $max_length)
	{
		$next_space = strpos(substr($text, $max_length), ' ');
		$next_el = strpos(substr($text, $max_length), "\n");
		if ($next_space !== false)
		{
			if ($next_el !== false)
			{
				$max_length = ($next_space < $next_el) ? $next_space + $max_length : $next_el + $max_length;
			}
			else
			{
				$max_length = $next_space + $max_length;
			}
		}
		else if ($next_el !== false)
		{
			$max_length = $next_el + $max_length;
		}
		else
		{
			$max_length = utf8_strlen($text);
		}

		$text = utf8_substr($text, 0, $max_length);

		// Append three dots indicating that this is not the real end of the text
		$text .= '...';

		if (!$enable_bbcode)
		{
			return $text;
		}
	}
	else
	{
		return $text;
	}

	// Some tags may contain spaces inside the tags themselves.
	// If there is any tag that had been started but not ended
	// cut the string off before it begins and add three dots
	// to the end of the text again as this has been just cut off too.
	$unsafe_tags = array(
		array('<', '>'),
		array('[quote=&quot;', "&quot;:$uid]"),
	);

	// If bitfield is given only check for tags that are surely existing in the text
	if (!empty($bitfield))
	{
		// Get all used tags
		$bitfield = new bitfield($bitfield);
		$bbcodes_set = $bitfield->get_all_set();

		// Add custom BBCodes having a parameter and being used
		// to the array of potential tags that can be cut apart.
		foreach ($custom_bbcodes as $bbcode_id => $bbcode_name)
		{
			if (in_array($bbcode_id, $bbcodes_set))
			{
				$unsafe_tags[] = $bbcode_name;
			}
		}
	}
	// Do the check for all possible tags
	else
	{
		$unsafe_tags = array_merge($unsafe_tags, $custom_bbcodes);
	}

	foreach($unsafe_tags as $tag)
	{
		if (($start_pos = strrpos($text, $tag[0])) > strrpos($text, $tag[1]))
		{
			$text = substr($text, 0, $start_pos) . ' ...';
		}
	}

	// Get all of the BBCodes the text contains.
	// If it does not contain any than just skip this step.
	// Preg expression is borrowed from strip_bbcode()
	if (preg_match_all("#\[(\/?)([a-z0-9_\*\+\-]+)(?:=(&quot;.*&quot;|[^\]]*))?(?::[a-z])?(?:\:$uid)\]#", $text, $matches, PREG_PATTERN_ORDER) != 0)
	{
		$open_tags = array();

		for ($i = 0, $size = sizeof($matches[0]); $i < $size; ++$i)
		{
			$bbcode_name = &$matches[2][$i];
			$opening = ($matches[1][$i] == '/') ? false : true;

			// If a new BBCode is opened add it to the array of open BBCodes
			if ($opening)
			{
				$open_tags[] = array(
					'name' => $bbcode_name,
					'plus' => ($opening && $bbcode_name == 'list' && !empty($matches[3][$i])) ? ':o' : '',
				);
			}
			// If a BBCode is closed remove it from the array of open BBCodes.
			// As always only the last opened open tag can be closed
			// we only need to remove the last element of the array.
			else
			{
				array_pop($open_tags);
			}
		}

		// Sort open BBCode tags so the most recently opened will be the first (because it has to be closed first)
		krsort ($open_tags);

		// Close remaining open BBCode tags
		foreach ($open_tags as $tag)
		{
			$text .= '[/' . $tag['name'] . $tag['plus'] . ':' . $uid . ']';
		}
	}

	return $text;
}

/**
* trims the length of the text of a blog or reply
*
* @param int|bool $blog_id the blog_id for the blog we will trim the text length for (if not triming the blog text length, set to false)
* @param int|bool $reply_id same as blog_id, except for replies
* @param int str_limit the string length limit
* @param bool $always_return If it is false this function returns false if the string is not shortened, if true it always returns the text whether it was shortened or not
*
* @return Returns false if $always_return is false and the text is not trimmed, otherwise it returns the string (shortened if it was)
*/
function trim_text_length($blog_id, $reply_id, $str_limit, $always_return = false)
{
	global $phpbb_root_path, $phpEx, $user;

	$bbcode_bitfield = $text_only_message = $text = '';

	if ($blog_id !== false)
	{
		$data = blog_data::$blog[$blog_id];
		$text = $data['blog_text'];
	}
	else
	{
		if ($reply_id === false)
		{
			return false;
		}

		$data = blog_data::$reply[$reply_id];
		$blog_id = $data['blog_id'];
		$text = $data['reply_text'];
	}

	if (utf8_strlen($text) > $str_limit)
	{
		$text = trim_text($text, $data['bbcode_uid'], $str_limit, $data['bbcode_uid']);

		$text .= "\n \n <a href=\"";
		if ($reply_id !== false)
		{
			$text .= blog_url((isset(blog_data::$blog[$blog_id]) ? blog_data::$blog[$blog_id]['user_id'] : false), $blog_id, $reply_id);
		}
		else
		{
			$text .= blog_url(blog_data::$blog[$blog_id]['user_id'], $blog_id);
		}
		$text .= '">[ ' . $user->lang['CONTINUED'] . ' ]</a>';

		return $text;
	}
	else
	{
		if ($always_return)
		{
			return $text;
		}
		else
		{
			return false;
		}
	}
}

/**
* Updates the blog and reply information to add edit and delete messages.
*
* I have this seperate so I can grab the blogs, replies, users, then update the edit and delete data (to cut on SQL queries)
*
* @param string $mode The mode (all, blog, or reply)
*/
function update_edit_delete($mode = 'all')
{
	global $auth, $user, $phpbb_root_path, $phpEx;

	if (!isset($user->lang['EDITED_TIME_TOTAL']))
	{
		$user->add_lang('viewtopic');
	}

	if ($mode == 'all' || $mode == 'blog')
	{
		foreach (blog_data::$blog as $row)
		{
			if ((!isset($row['edited_message'])) && (!isset($row['deleted_message'])) )
			{
				$blog_id = $row['blog_id'];

				// has the blog been edited?
				if ($row['blog_edit_count'] != 0)
				{
					if ($row['blog_edit_count'] == 1)
					{
						if ($auth->acl_get('u_viewprofile'))
						{
							blog_data::$blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], blog_data::$user[$row['blog_edit_user']]['username_full'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
						}
						else
						{
							if (blog_data::$user[$row['blog_edit_user']]['user_colour'] != '')
							{
								blog_data::$blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], '<strong style="color: ' . blog_data::$user[$row['blog_edit_user']]['user_colour'] . '">' . blog_data::$user[$row['blog_edit_user']]['username'] . '</strong>', $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
							else
							{
								blog_data::$blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], blog_data::$user[$row['blog_edit_user']]['username'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
						}
					}
					else if ($row['blog_edit_count'] > 1)
					{
						if ($auth->acl_get('u_viewprofile'))
						{
							blog_data::$blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], blog_data::$user[$row['blog_edit_user']]['username_full'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
						}
						else
						{
							if (blog_data::$user[$row['blog_edit_user']]['user_colour'] != '')
							{
								blog_data::$blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], '<strong style="color: ' . blog_data::$user[$row['blog_edit_user']]['user_colour'] . '">' . blog_data::$user[$row['blog_edit_user']]['username'] . '</strong>', $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
							else
							{
								blog_data::$blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], blog_data::$user[$row['blog_edit_user']]['username'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
						}
					}

					blog_data::$blog[$blog_id]['edit_reason'] = censor_text($row['blog_edit_reason']);
				}
				else
				{
					blog_data::$blog[$blog_id]['edited_message'] = '';
					blog_data::$blog[$blog_id]['edit_reason'] = '';
				}

				// has the blog been deleted?
				if ($row['blog_deleted'] != 0)
				{
					blog_data::$blog[$blog_id]['deleted_message'] = sprintf($user->lang['BLOG_DELETED_BY_MSG'], blog_data::$user[$row['blog_deleted']]['username_full'], $user->format_date($row['blog_deleted_time']), '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "page=blog&amp;mode=undelete&amp;b=$blog_id") . '">', '</a>');
				}
				else
				{
					blog_data::$blog[$blog_id]['deleted_message'] = '';
				}
			}
		}
	}

	if ($mode == 'all' || $mode == 'reply')
	{
		foreach (blog_data::$reply as $row)
		{
			if ((!isset($row['edited_message'])) && (!isset($row['deleted_message'])) )
			{
				$reply_id = $row['reply_id'];

				// has the reply been edited?
				if ($row['reply_edit_count'] != 0)
				{
					if ($row['reply_edit_count'] == 1)
					{
						if ($auth->acl_get('u_viewprofile'))
						{
							blog_data::$reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], blog_data::$user[$row['reply_edit_user']]['username_full'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
						}
						else
						{
							if (blog_data::$user[$row['reply_edit_user']]['user_colour'] != '')
							{
								blog_data::$reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], '<strong style="color: ' . blog_data::$user[$row['reply_edit_user']]['user_colour'] . '">' . blog_data::$user[$row['reply_edit_user']]['username'] . '</strong>', $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
							else
							{
								blog_data::$reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], blog_data::$user[$row['reply_edit_user']]['username'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
						}
					}
					else if ($row['reply_edit_count'] > 1)
					{
						if ($auth->acl_get('u_viewprofile'))
						{
							blog_data::$reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], blog_data::$user[$row['reply_edit_user']]['username_full'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
						}
						else
						{
							if (blog_data::$user[$row['reply_edit_user']]['user_colour'] != '')
							{
								blog_data::$reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], '<strong style="color: ' . blog_data::$user[$row['reply_edit_user']]['user_colour'] . '">' . blog_data::$user[$row['reply_edit_user']]['username'] . '</strong>', $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
							else
							{
								blog_data::$reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], blog_data::$user[$row['reply_edit_user']]['username'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
						}
					}

					blog_data::$reply[$reply_id]['edit_reason'] = censor_text($row['reply_edit_reason']);
				}
				else
				{
					blog_data::$reply[$reply_id]['edited_message'] = '';
					blog_data::$reply[$reply_id]['edit_reason'] = '';
				}

				// has the reply been deleted?
				if ($row['reply_deleted'] != 0)
				{
					blog_data::$reply[$reply_id]['deleted_message'] = sprintf($user->lang['REPLY_DELETED_BY_MSG'], blog_data::$user[$row['reply_deleted']]['username_full'], $user->format_date($row['reply_deleted_time']), '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=undelete&amp;r=$reply_id") . '">', '</a>');
				}
				else
				{
					blog_data::$reply[$reply_id]['deleted_message'] = '';
				}
			}
		}
	}
}

/**
* Outputs data as a Feed.
*
* @param int|array $blog_ids The id's of blogs that are going to get outputted,
* @param string $feed_type The type of feed we are outputting
*/
function feed_output($ids, $feed_type)
{
	global $template, $phpbb_root_path, $phpEx, $page, $mode, $limit, $config, $user, $blog_data, $user_id, $blog_id;

	// Feed explanation page
	if ($feed_type == 'explain')
	{
		$available_feeds = array(
			'RSS 0.91'		=> blog_url(false, false, false, array_merge($_GET, array('feed' => 'RSS_0.91'))),
			'RSS 1.0'		=> blog_url(false, false, false, array_merge($_GET, array('feed' => 'RSS_1.0'))),
			'RSS 2.0'		=> blog_url(false, false, false, array_merge($_GET, array('feed' => 'RSS_2.0'))),
			'ATOM'			=> blog_url(false, false, false, array_merge($_GET, array('feed' => 'ATOM'))),
			'JAVASCRIPT'	=> array(
				'url'		=> blog_url(false, false, false, array_merge($_GET, array('feed' => 'JAVASCRIPT'))),
				'text'		=> htmlspecialchars('<script type="text/javascript" src="' . blog_url(false, false, false, array_merge($_GET, array('feed' => 'JAVASCRIPT', 'output' => 'true'))) . '"></script>'),
				'demo'		=> '<script type="text/javascript" src="' . blog_url(false, false, false, array_merge($_GET, array('feed' => 'JAVASCRIPT', 'output' => 'true'))) . '"></script>',
			),
		);

		blog_plugins::plugin_do_ref('available_feeds', $available_feeds);

		$message = '<strong>' . $user->lang['AVAILABLE_FEEDS'] . '</strong><br /><br />';
		foreach ($available_feeds as $feed_name => $data)
		{
			if (!is_array($data))
			{
				$message .= '<br /><h2><a href="' . $data . '">' . $feed_name . '</a></h2><div><a href="' . $data . '">' . $data . '</a></div><br />';
			}
			else
			{
				$message .= '<br /><h2><a href="' . $data['url'] . '">' . $feed_name . '</a></h2><div><dl class="codebox"><dt>' . $user->lang['CODE'] . ': <a href="#" onclick="selectCode(this); return false;">Select all</a></dt><dd><code style="font-size: 12px;">' . $data['text'] . '</code></dd></dl></div><br />';

				if (isset($data['demo']))
				{
					$message .= $data['demo'];
				}
			}
		}

		trigger_error($message);
	}

	$title = ($feed_type == 'JAVASCRIPT') ? str_replace("'", "\\'", $template->_tpldata['navlinks'][(sizeof($template->_tpldata['navlinks']) - 1)]['FORUM_NAME']) : $template->_tpldata['navlinks'][(sizeof($template->_tpldata['navlinks']) - 1)]['FORUM_NAME'];

	$template->assign_vars(array(
		'FEED'				=> $feed_type,
		'SELF_URL'			=> blog_url(false, false, false, array('page' => $page, 'mode' => $mode)),
		'SELF_FULL_URL'		=> blog_url(false, false, false, array('page' => $page, 'mode' => $mode, 'feed' => $feed_type, 'limit' => $limit)),
		'TITLE'				=> $config['sitename'] . ' ' . $title . ' ' . $user->lang['FEED'],
		'SITE_URL'			=> generate_board_url(),
		'SITE_DESC'			=> $config['site_desc'],
		'SITE_LANG'			=> $config['default_lang'],
		'CURRENT_TIME'		=> ($feed_type == 'ATOM') ? date3339() : date('r'),

		// used for Javascript output feeds
		'IMG_MIN'			=> generate_board_url() . '/styles/' . $user->theme['theme_path'] . '/theme/images/blog/min_dark_blue.gif',
		'IMG_MAX'			=> generate_board_url() . '/styles/' . $user->theme['theme_path'] . '/theme/images/blog/max_dark_blue.gif',
		'S_OUTPUT'			=> (isset($_GET['output'])) ? true : false,
	));

	if ($ids !== false)
	{
		if (!is_array($ids))
		{
			$ids = array(intval($ids));
		}

		// the items section is only used in RSS 1.0
		if ($feed_type == 'RSS_1.0')
		{
			if (strpos($mode, 'comments') === false)
			{
				// output the URLS for the items section
				foreach ($ids as $id)
				{
					$template->assign_block_vars('items', array(
						'URL'	=> blog_url(blog_data::$blog[$id]['user_id'], $id),
					));
				}
			}
			else
			{
				// output the URLS for the items section
				foreach ($ids as $id)
				{
					$template->assign_block_vars('items', array(
						'URL'	=> blog_url(blog_data::$reply[$id]['user_id'], $id),
					));
				}
			}
		}

		if (strpos($mode, 'comments') === false)
		{
			// Output the main data
			foreach ($ids as $id)
			{
				$blog_row = $blog_data->handle_blog_data($id, true);

				$row = array(
					'URL'				=> blog_url(blog_data::$blog[$id]['user_id'], $id),
					'USERNAME'			=> blog_data::$user[blog_data::$blog[$id]['user_id']]['username'],
					'MESSAGE'			=> str_replace("'", '&#039;', $blog_row['MESSAGE']),
					'PUB_DATE'			=> date('r', blog_data::$blog[$id]['blog_time']),
					'DATE_3339'			=> ($feed_type == 'ATOM') ? date3339(blog_data::$blog[$id]['blog_time']) : '',
				);

				$template->assign_block_vars('item', array_merge($blog_row, $row));
			}
		}
		else
		{
			// Output the main data
			foreach ($ids as $id)
			{
				$reply_row = $blog_data->handle_reply_data($id, true);

				$row = array(
					'URL'				=> blog_url(blog_data::$reply[$id]['user_id'], $id),
					'USERNAME'			=> blog_data::$user[blog_data::$reply[$id]['user_id']]['username'],
					'MESSAGE'			=> str_replace("'", '&#039;', $reply_row['MESSAGE']),
					'PUB_DATE'			=> date('r', blog_data::$reply[$id]['reply_time']),
					'DATE_3339'			=> ($feed_type == 'ATOM') ? date3339(blog_data::$reply[$id]['reply_time']) : '',
				);

				$template->assign_block_vars('item', array_merge($reply_row, $row));
			}
		}

		blog_plugins::plugin_do_arg('function_feed_output', compact('ids', 'feed_type', 'mode'));
	}

	// Output time
	if ($feed_type == 'JAVASCRIPT')
	{
		header('Content-type: text/html; charset=UTF-8');
	}
	else
	{
		header('Content-type: application/xml; charset=UTF-8');
	}

	header('Cache-Control: private, no-cache="set-cookie"');
	header('Expires: 0');
	header('Pragma: no-cache');

	$template->set_template();
	$template->set_filenames(array(
		'body' => 'blog/blog_feed.xml'
	));

	$template->display('body');

	garbage_collection();
	exit_handler();
}

/**
* General attachment parsing
*
* @param string &$message The post/private message
* @param array &$attachments The attachments to parse for (inline) display. The attachments array will hold templated data after parsing.
* @param array &$update_count The attachment counts to be updated - will be filled
* @param bool $preview If set to true the attachments are parsed for preview. Within preview mode the comments are fetched from the given $attachments array and not fetched from the database.
*/
function parse_attachments_for_view(&$message, &$attachments, &$update_count, $preview = false)
{
	global $template, $user, $config, $phpbb_root_path, $auth;

	if (!$config['user_blog_enable_attachments'] || !sizeof($attachments) || !$auth->acl_get('u_download'))
	{
		return;
	}

	$compiled_attachments = array();

	$temp = compact('message', 'attachments', 'update_count', 'preview', 'compiled_attachments');
	blog_plugins::plugin_do_ref('function_parse_attachments_for_view', $temp);
	extract($temp);

	if (!isset($template->filename['attachment_tpl']))
	{
		$template->set_filenames(array(
			'attachment_tpl'	=> 'attachment.html')
		);
	}

	$extensions = obtain_blog_attach_extensions();

	// Look for missing attachment information...
	$attach_ids = array();
	foreach ($attachments as $pos => $attachment)
	{
		// If is_orphan is set, we need to retrieve the attachments again...
		if (!isset($attachment['extension']) && !isset($attachment['physical_filename']))
		{
			$attach_ids[(int) $attachment['attach_id']] = $pos;
		}
	}

	// Grab attachments (security precaution)
	if (sizeof($attach_ids))
	{
		global $db;

		$new_attachment_data = array();

		$sql = 'SELECT *
			FROM ' . BLOGS_ATTACHMENT_TABLE . '
			WHERE ' . $db->sql_in_set('attach_id', array_keys($attach_ids));
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (!isset($attach_ids[$row['attach_id']]))
			{
				continue;
			}

			// If we preview attachments we will set some retrieved values here
			if ($preview)
			{
				$row['attach_comment'] = $attachments[$attach_ids[$row['attach_id']]]['attach_comment'];
			}

			$new_attachment_data[$attach_ids[$row['attach_id']]] = $row;
		}
		$db->sql_freeresult($result);

		$attachments = $new_attachment_data;
		unset($new_attachment_data);
	}

	ksort($attachments);


	foreach ($attachments as $attachment)
	{
		if (!sizeof($attachment))
		{
			continue;
		}

		// We need to reset/empty the _file block var, because this function might be called more than once
		$template->destroy_block_vars('_file');

		$block_array = array();

		// Some basics...
		$attachment['extension'] = strtolower(trim($attachment['extension']));
		$filename = $phpbb_root_path . $config['upload_path'] . '/blog_mod/' . basename($attachment['physical_filename']);
		$thumbnail_filename = $phpbb_root_path . $config['upload_path'] . '/blog_mod/thumb_' . basename($attachment['physical_filename']);

		$upload_icon = '';

		if (isset($extensions[$attachment['extension']]))
		{
			if ($user->img('icon_topic_attach', '') && !$extensions[$attachment['extension']]['upload_icon'])
			{
				$upload_icon = $user->img('icon_topic_attach', '');
			}
			else if ($extensions[$attachment['extension']]['upload_icon'])
			{
				$upload_icon = '<img src="' . $phpbb_root_path . $config['upload_icons_path'] . '/' . trim($extensions[$attachment['extension']]['upload_icon']) . '" alt="" />';
			}
		}

		$filesize = $attachment['filesize'];
		$size_lang = ($filesize >= 1048576) ? $user->lang['MB'] : ( ($filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );
		$filesize = ($filesize >= 1048576) ? round((round($filesize / 1048576 * 100) / 100), 2) : (($filesize >= 1024) ? round((round($filesize / 1024 * 100) / 100), 2) : $filesize);

		$comment = str_replace("\n", '<br />', censor_text($attachment['attach_comment']));

		$block_array += array(
			'UPLOAD_ICON'		=> $upload_icon,
			'FILESIZE'			=> $filesize,
			'SIZE_LANG'			=> $size_lang,
			'DOWNLOAD_NAME'		=> basename($attachment['real_filename']),
			'COMMENT'			=> $comment,
		);

		$denied = false;

		if (!isset($extensions['_allowed_'][$attachment['extension']]))
		{
			$denied = true;

			$block_array += array(
				'S_DENIED'			=> true,
				'DENIED_MESSAGE'	=> sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension'])
			);
		}

		if (!$denied)
		{
			$l_downloaded_viewed = $download_link = '';
			$display_cat = $extensions[$attachment['extension']]['display_cat'];

			if ($display_cat == ATTACHMENT_CATEGORY_IMAGE)
			{
				if ($attachment['thumbnail'])
				{
					$display_cat = ATTACHMENT_CATEGORY_THUMB;
				}
				else
				{
					if ($config['img_display_inlined'])
					{
						if ($config['img_link_width'] || $config['img_link_height'])
						{
							$dimension = @getimagesize($filename);

							// If the dimensions could not be determined or the image being 0x0 we display it as a link for safety purposes
							if ($dimension === false || empty($dimension[0]) || empty($dimension[1]))
							{
								$display_cat = ATTACHMENT_CATEGORY_NONE;
							}
							else
							{
								$display_cat = ($dimension[0] <= $config['img_link_width'] && $dimension[1] <= $config['img_link_height']) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE;
							}
						}
					}
					else
					{
						$display_cat = ATTACHMENT_CATEGORY_NONE;
					}
				}
			}

			// Make some descisions based on user options being set.
			if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !$user->optionget('viewimg'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}

			if ($display_cat == ATTACHMENT_CATEGORY_FLASH && !$user->optionget('viewflash'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}

			$download_link = blog_url(false, false, false, array('page' => 'download', 'mode' => 'download', 'id' => $attachment['attach_id']));

			switch ($display_cat)
			{
				// Images
				case ATTACHMENT_CATEGORY_IMAGE:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$inline_link = blog_url(false, false, false, array('page' => 'download', 'mode' => 'download', 'id' => $attachment['attach_id']));

					$block_array += array(
						'S_IMAGE'		=> true,
						'U_INLINE_LINK'		=> $inline_link,
					);

					$update_count[] = $attachment['attach_id'];
				break;

				// Images, but display Thumbnail
				case ATTACHMENT_CATEGORY_THUMB:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$thumbnail_link = blog_url(false, false, false, array('page' => 'download', 'mode' => 'thumbnail', 'id' => $attachment['attach_id']));

					$block_array += array(
						'S_THUMBNAIL'		=> true,
						'THUMB_IMAGE'		=> $thumbnail_link,
					);
				break;

				// Windows Media Streams
				case ATTACHMENT_CATEGORY_WM:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					// Giving the filename directly because within the wm object all variables are in local context making it impossible
					// to validate against a valid session (all params can differ)
					// $download_link = $filename;

					$block_array += array(
						'U_FORUM'		=> generate_board_url(),
						'ATTACH_ID'		=> $attachment['attach_id'],
						'S_WM_FILE'		=> true,
					);

					// Viewed/Heared File ... update the download count
					$update_count[] = $attachment['attach_id'];
				break;

				// Real Media Streams
				case ATTACHMENT_CATEGORY_RM:
				case ATTACHMENT_CATEGORY_QUICKTIME:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$block_array += array(
						'S_RM_FILE'			=> ($display_cat == ATTACHMENT_CATEGORY_RM) ? true : false,
						'S_QUICKTIME_FILE'	=> ($display_cat == ATTACHMENT_CATEGORY_QUICKTIME) ? true : false,
						'U_FORUM'			=> generate_board_url(),
						'ATTACH_ID'			=> $attachment['attach_id'],
					);

					// Viewed/Heared File ... update the download count
					$update_count[] = $attachment['attach_id'];
				break;

				// Macromedia Flash Files
				case ATTACHMENT_CATEGORY_FLASH:
					list($width, $height) = @getimagesize($filename);

					$l_downloaded_viewed = 'VIEWED_COUNT';

					$block_array += array(
						'S_FLASH_FILE'	=> true,
						'WIDTH'			=> $width,
						'HEIGHT'		=> $height,
					);

					// Viewed/Heared File ... update the download count
					$update_count[] = $attachment['attach_id'];
				break;

				default:
					$l_downloaded_viewed = 'DOWNLOAD_COUNT';

					$block_array += array(
						'S_FILE'		=> true,
					);
				break;
			}

			$l_download_count = (!isset($attachment['download_count']) || $attachment['download_count'] == 0) ? $user->lang[$l_downloaded_viewed . '_NONE'] : (($attachment['download_count'] == 1) ? sprintf($user->lang[$l_downloaded_viewed], $attachment['download_count']) : sprintf($user->lang[$l_downloaded_viewed . 'S'], $attachment['download_count']));

			$block_array += array(
				'U_DOWNLOAD_LINK'		=> $download_link,
				'L_DOWNLOAD_COUNT'		=> $l_download_count
			);
		}

		$template->assign_block_vars('_file', $block_array);

		$compiled_attachments[] = $template->assign_display('attachment_tpl');
	}

	$attachments = $compiled_attachments;
	unset($compiled_attachments);

	$tpl_size = sizeof($attachments);

	$unset_tpl = array();

	preg_match_all('#<!\-\- ia([0-9]+) \-\->(.*?)<!\-\- ia\1 \-\->#', $message, $matches, PREG_PATTERN_ORDER);

	$replace = array();
	foreach ($matches[0] as $num => $capture)
	{
		// Flip index if we are displaying the reverse way
		$index = ($config['display_order']) ? ($tpl_size-($matches[1][$num] + 1)) : $matches[1][$num];

		$replace['from'][] = $matches[0][$num];
		$replace['to'][] = (isset($attachments[$index])) ? $attachments[$index] : sprintf($user->lang['MISSING_INLINE_ATTACHMENT'], $matches[2][array_search($index, $matches[1])]);

		$unset_tpl[] = $index;
	}

	if (isset($replace['from']))
	{
		$message = str_replace($replace['from'], $replace['to'], $message);
	}

	$unset_tpl = array_unique($unset_tpl);

	// Needed to let not display the inlined attachments at the end of the post again
	foreach ($unset_tpl as $index)
	{
		unset($attachments[$index]);
	}
}

/**
* Obtain allowed extensions
*
* @return array allowed extensions array.
*/
function obtain_blog_attach_extensions()
{
	global $cache, $config;

	if (!$config['user_blog_enable_attachments'])
	{
		return;
	}

	if (($extensions = $cache->get('_blog_extensions')) === false)
	{
		global $db;

		$extensions = array(
			'_allowed_blog'	=> array(),
		);

		// The rule is to only allow those extensions defined. ;)
		$sql = 'SELECT e.extension, g.*
			FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
			WHERE e.group_id = g.group_id
				AND (g.allow_group = 1 OR g.allow_in_blog = 1)';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$extension = strtolower(trim($row['extension']));

			$extensions[$extension] = array(
				'display_cat'	=> (int) $row['cat_id'],
				'download_mode'	=> (int) $row['download_mode'],
				'upload_icon'	=> trim($row['upload_icon']),
				'max_filesize'	=> (int) $row['max_filesize'],
				'allow_group'	=> $row['allow_group'],
				'allow_in_blog'	=> $row['allow_in_blog'],
			);

			if ($row['allow_in_blog'])
			{
				$extensions['_allowed_blog'][$extension] = 0;
			}
		}
		$db->sql_freeresult($result);

		$cache->put('_blog_extensions', $extensions);
	}

	$return = array('_allowed_' => array());

	foreach ($extensions['_allowed_blog'] as $extension => $check)
	{
		$return['_allowed_'][$extension] = 0;
		$return[$extension] = $extensions[$extension];
	}

	blog_plugins::plugin_do_ref('function_obtain_blog_attach_extensions', $return);

	return $return;
}

/**
 * Get date in RFC3339
 * For example used in XML/Atom
 *
 * @param integer $timestamp
 * @return string date in RFC3339
 * @author Boris Korobkov
 * @see http://tools.ietf.org/html/rfc3339
 */
function date3339($timestamp=0) {

    if (!$timestamp) {
        $timestamp = time();
    }
    $date = date('Y-m-d\TH:i:s', $timestamp);

    $matches = array();
    if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) {
        $date .= $matches[1].$matches[2].':'.$matches[3];
    } else {
        $date .= 'Z';
    }
    return $date;

}
?>