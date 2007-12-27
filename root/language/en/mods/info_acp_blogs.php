<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
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
	'ACP_BLOGS'				=> 'User Blog Mod',
	'ACP_BLOG_CATEGORIES'	=> 'Blog Categories',
	'ACP_BLOG_PLUGINS'		=> 'Blog Plugins',
	'ACP_BLOG_SEARCH'		=> 'Blog Search',
	'ACP_BLOG_SETTINGS'		=> 'Blog Settings',
));

?>