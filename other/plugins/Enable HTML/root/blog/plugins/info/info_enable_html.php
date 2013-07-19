<?php
/**
*
* @package phpBB3 User Blog Enable HTML
* @version $Id$
* @copyright (c) 2009 EXreaction
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

$user->add_lang('mods/blog/plugins/' . $name);

self::$available_plugins[$name]['plugin_title'] = $user->lang['BLOG_ENABLE_HTML_TITLE'];
self::$available_plugins[$name]['plugin_description'] = $user->lang['BLOG_ENABLE_HTML_DESCRIPTION'];

self::$available_plugins[$name]['plugin_copyright'] = 'EXreaction';
self::$available_plugins[$name]['plugin_version'] = '1.0.0';

$to_do = array(
	'blog_add_preview'		=> array('blog_enable_html_add_preview'),
	'blog_edit_preview'		=> array('blog_enable_html_edit_preview'),
	'blog_handle_data_end'	=> array('blog_enable_html'),

	'reply_add_preview'		=> array('blog_enable_html_add_preview'),
	'reply_edit_preview'	=> array('blog_enable_html_edit_preview'),
	'reply_handle_data_end'	=> array('reply_enable_html'),

	'user_handle_data'		=> array('user_enable_html'),
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