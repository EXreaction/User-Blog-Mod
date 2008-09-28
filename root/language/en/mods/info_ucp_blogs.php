<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: info_ucp_blogs.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
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
	'UCP_BLOG'						=> 'Blog',
	'UCP_BLOG_PERMISSIONS'			=> 'Blog Permissions',
	'UCP_BLOG_SETTINGS'				=> 'Blog Settings',
	'UCP_BLOG_TITLE_DESCRIPTION'	=> 'Blog Title and Description',
));

?>