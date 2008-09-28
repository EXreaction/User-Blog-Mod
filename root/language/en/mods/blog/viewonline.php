<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: common.php 375 2008-04-22 16:43:42Z exreaction@gmail.com $
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
	'ADDING_BLOG_ENTRY'		=> 'Posting a new Blog Entry',
	'ADDING_BLOG_REPLY'		=> 'Commenting on a Blog Entry',

	'VIEWING_BLOGS'			=> 'Viewing Blogs',
	'VIEWING_BLOG_ENTRY'	=> 'Viewing Blog Entry',
	'VIEWING_USERS_BLOG'	=> 'Viewing %s\'s Blog',
));

?>