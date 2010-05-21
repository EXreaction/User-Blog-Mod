<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: edit.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

// If they did not include the $blog_id give them an error...
if ($blog_id == 0)
{
	trigger_error('BLOG_NOT_EXIST');
}

// Add the language Variables for posting
$user->add_lang('posting');

// check to see if editing this message is locked, or if the one editing it has mod powers
if (blog_data::$blog[$blog_id]['blog_edit_locked'] && !$auth->acl_get('m_blogedit'))
{
	trigger_error('BLOG_EDIT_LOCKED');
}

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['EDIT_BLOG']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['EDIT_BLOG']);

// Posting permissions
$post_options = new post_options;

blog_plugins::plugin_do('blog_edit_start');

$category_ary = request_var('category', array(0));

// Polls
$blog_data->get_polls($blog_id);
$poll_option_text = $original_poll_text = '';
$poll_options = array();
foreach (blog_data::$blog[$blog_id]['poll_options'] as $row)
{
	$poll_option_text .= $row['poll_option_text'] . "\n";
	$poll_options[] = $row['poll_option_text'];
}
decode_message($poll_option_text, blog_data::$blog[$blog_id]['bbcode_uid']);
$original_poll_text = $poll_option_text;

if ($submit || $preview || $refresh)
{
	$blog_subject = utf8_normalize_nfc(request_var('subject', '', true));
	$blog_text = utf8_normalize_nfc(request_var('message', '', true));

	$post_options->set_status(!isset($_POST['disable_bbcode']), !isset($_POST['disable_smilies']), !isset($_POST['disable_magic_url']));

	// set up the message parser to parse BBCode, Smilies, etc
	$message_parser = new parse_message();
	$message_parser->message = $blog_text;
	$message_parser->parse($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, $post_options->img_status, $post_options->flash_status, $post_options->bbcode_status, $post_options->url_status);

	// Check the basic posting data
	$error = handle_basic_posting_data(true, 'blog', 'edit');

	// If they did not include a subject, give them the empty subject error
	if ($blog_subject == '' && !$refresh)
	{
		$error[] = $user->lang['EMPTY_SUBJECT'];
	}

	// Polls
	$poll_title			= utf8_normalize_nfc(request_var('poll_title', '', true));
	$poll_length		= request_var('poll_length', 0);
	$poll_option_text	= utf8_normalize_nfc(request_var('poll_option_text', '', true));
	$poll_max_options	= request_var('poll_max_options', 1);
	$poll_vote_change	= isset($_POST['poll_vote_change']) ? 1 : 0;
	if ($poll_option_text && $auth->acl_get('u_blog_create_poll') && !isset($_POST['poll_delete']))
	{
		$poll = array(
			'poll_title'		=> $poll_title,
			'poll_length'		=> $poll_length,
			'poll_max_options'	=> $poll_max_options,
			'poll_option_text'	=> $poll_option_text,
			'poll_start'		=> time(),
			'poll_last_vote'	=> 0,
			'poll_vote_change'	=> $poll_vote_change,
			'enable_bbcode'		=> $post_options->enable_bbcode,
			'enable_urls'		=> $post_options->enable_magic_url,
			'enable_smilies'	=> $post_options->enable_smilies,
			'img_status'		=> $post_options->img_status,
		);

		$message_parser->parse_poll($poll);

		$poll_options = (isset($poll['poll_options'])) ? $poll['poll_options'] : '';
		$poll_title = (isset($poll['poll_title'])) ? $poll['poll_title'] : '';
	}
	else
	{
		$poll = $poll_options = array();
	}

	if (isset($_POST['poll_delete']))
	{
		$poll_title = $poll_option_text = '';
		$poll_start = $poll_length = $poll_max_options = $poll_vote_change = false;
	}

	// If any errors were reported by the message parser add those as well
	if (sizeof($message_parser->warn_msg) && !$refresh)
	{
		$error[] = implode('<br />', $message_parser->warn_msg);
	}

	// Attachments
	$blog_attachment->get_submitted_attachment_data();
	$blog_attachment->parse_attachments('fileupload', $submit, $preview, $refresh, $blog_text);
	if (sizeof($blog_attachment->warn_msg))
	{
		$error[] = implode('<br />', $blog_attachment->warn_msg);
	}
}
else
{
	// Setup the message so we can import it to the edit page
	$blog_text = blog_data::$blog[$blog_id]['blog_text'];
	$blog_subject = blog_data::$blog[$blog_id]['blog_subject'];
	decode_message($blog_text, blog_data::$blog[$blog_id]['bbcode_uid']);
	$post_options->set_status(blog_data::$blog[$blog_id]['enable_bbcode'], blog_data::$blog[$blog_id]['enable_smilies'], blog_data::$blog[$blog_id]['enable_magic_url']);

	$sql = 'SELECT category_id FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . intval($blog_id);
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$category_ary[] = $row['category_id'];
	}

	// Polls
	$poll_title			= blog_data::$blog[$blog_id]['poll_title'];
	$poll_start			= blog_data::$blog[$blog_id]['poll_start'];
	$poll_length		= (blog_data::$blog[$blog_id]['poll_length']) ? ((blog_data::$blog[$blog_id]['poll_length'] - $poll_start) / 86400) : 0;
	$poll_max_options	= blog_data::$blog[$blog_id]['poll_max_options'];
	$poll_vote_change	= blog_data::$blog[$blog_id]['poll_vote_change'];
	decode_message($poll_title, blog_data::$blog[$blog_id]['bbcode_uid']);

	// Attachments
	$blog_attachment->get_attachment_data($blog_id);
	$blog_attachment->attachment_data = blog_data::$blog[$blog_id]['attachment_data'];
}

