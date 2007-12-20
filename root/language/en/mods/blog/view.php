<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
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
	'BLOG_REPORTED'					=> 'Blog has been reported, click to close the report',
	'BLOG_REPORTED_SHORT'			=> 'This Blog has been reported.',
	'BLOG_UNAPPROVED'				=> 'Blog Needs Approval',

	'CLICK_HERE_SHOW_POST'			=> 'Click here to show the post.',

	'DELETED_MESSAGE'				=> 'These blogs have all been deleted.',
	'DELETED_MESSAGE_EXPLAIN'		=> 'There is a link in every "This blog was deleted by..." section to un-delete the blog.',
	'DELETED_REPLY_SHOW'			=> 'This reply has been soft deleted.  Click here to show the reply.',

	'NO_DELETED_BLOGS'				=> 'There are no deleted blogs by this user.',
	'NO_DELETED_BLOGS_SORT_DAYS'	=> 'No deleted blogs were posted by this user in the last %s.',

	'POST_BY_FOE'					=> 'This post was made by %s who is currently on your ignore list.',

	'REPLY_REPORTED'				=> 'Reply has been reported, click to close the report',
	'REPLY_REPORTED_SHORT'			=> 'This Reply has been reported.',
	'REPLY_SHOW_NO_JS'				=> 'You must enable Javascript to view this post.',
	'REPLY_UNAPPROVED'				=> 'Reply Needs Approval',
));

?>