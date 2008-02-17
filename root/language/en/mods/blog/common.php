<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
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
	'AVAILABLE_FEEDS'				=> 'Available Feeds',

	'BLOG'							=> 'Blog',
	'BLOGS'							=> 'Blogs',
	'BLOG_CONTROL_PANEL'			=> 'Blog Control Panel',
	'BLOG_CREDITS'					=> 'Blog System powered by <a href="http://www.lithiumstudios.org/">User Blog Mod</a> &copy; EXreaction',
	'BLOG_DELETED_BY_MSG'			=> 'This blog was deleted by %1$s on %2$s.  Click <b>%3$shere%4$s</b> to un-delete this blog.',
	'BLOG_DESCRIPTION'				=> 'Blog Description',
	'BLOG_LINKS'					=> 'Blog Links',
	'BLOG_MCP'						=> 'Blog Moderator CP',
	'BLOG_NOT_EXIST'				=> 'The requested blog does not exist.',
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
	'CNT_BLOGS'						=> '%s blogs',
	'CNT_REPLIES'					=> '%s replies',
	'CNT_VIEWS'						=> 'Viewed %s times',
	'CONTINUE'						=> 'Continue',
	'CONTINUED'						=> 'Continued',

	'DELETE_BLOG'					=> 'Delete Blog',
	'DELETE_REPLY'					=> 'Delete Reply',

	'EDIT_BLOG'						=> 'Edit Blog',
	'EDIT_REPLY'					=> 'Edit Reply',

	'FEED'							=> 'Feed',
	'FOE_PERMISSIONS'				=> 'Foe Permissions',
	'FRIEND_PERMISSIONS'			=> 'Friend Permissions',

	'GUEST_PERMISSIONS'				=> 'Guest Permissions',

	'LAST_BLOG'						=> 'Last Blog',
	'LIMIT'							=> 'Limit',

	'MUST_BE_FOUNDER'				=> 'You must be a board founder to access this page.',
	'MY_BLOG'						=> 'My Blog',
	'MY_BLOGS'						=> 'My Blogs',

	'NEW_BLOG'						=> 'New Blog',
	'NO_BLOGS'						=> 'There are no blogs.',
	'NO_BLOGS_USER'					=> 'This user has not posted any blogs.',
	'NO_BLOGS_USER_SORT_DAYS'		=> 'This user has not posted any blogs in the past %s',
	'NO_CATEGORIES'					=> 'There are no Categories',
	'NO_CATEGORY'					=> 'The selected category does not exist.',
	'NO_PERMISSIONS_READ'			=> 'Sorry, but you are not allowed to read this blog.',
	'NO_REPLIES'					=> 'There are no replies.',

	'ONE_BLOG'						=> '1 blog',
	'ONE_REPLY'						=> '1 reply',
	'ONE_VIEW'						=> 'Viewed 1 time',

	'PERMANENT_LINK'				=> 'Permanent Link',
	'POPULAR_BLOGS'					=> 'Popular Blogs',
	'POST_A_NEW_BLOG'				=> 'Post a blog',
	'POST_A_NEW_REPLY'				=> 'Post a comment',

	'RANDOM_BLOGS'					=> 'Random Blogs',
	'RECENT_BLOGS'					=> 'Recent Blogs',
	'REGISTERED_PERMISSIONS'		=> 'Members Permissions',
	'REPLIES'						=> 'Replies',
	'REPLY'							=> 'Reply',
	'REPLY_COUNT'					=> 'Reply Count',
	'REPLY_DELETED_BY_MSG'			=> 'This reply was deleted by %1$s on %2$s.  Click <b>%3$shere%4$s</b> to un-delete this reply.',
	'REPLY_NOT_EXIST'				=> 'The requested reply does not exist.',
	'REPORT_BLOG'					=> 'Report Blog',
	'REPORT_REPLY'					=> 'Report Reply',
	'RETURN_BLOG_MAIN'				=> '%1$sReturn to %2$s\'s Blogs%3$s',
	'RETURN_BLOG_OWN'				=> '%sReturn to your blog%s',
	'RETURN_MAIN'					=> 'Click %shere%s to return to the main User Blog page',

	'SEARCH_BLOGS'					=> 'Search Blogs',
	'SUBSCRIBE'						=> 'Subscribe',
	'SUBSCRIBE_BLOG'				=> 'Subscribe to this Blog',
	'SUBSCRIBE_USER'				=> 'Subscribe to this user\'s Blogs',
	'SUBSCRIPTION'					=> 'Subscription',
	'SUBSCRIPTION_EXPLAIN'			=> 'Select how you would like to be notified of future replies made to this blog.',
	'SUBSCRIPTION_EXPLAIN_REPLY'	=> 'If you\'ve already subscribed to this blog, your current subscription options are shown (and whatever you select will be your new subscription selection).',

	'UNSUBSCRIBE'					=> 'Unsubscribe',
	'UNSUBSCRIBE_BLOG'				=> 'Unsubscribe from this Blog',
	'UNSUBSCRIBE_USER'				=> 'Unsubscribe from this User',
	'USERNAMES_BLOGS'				=> '%s\'s Blogs',
	'USERNAMES_DELETED_BLOGS'		=> '%s\'s Deleted Blogs',
	'USER_BLOGS'					=> 'User Blogs',
	'USER_BLOG_MOD_DISABLED'		=> 'The User Blog Mod has been disabled.',
	'USER_BLOG_RATINGS_DISABLED'	=> 'The ratings system has been disabled.',

	'VIEW_BLOG'						=> 'View Blog',
	'VIEW_BLOGS'					=> 'View Blogs',
	'VIEW_DELETED_BLOGS'			=> 'View Deleted Blogs',
	'VIEW_REPLY'					=> 'View Reply',

	'WARNING'						=> 'Warning',
));

?>