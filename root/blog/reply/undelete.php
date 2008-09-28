<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: undelete.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
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
page_header($user->lang['UNDELETE_REPLY']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['UNDELETE_REPLY']);

// if someone is trying to un-delete a reply and the reply is not deleted
if (blog_data::$reply[$reply_id]['reply_deleted'] == 0)
{
	trigger_error('REPLY_NOT_DELETED');
}

blog_plugins::plugin_do('reply_undelete');

if (confirm_box(true))
{
	blog_plugins::plugin_do('reply_undelete_confirm');

	$blog_search->index('add', $blog_id, $reply_id, blog_data::$reply[$reply_id]['reply_text'], blog_data::$reply[$reply_id]['reply_subject'], blog_data::$reply[$reply_id]['user_id']);

	$sql = 'UPDATE ' . BLOGS_REPLY_TABLE . ' SET reply_deleted = 0, reply_deleted_time = 0 WHERE reply_id = ' . intval($reply_id);
	$db->sql_query($sql);

	$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_reply_count = blog_reply_count + 1 WHERE blog_id = ' . intval($blog_id);
	$db->sql_query($sql);

	set_config('num_blog_replies', ++$config['num_blog_replies'], true);

	handle_blog_cache('undelete_reply', $user_id);

	blog_meta_refresh(3, $blog_urls['view_reply']);

	$message = $user->lang['REPLY_UNDELETED'] . '<br /><br />';
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
	confirm_box(false, 'UNDELETE_REPLY');
}

blog_meta_refresh(0, $blog_urls['view_reply']);
?>