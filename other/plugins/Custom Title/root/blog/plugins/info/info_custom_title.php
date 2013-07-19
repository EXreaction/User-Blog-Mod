<?php
/**
*
* @package phpBB3 User Blog Custom Title
* @version $Id: info_custom_title.php 245 2008-02-09 04:02:27Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$user->add_lang('mods/blog/plugins/' . $name);

self::$available_plugins[$name]['plugin_title'] = $user->lang['BLOG_CUSTOM_TITLE_TITLE'];
self::$available_plugins[$name]['plugin_description'] = $user->lang['BLOG_CUSTOM_TITLE_DESCRIPTION'];

self::$available_plugins[$name]['plugin_copyright'] = 'EXreaction';
self::$available_plugins[$name]['plugin_version'] = '0.7.4';

$to_do = array(
	'user_handle_data'			=> array('custom_title_user_handle_data'),
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