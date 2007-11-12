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

// Was Cancel pressed? If so then redirect to the appropriate page
if ($cancel)
{
	blog_meta_refresh(0, $blog_urls['view_blog'], true);
}

// check to see if editing this message is locked, or if the one editing it has mod powers
if ($blog_data->blog[$blog_id]['blog_edit_locked'] && !$auth->acl_get('m_blogedit'))
{
	trigger_error('BLOG_EDIT_LOCKED');
}

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['DELETE_BLOG']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['DELETE_BLOG']);

if ($blog_data->blog[$blog_id]['blog_deleted'] != 0 && !$auth->acl_get('a_blogdelete'))
{
	trigger_error('BLOG_ALREADY_DELETED');
}

$blog_plugins->plugin_do('blog_delete');

if (confirm_box(true))
{
	$blog_plugins->plugin_do('blog_delete_confirm');

	// if it has already been soft deleted, and we want to hard delete it
	if ($blog_data->blog[$blog_id]['blog_deleted'] != 0 && $auth->acl_get('a_blogdelete'))
	{
		// delete the blog
		$sql = 'DELETE FROM ' . BLOGS_TABLE . ' WHERE blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);

		// delete the replies
		$sql = 'DELETE FROM ' . BLOGS_REPLY_TABLE . ' WHERE blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);
	}
	else
	{
		$blog_search->index_remove($blog_id);

		// soft delete the blog
		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_deleted = \'' . $user->data['user_id'] . ' \', blog_deleted_time = \'' . time() . '\' WHERE blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);

		// Update the blog_count for the user
		$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = blog_count - 1 WHERE user_id = \'' . $user_id . '\' AND blog_count > 0';
		$db->sql_query($sql);
	}

	handle_blog_cache('delete_blog', $user_id);

	blog_meta_refresh(3, $blog_urls['view_user']);

	$message = $user->lang['BLOG_DELETED'];

	if ($user->data['user_id'] == $user_id)
	{
		$message .= '<br/><br/>' . sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
	}
	else
	{
		$message .= '<br/><br/>' . sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', $username, '</a>');
	}

	trigger_error($message);
}
else
{
	if ( ($blog_data->blog[$blog_id]['blog_deleted'] != 0)) // if it has already been soft deleted and we are not trying to undelete
	{
		confirm_box(false, 'PERMANENTLY_DELETE_BLOG');
	}
	else
	{
		confirm_box(false, 'DELETE_BLOG');
	}
}
blog_meta_refresh(0, $blog_urls['view_blog'], true);
?>