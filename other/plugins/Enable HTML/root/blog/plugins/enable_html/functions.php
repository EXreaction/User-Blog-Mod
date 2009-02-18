<?php
/**
*
* @package phpBB3 User Blog Enable HTML
* @version $Id$
* @copyright (c) 2009 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

function blog_enable_html_add_preview(&$arg)
{
	global $reply_id, $blog_id;

	if (!function_exists('enable_html_permission'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/mods/enable_html.' . $phpEx);
	}

	if (enable_html_permission_self())
	{
		$arg = enable_html($arg, '');
	}
}

function blog_enable_html_edit_preview(&$arg)
{
	global $reply_id, $blog_id;

	if (!function_exists('enable_html_permission'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/mods/enable_html.' . $phpEx);
	}

	if ($reply_id && isset(blog_data::$reply[$reply_id]))
	{
		$user_id = blog_data::$reply[$reply_id]['user_id'];
	}
	else if ($blog_id && isset(blog_data::$blog[$blog_id]))
	{
		$user_id = blog_data::$blog[$blog_id]['user_id'];
	}
	else
	{
		global $user;
		$user_id = $user->data['user_id'];
	}

	blog_data::get_user_data($user_id);
	$html_auth = enable_html_permission($user_id, blog_data::$user[$user_id]);

	if ($html_auth)
	{
		$arg = enable_html($arg, '');
	}
}

function blog_enable_html(&$arg)
{
	if (!function_exists('enable_html_permission'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/mods/enable_html.' . $phpEx);
	}

	$data = blog_data::$blog[$arg['ID']];
	$user_id = $data['user_id'];

	$html_auth = enable_html_permission($user_id, blog_data::$user[$user_id]);

	if ($html_auth)
	{
		$arg['MESSAGE'] = enable_html($arg['MESSAGE'], '');
	}
}

function reply_enable_html(&$arg)
{
	if (!function_exists('enable_html_permission'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/mods/enable_html.' . $phpEx);
	}

	$data = blog_data::$reply[$arg['ID']];
	$user_id = $data['user_id'];

	$html_auth = enable_html_permission($user_id, blog_data::$user[$user_id]);

	if ($html_auth)
	{
		$arg['MESSAGE'] = enable_html($arg['MESSAGE'], '');
	}
}
?>