$temp = compact('blog_subject', 'blog_text', 'error');
blog_plugins::plugin_do_ref('blog_edit_after_setup', $temp);
extract($temp);
unset($temp);

// Set the options up in the template
$post_options->set_in_template();

// if they did not submit or they have an error
if (!$submit || sizeof($error))
{
	// if they are trying to preview the message and do not have an error
	if ($preview && !sizeof($error))
	{
		$preview_message = $message_parser->format_display($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, false);

		// Poll Preview
		if (!empty($poll))
		{
			$parse_poll = new parse_message($poll_title);
			$parse_poll->bbcode_uid = $message_parser->bbcode_uid;
			$parse_poll->bbcode_bitfield = $message_parser->bbcode_bitfield;

			$parse_poll->format_display($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies);

			if ($poll_length)
			{
				$poll_end = ($poll_length * 86400) + (($poll_start) ? $poll_start : time());
			}

			$template->assign_vars(array(
				'S_HAS_POLL_OPTIONS'	=> (sizeof($poll_options)),
				'S_IS_MULTI_CHOICE'		=> ($poll_max_options > 1) ? true : false,

				'POLL_QUESTION'		=> $parse_poll->message,

				'L_POLL_LENGTH'		=> ($poll_length) ? sprintf($user->lang['POLL_RUN_TILL'], $user->format_date($poll_end)) : '',
				'L_MAX_VOTES'		=> ($poll_max_options == 1) ? $user->lang['MAX_OPTION_SELECT'] : sprintf($user->lang['MAX_OPTIONS_SELECT'], $poll_max_options))
			);

			$parse_poll->message = implode("\n", $poll_options);
			$parse_poll->format_display($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies);
			$preview_poll_options = explode('<br />', $parse_poll->message);
			unset($parse_poll);

			foreach ($preview_poll_options as $key => $option)
			{
				$template->assign_block_vars('poll_option', array(
					'POLL_OPTION_CAPTION'	=> $option,
					'POLL_OPTION_ID'		=> $key + 1)
				);
			}
			unset($preview_poll_options);
		}

		// Attachments
		if (sizeof($blog_attachment->attachment_data))
		{
			$template->assign_var('S_HAS_ATTACHMENTS', true);

			$update_count = array();
			$attachment_data = $blog_attachment->attachment_data;

			$blog_attachment->parse_attachments_for_view($preview_message, $attachment_data, $update_count, true);

			if (sizeof($attachment_data))
			{
				foreach ($attachment_data as $row)
				{
					$template->assign_block_vars('attachment', array(
						'DISPLAY_ATTACHMENT' => $row,
					));
				}
			}

			unset($attachment_data);
		}

		blog_plugins::plugin_do_ref('blog_edit_preview', $preview_message);

		// output some data to the template parser
		$template->assign_vars(array(
			'S_DISPLAY_PREVIEW'			=> true,
			'PREVIEW_SUBJECT'			=> censor_text($blog_subject),
			'PREVIEW_MESSAGE'			=> $preview_message,
			'POST_DATE'					=> $user->format_date(blog_data::$blog[$blog_id]['blog_time']),
		));
	}

	blog_plugins::plugin_do('blog_edit_after_preview');

	// handles the basic data we need to output for posting
	handle_basic_posting_data(false, 'blog', 'edit');

	// Assign some variables to the template parser
	$template->assign_vars(array(
		'ERROR'						=> (sizeof($error)) ? implode('<br />', $error) : '',
		'MESSAGE'					=> $blog_text,
		'POLL_TITLE'				=> $poll_title,
		'POLL_OPTIONS'				=> ($poll_option_text) ? $poll_option_text : '',
		'POLL_MAX_OPTIONS'			=> $poll_max_options,
		'POLL_LENGTH'				=> $poll_length,
		'SUBJECT'					=> $blog_subject,
		'VOTE_CHANGE_CHECKED'		=> ($poll_vote_change) ? 'checked="checked"' : '',

		'L_MESSAGE_BODY_EXPLAIN'	=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',
		'L_POST_A'					=> $user->lang['EDIT_A_BLOG'],
		'L_POLL_OPTIONS_EXPLAIN'	=> sprintf($user->lang['POLL_OPTIONS_EXPLAIN'], $config['max_poll_options']),

		'S_EDIT_REASON'				=> true,
		'S_LOCK_POST_ALLOWED'		=> (($auth->acl_get('m_bloglockedit')) && $user->data['user_id'] != blog_data::$blog[$blog_id]['user_id']) ? true : false,
		'S_POLL_DELETE'				=> ($poll_title) ? true : false,
		'S_POLL_VOTE_CHANGE'		=> true,
	));

	$template->set_filenames(array(
		'body'		=> 'blog/blog_posting_layout.html',
	));
}
else // user submitted and there are no errors
{
	// insert array
	$sql_data = array(
		'user_ip'					=> ($user->data['user_id'] == $user_id) ? $user->data['user_ip'] : blog_data::$blog[$blog_id]['user_ip'],
		'blog_subject'				=> $blog_subject,
		'blog_text'					=> $message_parser->message,
		'blog_checksum'				=> md5($message_parser->message),
		'blog_approved' 			=> (blog_data::$blog[$blog_id]['blog_approved'] == 1 || $auth->acl_get('u_blognoapprove')) ? 1 : 0,
		'enable_bbcode' 			=> $post_options->enable_bbcode,
		'enable_smilies'			=> $post_options->enable_smilies,
		'enable_magic_url'			=> $post_options->enable_magic_url,
		'bbcode_bitfield'			=> $message_parser->bbcode_bitfield,
		'bbcode_uid'				=> $message_parser->bbcode_uid,
		'blog_edit_time'			=> time(),
		'blog_edit_reason'			=> utf8_normalize_nfc(request_var('edit_reason', '', true)),
		'blog_edit_user'			=> $user->data['user_id'],
		'blog_edit_count'			=> blog_data::$blog[$blog_id]['blog_edit_count'] + 1,
		'blog_edit_locked'			=> ($auth->acl_get('m_bloglockedit') && ($user->data['user_id'] != blog_data::$blog[$blog_id]['user_id'])) ? request_var('lock_post', false) : false,
		'perm_guest'				=> request_var('perm_guest', 1),
		'perm_registered'			=> request_var('perm_registered', 2),
		'perm_foe'					=> request_var('perm_foe', 0),
		'perm_friend'				=> request_var('perm_friend', 2),
		'blog_attachment'			=> (sizeof($blog_attachment->attachment_data)) ? 1 : 0,
		'poll_title'				=> (!empty($poll)) ? $poll_title : '',
		'poll_length'				=> (!empty($poll) && $poll_length) ? (time() + ($poll_length * 86400)) : 0,
		'poll_max_options'			=> (!empty($poll)) ? max($poll_max_options, 1) : 1,
		'poll_vote_change'			=> (!empty($poll)) ? $poll_vote_change : 0,
	);

	if ($original_poll_text != $poll_option_text)
	{
		$sql_data['poll_start'] = (empty($poll)) ? 0 : time();
	}

	blog_plugins::plugin_do_ref('blog_edit_sql', $sql_data);

	$sql = 'UPDATE ' . BLOGS_TABLE . '
		SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
			WHERE blog_id = ' . intval($blog_id);
	$db->sql_query($sql);

	// Reindex the blog
	$blog_search->index('edit', $blog_id, 0, $message_parser->message, $blog_subject, $user_id);

	// Update the attachments
	$blog_attachment->update_attachment_data($blog_id);

	blog_plugins::plugin_do_arg('blog_edit_after_sql', $blog_id);

	// Submit the poll
	if ($auth->acl_get('u_blog_create_poll'))
	{
		submit_blog_poll($poll, $blog_id, 'edit');
	}

	// Handle the subscriptions
	add_blog_subscriptions($blog_id, 'subscription_');

	// Insert into the categories list
	if (sizeof($category_ary) > 1 || (isset($category_ary[0]) && $category_ary[0] != 0))
	{
		$category_list = get_blog_categories('category_id');

		foreach ($category_ary as $i => $cat_id)
		{
			if (!isset($category_list[$cat_id]))
			{
				unset($category_ary[$i]);
			}
		}
	}
	put_blogs_in_cats($blog_id, $category_ary, ((blog_data::$blog[$blog_id]['blog_approved'] == 1 || $auth->acl_get('u_blognoapprove')) ? true : false));

	// If it needs reapproval...
	if (blog_data::$blog[$blog_id]['blog_approved'] == 0 && !$auth->acl_get('u_blognoapprove'))
	{
		$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = blog_count - 1 WHERE user_id = ' . $user->data['user_id'];
		$db->sql_query($sql);
		set_config('num_blogs', --$config['num_blogs'], true);

		inform_approve_report('blog_approve', $blog_id);
	}

	handle_blog_cache('edit_blog', $user_id);

	$message = ((!$sql_data['blog_approved']) ? $user->lang['BLOG_NEED_APPROVE'] . '<br /><br />' : $user->lang['BLOG_EDIT_SUCCESS']) . '<br /><br />';
	$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br /><br />';
	if ($user->data['user_id'] == $user_id)
	{
		$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
	}
	else
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', blog_data::$user[$user_id]['username'], '</a>');
	}

	blog_meta_refresh(3, $blog_urls['view_blog']);

	trigger_error($message);
}
?>