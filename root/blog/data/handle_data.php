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
	global $blog_data, $reply_data, $user_data;

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

	$text = html_entity_decode($original_text);

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

		if (!class_exists('parse_message'))
		{
			include("{$phpbb_root_path}includes/message_parser.$phpEx");
		}

		// Now lets get the bbcode back
		$message_parser = new parse_message();
		$message_parser->message = $text;
		$message_parser->parse($data['enable_bbcode'], $data['enable_magic_url'], $data['enable_smilies']);
		$text = $message_parser->format_display($data['enable_bbcode'], $data['enable_magic_url'], $data['enable_smilies'], false);
		unset($message_parser);

		$text .= '...<br/><br/><!-- m --><a href="';
		if ($reply_id !== false)
		{
			$text .= blog_url(false, $blog_id, $reply_id);
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

/**
* handles sending subscription notices for blogs or replies
*
* Sends a PM or Email to each user in the subscription list, depending on what they want
*
* @param string $mode The mode (new_blog, or new_reply)
* @param string $post_subject The subject of the post made
* @param int|bool $uid The user_id of the user who made the new blog (if there is one).  If this is left as 0 it will grab the global value of $user_id.
* @param int|bool $bid The blog_id of the blog.  If this is left as 0 it will grab the global value of $blog_id.
* @param int|bool $rid The reply_id of the new reply (if there is one).  If this is left as 0 it will grab the global value of $reply_id.
*/
function handle_subscription($mode, $post_subject, $uid = 0, $bid = 0, $rid = 0)
{
	global $db, $user, $phpbb_root_path, $phpEx, $config;
	global $user_id, $blog_id, $reply_id;
	global $blog_data, $reply_data, $user_data, $blog_urls, $blog_plugins;

	// if $uid, $bid, or $rid are not set, use the globals
	$uid = ($uid != 0) ? $uid : $user_id;
	$bid = ($bid != 0) ? $bid : $blog_id;
	$rid = ($rid != 0) ? $rid : $reply_id;

	// make sure that subscriptions are enabled and that a blog_id is sent
	if (!$config['user_blog_subscription_enabled'] || $bid == 0)
	{
		return;
	}

	$subscribe_modes = array(0 => 'send_via_pm', 1 => 'send_via_email', 2 => array('send_via_pm', 'send_via_email'));
	$blog_plugins->plugin_do_arg_ref('function_handle_subscription', $subscribe_modes);

	// setup the arrays which will hold the to info for PM's/Emails
	$send_via_pm = array();
	$send_via_email = array();

	// Fix the URL's...
	if (isset($config['user_blog_seo']) && $config['user_blog_seo'])
	{
		$view_url = ($rid) ? blog_url($uid, $bid, $rid) : blog_url($uid, $bid);
		$unsubscribe_url = ($rid) ? blog_url($uid, $bid, false, array('page' => 'unsubscribe')) : blog_url($uid, false, false, array('page' => 'unsubscribe'));
	}
	else
	{
		$view_url = redirect((($rid) ? blog_url($uid, $bid, $rid) : blog_url($uid, $bid)), true);
		$unsubscribe_url = redirect((($rid) ? blog_url($uid, $bid, false, array('page' => 'unsubscribe')) : blog_url($uid, false, false, array('page' => 'unsubscribe'))), true);
	}

	if ($mode == 'new_reply' && $rid != 0)
	{
		$sql = 'SELECT* FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE blog_id = \'' . $bid . '\'
			AND sub_user_id != \'' . $user->data['user_id'] . '\'';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			if (is_array($subscribe_modes[$row['sub_type']]))
			{
				foreach ($subscribe_modes[$row['sub_type']] as $var)
				{
					array_push($$var, $row['sub_user_id']);
				}
			}
			else
			{
				array_push($$subscribe_modes[$row['sub_type']], $row['sub_user_id']);
			}
		}
		$db->sql_freeresult($result);

		$message = sprintf($user->lang['BLOG_SUBSCRIPTION_NOTICE'], $view_url, $user->data['username'], $unsubscribe_url);
	}
	else if ($mode == 'new_blog' && $uid != 0)
	{
		$sql = 'SELECT* FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE user_id = \'' . $uid . '\'
			AND sub_user_id != \'' . $user->data['user_id'] . '\'';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			if (is_array($subscribe_modes[$row['sub_type']]))
			{
				foreach ($subscribe_modes[$row['sub_type']] as $var)
				{
					array_push($$var, $row['sub_user_id']);
				}
			}
			else
			{
				array_push($$subscribe_modes[$row['sub_type']], $row['sub_user_id']);
			}
		}
		$db->sql_freeresult($result);

		$message = sprintf($user->lang['USER_SUBSCRIPTION_NOTICE'], $user->data['username'], $view_url, $unsubscribe_url);
	}

	$user_data->get_user_data('2');

	// Send the PM
	if (count($send_via_pm) > 0)
	{
		if (!function_exists('submit_pm'))
		{
			// include the private messages functions page
			include("{$phpbb_root_path}includes/functions_privmsgs.$phpEx");
		}

		if (!class_exists('parse_message'))
		{
			include("{$phpbb_root_path}includes/message_parser.$phpEx");
		}

		$message_parser = new parse_message();

		$message_parser->message = $message;
		$message_parser->parse(true, true, true);

		// setup out to address list
		foreach ($send_via_pm as $id)
		{
			$address_list[$id] = 'to';
		}

		$pm_data = array(
			'from_user_id'		=> 2,
			'from_username'		=> $user_data->user[2]['username'],
			'address_list'		=> array('u' => $address_list),
			'icon_id'			=> 10,
			'from_user_ip'		=> '0.0.0.0',
			'enable_bbcode'		=> true,
			'enable_smilies'	=> true,
			'enable_urls'		=> true,
			'enable_sig'		=> true,
			'message'			=> $message_parser->message,
			'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
			'bbcode_uid'		=> $message_parser->bbcode_uid,
		);

		submit_pm('post', $user->lang['SUBSCRIPTION_NOTICE'], $pm_data, false);
		unset($message_parser, $address_list, $pm_data);
	}

	// Send the email
	if (count($send_via_email) > 0 && $config['email_enable'])
	{
		if (!class_exists('messenger'))
		{
			include("{$phpbb_root_path}includes/functions_messenger.$phpEx");
		}

		$messenger = new messenger(false);

		$user_data->get_user_data($send_via_email);
		$reply_url_var = ($rid !== false) ? "r={$rid}#r{$rid}" : '';

		foreach ($send_via_email as $uid)
		{
			$messenger->template('blog_notify', $config['default_lang']);
			$messenger->replyto($config['board_contact']);
			$messenger->to($user_data->user[$uid]['user_email'], $user_data->user[$uid]['username']);

			$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
			$messenger->headers('X-AntiAbuse: User_id - ' . $user_data->user[2]['user_id']);
			$messenger->headers('X-AntiAbuse: Username - ' . $user_data->user[2]['username']);
			$messenger->headers('X-AntiAbuse: User IP - ' . $user_data->user[2]['user_ip']);

			$messenger->assign_vars(array(
				'BOARD_CONTACT'	=> $config['board_contact'],
				'SUBJECT'		=> $user->lang['SUBSCRIPTION_NOTICE'],
				'TO_USERNAME'	=> $user_data->user[$uid]['username'],
				'TYPE'			=> ($rid !== false) ? $user->lang['REPLY'] : $user->lang['BLOG'],
				'NAME'			=> $post_subject,
				'BY_USERNAME'	=> $user->data['username'],
				'U_VIEW'		=> $view_url,
				'U_UNSUBSCRIBE'	=> $unsubscribe_url,
			));

			$messenger->send(NOTIFY_EMAIL);
		}
		unset($messenger);
	}

	$blog_plugins->plugin_do('function_handle_subscription_end');
}

