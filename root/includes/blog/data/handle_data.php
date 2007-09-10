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
	global $blog_data, $reply_data, $user_data, $user_founder;

	$bbcode_bitfield = $text_only_message = $text = '';

	if ($blog_id !== false)
	{
		$data = $blog_data->blog[$blog_id];
		$original_text = $data['blog_text'];
	}
	else
	{
		if ($reply_id === false)
		{
			return false;
		}

		$data = $reply_data->reply[$reply_id];
		$blog_id = $data['blog_id'];
		$original_text = $data['reply_text'];
	}

	$text = $original_text;

	decode_message($text, $data['bbcode_uid']);

	if (utf8_strlen($text) > $str_limit)
	{
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

		// now trim the text
		$text = substr($text, 0, $str_limit);

		// Now lets get the URL's back and nl2br
		$message_parser = new parse_message();
		$message_parser->message = $text;
		$message_parser->parse($data['enable_bbcode'], $data['enable_magic_url'], $data['enable_smilies']);
		$text = $message_parser->format_display($data['enable_bbcode'], $data['enable_magic_url'], $data['enable_smilies'], false);
		unset($message_parser);

		$text .= '...<br/><br/><!-- m --><a href="';
		if ($reply_id !== false)
		{
			$text .= append_sid("{$phpbb_root_path}blog.$phpEx", "b={$blog_id}r={$reply_id}#r{$reply_id}");
		}
		else
		{
			$text .= append_sid("{$phpbb_root_path}blog.$phpEx", "b=$blog_id");
		}
		$text .= '">[ ' . $user->lang['CONTINUED'] . ' ]</a><!-- m -->';

		return $text;
	}
	else
	{
		if ($always_return)
		{
			return $original_text;
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
	global $blog_data, $reply_data, $user_data, $user_founder;

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
					$blog_data->blog[$blog_id]['deleted_message'] = sprintf($user->lang['BLOG_IS_DELETED'], $user_data->user[$row['blog_deleted']]['username_full'], $user->format_date($row['blog_deleted_time']), '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "page=blog&amp;mode=undelete&amp;b=$blog_id") . '">', '</a>');
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
					$reply_data->reply[$reply_id]['deleted_message'] = sprintf($user->lang['REPLY_IS_DELETED'], $user_data->user[$row['reply_deleted']]['username_full'], $user->format_date($row['reply_deleted_time']), '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=undelete&amp;r=$reply_id") . '">', '</a>');
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
* Fix Where SQL function
*
* Checks to make sure there is a WHERE if there are any AND sections in the SQL and fixes them if needed
*
* @param string $sql The (possibly) broken SQL query to check
* @return The fixed SQL query.
*/
function fix_where_sql($sql)
{
	if (!strpos($sql, 'WHERE') && strpos($sql, 'AND'))
	{
		return substr($sql, 0, strpos($sql, 'AND')) . 'WHERE' . substr($sql, strpos($sql, 'AND') + 3);
	}

	return $sql;
}

/**
* Outputs data as a Feed.
 *
* @param int|array $blog_ids The id's of blogs that are going to get outputted,
 * @param string $feed_type The type of feed we are outputting
*/
function feed_output($blog_ids, $feed_type)
{
	global $template, $phpbb_root_path, $phpEx, $page, $mode, $limit, $config, $user, $blog_data;

	if (!is_array($blog_ids))
	{
		$blog_ids = array($blog_ids);
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
			'URL'		=> $board_url . "/blog.{$phpEX}?b=$id",
			'USERNAME'	=> $user_data->user[$blog_data->blog[$id]['user_id']]['username'],
		);

		$template->assign_block_vars('item', $blog_row + $row);
	}

	// tell the template parser what template file to use
	$template->set_filenames(array(
		'body' => 'blog_feed.xml'
	));
}
?>