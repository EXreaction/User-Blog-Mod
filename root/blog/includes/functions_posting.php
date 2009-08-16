<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: functions_posting.php 493 2008-08-28 17:43:39Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Handle basic posting setup and some basic checks
*/
function handle_basic_posting_data($check = false, $page = 'blog', $mode = 'add')
{
	global $auth, $blog_attachment, $blog_id, $config, $db, $template, $user, $phpbb_root_path, $phpEx, $category_ary;

	$submit = (isset($_POST['submit'])) ? true : false;
	$preview = (isset($_POST['preview'])) ? true : false;
	$refresh = (isset($_POST['add_file']) || isset($_POST['delete_file']) || isset($_POST['cancel_unglobalise'])) ? true : false;
	$submitted = ($submit || $preview || $refresh) ? true : false; // shortcut for any of the 3 above

	if ($check)
	{
		$error = array();

		// check the captcha
		if ($mode == 'add')
		{
			if (!handle_captcha('check'))
			{
				$error[] = $user->lang['CONFIRM_CODE_WRONG'];
			}
		}

		// check the form key
		if (!check_form_key('postform'))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}

		return $error;
	}
	else
	{
		$above_subject = $above_message = $above_submit = $panel_data = '';

		$panels = array(
			'options-panel'			=> $user->lang['OPTIONS'],
		);

		if ($page == 'blog')
		{
			$category_list = make_category_select($category_ary);

			if ($category_list)
			{
				$panels['categories-panel'] = $user->lang['CATEGORIES'];
			}

			$panels['poll-panel'] = $user->lang['ADD_POLL'];

			if ($user->data['is_registered'])
			{
				// Build permissions box
				permission_settings_builder(true, $mode);
				$panels['permissions-panel'] = $user->lang['PERMISSIONS'];
			}

			// Some variables
			$template->assign_vars(array(
				'CATEGORY_LIST'				=> $category_list,

				'S_CAT_0_SELECTED'			=> (is_array($category_ary) && in_array(0, $category_ary)),
				'S_SHOW_POLL_BOX'			=> true,
			));
		}

		if ($mode == 'add')
		{
			// setup the captcha
			handle_captcha('build');
		}

		// Subscriptions
		if ($config['user_blog_subscription_enabled'] && $user->data['is_registered'])
		{
			$panels['subscriptions-panel'] = $user->lang['SUBSCRIPTION'];

			$subscription_types = get_blog_subscription_types();
			$subscribed = array();

			if ($page == 'blog' && $mode == 'add' && !$submitted)
			{
				// check default subscription settings from user_settings
				global $user_settings;
				get_user_settings($user->data['user_id']);

				if (isset($user_settings[$user->data['user_id']]))
				{
					foreach ($subscription_types as $type => $name)
					{
						// Bitwise check
						if ($user_settings[$user->data['user_id']]['blog_subscription_default'] & $type)
						{
							$subscribed[$type] = true;
						}
					}
				}
			}
			else if (!$submitted)
			{
				// check set subscription settings
				$sql = 'SELECT * FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
					WHERE sub_user_id = ' . $user->data['user_id'] . '
						AND blog_id = ' . intval($blog_id);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$subscribed[$row['sub_type']] = true;
				}
			}

			foreach ($subscription_types as $type => $name)
			{
				$template->assign_block_vars('subscriptions', array(
					'TYPE'		=> 'subscription_' . $type,
					'NAME'		=> ((isset($user->lang[$name])) ? $user->lang[$name] : $name),
					'S_CHECKED'	=> ((($submitted && request_var('subscription_' . $type, false)) || isset($subscribed[$type])) ? true : false),
				));
			}
		}

		// Attachments
		$attachment_data = $blog_attachment->attachment_data;
		$filename_data = $blog_attachment->filename_data;
		$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments'] || !$auth->acl_get('u_attach')) ? '' : ' enctype="multipart/form-data"';
		posting_gen_inline_attachments($attachment_data);
		if (($auth->acl_get('u_blogattach')) && $config['allow_attachments'] && $form_enctype)
		{
			$allowed_extensions = $blog_attachment->obtain_blog_attach_extensions();

			if (sizeof($allowed_extensions['_allowed_']))
			{
				$blog_attachment->posting_gen_attachment_entry($attachment_data, $filename_data);

				$panels['attach-panel'] = $user->lang['ADD_ATTACHMENT'];
			}
		}

		// Add the forum key
		add_form_key('postform');

		// Generate smiley listing
		generate_smilies('inline', false);

		// Build custom bbcodes array
		display_custom_bbcodes();

		$temp = compact('page', 'mode', 'panels', 'panel_data', 'above_subject', 'above_message', 'above_submit');
		blog_plugins::plugin_do_ref('function_handle_basic_posting_data', $temp);
		extract($temp);

		$template->assign_vars(array(
			'EXTRA_ABOVE_SUBJECT'		=> $above_subject,
			'EXTRA_ABOVE_MESSAGE'		=> $above_message,
			'EXTRA_ABOVE_SUBMIT'		=> $above_submit,
			'EXTRA_PANELS'				=> $panel_data,
			'JS_PANELS_LIST'			=> "'" . implode("', '", array_keys($panels)) . "'",

			'UA_PROGRESS_BAR'			=> append_sid("{$phpbb_root_path}posting.$phpEx", "mode=popup", false),

			'S_BLOG'					=> ($page == 'blog') ? true : false,
			'S_REPLY'					=> ($page == 'reply') ? true : false,
			'S_CLOSE_PROGRESS_WINDOW'	=> (isset($_POST['add_file'])) ? true : false,
			'S_FORM_ENCTYPE'			=> $form_enctype,
		));

		foreach ($panels as $name => $title)
		{
			$template->assign_vars(array(
				'S_' . strtoupper(str_replace('-', '_', $name))		=> true,
			));
			$template->assign_block_vars('panel_list', array(
				'NAME'		=> $name,
				'TITLE'		=> $title,
			));
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
	global $db, $template, $phpbb_root_path, $phpEx, $user, $config, $s_hidden_fields;

	if ($user->data['user_id'] != ANONYMOUS || !$config['user_blog_guest_captcha'])
	{
		return true;
	}

	blog_plugins::plugin_do_arg('function_handle_captcha', $mode);

	if (file_exists($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx))
	{
		if (!class_exists('phpbb_captcha_factory'))
		{
			include($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
		}

		$captcha =& phpbb_captcha_factory::get_instance($config['captcha_plugin']);
		$captcha->init(CONFIRM_POST);

		if ($mode == 'check')
		{
			$captcha->validate();

			// add confirm_id and confirm_code to hidden fields if not already there so the user doesn't need to retype in the confirm code
			if (strpos($s_hidden_fields, 'confirm_id') === false)
			{
				$s_hidden_fields .= build_hidden_fields($captcha->get_hidden_fields());
			}

			return $captcha->is_solved();
		}
		else if ($mode == 'build' && !$captcha->solved)
		{
			// add confirm_id and confirm_code to hidden fields if not already there so the user doesn't need to retype in the confirm code
			if (strpos($s_hidden_fields, 'confirm_id') === false)
			{
				$s_hidden_fields .= build_hidden_fields($captcha->get_hidden_fields());
			}

			$template->assign_vars(array(
				'CAPTCHA_TEMPLATE'		=> $captcha->get_template(),
			));

			$template->set_filenames(array(
				'new_captcha'		=> 'blog/new_captcha.html'
			));

			$template->assign_display('new_captcha', 'CAPTCHA', false);

			return;
		}
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

		// add confirm_id and confirm_code to hidden fields if not already there so the user doesn't need to retype in the confirm code
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

		$template->set_filenames(array(
			'old_captcha'		=> 'blog/old_captcha.html'
		));

		$template->assign_var('CAPTCHA', $template->display('old_captcha'));
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
	global $phpbb_root_path, $phpEx, $config, $user;

	switch ($mode)
	{
		case 'blog_report' :
			$message = sprintf($user->lang['BLOG_REPORT_PM'], $user->data['username'], blog_url($user->data['user_id'], $id));
			$subject = $user->lang['BLOG_REPORT_PM_SUBJECT'];
			break;
		case 'reply_report' :
			$message = sprintf($user->lang['REPLY_REPORT_PM'], $user->data['username'], blog_url($user->data['user_id'], false, $id));
			$subject = $user->lang['REPLY_REPORT_PM_SUBJECT'];
			break;
		case 'blog_approve' :
			$message = sprintf($user->lang['BLOG_APPROVE_PM'], $user->data['username'], blog_url($user->data['user_id'], $id));
			$subject = $user->lang['BLOG_APPROVE_PM_SUBJECT'];
			break;
		case 'reply_approve' :
			$message = sprintf($user->lang['REPLY_APPROVE_PM'], $user->data['username'], blog_url($user->data['user_id'], false, $id));
			$subject = $user->lang['REPLY_APPROVE_PM_SUBJECT'];
			break;
		default:
			blog_plugins::plugin_do_arg('function_inform_approve_report', compact('mode', 'id'));
	}

	$to = explode(",", $config['user_blog_inform']);

	// setup out to address list
	$address_list = array();
	foreach ($to as $id)
	{
		$id = (int) $id;

		if ($id)
		{
			$address_list[$id] = 'to';
		}
	}

	if (sizeof($address_list))
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
		$message_parser->parse(true, true, true, true, true, true, true);

		$pm_data = array(
			'from_user_id'		=> $config['user_blog_message_from'],
			'from_username'		=> $user->lang['ADMINISTRATOR'],
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
}

/**
* Submit Poll
*
* @param array $poll All of the poll data required to submit it.
* @param int $blog_id The ID of the blog this is for
* @param string $mode The mode (edit or add)
*/
function submit_blog_poll($poll, $blog_id, $mode = 'add')
{
	global $db;

	// Update Poll Tables
	if (isset($poll['poll_options']))
	{
		$cur_poll_options = array();

		if ($poll['poll_start'] && $mode == 'edit')
		{
			$sql = 'SELECT *
				FROM ' . BLOGS_POLL_OPTIONS_TABLE . '
				WHERE blog_id = ' . $blog_id;
			$result = $db->sql_query($sql);

			$cur_poll_options = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$cur_poll_options[] = $row;
			}
			$db->sql_freeresult($result);
		}

		// If edited, we would need to reset votes (since options can be re-ordered above, you can't be sure if the change is for changing the text or adding an option
		if ($mode == 'edit' && sizeof($poll['poll_options']) != sizeof($cur_poll_options))
		{
			$sql = 'DELETE FROM ' . BLOGS_POLL_OPTIONS_TABLE . '
				WHERE blog_id = ' . $blog_id;
			$db->sql_query($sql);
			$db->sql_query('DELETE FROM ' . BLOGS_POLL_VOTES_TABLE . ' WHERE blog_id = ' . $blog_id);
			$db->sql_query('UPDATE ' . BLOGS_POLL_OPTIONS_TABLE . ' SET poll_option_total = 0 WHERE blog_id = ' . $blog_id);
		}
		else if ($mode == 'edit')
		{
			return;
		}

		$sql_insert_ary = array();

		for ($i = 0, $size = sizeof($poll['poll_options']); $i < $size; $i++)
		{
			if (strlen(trim($poll['poll_options'][$i])))
			{
				// If we add options we need to put them to the end to be able to preserve votes...
				$sql_insert_ary[] = array(
					'poll_option_id'	=> (int) sizeof($sql_insert_ary) + 1,
					'blog_id'			=> (int) $blog_id,
					'poll_option_text'	=> (string) $poll['poll_options'][$i]
				);
			}
		}

		$db->sql_multi_insert(BLOGS_POLL_OPTIONS_TABLE, $sql_insert_ary);
	}
	else if ($mode == 'edit')
	{
		$sql = 'DELETE FROM ' . BLOGS_POLL_OPTIONS_TABLE . '
			WHERE blog_id = ' . $blog_id;
		$db->sql_query($sql);
	}
}

/**
* Add Blog Subscriptions
*
* Automatically adds subscriptions for a blog depending on what settings were sent
*
* @param int $blog_id The blog_id this is for
* @param string $prefix The prefix for the $_POST setting
*/
function add_blog_subscriptions($blog_id, $prefix = '')
{
	global $cache, $config, $db, $user;

	if (!$config['user_blog_subscription_enabled'])
	{
		return;
	}

	// First delete any existing subscription for this blog
	$sql = 'DELETE FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
		WHERE sub_user_id = ' . $user->data['user_id'] . '
			AND blog_id = ' . intval($blog_id) . '
			AND user_id = 0';
	$db->sql_query($sql);

	// Then get the subscription types
	$subscription_types = get_blog_subscription_types();

	// Go through each subscription type and see if it is set...
	foreach ($subscription_types as $type => $name)
	{
		if (request_var($prefix . $type, false))
		{
			$sql_ary = array(
				'sub_user_id'	=> $user->data['user_id'],
				'sub_type'		=> $type,
				'blog_id'		=> intval($blog_id),
				'user_id'		=> 0,
			);

			$sql = 'INSERT INTO ' . BLOGS_SUBSCRIPTION_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}
	}

	$cache->destroy('_blog_subscription_' . $user->data['user_id']);
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
	global $blog_data, $blog_urls;

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

	// This will hold all the send info, all ones that will be sent via PM would be $send[1], or Email would be $send[2], next would be $send[4], etc.
	$send = array();

	$subscribe_modes = get_blog_subscription_types();
	$temp = compact('mode', 'post_subject', 'uid', 'bid', 'rid', 'send');
	blog_plugins::plugin_do_ref('function_handle_subscription', $temp);
	extract($temp);

	// Fix the URLs...
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
			if (!array_key_exists($row['sub_type'], $send))
			{
				$send[$row['sub_type']] = array($row['sub_user_id']);
			}
			else
			{
				$send[$row['sub_type']][] = $row['sub_user_id'];
			}
		}
		$db->sql_freeresult($result);

		$message = sprintf($user->lang['BLOG_SUBSCRIPTION_NOTICE'], $view_url, $user->data['username'], $unsubscribe_url);
	}
	else if ($mode == 'new_blog' && $uid != 0)
	{
		$sql = 'SELECT * FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE user_id = ' . intval($uid) . '
			AND sub_user_id != ' . $user->data['user_id'];
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			if (!array_key_exists($row['sub_type'], $send))
			{
				$send[$row['sub_type']] = array($row['sub_user_id']);
			}
			else
			{
				$send[$row['sub_type']][] = $row['sub_user_id'];
			}
		}
		$db->sql_freeresult($result);

		$message = sprintf($user->lang['USER_SUBSCRIPTION_NOTICE'], $user->data['username'], $view_url, $unsubscribe_url);
	}

	$blog_data->get_user_data($config['user_blog_message_from']);

	// Send the PM
	if (isset($send[1]) && sizeof($send[1]))
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
		$address_list = array();
		foreach ($send[1] as $id)
		{
			$address_list[$id] = 'to';
		}

		$pm_data = array(
			'from_user_id'		=> $config['user_blog_message_from'],
			'from_username'		=> blog_data::$user[$config['user_blog_message_from']]['username'],
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
	if (isset($send[2]) && sizeof($send[2]) && $config['email_enable'])
	{
		if (!class_exists('messenger'))
		{
			include("{$phpbb_root_path}includes/functions_messenger.$phpEx");
		}

		$messenger = new messenger(false);

		$blog_data->get_user_data($send[2]);
		$reply_url_var = ($rid) ? "r={$rid}#r{$rid}" : '';

		foreach ($send[2] as $uid)
		{
			$messenger->template('blog_notify', $config['default_lang']);
			$messenger->replyto($config['board_contact']);
			$messenger->to(blog_data::$user[$uid]['user_email'], blog_data::$user[$uid]['username']);

			$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
			$messenger->headers('X-AntiAbuse: User_id - ' . blog_data::$user[$config['user_blog_message_from']]['user_id']);
			$messenger->headers('X-AntiAbuse: Username - ' . blog_data::$user[$config['user_blog_message_from']]['username']);
			$messenger->headers('X-AntiAbuse: User IP - ' . blog_data::$user[$config['user_blog_message_from']]['user_ip']);

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

	blog_plugins::plugin_do('function_handle_subscription_end');
}

/**
* Check permission and settings for bbcode, img, url, etc
*/
class post_options
{
	// the permissions, so I can change them later easier if need be for a different mod or whatever...
	var $auth_bbcode = false;
	var $auth_smilies = false;
	var $auth_img = false;
	var $auth_url = false;
	var $auth_flash = false;

	// whether these are allowed or not
	var $bbcode_status = false;
	var $smilies_status = false;
	var $img_status = false;
	var $url_status = false;
	var $flash_status = false;

	// whether or not they are enabled in the post
	var $enable_bbcode = false;
	var $enable_smilies = false;
	var $enable_magic_url = false;

	/**
	 * Automatically sets the defaults for the $auth_ vars
	 */
	function post_options()
	{
		global $auth;

		$this->auth_bbcode = ($auth->acl_get('u_blogbbcode')) ? true : false;
		$this->auth_smilies = ($auth->acl_get('u_blogsmilies')) ? true : false;
		$this->auth_img = ($auth->acl_get('u_blogimg')) ? true : false;
		$this->auth_url = ($auth->acl_get('u_blogurl')) ? true : false;
		$this->auth_flash = ($auth->acl_get('u_blogflash')) ? true : false;

		blog_plugins::plugin_do('post_options');
	}

	/**
	 * set the status to the  variables above, the enabled options are if they are enabled in the posts(by who ever is posting it)
	 */
	function set_status($bbcode, $smilies, $url)
	{
		global $config, $auth;

		$this->bbcode_status = ($config['allow_bbcode'] && $this->auth_bbcode) ? true : false;
		$this->smilies_status = ($config['allow_smilies'] && $this->auth_smilies) ? true : false;
		$this->img_status = ($this->auth_img && $this->bbcode_status) ? true : false;
		$this->url_status = ($config['allow_post_links'] && $this->auth_url && $this->bbcode_status) ? true : false;
		$this->flash_status = ($this->auth_flash && $this->bbcode_status) ? true : false;

		$this->enable_bbcode = ($this->bbcode_status && $bbcode) ? true : false;
		$this->enable_smilies = ($this->smilies_status && $smilies) ? true : false;
		$this->enable_magic_url = ($this->url_status && $url) ? true : false;

		blog_plugins::plugin_do('post_options_set_status');
	}

	/**
	 * Set the options in the template
	 */
	function set_in_template()
	{
		global $template, $user, $phpbb_root_path, $phpEx;

		// Assign some variables to the template parser
		$template->assign_vars(array(
			// If they hit preview or submit and got an error, or are editing their post make sure we carry their existing post info & options over
			'S_BBCODE_CHECKED'			=> ($this->enable_bbcode) ? '' : ' checked="checked"',
			'S_SMILIES_CHECKED'			=> ($this->enable_smilies) ? '' : ' checked="checked"',
			'S_MAGIC_URL_CHECKED'		=> ($this->enable_magic_url) ? '' : ' checked="checked"',

			// To show the Options: section on the bottom left
			'BBCODE_STATUS'				=> ($this->bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
			'IMG_STATUS'				=> ($this->img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
			'FLASH_STATUS'				=> ($this->flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
			'SMILIES_STATUS'			=> ($this->smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
			'URL_STATUS'				=> ($this->url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],

			// To show the option to turn each off while posting
			'S_BBCODE_ALLOWED'			=> $this->bbcode_status,
			'S_SMILIES_ALLOWED'			=> $this->smilies_status,
			'S_LINKS_ALLOWED'			=> $this->url_status,

			// To show the BBCode buttons for each on top
			'S_BBCODE_IMG'				=> $this->img_status,
			'S_BBCODE_URL'				=> $this->url_status,
			'S_BBCODE_FLASH'			=> $this->flash_status,
			'S_BBCODE_QUOTE'			=> true,
		));

		blog_plugins::plugin_do('post_options_set_in_template');
	}
}
?>