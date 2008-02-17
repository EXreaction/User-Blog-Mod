<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
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
	'AVERAGE_OF_RATING'				=> 'Average of %s rating',
	'AVERAGE_OF_RATINGS'			=> 'Average of %s ratings',

	'CLICK_HERE_SHOW_POST'			=> 'Click here to show the post.',
	'CNT_COMMENTS'					=> '%s Comments',
	'COMMENTS'						=> 'Comments',

	'DELETED_MESSAGE'				=> 'These blogs have all been deleted.',
	'DELETED_MESSAGE_EXPLAIN'		=> 'There is a link in every "This blog was deleted by..." section to un-delete the blog.',
	'DELETED_REPLY_SHOW'			=> 'This reply has been soft deleted.  Click here to show the reply.',

	'MY_RATING'						=> 'My Rating',

	'NO_DELETED_BLOGS'				=> 'There are no deleted blogs by this user.',
	'NO_DELETED_BLOGS_SORT_DAYS'	=> 'No deleted blogs were posted by this user in the last %s.',

	'ONE_COMMENT'					=> '1 Comment',

	'POSTED_BY_FOE'					=> 'This post was made by %s who is currently on your ignore list.',

	'RANDOM_BLOG'					=> 'Random Blog',
	'RATE_ME'						=> '%1$s out of %2$s',
	'RECENT_REPLIES'				=> 'Recent Replies',
	'REMOVE_RATING'					=> 'Reset Rating',
	'REPLY_SHOW_NO_JS'				=> 'You must enable Javascript to view this post.',
	'REPORTED'						=> 'This message has been reported.  Click here to close the report.',

	'SUBCATEGORIES'					=> 'Subcategories',
	'SUBCATEGORY'					=> 'Subcategory',

	'TOTAL_NUMBER_OF_BLOGS'			=> 'Total Blogs',
	'TOTAL_NUMBER_OF_REPLIES'		=> 'Total Blog Replies',

	'UNAPPROVED'					=> 'This message needs approval.  Click here to approve this message.',
));

?>