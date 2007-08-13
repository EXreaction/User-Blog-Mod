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
	redirect($blog_urls['view_blog']);
}

// if someone is trying to un-delete a blog and the blog is not deleted
if ($blog_data->blog[$blog_id]['blog_deleted'] == 0)
{
	trigger_error('BLOG_NOT_DELETED');
}

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['UNDELETE_BLOG']);

// Generate the breadcrumbs
generate_blog_breadcrumbs(array(
	sprintf($user->lang['USERNAMES_BLOGS'], $username)			=> $blog_urls['view_user'],
	censor_text($blog_data->blog[$blog_id]['blog_subject'])		=> $blog_urls['view_blog'],
	$user->lang['UNDELETE_BLOG']								=> $blog_urls['self'],
));

if (confirm_box(true))
{
	// magically un-delete the blog ;-)
	$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_deleted = \'0\', blog_deleted_time = \'0\' WHERE blog_id = \'' . $blog_id . '\'';
	$db->sql_query($sql);

	// Update the blog_count for the user
	$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = blog_count + 1 WHERE user_id = \'' . $user_id . '\'';
	$db->sql_query($sql);

	handle_blog_cache('delete_blog', $user_id);

	meta_refresh(3, $blog_urls['view_blog']);

	$message = $user->lang['BLOG_UNDELETED'] .'<br/><br/>';
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
else
{
	confirm_box(false, 'UNDELETE_BLOG');
}

// they pressed No, so redirect them
redirect($blog_urls['view_blog']);
?>