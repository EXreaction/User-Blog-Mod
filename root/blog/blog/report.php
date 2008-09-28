<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: report.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

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

// add the mcp language file
$user->add_lang('mcp');

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['REPORT_BLOG']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['REPORT_BLOG']);

blog_plugins::plugin_do('blog_report_start');

// To close the reports
if (blog_data::$blog[$blog_id]['blog_reported'] && $auth->acl_get('m_blogreport'))
{
	if (confirm_box(true))
	{
		blog_plugins::plugin_do('blog_report_confirm');

		$sql = 'UPDATE ' . BLOGS_TABLE . '
			SET blog_reported = 0
			WHERE blog_id = ' . intval($blog_id);
		$db->sql_query($sql);

		handle_blog_cache('report_blog', $user_id);

		blog_meta_refresh(3, $blog_urls['view_blog']);

		$message = $user->lang['REPORT_CLOSED_SUCCESS'];
		$message .= '<br /><br /><a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a>';
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
		if (!blog_data::$blog[$blog_id]['blog_reported'])
		{
			$sql = 'UPDATE ' . BLOGS_TABLE . '
				SET blog_reported = 1
				WHERE blog_id = ' . intval($blog_id);
			$db->sql_query($sql);
		}

		inform_approve_report('blog_report', $blog_id);

		handle_blog_cache('report_blog', $user_id);

		blog_meta_refresh(3, $blog_urls['view_blog']);
	
		$message = $user->lang['POST_REPORTED_SUCCESS'] . '<br /><br /><a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a>';
		trigger_error($message);
	}
	else
	{
		confirm_box(false, 'BLOG_REPORT');
	}
}
blog_meta_refresh(0, $blog_urls['view_blog']);
?>