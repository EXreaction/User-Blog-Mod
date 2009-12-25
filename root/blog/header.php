<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: header.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (isset($config['user_blog_enable']) && $config['user_blog_enable'])
{
	if (!isset($user->lang['USER_BLOGS']) || !isset($user->lang['MY_BLOG']))
	{
		$user->add_lang('mods/blog/common');
	}

	$template->assign_vars(array(
		'TOTAL_BLOG_ENTRIES'		=> sprintf($user->lang['TOTAL_BLOG_ENTRIES'], $config['num_blogs']),
	));

	// Add the User Blog's Link if they can view blog's
	if ($auth->acl_get('u_blogview'))
	{
		if (isset($config['user_blog_seo']) && $config['user_blog_seo'])
		{
			$blog_url = $my_blog_url = ((defined('BLOG_ROOT')) ? generate_board_url(true) . BLOG_ROOT : generate_board_url()) . '/blog/';
			$my_blog_url .= urlencode($user->data['username']) . '/';

			$extras = 'index';
			// Add the style if required
			if (isset($_GET['style']) && !isset($url_data['style']))
			{
				$extras .= '_style-' . request_var('style', '');
			}

			// Add the Session ID if required, do not add it for guests.
			if ($_SID && $user->data['is_registered'])
			{
				$extras .= "_sid-{$_SID}";
			}

			// If there are any extras, add them
			if ($extras != 'index')
			{
				$blog_url .= $extras . '.html';
				$my_blog_url .= $extras . '.html';
			}

			$username_check = '#&+/\:?"<>%|';
			if (strpbrk($user->data['username'], $username_check))
			{
				$my_blog_url = append_sid("{$phpbb_root_path}blog.$phpEx", 'u=' . $user->data['user_id']);
			}
		}
		else
		{
			$blog_url = append_sid("{$phpbb_root_path}blog.$phpEx");
			$my_blog_url = append_sid("{$phpbb_root_path}blog.$phpEx", 'u=' . $user->data['user_id']);
		}

		$template->assign_block_vars('blog_links', array(
			'URL'		=> $blog_url,
			'CLASS'		=> 'icon-members',
			'IMG'		=> '<img src="' . $phpbb_root_path . 'styles/' . $user->theme['theme_path'] . '/theme/images/icon_mini_members.gif" alt="' . $user->lang['BLOGS'] . '" />',
			'TEXT'		=> $user->lang['BLOGS'],
		));

		// Also output it normally, since it has been requested a few times...
		$template->assign_vars(array(
			'U_BLOG'	=> $blog_url,
		));

		// Add the My Blog's Link if they can view blogs and are registered
		if ($auth->acl_get('u_blogpost'))
		{
			$template->assign_block_vars('blog_links', array(
				'URL'		=> $my_blog_url,
				'CLASS'		=> 'icon-ucp',
				'IMG'		=> '<img src="' . $phpbb_root_path . 'styles/' . $user->theme['theme_path'] . '/theme/images/icon_mini_message.gif" alt="' . $user->lang['MY_BLOG'] . '" />',
				'TEXT'		=> $user->lang['MY_BLOG'],
			));

			$template->assign_vars(array(
				'U_MY_BLOG'	=> $my_blog_url,
			));
		}
	}

	// If we are viewing a users' profile add a link to view the users' blog in the custom profile section
	$user_id = request_var('u', 0);
	$username = request_var('un', '', true);
	if (request_var('mode', '') == 'viewprofile' && ($user_id || $username))
	{
		if (!$user_id)
		{
			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE  username_clean = \'' . $db->sql_escape(utf8_clean_string($username)) . "'";
			$db->sql_query($sql);
			$user_id = $db->sql_fetchfield('user_id');
			$db->sql_freeresult();

			if (!$user_id)
			{
				return;
			}
		}

		if (!function_exists('url_replace'))
		{
			include($phpbb_root_path . 'blog/includes/functions.' . $phpEx);
		}
		if (!function_exists('get_attachment_data'))
		{
			include($phpbb_root_path . 'blog/includes/functions_view.' . $phpEx);
		}
		if (!class_exists('blog_plugins'))
		{
			include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);
			new blog_plugins();
		}

		add_blog_links($user_id, 'custom_fields', false, true);
	}
}
?>