<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: approve.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

// If they did not include the $reply_id give them an error...
if ($reply_id == 0)
{
	trigger_error('REPLY_NOT_EXIST');
}

// Was Cancel pressed? If so then redirect to the appropriate page
if ($cancel)
{
	blog_meta_refresh(0, $blog_urls['view_reply'], true);
}

// Add the language Variables for posting
$user->add_lang('posting');
	
// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['APPROVE_REPLY']);

blog_plugins::plugin_do('reply_approve');

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['APPROVE_REPLY']);

if (blog_data::$reply[$reply_id]['reply_approved'] == 0)
{
	if (confirm_box(true))
	{
		blog_plugins::plugin_do('reply_approve_confirm');

		$sql = 'UPDATE ' . BLOGS_REPLY_TABLE . '
			SET reply_approved = 1
			WHERE reply_id = ' . intval($reply_id);
		$db->sql_query($sql);

		// update the reply count for the blog
		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_reply_count = blog_reply_count + 1 WHERE blog_id = ' . intval($blog_id);
		$db->sql_query($sql);

		set_config('num_blog_replies', ++$config['num_blog_replies'], true);

		handle_subscription('new_reply',  censor_text(blog_data::$reply[$reply_id]['reply_subject']), 0, 0, $reply_id);

		handle_blog_cache('approve_reply', $user_id);

		blog_meta_refresh(3, $blog_urls['view_reply']);

		$message = $user->lang['APPROVE_REPLY_SUCCESS'] . '<br /><br />';
		$message .= '<a href="' . $blog_urls['view_reply'] . '">' . $user->lang['VIEW_REPLY'] . '</a><br />';
		$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br />';
		if ($user_id == $user->data['user_id'])
		{
			$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
		}
		else
		{
			$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', blog_data::$user[$user_id]['username'], '</a>') . '<br />';
			$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
		}

		trigger_error($message);
	}
	else
	{
		confirm_box(false, 'APPROVE_REPLY');
	}
}
else
{
	$message = $user->lang['REPLY_ALREADY_APPROVED'] . '<br /><br />';
	$message .= '<a href="' . $blog_urls['view_reply'] . '">' . $user->lang['VIEW_REPLY'] . '</a><br />';
	$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br />';
	if ($user_id == $user->data['user_id'])
	{
		$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
	}
	else
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', blog_data::$user[$user_id]['username'], '</a>') . '<br />';
		$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
	}

	trigger_error($message);
}
blog_meta_refresh(0, $blog_urls['view_reply']);
?>