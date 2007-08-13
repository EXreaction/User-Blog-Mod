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

// If they did not include the $blog_id give them an error...
if ($blog_id == 0)
{
	trigger_error('NO_BLOG');
}

// Add the language Variables for posting
$user->add_lang('posting');

// check to see if editing this message is locked, or if the one editing it has mod powers
if ($blog_data->blog[$blog_id]['blog_edit_locked'] && !$auth->acl_get('m_blogedit') && !$user_founder)
{
	trigger_error('BLOG_EDIT_LOCKED');
}

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['EDIT_BLOG']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['EDIT_BLOG']);

// can they delete the blog?  Setting this for later.
$can_delete = check_blog_permissions('blog' , 'delete', true, $blog_id);

// Posting permissions
$post_options = new post_options;

$blog_attachment->get_submitted_attachment_data();

// If they select edit mode and didn't submit or hit preview(means they came directly from the view blog page)
if (!$submit && !$preview && !$refresh)
{
	// Setup the message so we can import it to the edit page
	$blog_text = $blog_data->blog[$blog_id]['blog_text'];
	$blog_subject = $blog_data->blog[$blog_id]['blog_subject'];
	decode_message($blog_text, $blog_data->blog[$blog_id]['bbcode_uid']);
	$post_options->set_status($blog_data->blog[$blog_id]['enable_bbcode'], $blog_data->blog[$blog_id]['enable_smilies'], $blog_data->blog[$blog_id]['enable_magic_url']);

	// get the attachment_data
	$blog_attachment->get_attachment_data($blog_id);
	$blog_attachment->attachment_data = $blog_data->blog[$blog_id]['attachment_data'];
}
else
{
	// so we can check if they did edit any text when they hit submit
	$original_subject = $blog_data->blog[$blog_id]['blog_subject'];
	$original_text = $blog_data->blog[$blog_id]['blog_text'];
	decode_message($original_text, $blog_data->blog[$blog_id]['bbcode_uid']);

	$blog_subject = utf8_normalize_nfc(request_var('subject', '', true));
	$blog_text = utf8_normalize_nfc(request_var('message', '', true));

	$post_options->set_status(!isset($_POST['disable_bbcode']), !isset($_POST['disable_smilies']), !isset($_POST['disable_magic_url']));

	// set up the message parser to parse BBCode, Smilies, etc
	$message_parser = new parse_message();
	$message_parser->message = $blog_text;
	$message_parser->parse($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, $post_options->img_status, $post_options->flash_status, $post_options->bbcode_status, $post_options->url_status);

	// Attachments
	$blog_attachment->parse_attachments('fileupload', $submit, $preview, $refresh);

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
	if (sizeof($blog_attachment->warn_msg))
	{
		$error[] = implode('<br />', $blog_attachment->warn_msg);
	}
}

// Set the options up in the template
$post_options->set_in_template();

