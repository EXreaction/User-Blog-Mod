<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: info_acp_blogs.php 493 2008-08-28 17:43:39Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	'ACP_BLOGS'						=> 'User Blog Mod',
	'ACP_BLOG_CATEGORIES'			=> 'Blog Categories',
	'ACP_BLOG_PLUGINS'				=> 'Blog Plugins',
	'ACP_BLOG_SEARCH'				=> 'Blog Search',
	'ACP_BLOG_SETTINGS'				=> 'Blog Settings',

	'IMG_BUTTON_BLOG_NEW'			=> 'New Blog Entry',

	'LOG_BLOG_CATEGORY_ADD'			=> '<strong>Added New Blog Category</strong><br />» %s',
	'LOG_BLOG_CATEGORY_DELETE'		=> '<strong>Deleted Blog Category</strong><br />» %s',
	'LOG_BLOG_CATEGORY_EDIT'		=> '<strong>Edited Blog Category</strong><br />» %s',
	'LOG_BLOG_CONFIG'				=> '<strong>Altered Blog Settings</strong>',
	'LOG_BLOG_CONFIG_SEARCH'		=> '<strong>Altered Blog Search Settings</strong>',
	'LOG_BLOG_PLUGIN_DISABLED'		=> '<strong>Disabled Blog Plugin</strong><br />» %s',
	'LOG_BLOG_PLUGIN_ENABLED'		=> '<strong>Enabled Blog Plugin</strong><br />» %s',
	'LOG_BLOG_PLUGIN_INSTALLED'		=> '<strong>Installed Blog Plugin</strong><br />» %s',
	'LOG_BLOG_PLUGIN_UNINSTALLED'	=> '<strong>Uninstalled Blog Plugin</strong><br />» %s',
	'LOG_BLOG_PLUGIN_UPDATED'		=> '<strong>Updated Blog Plugin</strong><br />» %s',
	'LOG_BLOG_SEARCH_INDEX_CREATED'	=> '<strong>Rebuilt Blog Search Index</strong>',
	'LOG_BLOG_SEARCH_INDEX_REMOVED'	=> '<strong>Deleted Blog Search Index</strong>',
));

?>