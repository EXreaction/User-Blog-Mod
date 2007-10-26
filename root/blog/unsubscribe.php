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

if (!$config['user_blog_subscription_enabled'])
{
	blog_meta_refresh(0, $blog_urls['main'], true);
}

$subscribe_mode = request_var('post', '', true);

// generate the header
page_header($user->lang['UNSUBSCRIBE']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['UNSUBSCRIBE']);

$blog_plugins->plugin_do('unsubscribe_start');

if ($blog_id != 0)
{
	if (!$subscribed)
	{
		trigger_error('NOT_SUBSCRIBED_BLOG');
	}

	if (confirm_box(true))
	{
		$blog_plugins->plugin_do('unsubscribe_blog_confirm');

		$sql = 'DELETE FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE sub_user_id = \'' . $user->data['user_id'] . '\'
				AND blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);

		handle_blog_cache('subscription', $user->data['user_id']);

		$template->assign_vars(array(
			'S_WATCH_FORUM_TITLE'	=> $user->lang['SUBSCRIBE_BLOG'],
			'S_WATCH_FORUM_LINK'	=> $blog_urls['subscribe'],
			'S_WATCHING_FORUM'		=> false,
		));

		$message = $user->lang['SUBSCRIPTION_REMOVED'] . '<br /><br />'; 
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

		blog_meta_refresh(3, $blog_urls['view_blog']);

		trigger_error($message);
	}
	else
	{
		$blog_plugins->plugin_do('unsubscribe_blog');

		confirm_box(false, 'UNSUBSCRIBE_BLOG');
	}
}
else if ($user_id != 0)
{
	if (!$subscribed)
	{
		trigger_error('NOT_SUBSCRIBED_USER');
	}

	if (confirm_box(true))
	{
		$blog_plugins->plugin_do('unsubscribe_user_confirm');

		$sql = 'DELETE FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE sub_user_id = \'' . $user->data['user_id'] . '\'
				AND user_id = \'' . $user_id . '\'';
		$db->sql_query($sql);

		handle_blog_cache('subscription', $user->data['user_id']);

		$template->assign_vars(array(
			'S_WATCH_FORUM_TITLE'	=> $user->lang['SUBSCRIBE_USER'],
			'S_WATCH_FORUM_LINK'	=> $blog_urls['subscribe'],
			'S_WATCHING_FORUM'		=> false,
		));

		$message = $user->lang['SUBSCRIPTION_REMOVED'] . '<br /><br />'; 
		if ($user_id == $user->data['user_id'])
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
		}
		else
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', $user_data->user[$user_id]['username'], '</a>') . '<br/>';
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
		}

		blog_meta_refresh(3, $blog_urls['view_user']);

		trigger_error($message);
	}
	else
	{
		$blog_plugins->plugin_do('unsubscribe_user');

		confirm_box(false, 'UNSUBSCRIBE_USER');
	}
}
else
{
	trigger_error($user->lang['BLOG_USER_NOT_PROVIDED']);
}
blog_meta_refresh(0, $blog_urls['main'], true);
?>