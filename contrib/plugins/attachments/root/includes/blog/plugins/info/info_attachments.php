<?php
/**
*
* @package phpBB3 User Blog Attachments
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* Define some constants
*/
if (!isset($table_prefix))
{
	include($phpbb_root_path . 'config.' . $phpEx);
	unset($dbpasswd);
	unset($dbuser);
	unset($dbname);
}

define('BLOGS_ATTACHMENT_TABLE',	$table_prefix . 'blogs_attachment');

// setup some basic information about the plugin
$this->available_plugins[$name]['plugin_copyright'] = '2007 EXreaction';
$this->available_plugins[$name]['plugin_version'] = '0.7.1';

// Only do this if the plugin is enabled (set in the load_plugins function right before this file is loaded)
if ($plugin_enabled)
{
	/**
	* Setup the To Do list and add it to the plugin's to-do list
	*/
	$attach_to_do = array(
		'blog_add_start'			=> array('attach_blog_add_start'),
		'blog_add_preview'			=> array('attach_blog_add_preview'),
		'blog_add_after_preview'	=> array('attach_blog_add_after_preview'),
		'blog_add_sql'				=> array('attach_blog_add_sql'),
		'blog_add_after_sql'		=> array('attach_blog_add_after_sql'),
		'blog_delete_confirm'		=> array('attach_blog_delete_confirm'),
		'blog_edit_start'			=> array('attach_blog_edit_start'),
		'blog_edit_preview'			=> array('attach_blog_add_preview'),
		'blog_edit_after_preview'	=> array('attach_blog_add_after_preview'),
		'blog_edit_sql'				=> array('attach_blog_add_sql'),
		'blog_edit_after_sql'		=> array('attach_blog_add_after_sql'),

		'blog_data_while'			=> array('attach_blog_data_while'),
		'blog_handle_data_end'		=> array('attach_blog_handle_data_end'),
		'reply_data_while'			=> array('attach_blog_data_while'),
		'reply_handle_data_end'		=> array('attach_reply_handle_data_end'),

		'reply_add_start'			=> array('attach_blog_add_start'),
		'reply_add_preview'			=> array('attach_blog_add_preview'),
		'reply_add_after_preview'	=> array('attach_blog_add_after_preview'),
		'reply_add_sql'				=> array('attach_reply_add_sql'),
		'reply_add_after_sql'		=> array('attach_reply_add_after_sql'),
		'reply_edit_start'			=> array('attach_reply_edit_start'),
		'reply_edit_preview'		=> array('attach_blog_add_preview'),
		'reply_edit_after_preview'	=> array('attach_blog_add_after_preview'),
		'reply_edit_sql'			=> array('attach_reply_add_sql'),
		'reply_edit_after_sql'		=> array('attach_reply_add_after_sql'),

		'view_blog_start'			=> array('attach_view_blog_start'),
		'view_user_start'			=> array('attach_view_user_start'),

		'acp_main_settings'			=> array('attach_acp_main_settings'),
	);

	foreach($attach_to_do as $do => $what)
	{
		if (!array_key_exists($do, $this->to_do))
		{
			$this->to_do[$do] = $what;
		}
		else
		{
			$this->to_do[$do] = array_merge($this->to_do[$do], $what);
		}
	}

	/**
	* Add the needed language files
	*/
	$user->add_lang('mods/blog/plugins/attachments');

	/**
	* Include the necessary files
	*/
	include($blog_plugins_path . 'attachments/functions.' . $phpEx);
	include($blog_plugins_path . 'attachments/attachment_class.' . $phpEx);

	/**
	* Setup the $blog_attachment class
	*/
	global $blog_attachment;
	$blog_attachment = new blog_attachment();
}
?>