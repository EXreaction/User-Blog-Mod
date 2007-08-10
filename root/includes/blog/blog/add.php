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

// setup the message parser
$message_parser = new parse_message();

//$message_parser->get_submitted_attachment_data();

// If they did submit or hit preview
if ($submit || $preview || $refresh)
{
	// see if they tried submitting a message or suject(if they hit preview or submit) put it in an array for consistency with the edit mode
	$blog_subject = utf8_normalize_nfc(request_var('subject', '', true));
	$blog_text = utf8_normalize_nfc(request_var('message', '', true));

	// set up the message parser to parse BBCode, Smilies, etc
	$message_parser->message = $blog_text;
	$message_parser->parse($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, $post_options->img_status, $post_options->flash_status, $post_options->bbcode_status, $post_options->url_status);

	// check the captcha if required
	if (!handle_captcha('check'))
	{
		$error[] = $user->lang['CONFIRM_CODE_WRONG'];
	}

	// Attachments
	//$message_parser->parse_attachments('fileupload', 'post', 2, $submit, $preview, $refresh);

	// If they did not include a subject, give them the empty subject error
	if ($blog_subject == '')
	{
		$error[] = $user->lang['EMPTY_SUBJECT'];
	}

	// If any errors were reported by the message parser add those as well
	if (sizeof($message_parser->warn_msg))
	{
		$error[] = implode('<br />', $message_parser->warn_msg);
	}
}
else
{
	$blog_subject = '';
	$blog_text = '';
}

/*
$template->assign_vars(array(
	'S_CLOSE_PROGRESS_WINDOW'	=> (isset($_POST['add_file'])) ? true : false,
	'UA_PROGRESS_BAR'			=> append_sid("{$phpbb_root_path}posting.$phpEx", "f=2&mode=popup", false),
));
*/

// if they did not submit or they have an error
if (!$submit || sizeof($error))
{
	// setup the captcha
	handle_captcha('build');

	// if they are trying to preview the message and do not have an error
	if ($preview && !sizeof($error))
	{
		$preview_message = $message_parser->format_display($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, false);

		// Attachment Preview
		/*if (sizeof($message_parser->attachment_data))
		{
			$template->assign_var('S_HAS_ATTACHMENTS', true);

			$update_count = array();
			$attachment_data = $message_parser->attachment_data;

			parse_attachments(2, $preview_message, $attachment_data, $update_count, true);

			foreach ($attachment_data as $i => $attachment)
			{
				$template->assign_block_vars('attachment', array(
					'DISPLAY_ATTACHMENT'	=> $attachment)
				);
			}
			unset($attachment_data);
		}*/

		// output some data to the template parser
		$template->assign_vars(array(
			'S_DISPLAY_PREVIEW'			=> true,
			'PREVIEW_SUBJECT'			=> censor_text($blog_subject),
			'PREVIEW_MESSAGE'			=> $preview_message,
			'POST_DATE'					=> $user->format_date(time()),
		));
	}

/*	$attachment_data = $message_parser->attachment_data;
	$filename_data = $message_parser->filename_data;  */

	// Generate smiley listing
	generate_smilies('inline', false);

	// Build custom bbcodes array
	display_custom_bbcodes();

	// Generate inline attachment select box
/*	posting_gen_inline_attachments($attachment_data);

	$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments'] || !$auth->acl_get('u_attach')) ? '' : ' enctype="multipart/form-data"';

	// Attachment entry
	// Not using acl_gets here, because it is using OR logic
	//if ($auth->acl_get('u_attach') && $config['allow_attachments'] && $form_enctype)
	//{
		posting_gen_attachment_entry($attachment_data, $filename_data);
	//}  */

	// Assign some variables to the template parser
	$template->assign_vars(array(
		// If we have any limit on the number of chars a user can enter display that, otherwise don't
		'L_MESSAGE_BODY_EXPLAIN'	=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',

		// If they hit preview or submit and got an error, or are editing their post make sure we carry their existing post info & options over
		'SUBJECT'					=> $blog_subject,
		'MESSAGE'					=> $blog_text,

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
	// insert array
	$sql_data = array(
		'user_id' 					=> $user->data['user_id'],
		'user_ip'					=> $user->data['user_ip'],
		'blog_time'					=> time(),
		'blog_subject'				=> $blog_subject,
		'blog_text'					=> $message_parser->message,
		'blog_checksum'				=> md5($message_parser->message),
		'blog_approved' 			=> check_blog_permissions('blog', 'no_approve', true),
		'enable_bbcode' 			=> $post_options->enable_bbcode,
		'enable_smilies'			=> $post_options->enable_smilies,
		'enable_magic_url'			=> $post_options->enable_magic_url,
		'bbcode_bitfield'			=> $message_parser->bbcode_bitfield,
		'bbcode_uid'				=> $message_parser->bbcode_uid,
		'blog_edit_reason'			=> '',
	);

	// insert query
	$sql = 'INSERT INTO ' . BLOGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);

	// run the query
	$db->sql_query($sql);

	// we no longer need the message parser
	unset($message_parser);

	$blog_id = $db->sql_nextid();

	// regenerate the urls to include the blog_id
	generate_blog_urls();

	handle_blog_cache('new_blog', $user->data['user_id']);

	if ($auth->acl_get('u_blognoapprove') || $user_founder)
	{
		handle_subscription('new_blog', censor_text($blog_subject));

		// Update the blog_count for the user
		$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = blog_count + 1 WHERE user_id = \'' . $user->data['user_id'] . '\'';
		$db->sql_query($sql);
	}
	else
	{
		inform_approve_report('blog_approve', $blog_id);
	}

	$message = (!$auth->acl_get('u_blognoapprove') && !$user_founder) ? $user->lang['BLOG_NEED_APPROVE'] . '<br /><br />' : ''; 
	$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br/><br/>';

	$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');

	meta_refresh(3, $blog_urls['view_blog']);

	trigger_error($message);
}
?>