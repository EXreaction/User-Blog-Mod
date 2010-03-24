<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: common.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
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
	'AVAILABLE_FEEDS'				=> 'Available Feeds',

	'BLOG'							=> 'Blog',
	'BLOGS'							=> 'Blogs',
	'BLOG_CONTROL_PANEL'			=> 'Blog Control Panel',
	'BLOG_CREDITS'					=> 'Blogs powered by <a href="http://www.lithiumstudios.org/">User Blog Mod</a> &copy; EXreaction',
	'BLOG_DELETED_BY_MSG'			=> 'This blog entry was deleted by %1$s on %2$s.  Click <b>%3$shere%4$s</b> to un-delete this blog.',
	'BLOG_DESCRIPTION'				=> 'Blog Description',
	'BLOG_LINKS'					=> 'Blog Links',
	'BLOG_MCP'						=> 'Blog Moderator CP',
	'BLOG_NOT_EXIST'				=> 'The requested blog entry does not exist.',
	'BLOG_SEARCH_BACKEND_NOT_EXIST'	=> 'The Search backend was not found.  Please contact an administrator or moderator.',
	'BLOG_STATS'					=> 'Blog Stats',
	'BLOG_SUBJECT'					=> 'Blog Subject',
	'BLOG_TITLE'					=> 'Blog Title',

	'CATEGORIES'					=> 'Categories',
	'CATEGORY'						=> 'Category',
	'CATEGORY_DESCRIPTION'			=> 'Category Description',
	'CATEGORY_NAME'					=> 'Category Name',
	'CATEGORY_RULES'				=> 'Category Rules',
	'CLICK_INSTALL_BLOG'			=> 'Click %shere%s to install the User Blog Mod',
	'CNT_BLOGS'						=> '%s blog entries',
	'CNT_REPLIES'					=> '%s replies',
	'CNT_VIEWS'						=> 'Viewed %s times',
	'CONTINUE'						=> 'Continue',
	'CONTINUED'						=> 'Continued',

	'DELETE_BLOG'					=> 'Delete Blog Entry',
	'DELETE_REPLY'					=> 'Delete Comment',

	'EDIT_BLOG'						=> 'Edit Blog Entry',
	'EDIT_REPLY'					=> 'Edit Reply',

	'FEED'							=> 'Feed',
	'FOE_PERMISSIONS'				=> 'Foe Permissions',
	'FRIEND_PERMISSIONS'			=> 'Friend Permissions',

	'GUEST_PERMISSIONS'				=> 'Guest Permissions',

	'LIMIT'							=> 'Limit',

	'MUST_BE_FOUNDER'				=> 'You must be a board founder to access this page.',
	'MY_BLOG'						=> 'My Blog',

	'NEW_BLOG'						=> 'New Blog Entry',
	'NO_BLOGS'						=> 'There are no blog entries.',
	'NO_BLOGS_USER'					=> 'This user has not posted any blog entries.',
	'NO_BLOGS_USER_SORT_DAYS'		=> 'This user has not posted any blog entries in the past %s',
	'NO_CATEGORIES'					=> 'There are no categories',
	'NO_CATEGORY'					=> 'The selected category does not exist.',
	'NO_PERMISSIONS_READ'			=> 'Sorry, but you are not allowed to read this blog.',
	'NO_REPLIES'					=> 'There are no comments.',

	'ONE_BLOG'						=> '1 blog',
	'ONE_REPLY'						=> '1 comment',
	'ONE_VIEW'						=> 'Viewed 1 time',

	'PERMANENT_LINK'				=> 'Permanent Link',
	'PLUGIN_TEMPLATE_MISSING'		=> 'The plugin template file is missing.',
	'POPULAR_BLOGS'					=> 'Popular Blog Entries',
	'POST_A_NEW_BLOG'				=> 'Post a Blog Entry',
	'POST_A_NEW_REPLY'				=> 'Post a Comment',

	'RANDOM_BLOGS'					=> 'Random Blog Entries',
	'RECENT_BLOGS'					=> 'Recent Blog Entries',
	'REGISTERED_PERMISSIONS'		=> 'Members Permissions',
	'BLOG_REPLIES'					=> 'Comments',
	'REPLY'							=> 'Comment',
	'REPLY_COUNT'					=> 'Comment Count',
	'REPLY_DELETED_BY_MSG'			=> 'This comment was deleted by %1$s on %2$s.  Click <b>%3$shere%4$s</b> to un-delete this comment.',
	'REPLY_NOT_EXIST'				=> 'The requested reply does not exist.',
	'REPORT_BLOG'					=> 'Report Blog Entry',
	'REPORT_REPLY'					=> 'Report Comment',
	'RETURN_BLOG_MAIN'				=> '%1$sReturn to %2$s\'s Blog%3$s',
	'RETURN_BLOG_OWN'				=> '%sReturn to your blog%s',
	'RETURN_MAIN'					=> 'Click %shere%s to return to the main User Blog page',

	'SEARCH_BLOGS'					=> 'Search Blogs',
	'SUBSCRIBE'						=> 'Subscribe',
	'SUBSCRIBE_BLOG'				=> 'Subscribe to this Blog',
	'SUBSCRIBE_USER'				=> 'Subscribe to this user\'s Blog',
	'SUBSCRIPTION'					=> 'Subscription',
	'SUBSCRIPTION_EXPLAIN'			=> 'Select how you would like to be notified of future replies made to this blog.',
	'SUBSCRIPTION_EXPLAIN_REPLY'	=> 'If you\'ve already subscribed to this blog, your current subscription options are shown (and whatever you select will be your new subscription selection).',

	'TOTAL_BLOG_ENTRIES'			=> 'Total Blog Entries <strong>%s</strong>',

	'UNSUBSCRIBE'					=> 'Unsubscribe',
	'UNSUBSCRIBE_BLOG'				=> 'Unsubscribe from this Blog',
	'UNSUBSCRIBE_USER'				=> 'Unsubscribe from this User',
	'USERNAMES_BLOGS'				=> '%s\'s Blog',
	'USERNAMES_DELETED_BLOGS'		=> '%s\'s Deleted Entries',
	'USER_BLOGS'					=> 'User Blogs',
	'USER_BLOG_MOD_DISABLED'		=> 'The User Blog Mod has been disabled.',
	'USER_BLOG_RATINGS_DISABLED'	=> 'The ratings system has been disabled.',

	'VIEW_BLOG'						=> 'View Blog',
	'VIEW_REPLY'					=> 'View Reply',

	'WARNING'						=> 'Warning',
));

?>