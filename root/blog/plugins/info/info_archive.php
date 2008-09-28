<?php
/**
*
* @package phpBB3 User Blog Archives
* @version $Id: info_archive.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

$user->add_lang('mods/blog/plugins/' . $name);

self::$available_plugins[$name]['plugin_title'] = $user->lang['ARCHIVES'];
self::$available_plugins[$name]['plugin_description'] = $user->lang['BLOG_ARCHIVES_DESCRIPTION'];

self::$available_plugins[$name]['plugin_copyright'] = 'EXreaction';
self::$available_plugins[$name]['plugin_version'] = '1.0.0';

$to_do = array(
	'function_generate_menu'		=> array('archive_function_generate_menu'),
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