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
	include($phpbb_root_path . 'includes/blog/data/constants.' . $phpEx);

	/**
	* URL handler
	*/
	function blog_url($user_id, $blog_id = false, $reply_id = false, $url_data = array(), $extra_data = array())
	{
		global $config, $phpbb_root_path, $phpEx, $user;
		global $blog_data, $reply_data, $user_data;

		if ($config['user_blog_seo'])
		{
			if ($user_id != false)
			{
				if (!array_key_exists($user_id, $user_data->user))
				{
					$user_data->get_user_data($user_id);
				}
				$username = utf8_clean_string($user_data->user[$user_id]['username']);
			}
			else
			{
				$username = 'data';
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
				if (array_key_exists($blog_id, $blog_data->blog))
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
	* Generates the left side menu
	*
	* @param int $user_id The user_id of the user whom we are building the menu for
	*/
	function generate_menu($user_id)
	{
		global $db, $template, $phpbb_root_path, $phpEx, $user, $cache;
		global $blog_data, $reply_data, $user_data, $user_founder, $blog_urls, $blog_plugins;

	// output the data for the left author info menu
		$template->assign_vars($user_data->handle_user_data($user_id));
		$user_data->handle_user_data($user_id, 'custom_fields');

	// archive menu
		// Last Month's ID(set to 0 now, will be updated in the loop)
		$last_mon = 0;

		$archive_rows = array();

		// attempt to get the data from the cache
		$cache_data = $cache->get("_blog_archive{$user_id}");

		if ($cache_data === false)
		{
			$sql = 'SELECT blog_id, blog_time, blog_subject FROM ' . BLOGS_TABLE . '
						WHERE user_id = \'' . $user_id . '\'
							AND blog_deleted = \'0\'
								ORDER BY blog_id DESC';
			$result = $db->sql_query($sql);

			while($row = $db->sql_fetchrow($result))
			{
				$date = getdate($row['blog_time']);

				// If we are starting a new month
				if ($date['mon'] != $last_mon)
				{
					$archive_row = array(
						'MONTH'			=> $date['month'],
						'YEAR'			=> $date['year'],

						'monthrow'		=> array(),
					);

					$archive_rows[] = $archive_row;
				}

				$archive_row_month = array(
					'TITLE'			=> censor_text($row['blog_subject']),
					'U_VIEW'		=> blog_url($user_id, $row['blog_id'], false, array(), array('blog_subject' => $row['blog_subject'])),
					'DATE'			=> $user->format_date($row['blog_time']),
				);

				$archive_rows[count($archive_rows) - 1]['monthrow'][] = $archive_row_month;

				// set the last month variable as the current month
				$last_mon = $date['mon'];
			}
			$db->sql_freeresult($result);

			// cache the result
			$cache->put("_blog_archive{$user_id}", $archive_rows);
			$cache_data = $archive_rows;
		}

		if (count($cache_data))
		{
			foreach($cache_data as $row)
			{
				$template->assign_block_vars('archiverow', $row);
			}
		}

		// output some data
		$template->assign_vars(array(
			// are there any archives?
			'S_ARCHIVES'	=> (count($cache_data)) ? true : false,
		));

		$blog_plugins->plugin_do('function_generate_menu');
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
						return true;
					}
				}
			}
		}

		return false;
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
		global $blog_data, $reply_data, $user_data, $user_founder, $blog_urls, $blog_plugins;

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
		$blog_plugins->plugin_do_arg('function_handle_subscription', $subscribe_modes);

		// setup the arrays which will hold the to info for PM's/Emails
		$send_via_pm = array();
		$send_via_email = array();

		if ($mode == 'new_reply' && $rid != 0)
		{
			$sql = 'SELECT* FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
				WHERE blog_id = \'' . $bid . '\'';
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

			$message = sprintf($user->lang['BLOG_SUBSCRIPTION_NOTICE'], redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "b=$bid"), true), $user->data['username'], redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "page=unsubscribe&amp;b=$bid"), true));
		}
		else if ($mode == 'new_blog' && $uid != 0)
		{
			$sql = 'SELECT* FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
				WHERE user_id = \'' . $uid . '\'';
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

			$message = sprintf($user->lang['USER_SUBSCRIPTION_NOTICE'], $user->data['username'], redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "b=$bid"), true), redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "page=unsubscribe&amp;u=$uid"), true));
		}

		$user_data->get_user_data('2');

		// Send the PM
		if (count($send_via_pm) > 0)
		{
			// include the private messages functions page
			include_once("{$phpbb_root_path}includes/functions_privmsgs.$phpEx");

			$message_parser = new parse_message();
			$message_parser->message = $message;
			$message_parser->parse(true, true, true, true, true, true, true);

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
				'enable_sig'		=> false,
				'message'			=> $message_parser->message,
				'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
				'bbcode_uid'		=> $message_parser->bbcode_uid,
			);

			submit_pm('post', $user->lang['SUBSCRIPTION_NOTICE'], $pm_data, false);
		}

		// Send the email
		if (count($send_via_email) > 0 && $config['email_enable'])
		{
			// include the messenger functions file
			include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
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
					'U_VIEW'		=> redirect(append_sid("{$phpbb_root_path}blog.$phpEx", "u={$uid}&amp;b={$bid}" . $reply_url_var), true),
					'U_UNSUBSCRIBE'	=> ($rid !== false) ? redirect(append_sid("{$phpbb_root_path}blog.$phpEx", "u={$uid}&amp;b={$bid}"), true) : redirect(append_sid("{$phpbb_root_path}blog.$phpEx", "u={$uid}")),
				));

				$messenger->send(NOTIFY_EMAIL);
			}
		}

		$blog_plugins->plugin_do('function_handle_subscription_end');
	}

	/**
	* handle_captcha
	*
	* @param string $mode The mode, build or check, to either build the captcha/confirm box, or to check if the user entered the correct confirm_code
	*
	* @return Returns
	*	- True if the captcha code is correct and $mode is check or they do not need to view the captcha (permissions) 
	*	- False if the captcha code is incorrect, or not given and $mode is check
	*/
	function handle_captcha($mode)
	{
		global $auth, $db, $template, $phpbb_root_path, $phpEx, $user, $config, $s_hidden_fields;
		global $user_founder;

		// check if they need to have the captcha displayed at all.  If they don't just return.
		if ($auth->acl_get('u_blognocaptcha') || $user_founder)
		{
			return true;
		}

		if ($mode == 'check')
		{
			$confirm_id = request_var('confirm_id', '');
			$confirm_code = request_var('confirm_code', '');

			if ($confirm_id == '' || $confirm_code == '')
			{
				return false;
			}

			$sql = 'SELECT code
				FROM ' . CONFIRM_TABLE . "
				WHERE confirm_id = '" . $db->sql_escape($confirm_id) . "'
					AND session_id = '" . $db->sql_escape($user->session_id) . "'
					AND confirm_type = " . CONFIRM_POST;
			$result = $db->sql_query($sql);
			$confirm_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (empty($confirm_row['code']) || strcasecmp($confirm_row['code'], $confirm_code) !== 0)
			{
				return false;
			}

			// add confirm_id and confirm_code to hidden fields if not already there so the user doesn't need to retype in the confirm code if 
			if (strpos($s_hidden_fields, 'confirm_id') === false)
			{
				$s_hidden_fields .= build_hidden_fields(array('confirm_id' => $confirm_id, 'confirm_code' => $confirm_code));
			}

			return true;
		}
		else if ($mode == 'build' && !handle_captcha('check'))
		{
			// Show confirm image
			$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
				WHERE session_id = '" . $db->sql_escape($user->session_id) . "'
					AND confirm_type = " . CONFIRM_POST;
			$db->sql_query($sql);

			// Generate code
			$code = gen_rand_string(mt_rand(5, 8));
			$confirm_id = md5(unique_id($user->ip));
			$seed = hexdec(substr(unique_id(), 4, 10));

			// compute $seed % 0x7fffffff
			$seed -= 0x7fffffff* floor($seed / 0x7fffffff);

			$sql = 'INSERT INTO ' . CONFIRM_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'confirm_id'	=> (string) $confirm_id,
				'session_id'	=> (string) $user->session_id,
				'confirm_type'	=> (int) CONFIRM_POST,
				'code'			=> (string) $code,
				'seed'			=> (int) $seed)
			);
			$db->sql_query($sql);

			$template->assign_vars(array(
				'S_CONFIRM_CODE'			=> true,
				'CONFIRM_ID'				=> $confirm_id,
				'CONFIRM_IMAGE'				=> '<img src="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=confirm&amp;id=' . $confirm_id . '&amp;type=' . CONFIRM_POST) . '" alt="" title="" />',
				'L_POST_CONFIRM_EXPLAIN'	=> sprintf($user->lang['POST_CONFIRM_EXPLAIN'], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>'),
			));
		}
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
		if ($user_id > 1 && !isset($user_data['blog_count']) && $grab_from_db)
		{
			$sql = 'SELECT blog_count FROM ' . USERS_TABLE . ' WHERE user_id = \'' . intval($user_id) . '\'';
			$result = $db->sql_query($sql);
			$user_data = $db->sql_fetchrow($result);
		}
		else if (!isset($user_data['blog_count']))
		{
			$user_data['blog_count'] = -1;
		}
		
		if ($user_data['blog_count'] > 0 || (($config['user_blog_always_show_blog_url'] || $force_output) && $user_data['blog_count'] >= 0))
		{
			$template->assign_block_vars($block, array(
				'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
				'PROFILE_FIELD_VALUE'		=> '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", 'u=' . $user_id) . '">' . $user->lang['VIEW_BLOGS'] . ' (' .$user_data['blog_count'] . ')</a>',
			));
		}
		else if (!$grab_from_db && $user_data['blog_count'] == -1)
		{
			$template->assign_block_vars($block, array(
				'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
				'PROFILE_FIELD_VALUE'		=> '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", 'u=' . $user_id) . '">' . $user->lang['VIEW_BLOGS'] . '</a>',
			));
		}
	}

	/**
	* Informs users when a blog or reply was reported or needs approval
	*
	* Informs users in the $config['user_blog_inform'] variable (in the variable should be user_id's seperated by commas if there is more than one)
	*
	* @param string $mode The mode - blog_report, reply_report, blog_approve, reply_approve
	*/
	function inform_approve_report($mode, $id)
	{
		global $phpbb_root_path, $phpEx, $config, $user, $blog_plugins;
		
		if ($config['user_blog_inform'] == '')
		{
			return;
		}

		switch ($mode)
		{
			case 'blog_report' :
				$message = sprintf($user->lang['BLOG_REPORT_PM'], $user->data['username'], append_sid("{$phpbb_root_path}blog.$phpEx", "b=$id"));
				$subject = $user->lang['BLOG_REPORT_PM_SUBJECT'];
				break;
			case 'reply_report' :
				$message = sprintf($user->lang['REPLY_REPORT_PM'], $user->data['username'], append_sid("{$phpbb_root_path}blog.$phpEx", "r=$id"));
				$subject = $user->lang['REPLY_REPORT_PM_SUBJECT'];
				break;
			case 'blog_approve' :
				$message = sprintf($user->lang['BLOG_APPROVE_PM'], $user->data['username'], append_sid("{$phpbb_root_path}blog.$phpEx", "b=$id"));
				$subject = $user->lang['BLOG_APPROVE_PM_SUBJECT'];
				break;
			case 'reply_approve' :
				$message = sprintf($user->lang['REPLY_APPROVE_PM'], $user->data['username'], append_sid("{$phpbb_root_path}blog.$phpEx", "r=$id"));
				$subject = $user->lang['REPLY_APPROVE_PM_SUBJECT'];
				break;
			default:
				$blog_plugins->plugin_do_arg('function_inform_approve_report', $mode);
		}

		$to = explode(",", $config['user_blog_inform']);

		// include the private messages functions page
		include_once("{$phpbb_root_path}includes/functions_privmsgs.$phpEx");

		$message_parser = new parse_message();
		$message_parser->message = $message;
		$message_parser->parse(true, true, true, true, true, true, true);

		// setup out to address list
		foreach ($to as $id)
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
			'enable_sig'		=> false,
			'message'			=> $message_parser->message,
			'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
			'bbcode_uid'		=> $message_parser->bbcode_uid,
		);

		submit_pm('post', $subject, $pm_data, false);
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

		// clear the user blog mod's cache
		handle_blog_cache('blog', false);

		$blog_plugins->plugin_do_arg('function_resync_blog', $mode);
	}
}
?>