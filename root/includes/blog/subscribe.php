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

$subscribe_mode = request_var('post', '', true);

// generate the header
page_header($user->lang['SUBSCRIBE']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['SUBSCRIBE']);

if ($blog_id != 0)
{
	if ($submit)
	{
		if ($subscribe_mode == $user->lang['PRIVATE_MESSAGE'])
		{
			add_subscription($user->data['user_id'], 0, false, $blog_id);
		}
		else if ($subscribe_mode == $user->lang['EMAIL'])
		{
			add_subscription($user->data['user_id'], 1, false, $blog_id);
		}
		else
		{
			redirect($blog_urls['view_blog']);
		}

		$message = $user->lang['SUBSCRIPTION_ADDED'] . '<br /><br />'; 
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

		// redirect
		meta_refresh(3, $blog_urls['view_blog']);

		trigger_error($message);
	}
	else
	{
		$template->assign_vars(array(
			'S_CONFIRM_ACTION'		=> $blog_urls['self'],
			'MESSAGE_TITLE'			=> $user->lang['SUBSCRIBE_BLOG'],
			'MESSAGE_TEXT'			=> $user->lang['SUBSCRIBE_BLOG_CONFIRM'],
		));
	}
}
else if ($user_id != 0)
{
	if ($submit)
	{
		if ($subscribe_mode == $user->lang['PRIVATE_MESSAGE'])
		{
			add_subscription($user->data['user_id'], 0, $user_id, false);
		}
		else if ($subscribe_mode == $user->lang['EMAIL'])
		{
			add_subscription($user->data['user_id'], 1, $user_id, false);
		}
		else
		{
			redirect($blog_urls['view_user']);
		}

		$message = $user->lang['SUBSCRIPTION_ADDED'] . '<br /><br />'; 
		if ($user_id == $user->data['user_id'])
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
		}
		else
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', $blog_data->user[$user_id]['username'], '</a>') . '<br/>';
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
		}

		// redirect
		meta_refresh(3, $blog_urls['view_blog']);

		trigger_error($message);
	}
	else
	{
		$template->assign_vars(array(
			'S_CONFIRM_ACTION'		=> $blog_urls['self'],
			'MESSAGE_TITLE'			=> $user->lang['SUBSCRIBE_USER'],
			'MESSAGE_TEXT'			=> $user->lang['SUBSCRIBE_USER_CONFIRM'],
		));
	}
}
else
{
	trigger_error($user->lang['BLOG_USER_NOT_PROVIDED']);
}

// Tell the template parser what template file to use
$template->set_filenames(array(
	'body' => 'blog_subscribe.html'
));
?>