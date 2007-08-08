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
page_header($user->lang['SUBSCRIBE']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['SUBSCRIBE']);

if ($subscribed)
{
	trigger_error('ALREADY_SUBSCRIBED');
}

if ($blog_id != 0)
{
	if ($submit)
	{
		switch($subscribe_mode)
		{
			case $user->lang['PRIVATE_MESSAGE'] :
				add_subscription($user->data['user_id'], 0, false, $blog_id);
				break;
			case $user->lang['EMAIL'] :
				add_subscription($user->data['user_id'], 1, false, $blog_id);
				break;
			case $user->lang['PM_AND_EMAIL'] :
				add_subscription($user->data['user_id'], 2, false, $blog_id);
				break;
			default :
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
			'MESSAGE_TITLE'			=> $user->lang['SUBSCRIBE_BLOG_TITLE'],
			'MESSAGE_TEXT'			=> $user->lang['SUBSCRIBE_BLOG_CONFIRM'],
			'S_EMAIL_ENABLED'		=> ($config['email_enable']) ? true : false,
		));
	}
}
else if ($user_id != 0)
{
	if ($submit)
	{
		switch($subscribe_mode)
		{
			case $user->lang['PRIVATE_MESSAGE'] :
				add_subscription($user->data['user_id'], 0, $user_id, false);
				break;
			case $user->lang['EMAIL'] :
				add_subscription($user->data['user_id'], 1, $user_id, false);
				break;
			case $user->lang['PM_AND_EMAIL'] :
				add_subscription($user->data['user_id'], 2, $user_id, false);
				break;
			default :
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
		meta_refresh(3, $blog_urls['view_user']);

		trigger_error($message);
	}
	else
	{
		$template->assign_vars(array(
			'S_CONFIRM_ACTION'		=> $blog_urls['self'],
			'MESSAGE_TITLE'			=> $user->lang['SUBSCRIBE_USER_TITLE'],
			'MESSAGE_TEXT'			=> $user->lang['SUBSCRIBE_USER_CONFIRM'],
			'S_EMAIL_ENABLED'		=> ($config['email_enable']) ? true : false,
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

/**
 * Adds a subscription to a blog or user
 *
 * @param int $subscribe_user_id The user_id of the user who we want to add the subscription for
 * @param int $mode The type of subscription (0 is Private Message, 1 is Email, 2 is both)
 * @param int|bool $blog_id The user_id of the user we want to subscribe to (if we want to subscribe to a user_id)
 * @param int|bool $reply_id The blog_id of the user we want to subscribe to (if we want to subscribe to a blog_id)
 */
function add_subscription($subscribe_user_id, $mode, $user_id, $blog_id = false)
{
	global $db;

	$sql_data = array(
		'sub_user_id'	=> $subscribe_user_id,
		'sub_type'		=> $mode,
		'blog_id'		=> $blog_id,
		'user_id'		=> $user_id,
	);

	$sql = 'INSERT INTO ' . BLOGS_SUBSCRIPTION_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);
	$db->sql_query($sql);
}
?>