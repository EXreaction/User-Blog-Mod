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

// get some data on the blog and user
if ($blog_id == 0)
{
	trigger_error('NO_BLOG');
}

// Add the language Variables for posting
$user->add_lang('posting');

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['REPLY']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['REPLY']);

// Posting permissions
$post_options = new post_options;
$post_options->set_status(!isset($_POST['disable_bbcode']), !isset($_POST['disable_smilies']), !isset($_POST['disable_magic_url']));
$post_options->set_in_template();

// If they did submit or hit preview
if ($submit || $preview)
{
	// see if they tried submitting a message or suject(if they hit preview or submit) put it in an array for consistency with the edit mode
	$reply_subject = utf8_normalize_nfc(request_var('subject', '', true));
	$reply_text = utf8_normalize_nfc(request_var('message', '', true));

	// set up the message parser to parse BBCode, Smilies, etc
	$message_parser = new parse_message();
	$message_parser->message = $reply_text;
	$message_parser->parse($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, $post_options->img_status, $post_options->flash_status, $post_options->bbcode_status, $post_options->url_status);

	// check the captcha if required
	if (!handle_captcha('check'))
	{
		$error[] = $user->lang['CONFIRM_CODE_WRONG'];
	}

	// If any errors were reported by the message parser add those as well
	if (sizeof($message_parser->warn_msg))
	{
		$error[] = implode('<br />', $message_parser->warn_msg);
	}

	// If they did not include a subject, give them the empty subject error
	if ($reply_subject == '')
	{
		$error[] = $user->lang['EMPTY_SUBJECT'];
	}
}
else
{
	// setup the captcha
	handle_captcha('build');

	$reply_subject = '';
	$reply_text = '';

	// if they are trying to quote a message
	if ($mode == 'quote')
	{
		if ($reply_id != 0)
		{
			if ($blog_data->get_reply_data(array('reply_id' => $reply_id, 'simple' => true)) !== false)
			{
				$reply_subject = 'RE: ' . $blog_data->reply[$reply_id]['reply_subject'];
				decode_message($blog_data->reply[$reply_id]['reply_text'], $blog_data->reply[$reply_id]['bbcode_uid']);
				$reply_text = '[quote="' . $blog_data->user[$reply_user_id]['username'] . '"]' . $blog_data->reply[$reply_id]['reply_text'] . '[/quote]';;
			}
		}
		else if ($blog_id != 0)
		{
			if ($blog_data->get_blog_data(array('blog_id' => $blog_id, 'simple' => true)) !== false)
			{
				decode_message($blog_data->blog[$blog_id]['blog_text'], $blog_data->blog[$blog_id]['bbcode_uid']);
				$reply_subject = 'RE: ' . $blog_data->blog[$blog_id]['blog_subject'];
				$reply_text = '[quote="' . $blog_data->user[$user_id]['username'] . '"]' . $blog_data->blog[$blog_id]['blog_text'] . '[/quote]';;
			}
		}
	}
	else
	{
		$reply_subject = 'RE: ' . $blog_data->blog[$blog_id]['blog_subject'];
	}
}

// if they did not submit or they have an error
if ( (!$submit) || (sizeof($error)) )
{
	// if they are trying to preview the message and do not have an error
	if ( ($preview) && (!sizeof($error)) )
	{
		// output some data to the template parser
		$template->assign_vars(array(
			'S_DISPLAY_PREVIEW'			=> true,
			'PREVIEW_SUBJECT'			=> censor_text($reply_subject),
			'PREVIEW_MESSAGE'			=> $message_parser->format_display($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, false),
			'POST_DATE'					=> $user->format_date(time()),
		));
	}

	// Generate smiley listing
	generate_smilies('inline', false);

	// Build custom bbcodes array
	display_custom_bbcodes();

	// Assign some variables to the template parser
	$template->assign_vars(array(
		'L_POST_A'					=> $user->lang['POST_A_REPLY'],

		// If we have any limit on the number of chars a user can enter display that, otherwise don't
		'L_MESSAGE_BODY_EXPLAIN'	=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',

		// If they hit preview or submit and got an error, or are editing their post make sure we carry their existing post info & options over
		'SUBJECT'					=> $reply_subject,
		'MESSAGE'					=> $reply_text,

		// if there are any errors report them
		'ERROR'						=> (sizeof($error)) ? implode('<br />', $error) : '',
	));

	// Tell the template parser what template file to use
	$template->set_filenames(array(
		'body' => 'posting_body.html'
	));
}
else // user submitted and there are no errors
{
	// insert array, not all of these really need to be inserted, since some are what the fields are as default, but I want it this way. :P
	$sql_data = array(
		'blog_id'				=> $blog_id,
		'user_id' 				=> $user->data['user_id'],
		'user_ip'				=> $user->data['user_ip'],
		'reply_time'			=> time(),
		'reply_subject'			=> $reply_subject,
		'reply_text'			=> $message_parser->message,
		'reply_checksum'		=> md5($message_parser->message),
		'reply_approved' 		=> check_blog_permissions('reply', 'no_approve', true),
		'enable_bbcode' 		=> $post_options->enable_bbcode,
		'enable_smilies'		=> $post_options->enable_smilies,
		'enable_magic_url'		=> $post_options->enable_magic_url,
		'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
		'bbcode_uid'			=> $message_parser->bbcode_uid,
	);

	$sql = 'INSERT INTO ' . BLOGS_REPLY_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);
	$db->sql_query($sql);

	// we no longer need the message parser
	unset($message_parser);

	$reply_id = $db->sql_nextid();

	// update the URLS to include the new reply_id
	generate_blog_urls();

	// update the reply count for the blog
	if (check_blog_permissions('reply', 'no_approve', true))
	{
		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_reply_count = blog_reply_count + 1, blog_real_reply_count = blog_real_reply_count + 1 WHERE blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);
	}
	else
	{
		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_real_reply_count = blog_real_reply_count + 1 WHERE blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);

		inform_approve_report('reply_approve', $reply_id);
	}

	$message = (!check_blog_permissions('reply', 'no_approve', true)) ? $user->lang['REPLY_NEED_APPROVE'] . '<br /><br />' : ''; 
	$message .= '<a href="' . $blog_urls['view_reply'] . '">' . $user->lang['VIEW_REPLY'] . '</a><br/>';
	$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br/>';
	if ($user_id == $user->data['user_id'])
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
	}
	else
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', $blog_data->user[$user_id]['username'], '</a>') . '<br/>';
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
	}

	// redirect
	meta_refresh(3, $blog_urls['view_reply']);

	trigger_error($message);
}
?>