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
	global $blog_data, $blog_urls, $blog_plugins;

	// if $uid, $bid, or $rid are not set, use the globals
	$uid = ($uid != 0) ? $uid : $user_id;
	$bid = ($bid != 0) ? $bid : $blog_id;
	$rid = ($rid != 0) ? $rid : $reply_id;

	// make sure that subscriptions are enabled and that a blog_id is sent
	if (!$config['user_blog_subscription_enabled'] || $bid == 0)
	{
		return;
	}

	if (!isset($user->lang['BLOG_SUBSCRIPTION_NOTICE']))
	{
		$user->add_lang('mods/blog/posting');
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
		$sql = 'SELECT * FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE blog_id = ' . intval($bid) . '
			AND sub_user_id != ' . $user->data['user_id'];
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
			WHERE user_id = ' . intval($uid) . '
			AND sub_user_id != ' . $user->data['user_id'];
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

	$blog_data->get_user_data('2');

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
			'from_username'		=> blog_data::$user[2]['username'],
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

		$blog_data->get_user_data($send_via_email);
		$reply_url_var = ($rid) ? "r={$rid}#r{$rid}" : '';

		foreach ($send_via_email as $uid)
		{
			$messenger->template('blog_notify', $config['default_lang']);
			$messenger->replyto($config['board_contact']);
			$messenger->to(blog_data::$user[$uid]['user_email'], blog_data::$user[$uid]['username']);

			$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
			$messenger->headers('X-AntiAbuse: User_id - ' . blog_data::$user[2]['user_id']);
			$messenger->headers('X-AntiAbuse: Username - ' . blog_data::$user[2]['username']);
			$messenger->headers('X-AntiAbuse: User IP - ' . blog_data::$user[2]['user_ip']);

			$messenger->assign_vars(array(
				'BOARD_CONTACT'	=> $config['board_contact'],
				'SUBJECT'		=> $user->lang['SUBSCRIPTION_NOTICE'],
				'TO_USERNAME'	=> blog_data::$user[$uid]['username'],
				'TYPE'			=> ($rid) ? $user->lang['REPLY'] : $user->lang['BLOG'],
				'NAME'			=> $post_subject,
				'BY_USERNAME'	=> $user->data['username'],
				'U_VIEW'		=> $view_url,
				'U_UNSUBSCRIBE'	=> $unsubscribe_url,
			));

			$messenger->send(NOTIFY_EMAIL);
		}

		// save the queue if we must
		$messenger->save_queue();

		unset($messenger);
	}

	$blog_plugins->plugin_do('function_handle_subscription_end');
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

?>