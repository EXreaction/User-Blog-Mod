<?php
/**
 *
 * @package phpBB3 User Blog
 * @copyright (c) 2007 EXreaction, Lithium Studios
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 *
 */

// check if the User Blog Mod is enabled
if ($config['user_blog_enable'])
{
	// Add some language variables
	$user->add_lang('mods/blog');

	// include the functions & permissions file
	include_once($phpbb_root_path . 'includes/blog/functions.' . $phpEx);
	include_once($phpbb_root_path . 'includes/blog/permissions.' . $phpEx);

	// Add the User Blog's Link if they can view blog's
	if (check_blog_permissions('', '', true))
	{
		$template->assign_block_vars('blog_links', array(
			'URL'		=> append_sid("{$phpbb_root_path}blog.$phpEx"),
			'CLASS'		=> 'icon-members',
			'IMG'		=> '<img src="' . $phpbb_root_path . 'styles/' . $user->theme['theme_path'] . '/theme/images/icon_mini_members.gif" />',
			'TEXT'		=> $user->lang['USER_BLOGS'],
		));
	}

	// Add the My Blog's Link if they can view blogs and are registered
	if (check_blog_permissions('blog', 'add', true) || ($user->data['blog_count'] > 0 && check_blog_permissions('', '', true)))
	{
		$template->assign_block_vars('blog_links', array(
			'URL'		=> append_sid("{$phpbb_root_path}blog.$phpEx", 'u=' . $user->data['user_id']),
			'CLASS'		=> 'icon-ucp',
			'IMG'		=> '<img src="' . $phpbb_root_path . 'styles/' . $user->theme['theme_path'] . '/theme/images/icon_mini_message.gif" alt="' . $user->lang['MY_BLOGS'] . '" />',
			'TEXT'		=> $user->lang['MY_BLOGS'],
		));
	}

	// If we are viewing a users' profile add a link to view the users' blog in the custom profile section
	if ( (request_var('mode', '') == 'viewprofile') && (request_var('u', '') != '') )
	{
		add_blog_links(request_var('u', ''), 'custom_fields', false, true);
	}
}
?>