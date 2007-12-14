<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	'BLOG'						=> 'Blog',
	'BLOGS_COUNT'				=> '%s blogs',
	'BLOG_COUNT'				=> '1 blog',
	'BLOG_CREDITS'				=> 'Blog System powered by <a href="http://www.lithiumstudios.org/">User Blog Mod</a> &copy; 2007 EXreaction',
	'BLOG_NOT_EXIST'			=> 'The requested blog does not exist.',
	'BLOG_NOT_EXISTS'			=> 'No Blogs',

	'MY_BLOG'					=> 'My Blog',
	'MY_BLOGS'					=> 'My Blogs',

	'REPLIES_COUNT'				=> '%s replies',
	'REPLY'						=> 'Reply',
	'REPLY_COUNT'				=> '1 reply',

	'USERNAMES_BLOGS'			=> '%s\'s Blogs',
	'USERNAMES_DELETED_BLOGS'	=> '%s\'s Deleted Blogs',
	'USER_BLOGS'				=> 'User Blogs',
	'USER_BLOG_MOD_DISABLED'	=> 'The User Blog Mod has been disabled.',
));

?>