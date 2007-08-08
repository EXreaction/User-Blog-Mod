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
 * handles sending subscription notices for blogs or replies
 *
 * Sends a PM or Email to each user in the subscription list, depending on what they want
 *
 * @param string $post_subject The subject of the post made
 * @param int|bool $blog_id The user_id of the user who made the new blog (if there is one)
 * @param int|bool $blog_id The blog_id of the blog
 * @param int|bool $reply_id The reply_id of the new reply (if there is one)
 */
function handle_subscription($post_subject, $user_id, $blog_id, $reply_id = false)
{
	global $db, $user, $phpbb_root_path, $phpEx, $blog_data, $config;

	if (!$config['user_blog_subscription_enabled'])
	{
		return;
	}

	$send_via_pm = array();
	$send_via_email = array();

	if ($reply_id !== false)
	{
		$sql = 'SELECT * FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE blog_id = \'' . $blog_id . '\'';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			switch ($row['sub_type'])
			{
				case 0 :
					$send_via_pm[] = $row['sub_user_id'];
					break;
				case 1 :
					$send_via_email[] = $row['sub_user_id'];
					break;
				case 2 :
					$send_via_pm[] = $row['sub_user_id'];
					$send_via_email[] = $row['sub_user_id'];
			}
		}

		$message = sprintf($user->lang['BLOG_SUBSCRIPTION_NOTICE'], redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "b=$blog_id"), true), $user->data['username'], redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "page=unsubscribe&amp;b=$blog_id"), true));
	}
	else if ($user_id !== false)
	{
		$sql = 'SELECT * FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE user_id = \'' . $user_id . '\'';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			switch ($row['sub_type'])
			{
				case 0 :
					$send_via_pm[] = $row['sub_user_id'];
					break;
				case 1 :
					$send_via_email[] = $row['sub_user_id'];
					break;
				case 2 :
					$send_via_pm[] = $row['sub_user_id'];
					$send_via_email[] = $row['sub_user_id'];
			}
		}

		$message = sprintf($user->lang['USER_SUBSCRIPTION_NOTICE'], $user->data['username'], redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "b=$blog_id"), true), redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "page=unsubscribe&amp;u=$user_id"), true));
	}

	$blog_data->get_user_data('2');

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
			'from_username'		=> $blog_data->user[2]['username'],
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

	if (count($send_via_email) > 0 && $config['email_enable'])
	{
		// include the messenger functions file
		include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		$messenger = new messenger(false);

		$blog_data->get_user_data($send_via_email);
		$reply_url_var = ($reply_id !== false) ? "r={$reply_id}#r{$reply_id}" : '';

		foreach ($send_via_email as $uid)
		{
			$messenger->template('blog_notify', $config['default_lang']);
			$messenger->replyto($config['board_contact']);
			$messenger->to($blog_data->user[$uid]['user_email'], $blog_data->user[$uid]['username']);

			$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
			$messenger->headers('X-AntiAbuse: User_id - ' . $blog_data->user[2]['user_id']);
			$messenger->headers('X-AntiAbuse: Username - ' . $blog_data->user[2]['username']);
			$messenger->headers('X-AntiAbuse: User IP - ' . $blog_data->user[2]['user_ip']);

			$messenger->assign_vars(array(
				'BOARD_CONTACT'	=> $config['board_contact'],
				'SUBJECT'		=> $user->lang['SUBSCRIPTION_NOTICE'],
				'TO_USERNAME'	=> $blog_data->user[$uid]['username'],
				'TYPE'			=> ($reply_id !== false) ? $user->lang['REPLY'] : $user->lang['BLOG'],
				'NAME'			=> $post_subject,
				'BY_USERNAME'	=> $user->data['username'],
				'U_VIEW'		=> redirect(append_sid("{$phpbb_root_path}blog.$phpEx", "u={$user_id}&amp;b={$blog_id}" . $reply_url_var), true),
				'U_UNSUBSCRIBE'	=> ($reply_id !== false) ? redirect(append_sid("{$phpbb_root_path}blog.$phpEx", "u={$user_id}&amp;b={$blog_id}"), true) : redirect(append_sid("{$phpbb_root_path}blog.$phpEx", "u={$user_id}")),
			));

			$messenger->send(NOTIFY_EMAIL);
		}
	}
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
	global $auth, $db, $template, $phpbb_root_path, $phpEx, $user, $config, $s_hidden_fields, $user_founder;

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
		$seed -= 0x7fffffff * floor($seed / 0x7fffffff);

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

	// check if the User Blog Mod is enabled
	if (!$config['user_blog_enable'])
	{
		return;
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
			'PROFILE_FIELD_VALUE'		=> '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "u=$user_id") . '">' . $user->lang['VIEW_BLOGS'] . ' (' .$user_data['blog_count'] . ')</a>',
		));
	}
	else if (!$grab_from_db && $user_data['blog_count'] == -1)
	{
		$template->assign_block_vars($block, array(
			'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
			'PROFILE_FIELD_VALUE'		=> '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "u=$user_id") . '">' . $user->lang['VIEW_BLOGS'] . '</a>',
		));
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
			'USERNAME'	=> $blog_data->user[$blog_data->blog[$id]['user_id']]['username'],
		);

		$template->assign_block_vars('item', $blog_row + $row);
	}

	// tell the template parser what template file to use
	$template->set_filenames(array(
		'body' => 'blog_feed.xml'
	));
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
	global $phpbb_root_path, $phpEx, $config, $user;
	
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
		'from_username'		=> $blog_data->user[2]['username'],
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
 * Create the breadcrumbs
 *
 * @param string $crumb_lang The last language option in the breadcrumbs
 */
