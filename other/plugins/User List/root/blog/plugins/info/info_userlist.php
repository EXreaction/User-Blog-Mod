<?php
if (!defined('IN_PHPBB'))
{
	exit;
}

$user->add_lang('mods/blog/plugins/userlist');

self::$available_plugins[$name]['plugin_title'] = $user->lang['BLOG_USERLIST_TITLE'];
self::$available_plugins[$name]['plugin_description'] = $user->lang['BLOG_USERLIST_DESCRIPTION'];

self::$available_plugins[$name]['plugin_copyright'] = 'EXreaction';
self::$available_plugins[$name]['plugin_version'] = '1.0.0';

self::add_to_do(array(
	'blog_page_switch'			=> array('userlist_blog_page_switch'),
	'function_generate_menu'	=> array('userlist_function_generate_menu'),
));

include($blog_plugins_path . 'userlist/functions.' . $phpEx);

?>