<?php
/**
*
* @package phpBB3 User Blog Attachments
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

define('BLOGS_ATTACHMENT_TABLE',	$table_prefix . 'blogs_attachment');

$user->add_lang('mods/blog/plugins/' . $name);

$this->available_plugins[$name]['plugin_title'] = $user->lang['BLOG_ATTACHMENT_TITLE'];
$this->available_plugins[$name]['plugin_description'] = $user->lang['BLOG_ATTACHMENT_DESCRIPTION'];

$this->available_plugins[$name]['plugin_copyright'] = 'EXreaction';
$this->available_plugins[$name]['plugin_version'] = '0.7.5';

if ($plugin_enabled)
{
	$to_do = array(
		'blog_page_switch'			=> array('attach_blog_page_switch'),

		'blog_add_after_setup'		=> array('attach_blog_add_after_setup'),
		'blog_add_preview'			=> array('attach_blog_add_preview'),
		'blog_add_after_preview'	=> array('attach_blog_add_after_preview'),
		'blog_add_sql'				=> array('attach_blog_add_sql'),
		'blog_add_after_sql'		=> array('attach_blog_add_after_sql'),
		'blog_delete_confirm'		=> array('attach_blog_delete_confirm'),
		'blog_edit_after_setup'		=> array('attach_blog_edit_after_setup'),
		'blog_edit_preview'			=> array('attach_blog_add_preview'),
		'blog_edit_after_preview'	=> array('attach_blog_add_after_preview'),
		'blog_edit_sql'				=> array('attach_blog_add_sql'),
		'blog_edit_after_sql'		=> array('attach_blog_add_after_sql'),

		'blog_data_while'			=> array('attach_blog_data_while'),
		'blog_handle_data_end'		=> array('attach_blog_handle_data_end'),
		'reply_data_while'			=> array('attach_blog_data_while'),
		'reply_handle_data_end'		=> array('attach_reply_handle_data_end'),

		'reply_add_after_setup'			=> array('attach_blog_add_after_setup'),
		'reply_add_preview'				=> array('attach_blog_add_preview'),
		'reply_add_after_preview'		=> array('attach_blog_add_after_preview'),
		'reply_add_sql'					=> array('attach_reply_add_sql'),
		'reply_add_after_sql'			=> array('attach_reply_add_after_sql'),
		'reply_edit_after_setup'		=> array('attach_reply_edit_after_setup'),
		'reply_edit_preview'			=> array('attach_blog_add_preview'),
		'reply_edit_after_preview'		=> array('attach_blog_add_after_preview'),
		'reply_edit_sql'				=> array('attach_reply_add_sql'),
		'reply_edit_after_sql'			=> array('attach_reply_add_after_sql'),

		'view_blog_start'			=> array('attach_view_blog_start'),
		'view_user_start'			=> array('attach_view_user_start'),

		'acp_main_settings'			=> array('attach_acp_main_settings'),

		'function_handle_basic_posting_data'	=> array('attach_function_handle_basic_posting_data'),
	);

	foreach($to_do as $do => $what)
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

	include($blog_plugins_path . 'attachments/functions.' . $phpEx);
	include($blog_plugins_path . 'attachments/attachment_class.' . $phpEx);

	global $blog_attachment;
	$blog_attachment = new blog_attachment();
}
?>