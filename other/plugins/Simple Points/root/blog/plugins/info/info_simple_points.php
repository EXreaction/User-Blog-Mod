<?php
/**
*
* @package phpBB3 User Blog Simple Points
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

$user->add_lang('mods/blog/plugins/simple_points');

self::$available_plugins[$name]['plugin_title'] = $user->lang['BLOG_SIMPLE_POINTS_TITLE'];
self::$available_plugins[$name]['plugin_description'] = $user->lang['BLOG_SIMPLE_POINTS_DESCRIPTION'];

self::$available_plugins[$name]['plugin_copyright'] = 'EXreaction';
self::$available_plugins[$name]['plugin_version'] = '1.0.0';

$to_do = array(
	'acp_main_settings'			=> array('sp_acp_main_settings'),
	'blog_add_after_sql'		=> array('sp_blog_add_after_sql'),
	'blog_approve_confirm'		=> array('sp_blog_approve_confirm'),
	'reply_add_after_sql'		=> array('sp_reply_add_after_sql'),
	'reply_approve_confirm'		=> array('sp_reply_approve_confirm'),
	'user_handle_data'			=> array('sp_user_handle_data'),
);

foreach($to_do as $do => $what)
{
	if (!array_key_exists($do, self::$to_do))
	{
		self::$to_do[$do] = $what;
	}
	else
	{
		self::$to_do[$do] = array_merge(self::$to_do[$do], $what);
	}
}

include($blog_plugins_path . $name . '/functions.' . $phpEx);

?>