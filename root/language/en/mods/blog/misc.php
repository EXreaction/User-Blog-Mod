<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: misc.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
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
	'ALREADY_SUBSCRIBED'		=> 'You are already subscribed.',

	'BLOG_USER_NOT_PROVIDED'	=> 'You must provide the user_id or blog_id of the item you would like to subscribe to.',

	'NOT_ALLOWED_CHANGE_VOTE'	=> 'You are not allowed to change your vote.',
	'NOT_SUBSCRIBED'			=> 'You are not subscribed.',

	'RESYNC_BLOG'				=> 'Synchronise Blog',
	'RESYNC_BLOG_CONFIRM'		=> 'Are you sure you want to resynchronise all of the blog data?  This may take a while.',
	'RESYNC_BLOG_SUCCESS'		=> 'The User Blog Mod has been successfully resynchronised.',

	'SEARCH_BLOG_ONLY'			=> 'Search Blogs Only',
	'SEARCH_BLOG_TITLE_ONLY'	=> 'Search Titles Only',
	'SEARCH_TITLE_MSG'			=> 'Search Titles and Message',
	'SUBSCRIBE_BLOG_CONFIRM'	=> 'How would you like to recieve notices when a new reply is added to this blog?',
	'SUBSCRIBE_BLOG_TITLE'		=> 'Blog subscription',
	'SUBSCRIPTION_ADDED'		=> 'Subscription has been added successfully.',
	'SUBSCRIPTION_REMOVED'		=> 'Your subscription has been removed successfully',

	'UNSUBSCRIBE_BLOG_CONFIRM'	=> 'Are you sure you would like to remove your subscription from this blog?',
	'UNSUBSCRIBE_USER_CONFIRM'	=> 'Are you sure you would like to remove your subscription from this user?',
));

?>