// if they did not submit or they have an error
if (!$submit || sizeof($error))
{
	// if they are trying to preview the message and do not have an error
	if ($preview && !sizeof($error))
	{
		$preview_message = $message_parser->format_display($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, false);

		// Attachment Preview
		if (sizeof($blog_attachment->attachment_data))
		{
			$template->assign_var('S_HAS_ATTACHMENTS', true);

			$update_count = array();
			$attachment_data = $blog_attachment->attachment_data;

			$blog_attachment->parse_attachments_for_view($preview_message, $attachment_data, $update_count, true);

			$blog_attachment->output_attachment_data($attachment_data);
			unset($attachment_data);
		}

		// output some data to the template parser
		$template->assign_vars(array(
			'S_DISPLAY_PREVIEW'			=> true,
			'PREVIEW_SUBJECT'			=> censor_text($blog_subject),
			'PREVIEW_MESSAGE'			=> $preview_message,
			'POST_DATE'					=> $user->format_date($blog_data->blog[$blog_id]['blog_time']),
		));
	}

	$attachment_data = $blog_attachment->attachment_data;
	$filename_data = $blog_attachment->filename_data;
	$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments'] || !$auth->acl_get('u_attach')) ? '' : ' enctype="multipart/form-data"';

	// Generate inline attachment select box
	posting_gen_inline_attachments($attachment_data);

	// Attachment entry
	if (($auth->acl_get('u_blogattach') || $user_founder) && $config['allow_attachments'] && $form_enctype)
	{
		posting_gen_attachment_entry($attachment_data, $filename_data);
	}

	// Generate smiley listing
	generate_smilies('inline', false);

	// Build custom bbcodes array
	display_custom_bbcodes();

	// Assign some variables to the template parser
	$template->assign_vars(array(
		'ERROR'						=> (sizeof($error)) ? implode('<br />', $error) : '',
		'MESSAGE'					=> $blog_text,
		'SUBJECT'					=> $blog_subject,

		'L_DELETE_POST'				=> $user->lang['DELETE_BLOG'],
		'L_DELETE_POST_WARN'		=> $user->lang['DELETE_BLOG_WARN'],
		'L_MESSAGE_BODY_EXPLAIN'	=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',

		'UA_PROGRESS_BAR'			=> append_sid("{$phpbb_root_path}posting.$phpEx", "mode=popup", false),

		'S_CLOSE_PROGRESS_WINDOW'	=> (isset($_POST['add_file'])) ? true : false,
		'S_DELETE_ALLOWED'			=> $can_delete,
		'S_EDIT_REASON'				=> true,
		'S_FORM_ENCTYPE'			=> $form_enctype,
		'S_LOCK_POST_ALLOWED'		=> (($auth->acl_get('m_bloglockedit') || $user_founder) && $user->data['user_id'] != $blog_data->blog[$blog_id]['user_id']) ? true : false,
	));

	// Tell the template parser what template file to use
	$template->set_filenames(array(
		'body' => 'posting_body.html'
	));
}
else // user submitted and there are no errors
{
	$new_att_val = (count($blog_attachment->attachment_data)) ? 1 : 0;
	$attachment_changed = ($blog_data->blog[$blog_id]['blog_attachment'] != $new_att_val) ? true : false;

	// lets check if they actually edited the text.  If they did not, don't do any SQL queries to update it.
	if ($original_subject != $blog_subject || $original_text != $blog_text || $attachment_changed || (request_var('edit_reason', '', true) != ''))
	{
		$sql_data = array(
			'user_ip'			=> ($user->data['user_id'] == $user_id) ? $user->data['user_ip'] : $blog_data->blog[$blog_id]['user_ip'],
			'blog_subject'		=> $blog_subject,
			'blog_text'			=> $message_parser->message,
			'blog_checksum'		=> md5($message_parser->message),
			'blog_approved' 	=> ($blog_data->blog[$blog_id]['blog_approved'] == 0) ? ($auth->acl_get('u_blognoapprove') || $user_founder) ? 1 : 0 : 1,
			'enable_bbcode' 	=> $post_options->enable_bbcode,
			'enable_smilies'	=> $post_options->enable_smilies,
			'enable_magic_url'	=> $post_options->enable_magic_url,
			'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
			'bbcode_uid'		=> $message_parser->bbcode_uid,
			'blog_edit_time'	=> time(),
			'blog_edit_reason'	=> utf8_normalize_nfc(request_var('edit_reason', '', true)),
			'blog_edit_user'	=> $user->data['user_id'],
			'blog_edit_count'	=> $blog_data->blog[$blog_id]['blog_edit_count'] + 1,
			'blog_edit_locked'	=> (($auth->acl_get('m_bloglockedit') && ($user->data['user_id'] != $blog_data->blog[$blog_id]['user_id'])) || $user_founder) ? request_var('lock_post', false) : false,
			'blog_attachment'	=> $new_att_val,
		);

		// add the delete section to the array if it was deleted, if it was already deleted ignore
		if ( (!$blog_data->blog[$blog_id]['blog_deleted']) && (isset($_POST['delete'])) && $can_delete)
		{
			$sql_data['blog_deleted'] = $user->data['user_id'];
			$sql_data['blog_deleted_time'] = time();
		}

		$sql = 'UPDATE ' . BLOGS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
			WHERE blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);
	}

	// we no longer need the message parser
	unset($message_parser);

	// update attachment data
	$blog_attachment->update_attachment_data($blog_id);

	if ( (isset($_POST['delete'])) && $can_delete )
	{
		handle_blog_cache('delete_blog', $user_id);

		$message = $user->lang['BLOG_DELETED'] . '<br/><br/>';
		if ($user->data['user_id'] == $user_id)
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
		}
		else
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', $user_data->user[$user_id]['username'], '</a>');
		}

		meta_refresh(3, $blog_urls['view_user']);
	}
	else
	{
		handle_blog_cache('approve_blog', $user_id);

		$message = (!$auth->acl_get('u_blognoapprove') && !$user_founder) ? $user->lang['BLOG_NEED_APPROVE'] . '<br /><br />' : ''; 
		$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br/><br/>';
		if ($user->data['user_id'] == $user_id)
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
		}
		else
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', $user_data->user[$user_id]['username'], '</a>');
		}

		meta_refresh(3, $blog_urls['view_blog']);
	}

	trigger_error($message);
}
?>