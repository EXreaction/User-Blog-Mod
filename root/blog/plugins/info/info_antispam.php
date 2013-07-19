<?php
/**
*
* @package phpBB3 User Blog Anti-Spam
* @version $Id$
* @copyright (c) 2008 EXreaction
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

$user->add_lang('mods/blog/plugins/' . $name);

self::$available_plugins[$name]['plugin_title'] = $user->lang['BLOG_ANTISPAM'];
self::$available_plugins[$name]['plugin_description'] = $user->lang['BLOG_ANTISPAM_EXPLAIN'];

self::$available_plugins[$name]['plugin_copyright'] = 'EXreaction';
self::$available_plugins[$name]['plugin_version'] = '1.0.0';

$to_do = array(
	'blog_add_after_setup'		=> array('antispam_blog_add_after_setup'),
	'blog_add_sql'				=> array('antispam_blog_add_sql'),
	'blog_add_after_sql'		=> array('antispam_blog_add_after_sql'),

	'blog_edit_after_setup'		=> array('antispam_blog_add_after_setup'),
	'blog_edit_sql'				=> array('antispam_blog_add_sql'),
	'blog_edit_after_sql'		=> array('antispam_blog_edit_after_sql'),

	'reply_add_after_setup'		=> array('antispam_reply_add_after_setup'),
	'reply_add_sql'				=> array('antispam_reply_add_sql'),
	'reply_add_after_sql'		=> array('antispam_reply_add_after_sql'),

	'reply_edit_after_setup'	=> array('antispam_reply_add_after_setup'),
	'reply_edit_sql'			=> array('antispam_reply_add_sql'),
	'reply_edit_after_sql'		=> array('antispam_reply_edit_after_sql'),
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