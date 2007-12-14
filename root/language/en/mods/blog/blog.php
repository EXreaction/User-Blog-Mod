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
	'ADD_BLOG'							=> 'Add a new blog',
	'ALLOWED_IN_BLOG'					=> 'Allowed in User Blogs',
	'ALLOW_IN_BLOG'						=> 'Allow in User Blogs',
	'ALREADY_INSTALLED'					=> 'You have already installed the user blog mod.<br/><br/>Click %shere%s to return to the main blog page.',
	'ALREADY_SUBSCRIBED'				=> 'You are already subscribed',
	'ALREADY_UPDATED'					=> 'You are running the latest version of the User Blog Mod.<br/><br/>Click %shere%s to return to the main blog page.',
	'APPROVE_BLOG'						=> 'Approve Blog',
	'APPROVE_BLOG_CONFIRM'				=> 'Are you sure you want to approve this blog?',
	'APPROVE_BLOG_SUCCESS'				=> 'You have successfully approved this blog for public viewing.',
	'APPROVE_REPLY'						=> 'Approve Reply',
	'APPROVE_REPLY_CONFIRM'				=> 'Are you sure you want to approve this reply?',
	'APPROVE_REPLY_SUCCESS'				=> 'You have successfully approved this reply for public viewing.',

	'BLOG_ALREADY_APPROVED'				=> 'This blog is already approved.',
	'BLOG_ALREADY_DELETED'				=> 'This blog has already been deleted.',
	'BLOG_APPROVE_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just posted <a href="%2$s">this blog</a> and it needs approval before it is publically viewable.<br/>Please take the time to read over the reply and decide what needs to be done.',
	'BLOG_APPROVE_PM_SUBJECT'			=> 'Blog Approval Needed!',
	'BLOG_AUTHOR'						=> 'About the Author',
	'BLOG_DELETED'						=> 'Blog has been deleted.',
	'BLOG_DESCRIPTION'					=> 'Blog Description',
	'BLOG_EDIT_LOCKED'					=> 'This blog is locked for editing.',
	'BLOG_INFO'							=> 'About the Blog',
	'BLOG_IS_DELETED'					=> 'This blog was deleted by %1$s on %2$s.  Click <b>%3$shere%4$s</b> to un-delete this blog.',
	'BLOG_MCP'							=> 'Blog Moderator CP',
	'BLOG_NEED_APPROVE'					=> 'A moderator or administrator must approve your blogs before they are public.',
	'BLOG_NOT_DELETED'					=> 'This blog is not deleted.  Why are you trying to un-delete it?',
	'BLOG_NOT_EXISTS'					=> 'There are no blogs.',
	'BLOG_NOT_EXISTS_USER'				=> 'No blogs have been posted by this user.',
	'BLOG_NOT_EXISTS_USER_SORT_DAYS'	=> 'No blogs were posted by this user in the last %s.',
	'BLOG_POST_IP'						=> 'IP Address used to post',
	'BLOG_POST_VIEW_SETTINGS'			=> 'Blog posting/viewing settings',
	'BLOG_REPLIES'						=> 'There are <b>%1$s</b> %2$sreplies%3$s to this blog',
	'BLOG_REPLY'						=> 'There is <b>1</b> %sreply%s to this blog',
	'BLOG_REPORTED'						=> 'Blog has been reported, click to close the report',
	'BLOG_REPORTED_SHORT'				=> 'Blog has been reported',
	'BLOG_REPORT_CONFIRM'				=> 'Are you sure you want to report this blog?',
	'BLOG_REPORT_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just reported <a href="%2$s">this blog</a>.<br/>Please take the time to read over the blog and decide what needs to be done.',
	'BLOG_REPORT_PM_SUBJECT'			=> 'Blog Reported!',
	'BLOG_SEARCH_BACKEND_NOT_EXIST'		=> 'The Search backend was not found.  Please contact an administrator or moderator.',
	'BLOG_SUBJECT'						=> 'Blog Subject',
	'BLOG_SUBSCRIPTION_NOTICE'			=> 'This is an automatically dispatched message from the User Blog mod notifying you that a reply has been made to [url=%1$s]this[/url] blog by %2$s.<br/><br/>If you would like to no longer recieve these notices click [url=%3$s]here[/url] to unsubscribe.',
	'BLOG_SUBSCRIPTION_NOTICE_EMAIL'	=> 'This is an automatically dispatched message from the User Blog mod notifying you that a reply has been made to this blog by %2$s: /r/n %1$s /r/n /r/n /r/n If you would like to no longer recieve these notices click the following link to unsubscribe:/r/n%3$s',
	'BLOG_UNAPPROVED'					=> 'Blog Needs Approval',
	'BLOG_UNDELETED'					=> 'The blog has been un-deleted.',
	'BLOG_USER_NOT_PROVIDED'			=> 'You must provide the user_id or blog_id of the item you would like to subscribe to.',
	'BLOG_VIEWS_COUNT'					=> 'This blog has been viewed <b>%s</b> times',
	'BLOG_VIEW_COUNT'					=> 'This blog has been viewed <b>1</b> time',
	'BREAK_CONTINUE_NOTICE'				=> 'Section %1$s of %2$s, Part %3$s of %4$s has been completed, but there are more sections and/or parts that need to be finished before before we are done.<br/>Click continue below if you are not automatically redirected to the next page.',

	'CLICK_HERE_SHOW_POST'				=> 'Click here to show the post.',
	'CLICK_INSTALL_BLOG'				=> 'Click %shere%s to install the User Blog Mod',
	'CLICK_UPDATE'						=> 'Click %shere%s to update the database for the User Blog Mod',
	'CONTINUE'							=> 'Continue',
	'CONTINUED'							=> 'Continued',
	'COPYRIGHT'							=> 'Copyright',

	'DELETED_BLOGS'						=> 'Deleted Blogs',
	'DELETED_MESSAGE'					=> 'These blogs have all been deleted.',
	'DELETED_MESSAGE_EXPLAIN'			=> 'There is a link in every "This blog was deleted by..." section to un-delete the blog.',
	'DELETED_MESSAGE_EXPLAIN_SINGLE'	=> 'There is a link in the "This blog was deleted by..." section to un-delete the blog.',
	'DELETED_MESSAGE_SINGLE'			=> 'This blog has been deleted.',
	'DELETED_REPLY_SHOW'				=> 'This reply has been soft deleted.  Click here to show the reply.',
	'DELETE_BLOG'						=> 'Delete Blog',
	'DELETE_BLOG_CONFIRM'				=> 'Are you sure you want to delete this blog?',
	'DELETE_BLOG_WARN'					=> 'Once deleted, only a moderator or administrator can un-delete this blog',
	'DELETE_REPLY'						=> 'Delete Reply',
	'DELETE_REPLY_CONFIRM'				=> 'Are you sure you want to delete this reply?',
	'DELETE_REPLY_WARN'					=> 'Once deleted, only a moderator or administrator can un-delete this reply',
	'DISAPPROVED_BLOGS'					=> 'These blogs need approval before they can be viewed by the public.',
	'DISAPPROVED_REPLIES'				=> 'These replies need approval before they can be viewed by the public.',

	'EDIT_BLOG'							=> 'Edit Blog',
	'EDIT_REPLY'						=> 'Edit Reply',

	'FEED'								=> 'Blog Feed',
	'FILES_CANT_WRITE'					=> 'The files/blog_mod/ folder is not writable, please CHMOD the directory to 777',
	'FOE_PERMISSIONS'					=> 'Foe Permissions',
	'FRIEND_PERMISSIONS'				=> 'Friend Permissions',

	'GUEST_PERMISSIONS'					=> 'Guest Permissions',

	'INSTALL'							=> 'Install',
	'INSTALL_BLOG_DB'					=> 'Install User Blog Mod',
	'INSTALL_BLOG_DB_CONFIRM'			=> 'Are you ready to install the database section of this mod?',
	'INSTALL_BLOG_DB_FAIL'				=> 'Installation of the User Blog Mod failed.<br/>Please report the following errors to EXreaction:<br/>',
	'INSTALL_BLOG_DB_SUCCESS'			=> 'You have successfully installed the database section of the User Blog mod.<br/><br/>Click %shere%s to return to the main User Blogs page.',

	'LIMIT'								=> 'Limit',
	'LOGIN_EXPLAIN_EDIT_BLOG'			=> 'You must log in before editing a blog.',
	'LOGIN_EXPLAIN_NEW_BLOG'			=> 'You must log in before creating a new blog.',
	'LOG_CONFIG_BLOG'					=> '<strong>Altered Blog Settings</strong>',

	'MUST_BE_FOUNDER'					=> 'You must be a board founder to access this page.',

	'NOT_ALLOWED_IN_BLOG'				=> 'Not allowed in User Blogs',
	'NOT_SUBSCRIBED_BLOG'				=> 'You are not subscribed to this blog.',
	'NOT_SUBSCRIBED_USER'				=> 'You are not subscribed to this user.',
	'NO_DELETED_BLOGS'					=> 'There are no deleted blogs by this user.',
	'NO_DELETED_BLOGS_SORT_DAYS'		=> 'No deleted blogs were posted by this user in the last %s.',
	'NO_INSTALLED_PLUGINS'				=> 'No Installed Plugins',
	'NO_PERMISSIONS_READ'				=> 'Sorry, but you are not allowed to read this blog.',
	'NO_PERMISSIONS_REPLY'				=> 'Sorry, but you are not allowed to reply to this blog.',
	'NO_PERMISSIONS_SINGLE'				=> 'Can not read or reply to this blog.',
	'NO_REPLIES'						=> 'There are no replies',
	'NO_REPLY'							=> 'The requested reply does not exist.',

	'PERMANENTLY_DELETE_BLOG_CONFIRM'	=> 'Are you sure you want to permanently delete this blog?  This can not be un-done.',
	'PERMANENTLY_DELETE_REPLY_CONFIRM'	=> 'Are you sure you want to permanently delete this reply?  This can not be un-done.',
	'PERMANENT_LINK'					=> 'Permanent Link',
	'PERMISSIONS'						=> 'Permissions',
	'PM_AND_EMAIL'						=> 'Private message and E-mail',
	'POPULAR_BLOGS'						=> 'Popular Blogs',
	'POST_A'							=> 'Post a new blog',
	'POST_A_REPLY'						=> 'Post a new reply',
	'POST_FOE'							=> 'This post was made by %s who is currently on your ignore list.',

	'RANDOM_BLOGS'						=> 'Random Blogs',
	'RECENT_BLOGS'						=> 'Recent Blogs',
	'REGISTERED_PERMISSIONS'			=> 'Members Permissions',
	'REPLY_ALREADY_APPROVED'			=> 'This reply is already approved.',
	'REPLY_ALREADY_DELETED'				=> 'This reply has already been deleted.',
	'REPLY_APPROVE_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just posted <a href="%2$s">this reply</a> and it needs approval before it is publically viewable.<br/>Please take the time to read over the reply and decide what needs to be done.',
	'REPLY_APPROVE_PM_SUBJECT'			=> 'Blog Reply Approval Needed!',
	'REPLY_DELETED'						=> 'Reply has been deleted.',
	'REPLY_EDIT_LOCKED'					=> 'This reply is locked for editing.',
	'REPLY_IS_DELETED'					=> 'This reply was deleted by %1$s on %2$s.  Click <b>%3$shere%4$s</b> to un-delete this reply.',
	'REPLY_NEED_APPROVE'				=> 'A moderator or administrator must approve your replies before they are public.',
	'REPLY_NOT_DELETED'					=> 'This reply is not deleted.  Why are you trying to un-delete it?',
	'REPLY_PERMISSIONS_SINGLE'			=> 'Can read and reply to this blog.',
	'REPLY_REPORTED'					=> 'Reply has been reported, click to close the report',
	'REPLY_REPORT_CONFIRM'				=> 'Are you sure you want to report this reply?',
	'REPLY_REPORT_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just reported <a href="%2$s">this reply</a>.<br/>Please take the time to read over the reply and decide what needs to be done.',
	'REPLY_REPORT_PM_SUBJECT'			=> 'Blog Reply Reported!',
	'REPLY_SHOW_NO_JS'					=> 'You must enable Javascript to view this post.',
	'REPLY_SUBMITTED'					=> 'Your reply has been submitted!',
	'REPLY_UNAPPROVED'					=> 'Reply Needs Approval',
	'REPLY_UNDELETED'					=> 'The reply has been un-deleted.',
	'REPORTED_BLOGS'					=> 'These blogs have been reported by users.',
	'REPORTED_REPLIES'					=> 'These replies have been reported by users.',
	'REPORT_BLOG'						=> 'Report Blog',
	'REPORT_REPLY'						=> 'Report Reply',
	'RESYNC_BLOG'						=> 'Synchronise Blog',
	'RESYNC_BLOG_CONFIRM'				=> 'Are you sure you want to synchronise all of the blog data?  This may take a while.',
	'RESYNC_BLOG_SUCCESS'				=> 'Blog data has been successfully synchronised.',
	'RESYNC_BLOG_SUCESS'				=> 'The blog has been successfully resyncronised.',
	'RETURN_BLOG_MAIN'					=> '%sReturn to %s\'s main blog page%s',
	'RETURN_BLOG_MAIN_OWN'				=> '%sReturn to your main blog page%s',
	'RETURN_MAIN'						=> 'Click %shere%s to return to the main User Blog page',

	'SCHEMA_NOT_EXIST'					=> 'The database install schema file is missing.  Please download a fresh copy of this mod and reupload all required files.  If that does not fix the problem, contact EXreaction.',
	'SEARCH_BLOGS'						=> 'Search Blogs',
	'SEARCH_BLOG_ONLY'					=> 'Search Blogs Only',
	'SEARCH_BLOG_TITLE_ONLY'			=> 'Search Titles Only',
	'SEARCH_TITLE_MSG'					=> 'Search Titles and Message',
	'SUBSCRIBE'							=> 'Subscribe',
	'SUBSCRIBE_BLOG'					=> 'Subscribe to this Blog',
	'SUBSCRIBE_BLOG_CONFIRM'			=> 'How would you like to recieve notices when a new reply is added to this blog?',
	'SUBSCRIBE_BLOG_TITLE'				=> 'Blog subscription',
	'SUBSCRIBE_RECIEVE'					=> 'I would like to recieve updates via',
	'SUBSCRIBE_USER'					=> 'Subscribe to this user\'s Blogs',
	'SUBSCRIBE_USER_CONFIRM'			=> 'How would you like to recieve notices when a new blog is added by this user?',
	'SUBSCRIBE_USER_TITLE'				=> 'User subscription',
	'SUBSCRIPTION_ADDED'				=> 'Subscription has been added successfully.',
	'SUBSCRIPTION_NOTICE'				=> 'Subscription notice from the User Blog Mod',
	'SUBSCRIPTION_REMOVED'				=> 'Your subscription has been removed successfully',
	'SUCCESSFULLY_UPDATED'				=> 'User blog mod has been updated to %1$s.<br/><br/>Click %2$shere%3$s to return to the main blog page.',

	'UNDELETE_BLOG'						=> 'Un-Delete Blog',
	'UNDELETE_BLOG_CONFIRM'				=> 'Are you sure you want to un-delete this blog?',
	'UNDELETE_REPLY'					=> 'Un-delete Reply',
	'UNDELETE_REPLY_CONFIRM'			=> 'Are you sure you want to un-delete this reply?',
	'UNSUBSCRIBE'						=> 'Unsubscribe',
	'UNSUBSCRIBE_BLOG'					=> 'Unsubscribe from this Blog',
	'UNSUBSCRIBE_BLOG_CONFIRM'			=> 'Are you sure you would like to remove your subscription from this blog?',
	'UNSUBSCRIBE_USER'					=> 'Unsubscribe from this User',
	'UNSUBSCRIBE_USER_CONFIRM'			=> 'Are you sure you would like to remove your subscription from this user?',
	'UNTITLED_REPLY'					=> 'Untitled Reply',
	'UPDATE_BLOG'						=> 'Update Blog',
	'UPDATE_INSTRUCTIONS'				=> 'Update',
	'UPDATE_INSTRUCTIONS_CONFIRM'		=> 'Make sure you read the upgrade instructions in the MOD History section of the main mod install document first <b>before</b> you do this.<br/><br/>Are you ready to upgrade the database for the User Blog Mod?',
	'UPDATE_IN_FILES_FIRST'				=> 'You must update the files (or at least includes/constants.php) before you run the database updater.',
	'UPGRADE_BLOG'						=> 'Upgrade Blog',
	'USER_PERMISSIONS_DISABLED'			=> 'The User Permissions System has been disabled by the Administrators.',
	'USER_SUBSCRIPTION_NOTICE'			=> 'This is an automatically dispatched message from the User Blog mod notifying you that a new blog has been posted by %1$s.  You can view the blog [url=%2$s]here[/url].<br/><br/>If you would like to no longer recieve these notices click [url=%3$s]here[/url] to unsubscribe.',
	'USER_SUBSCRIPTION_NOTICE_EMAIL'	=> 'This is an automatically dispatched message from the User Blog mod notifying you that a new blog has been posted by %1$s.  You can view the blog here:/r/n %2$s /r/n /r/n /r/n If you would like to no longer recieve these notices click the following link to unsubscribe:/r/n%3$s',

	'VIEW_BLOG'							=> 'View Blog',
	'VIEW_BLOGS'						=> 'View Blogs',
	'VIEW_DELETED_BLOGS'				=> 'View Deleted Blogs',
	'VIEW_PERMISSIONS_SINGLE'			=> 'Can read this blog.',
	'VIEW_REPLY'						=> 'View Reply',

	'WELCOME_MESSAGE'					=> 'Here are the current Author\'s Notes:
[code]## Author Notes:
##	This is Alpha quality software.  Do not install unless you are willing to lose any
##		data with future upgrades or glitches.  DO NOT complain to me if you lose any data,
##		I will take no resposibility for any damage with the use of this mod in a live environment.
##
##	Please report any bugs/problems at my website: http://www.lithiumstudios.org
##
##	The SVN repository for this project is: http://userblogmod.googlecode.com/svn/trunk/
##		You may check for updated code in the repository, but the latest files in the repository may be broken and have major errors.
[/code]
This message will be changed before the final version.',
	'WELCOME_SUBJECT'					=> 'Welcome to the User Blog Mod!',
));

?>