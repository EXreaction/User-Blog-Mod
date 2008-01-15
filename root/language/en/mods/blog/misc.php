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
	'ALREADY_SUBSCRIBED'		=> 'You are already subscribed.',

	'NOT_SUBSCRIBED'			=> 'You are not subscribed.',

	'PM_AND_EMAIL'				=> 'Private message and E-mail',

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