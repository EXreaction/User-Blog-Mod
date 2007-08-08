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
	redirect($blog_urls['main']);
}

$subscribe_mode = request_var('post', '', true);

// generate the header
page_header($user->lang['UNSUBSCRIBE']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['UNSUBSCRIBE']);

if ($blog_id != 0)
{
	$sql = 'SELECT count(sub_type) AS total FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
		WHERE sub_user_id = \'' . $user->data['user_id'] . '\'
			AND blog_id = \'' . $blog_id . '\'';
	$result = $db->sql_query($sql);
	$total = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($total['total'] == 0)
	{
		trigger_error('NOT_SUBSCRIBED_BLOG');
	}

	if (confirm_box(true))
	{
		$sql = 'DELETE FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE sub_user_id = \'' . $user->data['user_id'] . '\'
				AND blog_id = \'' . $blog_id . '\'';
		$db->sql_query($sql);

		$message = $user->lang['SUBSCRIPTION_REMOVED'] . '<br /><br />'; 
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

		meta_refresh(3, $blog_urls['view_blog']);

		trigger_error($message);
	}
	else
	{
		confirm_box(false, 'UNSUBSCRIBE_BLOG');
	}
}
else if ($user_id != 0)
{
	$sql = 'SELECT count(sub_type) AS total FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
		WHERE sub_user_id = \'' . $user->data['user_id'] . '\'
			AND user_id = \'' . $user_id . '\'';
	$result = $db->sql_query($sql);
	$total = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($total['total'] == 0)
	{
		trigger_error('NOT_SUBSCRIBED_USER');
	}

	if (confirm_box(true))
	{
		$sql = 'DELETE FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE sub_user_id = \'' . $user->data['user_id'] . '\'
				AND user_id = \'' . $user_id . '\'';
		$db->sql_query($sql);

		$message = $user->lang['SUBSCRIPTION_REMOVED'] . '<br /><br />'; 
		if ($user_id == $user->data['user_id'])
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
		}
		else
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', $blog_data->user[$user_id]['username'], '</a>') . '<br/>';
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
		}

		meta_refresh(3, $blog_urls['view_user']);

		trigger_error($message);
	}
	else
	{
		confirm_box(false, 'UNSUBSCRIBE_USER');
	}
}
else
{
	trigger_error($user->lang['BLOG_USER_NOT_PROVIDED']);
}
?>