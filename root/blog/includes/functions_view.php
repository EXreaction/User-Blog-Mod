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
	global $reverse_zebra_list, $user_settings;

	if (!isset($config['user_blog_enable']) || !$config['user_blog_enable'] || $user_id == ANONYMOUS)
	{
		return;
	}

	if (!function_exists('blog_url'))
	{
		include($phpbb_root_path . 'blog/includes/functions_url.' . $phpEx);
	}

	if (!isset($user->lang['BLOG']))
	{
		$user->add_lang('mods/blog/blog');
	}

	if (isset($user_settings[$user_id]))
	{
		$no_perm = false;

		if ($user->data['user_id'] == ANONYMOUS)
		{
			if ($user_settings[$user_id]['perm_guest'] == 0)
			{
				$no_perm = true;
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
						$no_perm = true;
					}
				}
				else if (isset($reverse_zebra_list[$user->data['user_id']]['friend']) && in_array($user_id, $reverse_zebra_list[$user->data['user_id']]['friend']))
				{
					if ($user_settings[$user_id]['perm_friend'] == 0)
					{
						$no_perm = true;
					}
				}
				else
				{
					if ($user_settings[$user_id]['perm_registered'] == 0)
					{
						$no_perm = true;
					}
				}
			}
			else
			{
				if ($user_settings[$user_id]['perm_registered'] == 0)
				{
					$no_perm = true;
				}
			}
		}

		if ($no_perm)
		{
			if ($config['user_blog_always_show_blog_url'] || $force_output)
			{
				$template->assign_block_vars($block, array(
					'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
					'PROFILE_FIELD_VALUE'		=> '<a href="' . blog_url($user_id, false, false, array(), array('username' => $user_data['username'])) . '">' . $user->lang['VIEW_BLOGS'] . ' (0)</a>',
				));

				return;
			}
			else
			{
				return;
			}
		}
	}

	// if they are not an anon user, and they blog_count row isn't set grab that data from the db.
	if ($user_id > 1 && (!isset($user_data['blog_count']) || !isset($user_data['username'])) && $grab_from_db)
	{
		$sql = 'SELECT username, blog_count FROM ' . USERS_TABLE . ' WHERE user_id = ' . intval($user_id);
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
* Create the breadcrumbs
*
* @param string $crumb_lang The last language option in the breadcrumbs
* @param string $crumb_url The last url option in the breadcrumbs (this will be set to the current URL if this is blank)
*/
function generate_blog_breadcrumbs($crumb_lang = '', $crumb_url = '')
{
	global $template, $user;
	global $page, $username, $blog_id, $reply_id;
	global $blog_data, $reply_data, $user_data, $blog_urls, $category_id;

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'		=> $user->lang['USER_BLOGS'],
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
				'FORUM_NAME'		=> sprintf($user->lang['USERNAMES_BLOGS'], $username),
				'U_VIEW_FORUM'		=> $blog_urls['view_user'],
			));
		}

		if ($blog_id != 0)
		{
			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'		=> censor_text($blog_data->blog[$blog_id]['blog_subject']),
				'U_VIEW_FORUM'		=> $blog_urls['view_blog'],
			));

			if ($reply_id != 0 && $page == 'reply')
			{
				$c_text = censor_text($reply_data->reply[$reply_id]['reply_subject']);
				
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
	global $config, $db, $template;
	global $user_data, $blog_plugins;

	$extra = $user_menu_extra = '';
	$temp = compact('user_id', 'user_menu_extra', 'extra');
	$blog_plugins->plugin_do_arg_ref('function_generate_menu', $temp);
	extract($temp);

	if ($user_id)
	{
		$template->assign_vars($user_data->handle_user_data($user_id));
		$user_data->handle_user_data($user_id, 'custom_fields');

		$template->assign_vars(array(
			'S_USER_BLOG_MENU'	=> true,
			'USER_MENU_EXTRA'	=> $user_menu_extra,
		));
	}

	if ($config['user_blog_search'])
	{
		$template->assign_vars(array(
			'S_DISPLAY_BLOG_SEARCH'	=> true,
			'U_BLOG_SEARCH'			=> blog_url(false, false, false, array('page' => 'search')),
		));
	}
}

