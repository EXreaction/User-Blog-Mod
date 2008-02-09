<?php
/**
*
* @package phpBB3 User Blog Friends
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$user->add_lang('mods/blog/plugins/' . $name);

self::$available_plugins[$name]['plugin_title'] = $user->lang['BLOG_FRIENDS_TITLE'];
self::$available_plugins[$name]['plugin_description'] = $user->lang['BLOG_FRIENDS_DESCRIPTION'];

self::$available_plugins[$name]['plugin_copyright'] = 'EXreaction';
self::$available_plugins[$name]['plugin_version'] = '0.7.2';

$to_do = array(
	'function_generate_menu'	=> array('friends_function_generate_menu'),
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