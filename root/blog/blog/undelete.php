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

// Was Cancel pressed? If so then redirect to the appropriate page
if ($cancel)
{
	blog_meta_refresh(0, $blog_urls['view_blog'], true);
}

// if someone is trying to un-delete a blog and the blog is not deleted
if (blog_data::$blog[$blog_id]['blog_deleted'] == 0)
{
	trigger_error('BLOG_NOT_DELETED');
}

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['UNDELETE_BLOG']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['UNDELETE_BLOG']);

blog_plugins::plugin_do('blog_undelete_start');

if (confirm_box(true))
{
	blog_plugins::plugin_do('blog_undelete_confirm');

	$blog_search->index('add', $blog_id, 0, blog_data::$blog[$blog_id]['blog_text'], blog_data::$blog[$blog_id]['blog_subject'], $user_id);

	// undelete the blog
	$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_deleted = 0, blog_deleted_time = 0 WHERE blog_id = ' . intval($blog_id);
	$db->sql_query($sql);

	// Update the blog_count for the user
	$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = blog_count + 1 WHERE user_id = ' . intval($user_id);
	$db->sql_query($sql);

	set_config('num_blogs', $config['num_blogs']++, true);

	// Update the blog_count for all the categories it is in.
	$sql = 'SELECT category_id FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . intval($blog_id);
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . ' SET blog_count = blog_count + 1 WHERE category_id = ' . $row['category_id'];
		$db->sql_query($sql);
	}

	handle_blog_cache('undelete_blog', $user_id);

	blog_meta_refresh(3, $blog_urls['view_blog']);

	$message = $user->lang['BLOG_UNDELETED'] .'<br/><br/>';
	$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br/>';
	if ($user_id == $user->data['user_id'])
	{
		$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
	}
	else
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', blog_data::$user[$user_id]['username'], '</a>') . '<br/>';
		$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
	}

	trigger_error($message);
}
else
{
	confirm_box(false, 'UNDELETE_BLOG');
}
blog_meta_refresh(0, $blog_urls['view_blog']);
?>