/**
* Close Tags
*
* Closes HTML tags at the end of a string.
*
* @param string $html The html code to close the tags for.
*/
function close_tags($html)
{
	// put all opened tags into an array
	preg_match_all("|<[^>^/]+?>|U", $html, $result);
	$openedtags=$result[0];

	// put all closed tags into an array
	preg_match_all("|</[^>^/]+?>|U",$html,$result);
	$closedtags=$result[0];

	// all tags are closed
	if (count($closedtags) == count($openedtags))
	{
		return $html;
	}

	// Reverse the arrays
	$openedtags = array_reverse($openedtags);
	$closedtags = array_reverse($closedtags);

	// close tags
	foreach ($openedtags as $tag)
	{
		if (!isset($closedtags[0]) || $tag != $closedtags[0])
		{
			// if there is a space there are attributes to the tag, and we do not want those on the closing tag
			if (strpos($tag, ' '))
			{
				$html .= '</' . substr($tag, 1, (strpos($tag, ' ') - 1)) . '>';
			}
			else
			{
				$html .= '</' . substr($tag, 1, -1) . '>';
			}
		}
		else
		{
			// If there is a match, remove the first item off of the closedtags array
			array_shift($closedtags);
		}
	}

	return $html;
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
	global $blog_data, $reply_data, $user_data;

	$bbcode_bitfield = $text_only_message = $text = '';

	if ($blog_id !== false)
	{
		$data = $blog_data->blog[$blog_id];
		$text = $data['blog_text'];
	}
	else
	{
		if ($reply_id === false)
		{
			return false;
		}

		$data = $reply_data->reply[$reply_id];
		$blog_id = $data['blog_id'];
		$text = $data['reply_text'];
	}

	if (utf8_strlen($text) > $str_limit)
	{
		// Parse the text
		$data['bbcode_options'] = (($data['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($data['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($data['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
		$text = generate_text_for_display($text, $data['bbcode_uid'], $data['bbcode_bitfield'], $data['bbcode_options']);

		// we will try not to cut off any words :)
		$next_space = strpos(substr($text, $str_limit), ' ');
		$next_el = strpos(substr($text, $str_limit), "\n");
		if ($next_space !== false)
		{
			if ($next_el !== false)
			{
				$str_limit = ($next_space < $next_el) ? $next_space + $str_limit : $next_el + $str_limit;
			}
			else
			{
				$str_limit = $next_space + $str_limit;
			}
		}
		else if ($next_el !== false)
		{
			$str_limit = $next_el + $str_limit;
		}
		else
		{
			$str_limit = utf8_strlen($text);
		}

		// now trim the text, then close any opened HTML tags
		$text = close_tags(substr($text, 0, $str_limit));

		$text .= '...<br/><br/><!-- m --><a href="';
		if ($reply_id !== false)
		{
			$text .= blog_url($blog_data->blog[$blog_id]['user_id'], $blog_id, $reply_id);
		}
		else
		{
			$text .= blog_url($blog_data->blog[$blog_id]['user_id'], $blog_id);
		}
		$text .= '">[ ' . $user->lang['CONTINUED'] . ' ]</a><!-- m -->';

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
	global $blog_data, $reply_data, $user_data;

	if (!isset($user->lang['EDITED_TIME_TOTAL']))
	{
		$user->add_lang('viewtopic');
	}

	if ($mode == 'all' || $mode == 'blog')
	{
		foreach ($blog_data->blog as $row)
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
							$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $user_data->user[$row['blog_edit_user']]['username_full'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
						}
						else
						{
							if ($user_data->user[$row['blog_edit_user']]['user_colour'] != '')
							{
								$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], '<b style="color: ' . $user_data->user[$row['blog_edit_user']]['user_colour'] . '">' . $user_data->user[$row['blog_edit_user']]['username'] . '</b>', $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
							else
							{
								$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $user_data->user[$row['blog_edit_user']]['username'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
						}
					}
					else if ($row['blog_edit_count'] > 1)
					{
						if ($auth->acl_get('u_viewprofile'))
						{
							$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $user_data->user[$row['blog_edit_user']]['username_full'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
						}
						else
						{
							if ($user_data->user[$row['blog_edit_user']]['user_colour'] != '')
							{
								$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], '<b style="color: ' . $user_data->user[$row['blog_edit_user']]['user_colour'] . '">' . $user_data->user[$row['blog_edit_user']]['username'] . '</b>', $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
							else
							{
								$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $user_data->user[$row['blog_edit_user']]['username'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
						}
					}
		
					$blog_data->blog[$blog_id]['edit_reason'] = censor_text($row['blog_edit_reason']);
				}
				else
				{
					$blog_data->blog[$blog_id]['edited_message'] = '';
					$blog_data->blog[$blog_id]['edit_reason'] = '';
				}
	
				// has the blog been deleted?
				if ($row['blog_deleted'] != 0)
				{
					$blog_data->blog[$blog_id]['deleted_message'] = sprintf($user->lang['BLOG_DELETED_BY_MSG'], $user_data->user[$row['blog_deleted']]['username_full'], $user->format_date($row['blog_deleted_time']), '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "page=blog&amp;mode=undelete&amp;b=$blog_id") . '">', '</a>');
				}
				else
				{
					$blog_data->blog[$blog_id]['deleted_message'] = '';
				}
			}
		}
	}

	if ($mode == 'all' || $mode == 'reply')
	{
		foreach ($reply_data->reply as $row)
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
							$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $user_data->user[$row['reply_edit_user']]['username_full'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
						}
						else
						{
							if ($user_data->user[$row['reply_edit_user']]['user_colour'] != '')
							{
								$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], '<b style="color: ' . $user_data->user[$row['reply_edit_user']]['user_colour'] . '">' . $user_data->user[$row['reply_edit_user']]['username'] . '</b>', $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
							else
							{
								$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $user_data->user[$row['reply_edit_user']]['username'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
						}
					}
					else if ($row['reply_edit_count'] > 1)
					{
						if ($auth->acl_get('u_viewprofile'))
						{
							$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $user_data->user[$row['reply_edit_user']]['username_full'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
						}
						else
						{
							if ($user_data->user[$row['reply_edit_user']]['user_colour'] != '')
							{
								$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], '<b style="color: ' . $user_data->user[$row['reply_edit_user']]['user_colour'] . '">' . $user_data->user[$row['reply_edit_user']]['username'] . '</b>', $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
							else
							{
								$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $user_data->user[$row['reply_edit_user']]['username'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
						}
					}
		
					$reply_data->reply[$reply_id]['edit_reason'] = censor_text($row['reply_edit_reason']);
				}
				else
				{
					$reply_data->reply[$reply_id]['edited_message'] = '';
					$reply_data->reply[$reply_id]['edit_reason'] = '';
				}
	
				// has the reply been deleted?
				if ($row['reply_deleted'] != 0)
				{
					$reply_data->reply[$reply_id]['deleted_message'] = sprintf($user->lang['REPLY_DELETED_BY_MSG'], $user_data->user[$row['reply_deleted']]['username_full'], $user->format_date($row['reply_deleted_time']), '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=undelete&amp;r=$reply_id") . '">', '</a>');
				}
				else
				{
					$reply_data->reply[$reply_id]['deleted_message'] = '';
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
function feed_output($blog_ids, $feed_type)
{
	global $template, $phpbb_root_path, $phpEx, $page, $mode, $limit, $config, $user;
	global $blog_data, $user_data, $reply_data;

	if (!is_array($blog_ids))
	{
		$blog_ids = array(intval($blog_ids));
	}

	$board_url = generate_board_url();

	$template->assign_vars(array(
		'FEED'				=> $feed_type,
		'SELF_URL'			=> "{$board_url}/blog.{$phpEx}?page={$page}&amp;mode={$mode}&amp;feed={$feed_type}&amp;limit={$limit}",
		'TITLE'				=> $config['sitename'] . ' ' . $user->lang['FEED'],
		'SITE_URL'			=> $board_url,
		'SITE_DESC'			=> $config['site_desc'],
		'SITE_LANG'			=> $config['default_lang'],
		'CURRENT_TIME'		=> date('r'),
	));

	// the items section is only used in RSS 1.0
	if ($feed_type == 'RSS_1.0')
	{
		// output the URLS for the items section
		foreach ($blog_ids as $id)
		{
			$template->assign_block_vars('items', array(
				'URL'	=> "{$board_url}/blog.{$phpEx}?b=$id",
			));
		}
	}

	// Output the main data
	foreach ($blog_ids as $id)
	{
		$blog_row = $blog_data->handle_blog_data($id, true);

		$row = array(
			'URL'		=> $board_url . "/blog.{$phpEx}?b=$id",
			'USERNAME'	=> $user_data->user[$blog_data->blog[$id]['user_id']]['username'],
		);

		$template->assign_block_vars('item', $blog_row + $row);
	}

	// tell the template parser what template file to use
	$template->set_filenames(array(
		'body' => 'blog/blog_feed.xml'
	));
}

?>