/**
* Syncronise Blog Data
*
* This should never need to be used unless someone manually deletes blogs or replies from the database
* It is not used by the User Blog mod anywhere, except for updates/upgrades and the resync page.
* To any potential users: Make sure you do not set this in a page where it gets ran often.  Resyncing data is a long process, especially when the number of blogs that you have is large
*
* @param string $mode can be all, reply_count, real_reply_count, delete_orphan_replies, or user_blog_count
*/
function resync_blog($mode)
{
	global $db, $blog_plugins;

	$blog_data = array();
	$reply_data = array();

	// Start by selecting all blog data that we will use
	$sql = 'SELECT blog_id, blog_reply_count, blog_real_reply_count FROM ' . BLOGS_TABLE . ' ORDER BY blog_id ASC';
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
		$blog_data[$row['blog_id']] = $row;
	}
	$db->sql_freeresult($result);

	/*
	* Update & Resync the reply counts
	*/
	if ( ($mode == 'reply_count') || ($mode == 'all') )
	{
		foreach($blog_data as $row)
		{
			// count all the replies (an SQL query seems the easiest way to do it)
			$sql = 'SELECT count(reply_id) AS total 
				FROM ' . BLOGS_REPLY_TABLE . ' 
					WHERE blog_id = \'' . $row['blog_id'] . '\' 
						AND reply_deleted = \'0\' 
						AND reply_approved = \'1\'';
			$result = $db->sql_query($sql);
			$total = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($total['total'] != $row['blog_reply_count'])
			{
				// Update the reply count
				$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_reply_count = \'' . $total['total'] . '\' WHERE blog_id = \'' . $row['blog_id'] . '\'';
				$db->sql_query($sql);
			}
		}
	}

	/*
	* Update & Resync the real reply counts
	*/
	if ( ($mode == 'real_reply_count') || ($mode == 'all') )
	{
		foreach($blog_data as $row)
		{
			// count all the replies (an SQL query seems the easiest way to do it)
			$sql = 'SELECT count(reply_id) AS total 
				FROM ' . BLOGS_REPLY_TABLE . ' 
					WHERE blog_id = \'' . $row['blog_id'] . '\'';
			$result = $db->sql_query($sql);
			$total = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($total['total'] != $row['blog_real_reply_count'])
			{
				// Update the reply count
				$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_real_reply_count = \'' . $total['total'] . '\' WHERE blog_id = \'' . $row['blog_id'] . '\'';
				$db->sql_query($sql);
			}
		}
	}

	/*
	* Delete's all oprhaned replies (replies where the blogs they should go under have been deleted).
	*/
	if ( ($mode == 'delete_orphan_replies') || ($mode == 'all') )
	{
		// Now get all reply data we will use
		$sql = 'SELECT reply_id, blog_id FROM ' . BLOGS_REPLY_TABLE . ' ORDER BY reply_id ASC';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			// if the blog_id it attached to is not in $blog_data
			if (!(array_key_exists($row['blog_id'], $blog_data)))
			{
				$sql2 = 'DELETE FROM ' . BLOGS_REPLY_TABLE . ' WHERE reply_id = \'' . $row['reply_id'] . '\'';
				$db->sql_query($sql2);
			}
		}
		$db->sql_freeresult($result);
	}

	/*
	* Updates the blog_count for each user
	*/
	if ( ($mode == 'user_blog_count') || ($mode == 'all') )
	{
		// select the users data we will need
		$sql = 'SELECT user_id, blog_count FROM ' . USERS_TABLE;
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			// count all the replies (an SQL query seems the easiest way to do it)
			$sql2 = 'SELECT count(blog_id) AS total 
				FROM ' . BLOGS_TABLE . ' 
					WHERE user_id = \'' . $row['user_id'] . '\' 
						AND blog_deleted = \'0\' 
						AND blog_approved = \'1\'';
			$result2 = $db->sql_query($sql2);
			$total = $db->sql_fetchrow($result2);
			$db->sql_freeresult($result2);

			if ($total['total'] != $row['blog_count'])
			{
				// Update the reply count
				$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = \'' . $total['total'] . '\' WHERE user_id = \'' . $row['user_id'] . '\'';
				$db->sql_query($sql);
			}
		}
		$db->sql_freeresult($result);
	}

	/**
	* Updates the user permissions for each blog
	*/
	if ( ($mode == 'user_permissions' ) || ($mode == 'all') )
	{
		$sql = 'SELECT * FROM ' . BLOGS_USERS_TABLE;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_ary = array(
				'perm_guest'		=> $row['perm_guest'],
				'perm_registered'	=> $row['perm_registered'],
				'perm_foe'			=> $row['perm_foe'],
				'perm_friend'		=> $row['perm_friend'],
			);

			$sql = 'UPDATE ' . BLOGS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE user_id = \'' . $row['user_id'] . '\'';
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);
	}

	// clear the user blog mod's cache
	handle_blog_cache('blog', false);

	$blog_plugins->plugin_do_arg('function_resync_blog', $mode);
}
?>