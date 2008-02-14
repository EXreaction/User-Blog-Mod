<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

// Add the language Variables for posting
$user->add_lang('posting');

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['ADD_BLOG']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['ADD_BLOG']);

// Posting permissions
$post_options = new post_options;
$post_options->set_status(!isset($_POST['disable_bbcode']), !isset($_POST['disable_smilies']), !isset($_POST['disable_magic_url']));
$post_options->set_in_template();

blog_plugins::plugin_do('blog_add_start');

// If they did submit or hit preview
if ($submit || $preview || $refresh)
{
	// see if they tried submitting a message or suject(if they hit preview or submit) put it in an array for consistency with the edit mode
	$blog_subject = utf8_normalize_nfc(request_var('subject', '', true));
	$blog_text = utf8_normalize_nfc(request_var('message', '', true));
	$category_ary = request_var('category', array(0));

	// set up the message parser to parse BBCode, Smilies, etc
	$message_parser->message = $blog_text;
	$message_parser->parse($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, $post_options->img_status, $post_options->flash_status, $post_options->bbcode_status, $post_options->url_status);

	// Check the basic posting data
	$error = handle_basic_posting_data(true);

	// If they did not include a subject, give them the empty subject error
	if ($blog_subject == '' && !$refresh)
	{
		$error[] = $user->lang['EMPTY_SUBJECT'];
	}

	// If any errors were reported by the message parser add those as well
	if (sizeof($message_parser->warn_msg) && !$refresh)
	{
		$error[] = implode('<br />', $message_parser->warn_msg);
	}

	// Polls
	$poll_title			= utf8_normalize_nfc(request_var('poll_title', '', true));
	$poll_length		= request_var('poll_length', 0);
	$poll_option_text	= utf8_normalize_nfc(request_var('poll_option_text', '', true));
	$poll_max_options	= request_var('poll_max_options', 1);
	$poll_vote_change	= isset($_POST['poll_vote_change']) ? 1 : 0;
	if ($poll_option_text && $auth->acl_get('u_blog_create_poll'))
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
		$poll = array();
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
	$blog_subject = $blog_text = '';
}

