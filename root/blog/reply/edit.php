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

// If they did not include the $reply_id give them an error...
if ($reply_id == 0)
{
	trigger_error('NO_REPLY');
}

// Add the language Variables for posting
$user->add_lang('posting');

// check to see if editing this message is locked, or if the one editing it has mod powers
if ($reply_data->reply[$reply_id]['reply_edit_locked'] && !$auth->acl_get('m_blogreplyedit'))
{
	trigger_error('REPLY_EDIT_LOCKED');
}

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['EDIT_REPLY']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['EDIT_REPLY']);

// can they delete the reply?  Setting this for later.
$can_delete = check_blog_permissions('reply', 'delete', true, $blog_id, $reply_id);

// Posting permissions
$post_options = new post_options;

$blog_plugins->plugin_do('reply_edit_start');

// If they select edit mode and didn't submit or hit preview(means they came directly from the view reply page)
if (!$submit && !$preview && !$refresh)
{
	// Setup the message so we can import it to the edit page
	$reply_subject = $reply_data->reply[$reply_id]['reply_subject'];
	$reply_text = $reply_data->reply[$reply_id]['reply_text'];
	decode_message($reply_text, $reply_data->reply[$reply_id]['bbcode_uid']);
	$post_options->set_status($reply_data->reply[$reply_id]['enable_bbcode'], $reply_data->reply[$reply_id]['enable_smilies'], $reply_data->reply[$reply_id]['enable_magic_url']);
}
else
{
	// so we can check if they did edit any text when they hit submit
	$original_subject = $reply_data->reply[$reply_id]['reply_subject'];
	$original_text = $reply_data->reply[$reply_id]['reply_text'];
	decode_message($original_text, $reply_data->reply[$reply_id]['bbcode_uid']);

	$reply_subject = utf8_normalize_nfc(request_var('subject', '', true));
	$reply_text = utf8_normalize_nfc(request_var('message', '', true));

	$post_options->set_status(!isset($_POST['disable_bbcode']), !isset($_POST['disable_smilies']), !isset($_POST['disable_magic_url']));

	// set up the message parser to parse BBCode, Smilies, etc
	$message_parser = new parse_message();
	$message_parser->message = $reply_text;
	$message_parser->parse($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, $post_options->img_status, $post_options->flash_status, $post_options->bbcode_status, $post_options->url_status);

	// If they did not include a subject, give them the empty subject error
	if ($reply_subject == '' && !$refresh)
	{
		$error[] = $user->lang['EMPTY_SUBJECT'];
	}

	// If any errors were reported by the message parser add those as well
	if (sizeof($message_parser->warn_msg) && !$refresh)
	{
		$error[] = implode('<br />', $message_parser->warn_msg);
	}
}

