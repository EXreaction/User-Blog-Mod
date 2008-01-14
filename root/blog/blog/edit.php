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

$blog_plugins->plugin_do('blog_edit_start');

$category_ary = request_var('category', array(0));

// If they select edit mode and didn't submit or hit preview(means they came directly from the view blog page)
if (!$submit && !$preview && !$refresh)
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

	// Attachments
	$blog_attachment->get_attachment_data($blog_id);
	$blog_attachment->attachment_data = blog_data::$blog[$blog_id]['attachment_data'];
}
else
{
	$blog_subject = utf8_normalize_nfc(request_var('subject', '', true));
	$blog_text = utf8_normalize_nfc(request_var('message', '', true));

	$post_options->set_status(!isset($_POST['disable_bbcode']), !isset($_POST['disable_smilies']), !isset($_POST['disable_magic_url']));

	// set up the message parser to parse BBCode, Smilies, etc
	$message_parser = new parse_message();
	$message_parser->message = $blog_text;
	$message_parser->parse($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, $post_options->img_status, $post_options->flash_status, $post_options->bbcode_status, $post_options->url_status);

	// check the form key
	if (!check_form_key('postform'))
	{
		$error[] = $user->lang['FORM_INVALID'];
	}

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

	// Attachments
	$blog_attachment->get_submitted_attachment_data();
	$blog_attachment->parse_attachments('fileupload', $submit, $preview, $refresh, $blog_text);
	if (sizeof($blog_attachment->warn_msg))
	{
		$error[] = implode('<br />', $blog_attachment->warn_msg);
	}
}

$temp = compact('blog_subject', 'blog_text', 'error');
$blog_plugins->plugin_do_arg_ref('blog_edit_after_setup', $temp);
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

		$blog_plugins->plugin_do_arg_ref('blog_edit_preview', $preview_message);

		// output some data to the template parser
		$template->assign_vars(array(
			'S_DISPLAY_PREVIEW'			=> true,
			'PREVIEW_SUBJECT'			=> censor_text($blog_subject),
			'PREVIEW_MESSAGE'			=> $preview_message,
			'POST_DATE'					=> $user->format_date(blog_data::$blog[$blog_id]['blog_time']),
		));
	}

	$blog_plugins->plugin_do('blog_edit_after_preview');

	// handles the basic data we need to output for posting
	handle_basic_posting_data('blog', 'edit');

	// Assign some variables to the template parser
	$template->assign_vars(array(
		'ERROR'						=> (sizeof($error)) ? implode('<br />', $error) : '',
		'MESSAGE'					=> $blog_text,
		'SUBJECT'					=> $blog_subject,

		'L_MESSAGE_BODY_EXPLAIN'	=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',
		'L_POST_A'					=> $user->lang['EDIT_A_BLOG'],

		'S_SHOW_PERMISSIONS_BOX'	=> true,
		'S_EDIT_REASON'				=> true,
		'S_LOCK_POST_ALLOWED'		=> (($auth->acl_get('m_bloglockedit')) && $user->data['user_id'] != blog_data::$blog[$blog_id]['user_id']) ? true : false,
	));

	// Tell the template parser what template file to use
	$template->set_filenames(array(
		'body' => 'blog/blog_posting_layout.html'
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
		'blog_approved' 			=> (blog_data::$blog[$blog_id]['blog_approved'] == 0) ? ($auth->acl_get('u_blognoapprove')) ? 1 : 0 : 1,
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
		'blog_attachment'			=> (count($blog_attachment->attachment_data)) ? 1 : 0,
	);

	$blog_search->index('edit', $blog_id, 0, $message_parser->message, $blog_subject, $user_id);

	$blog_plugins->plugin_do_arg_ref('blog_edit_sql', $sql_data);

	$sql = 'UPDATE ' . BLOGS_TABLE . '
		SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
			WHERE blog_id = ' . intval($blog_id);
	$db->sql_query($sql);

	$blog_attachment->update_attachment_data($blog_id);

	$blog_plugins->plugin_do_arg('blog_edit_after_sql', $blog_id);

	unset($message_parser, $sql_data);

	// First, delete the category in record for this blog
	$sql = 'SELECT category_id FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . intval($blog_id);
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . ' SET blog_count = blog_count - 1 WHERE category_id = ' . $row['category_id'] . ' AND blog_count > 0';
		$db->sql_query($sql);
	}
	$sql = 'DELETE FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . intval($blog_id);
	$db->sql_query($sql);

	// Insert into the categories list
	if (count($category_ary) > 1 || (isset($category_ary[0]) && $category_ary[0] != 0))
	{
		$category_list = get_blog_categories('category_id');

		foreach ($category_ary as $i => $cat_id)
		{
			if (array_key_exists($cat_id, $category_list))
			{
				$sql = 'INSERT INTO ' . BLOGS_IN_CATEGORIES_TABLE . ' ' . $db->sql_build_array('INSERT', array('blog_id' => intval($blog_id), 'category_id' => $cat_id));
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

	handle_blog_cache('edit_blog', $user_id);

	$message = ((!$auth->acl_get('u_blognoapprove')) ? $user->lang['BLOG_NEED_APPROVE'] . '<br /><br />' : $user->lang['BLOG_EDIT_SUCCESS']) . '<br /><br />'; 
	$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br/><br/>';
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