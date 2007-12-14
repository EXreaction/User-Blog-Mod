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
	'BLOGS'						=> 'Blogs',
	'BLOGS_COUNT'				=> '%s blogs',
	'BLOG_COUNT'				=> '1 blog',
	'BLOG_CREDITS'				=> 'Blog System powered by <a href="http://www.lithiumstudios.org/">User Blog Mod</a> &copy; 2007 EXreaction',
	'BLOG_NOT_EXIST'			=> 'The requested blog does not exist.',

	'MY_BLOG'					=> 'My Blog',
	'MY_BLOGS'					=> 'My Blogs',

	'NO_BLOGS'					=> 'There are no blogs.',
	'NO_BLOGS_USER'				=> 'This user has not posted any blogs.',
	'NO_BLOGS_USER_SORT_DAYS'	=> 'This user has not posted any blogs in the past %s',
	'NO_REPLIES'				=> 'There are no replies.',

	'REPLIES'					=> 'Replies',
	'REPLIES_COUNT'				=> '%s replies',
	'REPLY'						=> 'Reply',
	'REPLY_COUNT'				=> '1 reply',
	'REPLY_NOT_EXIST'			=> 'The requested reply does not exist.',

	'USERNAMES_BLOGS'			=> '%s\'s Blogs',
	'USERNAMES_DELETED_BLOGS'	=> '%s\'s Deleted Blogs',
	'USER_BLOGS'				=> 'User Blogs',
	'USER_BLOG_MOD_DISABLED'	=> 'The User Blog Mod has been disabled.',
));

?>