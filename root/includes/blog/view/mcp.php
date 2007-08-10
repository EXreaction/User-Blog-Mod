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

// Add the language Variables for viewtopic
$user->add_lang('viewtopic');

generate_blog_breadcrumbs($user->lang['BLOG_MCP']);
page_header($user->lang['BLOG_MCP']);

// Reported Blogs
$reported_blog_ids = $blog_data->get_blog_data('reported');

// Non-Approved blogs
$disapproved_blog_ids = $blog_data->get_blog_data('disapproved');

// Reported Replies
$reported_reply_ids = $reply_data->get_reply_data('reported');

// Non-Approved Replies
$disapproved_reply_ids = $reply_data->get_reply_data('disapproved');

$user_data->get_user_data(false, true);
update_edit_delete();

// Output the reported blogs
if ($reported_blog_ids !== false)
{
	foreach ($reported_blog_ids as $id)
	{
		$user_row = $blog_data->handle_user_data($blog_data->blog[$id]['user_id']);
		$blog_row = $blog_data->handle_blog_data($id, 50, 'reported_blogs');

		$template->assign_block_vars('blog_reportedrow', $user_row + $blog_row);
	}
}

// Output the non-approved blogs
if ($disapproved_blog_ids !== false)
{
	foreach ($disapproved_blog_ids as $id)
	{
		$user_row = $user_data->handle_user_data($blog_data->blog[$id]['user_id']);
		$blog_row = $blog_data->handle_blog_data($id, 50, 'disapproved_blogs');

		$template->assign_block_vars('blog_disapprovedrow', $user_row + $blog_row);
	}
}

// Output the reported replies
if ($reported_reply_ids !== false)
{
	foreach ($reported_reply_ids as $id)
	{
		$user_row = $user_data->handle_user_data($reply_data->reply[$id]['user_id']);
		$reply_row = $blog_data->handle_reply_data($id, 50, 'reported_replies');

		$template->assign_block_vars('reply_reportedrow', $user_row + $reply_row);
	}
}

// Output the non-approved replies
if ($disapproved_reply_ids !== false)
{
	foreach ($disapproved_reply_ids as $id)
	{
		$user_row = $user_data->handle_user_data($reply_data->reply[$id]['user_id']);
		$reply_row = $blog_data->handle_reply_data($id, 50, 'disapproved_replies');

		$template->assign_block_vars('reply_disapprovedrow', $user_row + $reply_row);
	}
}

// tell the template parser what template file to use
$template->set_filenames(array(
	'body' => 'mcp_blog.html'
));
?>