$temp = array('subject' => $reply_subject, 'text' => $reply_text);
$blog_plugins->plugin_do_arg_ref('reply_edit_after_setup', $temp);
$reply_subject = $temp['subject'];
$reply_text = $temp['text'];
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

		$blog_plugins->plugin_do_arg_ref('reply_edit_preview', $preview_message);

		// output some data to the template parser
		$template->assign_vars(array(
			'S_DISPLAY_PREVIEW'			=> true,
			'PREVIEW_SUBJECT'			=> censor_text($reply_subject),
			'PREVIEW_MESSAGE'			=> $preview_message,
			'POST_DATE'					=> $user->format_date($reply_data->reply[$reply_id]['reply_time']),
		));
	}

	$blog_plugins->plugin_do('reply_edit_after_preview');

	// handles the basic data we need to output for posting
	handle_basic_posting_data('reply', 'edit');

	// Assign some variables to the template parser
	$template->assign_vars(array(
		'ERROR'						=> (sizeof($error)) ? implode('<br />', $error) : '',
		'MESSAGE'					=> $reply_text,
		'SUBJECT'					=> $reply_subject,

		'L_DELETE_POST'				=> $user->lang['DELETE_REPLY'],
		'L_DELETE_POST_WARN'		=> $user->lang['DELETE_REPLY_WARN'],
		'L_MESSAGE_BODY_EXPLAIN'	=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',
		'L_POST_A'					=> $user->lang['POST_A_REPLY'],

		'S_DELETE_ALLOWED'			=> $can_delete,
		'S_EDIT_REASON'				=> true,
		'S_LOCK_POST_ALLOWED'		=> (($auth->acl_get('m_blogreplylockedit')) && $user->data['user_id'] != $reply_user_id) ? true : false,
	));

	// Tell the template parser what template file to use
	$template->set_filenames(array(
		'body' => 'blog/blog_posting_layout.html'
	));
}
else // user submitted and there are no errors
{
	// lets check if they actually edited the text.  If they did not, don't do any SQL queries to update it.
	if ($original_subject != $reply_subject || $original_text != $reply_text || (request_var('edit_reason', '', true) != ''))
	{
		$sql_data = array(
			'user_ip'			=> ($user->data['user_id'] == $reply_user_id) ? $user->data['user_ip'] : $reply_data->reply[$reply_id]['user_ip'],
			'reply_subject'		=> $reply_subject,
			'reply_text'		=> $message_parser->message,
			'reply_checksum'	=> md5($message_parser->message),
			'reply_approved' 	=> ($reply_data->reply[$reply_id]['reply_approved'] == 0) ? ($auth->acl_get('u_blogreplynoapprove')) ? 1 : 0 : 1,
			'enable_bbcode' 	=> $post_options->enable_bbcode,
			'enable_smilies'	=> $post_options->enable_smilies,
			'enable_magic_url'	=> $post_options->enable_magic_url,
			'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
			'bbcode_uid'		=> $message_parser->bbcode_uid,
			'reply_edit_time'	=> time(),
			'reply_edit_reason'	=> utf8_normalize_nfc(request_var('edit_reason', '', true)),
			'reply_edit_user'	=> $user->data['user_id'],
			'reply_edit_count'	=> $reply_data->reply[$reply_id]['reply_edit_count'] + 1,
			'reply_edit_locked'	=> ($auth->acl_get('m_blogreplylockedit') && $user->data['user_id'] != $reply_user_id) ? request_var('lock_post', false) : false,
		);

		// add the delete section to the array if it was deleted, if it was already deleted ignore
		if ( (!$reply_data->reply[$reply_id]['reply_deleted']) && (isset($_POST['delete'])) && $can_delete)
		{
			$sql_data['reply_deleted'] = $user->data['user_id'];
			$sql_data['reply_deleted_time'] = time();
		}

		$blog_plugins->plugin_do_arg_ref('reply_edit_sql', $sql_data);

		// the update query
		$sql = 'UPDATE ' . BLOGS_REPLY_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
			WHERE reply_id = \'' . $reply_id . '\'';

		$db->sql_query($sql);
	}

	$blog_plugins->plugin_do_arg('reply_edit_after_sql', $reply_id);

	unset($message_parser);

	// the confirm message & redirect
	if (isset($_POST['delete']) && $can_delete)
	{
		$blog_plugins->plugin_do('reply_edit_delete');

		// update the reply count for the blog
		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_reply_count = blog_reply_count - 1 WHERE blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);

		$message = $user->lang['REPLY_DELETED'] . '<br/><br/>';

		// redirect
		blog_meta_refresh(3, $blog_urls['view_blog']);
	}
	else
	{
		$message = (!$auth->acl_get('u_blogreplynoapprove')) ? $user->lang['REPLY_NEED_APPROVE'] . '<br /><br />' : ''; 
		$message .= '<a href="' . $blog_urls['view_reply'] . '">' . $user->lang['VIEW_REPLY'] . '</a><br/>';

		// redirect
		blog_meta_refresh(3, $blog_urls['view_reply']);
	}

	$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br/>';
	if ($user_id == $user->data['user_id'])
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
	}
	else
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', $user_data->user[$user_id]['username'], '</a>') . '<br/>';
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
	}

	trigger_error($message);
}
?>