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

// If they did not include the $reply_id give them an error...
if ($reply_id == 0)
{
	trigger_error('NO_REPLY');
}

// Was Cancel pressed? If so then redirect to the appropriate page
if ($cancel)
{
	redirect($blog_urls['view_reply']);
}

// Add the language Variables for the MCP
$user->add_lang('mcp');

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['REPORT_REPLY']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['REPORT_REPLY']);

$blog_plugins->plugin_do('reply_report');

// To close the reports
if ($reply_data->reply[$reply_id]['reply_reported'] && ($auth->acl_get('m_blogreplyreport') || $user_founder))
{
	if (confirm_box(true))
	{
		$blog_plugins->plugin_do('reply_report_confirm');

		$sql = 'UPDATE ' . BLOGS_REPLY_TABLE . '
			SET reply_reported = \'0\'
			WHERE reply_id = ' . $reply_id;
		$db->sql_query($sql);

		blog_meta_refresh(3, $$blog_urls['view_reply']);

		$message = $user->lang['REPORT_CLOSED_SUCCESS'] . '<br/><br/>';
		$message .= '<a href="' . $blog_urls['view_reply'] . '">' . $user->lang['VIEW_REPLY'] . '</a><br/>';
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
		confirm_box(false, 'CLOSE_REPORT');
	}
}
else
{
	if (confirm_box(true))
	{
		// we are making it look like the user can report the reply even if it has already been reported...but if it already has reported we can skip the extra SQL query
		if (!$reply_data->reply[$reply_id]['reply_reported'])
		{
			$sql = 'UPDATE ' . BLOGS_REPLY_TABLE . '
				SET reply_reported = \'1\'
				WHERE reply_id = ' . $reply_id;
			$db->sql_query($sql);
		}

		inform_approve_report('reply_report', $reply_id);

		blog_meta_refresh(3, $blog_urls['view_reply']);
	
		$message = $user->lang['POST_REPORTED_SUCCESS'] . '<br/><br/>';
		$message .= '<a href="' . $blog_urls['view_reply'] . '">' . $user->lang['VIEW_REPLY'] . '</a><br/>';
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
		confirm_box(false, 'REPLY_REPORT');
	}
}
?>