function generate_blog_breadcrumbs($crumb_lang = '')
{
	global $template, $user;
	global $page, $username, $blog_id, $reply_id;
	global $blog_urls, $blog_data;

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
					'FORUM_NAME'		=> (censor_text($blog_data->reply[$reply_id]['reply_subject']) != '') ? censor_text($blog_data->reply[$reply_id]['reply_subject']) : $user->lang['UNTITLED_REPLY'],
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
	global $db, $template, $phpbb_root_path, $phpEx, $user, $blog_data;

// output the data for the left author info menu
	$template->assign_vars($blog_data->handle_user_data($user_id));
	$blog_data->handle_user_data($user_id, 'custom_fields');

// archive menu
	// Last Month's ID(set to 0 now, will be updated in the loop)
	$last_mon = 0;

	// Count Variable
	$i = 0;

	// SQL query
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
			// Output the month and year
			$template->assign_block_vars('archiverow', array(
				'MONTH'			=> $date['month'],
				'YEAR'			=> $date['year'],

				'S_FIRST'		=> ($i == 0) ? true : false,

				// output the counter
				'I'				=> $i,
			));
		}

		// Now output the title, view blog link, and date
		$template->assign_block_vars('archiverow.monthrow', array(
			'TITLE'			=> $row['blog_subject'],
			'U_VIEW'		=> append_sid("{$phpbb_root_path}blog.$phpEx", 'b=' . $row['blog_id']),
			'DATE'			=> $user->format_date($row['blog_time']),
		));

		// set the last month variable as the current month
		$last_mon = $date['mon'];

		// Increment the counter
		$i++;
	}

	// output some data
	$template->assign_vars(array(
		// are there any archives?
		'S_ARCHIVES'	=> ($i > 0) ? true : false,
	));

	$db->sql_freeresult($result);
}

/**
 * generates the basic URL's used by this mod
 */
function generate_blog_urls()
{
	global $blog_urls, $blog_id, $reply_id, $user_id, $user;
	global $phpbb_root_path, $phpEx, $config;

	$self = $_SERVER['REQUEST_URI'];

	$blog_urls = array(
		'main'				=> append_sid("{$phpbb_root_path}blog.$phpEx"),
		'self'				=> reapply_sid($self),
		'self_print'		=> reapply_sid($self . '&amp;view=print'),
		'self_minus_start'	=> (strpos($self, 'start=')) ? reapply_sid(substr($self, 0, (strpos($self, 'start=')) - 1) . substr($self, (strpos($self, 'start=')) + 8)) : reapply_sid($self),
		'subscribe'			=> ($config['user_blog_subscription_enabled'] && ($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != $user_id && $user->data['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=subscribe&amp;b={$blog_id}&amp;u=$user_id") : '',
		'unsubscribe'		=> ($config['user_blog_subscription_enabled'] && ($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != $user_id && $user->data['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=unsubscribe&amp;b={$blog_id}&amp;u=$user_id") : '',

		'add_blog'			=> append_sid("{$phpbb_root_path}blog.$phpEx", 'page=blog&amp;mode=add'),
		'add_reply'			=> ($blog_id) ? append_sid("{$phpbb_root_path}blog.$phpEx", 'page=reply&amp;mode=add&amp;b=' . $blog_id) : '',

		'view_blog'			=> ($blog_id != 0) ? append_sid("{$phpbb_root_path}blog.$phpEx", "b={$blog_id}") : '',
		'view_reply'		=> ($reply_id != 0) ? append_sid("{$phpbb_root_path}blog.$phpEx", "b={$blog_id}&amp;r={$reply_id}#r{$reply_id}") : '',
		'view_user'			=> ($user_id != 0) ? append_sid("{$phpbb_root_path}blog.$phpEx", "u={$user_id}") : false,
		'view_user_deleted'	=> ($user_id != 0) ? append_sid("{$phpbb_root_path}blog.$phpEx", "mode=deleted&amp;u={$user_id}") : false,
		'view_user_self'	=> append_sid("{$phpbb_root_path}blog.$phpEx", 'u=' . $user->data['user_id']),
	);
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
	global $db;

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
}
?>