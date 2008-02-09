<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['RESYNC_BLOG']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['RESYNC_BLOG']);

if (confirm_box(true))
{
	include($phpbb_root_path . 'blog/includes/functions_admin.' . $phpEx);

	resync_blog('all');

	$message = $user->lang['RESYNC_BLOG_SUCCESS'] . '<br/><br/>';
	$message .= sprintf($user->lang['RETURN_MAIN'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx") . '">', '</a>');

	trigger_error($message);
}
else
{
	confirm_box(false, 'RESYNC_BLOG');
}

blog_meta_refresh(0, append_sid("{$phpbb_root_path}blog.$phpEx"));
?>