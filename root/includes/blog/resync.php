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

// Was Cancel pressed? If so then redirect to the appropriate page
if ($cancel)
{
	redirect($blog_urls['main']);
}


// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['RESYNC_BLOG']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['RESYNC_BLOG']);

if (confirm_box(true))
{
	resync_blog('all');

	$message = $user->lang['RESYNC_BLOG_SUCESS'] . '<br/><br/>';
	$message .= sprintf($user->lang['RETURN_MAIN'], '<a href="' . $blog_urls['main'] . '">', '</a>');

	trigger_error($message);
}
else
{
	confirm_box(false, 'RESYNC_BLOG');
}

// they pressed No, so redirect them
redirect($blog_urls['view_main']);
?>