$temp = compact('blog_subject', 'blog_text', 'error');
blog_plugins::plugin_do_ref('blog_add_after_setup', $temp);
extract($temp);
unset($temp);

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
				$poll_end = ($poll_length * 86400) + time();
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

			if (count($attachment_data))
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

		blog_plugins::plugin_do_ref('blog_add_preview', $preview_message);

		// output some data to the template parser
		$template->assign_vars(array(
			'S_DISPLAY_PREVIEW'			=> true,
			'PREVIEW_SUBJECT'			=> censor_text($blog_subject),
			'PREVIEW_MESSAGE'			=> $preview_message,
			'POST_DATE'					=> $user->format_date(time()),
		));
	}

	blog_plugins::plugin_do('blog_add_after_preview');

	// handles the basic data we need to output for posting
	handle_basic_posting_data();

	// Assign some variables to the template parser
	$template->assign_vars(array(
		'ERROR'						=> (sizeof($error)) ? implode('<br />', $error) : '',
		'MESSAGE'					=> $blog_text,
		'POLL_TITLE'				=> (isset($poll_title)) ? $poll_title : '',
		'POLL_OPTIONS'				=> (!empty($poll_options)) ? implode("\n", $poll_options) : '',
		'POLL_MAX_OPTIONS'			=> (isset($poll_max_options)) ? $poll_max_options : 1,
		'POLL_LENGTH'				=> (isset($poll_length)) ? $poll_length : 0,
		'SUBJECT'					=> $blog_subject,
		'VOTE_CHANGE_CHECKED'		=> (isset($poll_vote_change) && $poll_vote_change) ? 'checked="checked"' : '',

		'L_MESSAGE_BODY_EXPLAIN'	=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',
		'L_POST_A'					=> $user->lang['POST_A_NEW_BLOG'],
		'L_POLL_OPTIONS_EXPLAIN'	=> sprintf($user->lang['POLL_OPTIONS_EXPLAIN'], $config['max_poll_options']),

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
		'user_id' 					=> $user->data['user_id'],
		'user_ip'					=> $user->data['user_ip'],
		'blog_time'					=> time(),
		'blog_subject'				=> $blog_subject,
		'blog_text'					=> $message_parser->message,
		'blog_checksum'				=> md5($message_parser->message),
		'blog_approved' 			=> ($auth->acl_get('u_blognoapprove')) ? 1 : 0,
		'enable_bbcode' 			=> $post_options->enable_bbcode,
		'enable_smilies'			=> $post_options->enable_smilies,
		'enable_magic_url'			=> $post_options->enable_magic_url,
		'bbcode_bitfield'			=> $message_parser->bbcode_bitfield,
		'bbcode_uid'				=> $message_parser->bbcode_uid,
		'blog_edit_reason'			=> '',
		'perm_guest'				=> request_var('perm_guest', 1),
		'perm_registered'			=> request_var('perm_registered', 2),
		'perm_foe'					=> request_var('perm_foe', 0),
		'perm_friend'				=> request_var('perm_friend', 2),
		'blog_attachment'			=> (count($blog_attachment->attachment_data)) ? 1 : 0,
		'poll_title'				=> (!empty($poll)) ? $poll_title : '',
		'poll_start'				=> (!empty($poll)) ? time() : 0,
		'poll_length'				=> (!empty($poll) && $poll_length) ? (time() + ($poll_length * 86400)) : 0,
		'poll_max_options'			=> (!empty($poll)) ? $poll_max_options : 0,
		'poll_vote_change'			=> (!empty($poll)) ? $poll_vote_change : 0,
	);

	blog_plugins::plugin_do_ref('blog_add_sql', $sql_data);

	$sql = 'INSERT INTO ' . BLOGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);
	$db->sql_query($sql);
	$blog_id = $db->sql_nextid();

	// Index the blog
	$blog_search->index('add', $blog_id, 0, $message_parser->message, $blog_subject, $user->data['user_id']);

	// Update the attachments
	$blog_attachment->update_attachment_data($blog_id);

	// Submit the poll
	if ($auth->acl_get('u_blog_create_poll'))
	{
		submit_blog_poll($poll, $blog_id);
	}

	// Handle the subscriptions
	add_blog_subscriptions($blog_id, 'subscription_');

	// Insert into the categories list
	if (count($category_ary) > 1 || (isset($category_ary[0]) && $category_ary[0] != 0))
	{
		$category_list = get_blog_categories('category_id');

		foreach ($category_ary as $i => $cat_id)
		{
			if (array_key_exists($cat_id, $category_list))
			{
				$sql = 'INSERT INTO ' . BLOGS_IN_CATEGORIES_TABLE . ' ' . $db->sql_build_array('INSERT', array('blog_id' => $blog_id, 'category_id' => $cat_id));
				$db->sql_query($sql);
			}
		}

		// Update the blog_count for the categories
		if ($auth->acl_get('u_blognoapprove'))
		{
			$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . ' SET blog_count = blog_count + 1 WHERE ' . $db->sql_in_set('category_id', $category_ary);
			$db->sql_query($sql);
		}
	}

	// regenerate the urls to include the blog_id
	generate_blog_urls();

	blog_plugins::plugin_do_arg('blog_add_after_sql', $blog_id);

	unset($message_parser, $sql_data);

	handle_blog_cache('new_blog', $user->data['user_id']);

	if ($auth->acl_get('u_blognoapprove'))
	{
		// Update the blog_count for the user
		$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = blog_count + 1 WHERE user_id = ' . $user->data['user_id'];
		$db->sql_query($sql);

		set_config('num_blogs', $config['num_blogs']++, true);

		handle_subscription('new_blog', censor_text($blog_subject), $user->data['user_id'], $blog_id);
	}
	else
	{
		inform_approve_report('blog_approve', $blog_id);
	}

	$message = ((!$auth->acl_get('u_blognoapprove')) ? $user->lang['BLOG_NEED_APPROVE'] . '<br /><br />' : $user->lang['BLOG_SUBMIT_SUCCESS']) . '<br /><br />'; 
	$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br/><br/>';

	$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');

	blog_meta_refresh(3, $blog_urls['view_blog']);

	trigger_error($message);
}
?>