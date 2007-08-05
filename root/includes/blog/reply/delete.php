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

// Was Cancel pressed? If so then redirect to the appropriate page
if ($cancel)
{
	redirect($blog_urls['view_reply']);
}

// Add the language Variables for posting
$user->add_lang('posting');

// check to see if editing this message is locked, or if the one editing it has mod powers
if ($blog_data->reply[$reply_id]['reply_edit_locked'] && !$auth->acl_get('m_blogreplyedit') && !$user_founder)
{
	trigger_error('REPLY_EDIT_LOCKED');
}

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['DELETE_REPLY']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['DELETE_REPLY']);

if (confirm_box(true))
{
	// if it has already been soft deleted
	if ($blog_data->reply[$reply_id]['reply_deleted'] != 0 && ($auth->acl_get('a_blogreplydelete') || $user_founder))
	{
		$sql = 'DELETE FROM ' . BLOGS_REPLY_TABLE . ' WHERE reply_id = \'' . $reply_id . '\'';
		$db->sql_query($sql);

		// update the real reply count for the blog
		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_real_reply_count = blog_real_reply_count - 1 WHERE blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);
	}
	else if ($blog_data->reply[$reply_id]['reply_deleted'] == 0)
	{
		// soft delete the reply
		$sql = 'UPDATE ' . BLOGS_REPLY_TABLE . ' SET reply_deleted = \'' . $user->data['user_id'] . ' \', reply_deleted_time = \'' . time() . '\' WHERE reply_id = \'' . $reply_id . '\'';
		$db->sql_query($sql);

		// update the reply count for the blog
		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_reply_count = blog_reply_count - 1 WHERE blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);
	}

	meta_refresh(3, $blog_urls['view_blog']);

	$message = $user->lang['REPLY_DELETED'] . '<br/><br/>';
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

	trigger_error($message);
}
else
{
	// if it has already been soft deleted
	if ($blog_data->reply[$reply_id]['reply_deleted'] != 0 && ($auth->acl_get('a_blogreplydelete') || $user_founder))
	{
		confirm_box(false, 'PERMANENTLY_DELETE_REPLY');
	}
	else if ($blog_data->reply[$reply_id]['reply_deleted'] == 0)
	{
		confirm_box(false, 'DELETE_REPLY');
	}
}

// they pressed No, so redirect them
redirect($blog_urls['view_reply']);
?>