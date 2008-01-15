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
'HARD_DELETE' => 'Hard Delete',
'HARD_DELETE_EXPLAIN' => 'If you select this you will never get the post back!',
	'ADD_BLOG'							=> 'Add a new blog',
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
	'BLOG_DELETED'						=> 'Blog has been deleted.',
	'BLOG_EDIT_LOCKED'					=> 'This blog is locked for editing.',
	'BLOG_EDIT_SUCCESS'					=> 'The blog was edited successfully!',
	'BLOG_NEED_APPROVE'					=> 'A moderator or administrator must approve your blogs before they are public.',
	'BLOG_NOT_DELETED'					=> 'This blog is not deleted.  Why are you trying to un-delete it?',
	'BLOG_REPORT_CONFIRM'				=> 'Are you sure you want to report this blog?',
	'BLOG_REPORT_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just reported <a href="%2$s">this blog</a>.<br/>Please take the time to read over the blog and decide what needs to be done.',
	'BLOG_REPORT_PM_SUBJECT'			=> 'Blog Reported!',
	'BLOG_SUBMIT_SUCCESS'				=> 'The blog was submitted successfully!',
	'BLOG_SUBSCRIPTION_NOTICE'			=> 'This is an automatically dispatched message from the User Blog mod notifying you that a reply has been made to [url=%1$s]this[/url] blog by %2$s.<br/><br/>If you would like to no longer recieve these notices click [url=%3$s]here[/url] to unsubscribe.',
	'BLOG_SUBSCRIPTION_NOTICE_EMAIL'	=> 'This is an automatically dispatched message from the User Blog mod notifying you that a reply has been made to this blog by %2$s: /r/n %1$s /r/n /r/n /r/n If you would like to no longer recieve these notices click the following link to unsubscribe:/r/n%3$s',
	'BLOG_UNDELETED'					=> 'The blog has been un-deleted.',
	'BLOG_USER_NOT_PROVIDED'			=> 'You must provide the user_id or blog_id of the item you would like to subscribe to.',

	'CATEGORY_EXPLAIN'					=> 'You may select more than one category by holding CTRL and clicking more categories you would like to enter it in.<br/><br/>Blogs are <strong>always</strong> shown in your personal Blog page.',

	'DELETE_BLOG'						=> 'Delete Blog',
	'DELETE_BLOG_CONFIRM'				=> 'Are you sure you want to delete this blog?',
	'DELETE_BLOG_WARN'					=> 'Once deleted, only a moderator or administrator can un-delete this blog',
	'DELETE_REPLY'						=> 'Delete Reply',
	'DELETE_REPLY_CONFIRM'				=> 'Are you sure you want to delete this reply?',
	'DELETE_REPLY_WARN'					=> 'Once deleted, only a moderator or administrator can un-delete this reply',

	'EDIT_A_BLOG'						=> 'Edit a blog',
	'EDIT_A_REPLY'						=> 'Edit a reply',
	'EDIT_BLOG'							=> 'Edit Blog',
	'EDIT_REPLY'						=> 'Edit Reply',

	'FILES_CANT_WRITE'					=> 'The files/blog_mod/ folder is not writable, please CHMOD the directory to 777',

	'NOT_SUBSCRIBED_BLOG'				=> 'You are not subscribed to this blog.',
	'NOT_SUBSCRIBED_USER'				=> 'You are not subscribed to this user.',
	'NO_PERMISSIONS_SINGLE'				=> 'Can not read or reply to this blog.',

	'PERMANENTLY_DELETE_BLOG_CONFIRM'	=> 'Are you sure you want to permanently delete this blog?  This can not be un-done.',
	'PERMANENTLY_DELETE_REPLY_CONFIRM'	=> 'Are you sure you want to permanently delete this reply?  This can not be un-done.',
	'PERMISSIONS'						=> 'Permissions',
	'POST_A_NEW_BLOG'					=> 'Post a new blog',
	'POST_A_NEW_REPLY'					=> 'Post a new reply',

	'REPLY_ALREADY_APPROVED'			=> 'This reply is already approved.',
	'REPLY_ALREADY_DELETED'				=> 'This reply has already been deleted.',
	'REPLY_APPROVE_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just posted <a href="%2$s">this reply</a> and it needs approval before it is publically viewable.<br/>Please take the time to read over the reply and decide what needs to be done.',
	'REPLY_APPROVE_PM_SUBJECT'			=> 'Blog Reply Approval Needed!',
	'REPLY_DELETED'						=> 'Reply has been deleted.',
	'REPLY_EDIT_LOCKED'					=> 'This reply is locked for editing.',
	'REPLY_EDIT_SUCCESS'				=> 'The reply was edited successfully!',
	'REPLY_IS_DELETED'					=> 'This reply was deleted by %1$s on %2$s.  Click <b>%3$shere%4$s</b> to un-delete this reply.',
	'REPLY_NEED_APPROVE'				=> 'A moderator or administrator must approve your replies before they are public.',
	'REPLY_NOT_DELETED'					=> 'This reply is not deleted.  Why are you trying to un-delete it?',
	'REPLY_PERMISSIONS_SINGLE'			=> 'Can read and reply to this blog.',
	'REPLY_REPORT_CONFIRM'				=> 'Are you sure you want to report this reply?',
	'REPLY_REPORT_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just reported <a href="%2$s">this reply</a>.<br/>Please take the time to read over the reply and decide what needs to be done.',
	'REPLY_REPORT_PM_SUBJECT'			=> 'Blog Reply Reported!',
	'REPLY_SUBMIT_SUCCESS'				=> 'The reply was submitted successfully!',
	'REPLY_UNDELETED'					=> 'The reply has been un-deleted.',
	'REPORT_BLOG'						=> 'Report Blog',
	'REPORT_REPLY'						=> 'Report Reply',

	'SUBSCRIPTION_NOTICE'				=> 'Subscription notice from the User Blog Mod',

	'UNDELETE_BLOG'						=> 'Un-Delete Blog',
	'UNDELETE_BLOG_CONFIRM'				=> 'Are you sure you want to un-delete this blog?',
	'UNDELETE_REPLY'					=> 'Un-delete Reply',
	'UNDELETE_REPLY_CONFIRM'			=> 'Are you sure you want to un-delete this reply?',
	'USER_SUBSCRIPTION_NOTICE'			=> 'This is an automatically dispatched message from the User Blog mod notifying you that a new blog has been posted by %1$s.  You can view the blog [url=%2$s]here[/url].<br/><br/>If you would like to no longer recieve these notices click [url=%3$s]here[/url] to unsubscribe.',
	'USER_SUBSCRIPTION_NOTICE_EMAIL'	=> 'This is an automatically dispatched message from the User Blog mod notifying you that a new blog has been posted by %1$s.  You can view the blog here:/r/n %2$s /r/n /r/n /r/n If you would like to no longer recieve these notices click the following link to unsubscribe:/r/n%3$s',

	'VIEW_PERMISSIONS_SINGLE'			=> 'Can read this blog.',
));

?>