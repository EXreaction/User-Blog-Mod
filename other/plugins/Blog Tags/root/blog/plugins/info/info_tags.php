<?php
/**
*
* @package phpBB3 User Blog Tags
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

$user->add_lang('mods/blog/plugins/tags');

self::$available_plugins[$name]['plugin_title'] = $user->lang['BLOG_TAGS_TITLE'];
self::$available_plugins[$name]['plugin_description'] = $user->lang['BLOG_TAGS_DESCRIPTION'];

self::$available_plugins[$name]['plugin_copyright'] = 'EXreaction';
self::$available_plugins[$name]['plugin_version'] = '0.7.1';

$to_do = array(
	'function_handle_basic_posting_data'		=> array('tags_function_handle_basic_posting_data'),
	'blog_add_sql'								=> array('tags_blog_add_sql'),
	'blog_edit_sql'								=> array('tags_blog_edit_sql'),
	'blog_handle_data_end'						=> array('tags_blog_handle_data_end'),
	'blog_page_switch'							=> array('tags_blog_page_switch'),
	'function_generate_menu'					=> array('tags_function_generate_menu'),
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

define('BLOGS_TAGS_TABLE', $table_prefix . 'blogs_tags');

include($blog_plugins_path . 'tags/functions.' . $phpEx);
?>