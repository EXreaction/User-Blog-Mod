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
	global $blog_plugins;

	if (!defined('IN_BLOG'))
	{
		// Add some language variables
		$user->add_lang('mods/blog/blog');

		// include the functions & permissions file
		include($phpbb_root_path . 'includes/blog/functions.' . $phpEx);
		include($phpbb_root_path . 'includes/blog/permissions.' . $phpEx);
		include($phpbb_root_path . 'includes/blog/plugins/plugins.' . $phpEx);

		$blog_plugins = new blog_plugins();
		$blog_plugins_path = $phpbb_root_path . 'includes/blog/plugins/';
		$blog_plugins->load_plugins();
	}

	$blog_plugins->plugin_do('blog_header');

	// Add the User Blog's Link if they can view blog's
	if (check_blog_permissions('', '', true))
	{
		$template->assign_block_vars('blog_links', array(
			'URL'		=> blog_url(false),
			'CLASS'		=> 'icon-members',
			'IMG'		=> '<img src="' . $phpbb_root_path . 'styles/' . $user->theme['theme_path'] . '/theme/images/icon_mini_members.gif" />',
			'TEXT'		=> $user->lang['USER_BLOGS'],
		));

		// Add the My Blog's Link if they can view blogs and are registered
		if (check_blog_permissions('blog', 'add', true))// || ($user->data['blog_count'] > 0 && check_blog_permissions('', '', true)))
		{
			$template->assign_block_vars('blog_links', array(
				'URL'		=> blog_url($user->data['user_id']),
				'CLASS'		=> 'icon-ucp',
				'IMG'		=> '<img src="' . $phpbb_root_path . 'styles/' . $user->theme['theme_path'] . '/theme/images/icon_mini_message.gif" alt="' . $user->lang['MY_BLOGS'] . '" />',
				'TEXT'		=> $user->lang['MY_BLOGS'],
			));
		}
	}

	// If we are viewing a users' profile add a link to view the users' blog in the custom profile section
	if ( (request_var('mode', '') == 'viewprofile') && (request_var('u', '') != '') )
	{
		add_blog_links(request_var('u', ''), 'custom_fields', false, true);
	}
}
?>