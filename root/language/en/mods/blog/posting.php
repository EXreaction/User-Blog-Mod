<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: posting.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
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
	'ADD_BLOG'					=> 'Add a new blog entry',
	'APPROVE_BLOG'				=> 'Approve Blog Entry',
	'APPROVE_BLOG_CONFIRM'		=> 'Are you sure you want to approve this blog entry?',
	'APPROVE_BLOG_SUCCESS'		=> 'You have successfully approved this blog entry for public viewing.',
	'APPROVE_REPLY'				=> 'Approve Comment',
	'APPROVE_REPLY_CONFIRM'		=> 'Are you sure you want to approve this comment?',
	'APPROVE_REPLY_SUCCESS'		=> 'You have successfully approved this comment for public viewing.',

	'BLOG_ALREADY_APPROVED'		=> 'This blog entry is already approved.',
	'BLOG_ALREADY_DELETED'		=> 'This blog entry has already been deleted.',
	'BLOG_APPROVE_PM'			=> 'This is an automatically dispatched message from the User Blog Mod.<br /><br />%1$s has just posted <a href="%2$s">this blog entry</a> and it needs approval before it is publically viewable.<br />Please take the time to read over the blog entry and decide what needs to be done.',
	'BLOG_APPROVE_PM_SUBJECT'	=> 'Blog Entry Approval Needed!',
	'BLOG_DELETED'				=> 'Blog entry has been deleted.',
	'BLOG_EDIT_LOCKED'			=> 'This blog entry is locked for editing.',
	'BLOG_EDIT_SUCCESS'			=> 'The blog entry was edited successfully!',
	'BLOG_NEED_APPROVE'			=> 'A moderator or administrator must approve your blog entries before they are public.',
	'BLOG_NOT_DELETED'			=> 'This blog entry is not deleted.  Why are you trying to un-delete it?',
	'BLOG_REPORT_CONFIRM'		=> 'Are you sure you want to report this blog entry?',
	'BLOG_REPORT_PM'			=> 'This is an automatically dispatched message from the User Blog Mod.<br /><br />%1$s has just reported <a href="%2$s">this blog entry</a>.<br />Please take the time to read over the blog entry and decide what needs to be done.',
	'BLOG_REPORT_PM_SUBJECT'	=> 'Blog Entry Reported!',
	'BLOG_SUBMIT_SUCCESS'		=> 'The blog entry was submitted successfully!',
	'BLOG_SUBSCRIPTION_NOTICE'	=> 'This is an automatically dispatched message from the User Blog Mod notifying you that a comment has been made to [url=%1$s]this[/url] blog entry by %2$s.<br /><br />If you would like to no longer recieve these notices click [url=%3$s]here[/url] to unsubscribe.',
	'BLOG_UNDELETED'			=> 'The blog entry has been un-deleted.',

	'CATEGORY_EXPLAIN'			=> 'You may select more than one category by holding CTRL and clicking more categories you would like to enter it in.<br /><br />Blogs entries are <strong>always</strong> shown in your personal Blog.',

	'DELETE_BLOG_CONFIRM'		=> 'Are you sure you want to delete this blog entry?',
	'DELETE_REPLY_CONFIRM'		=> 'Are you sure you want to delete this comment?',

	'EDIT_A_BLOG'				=> 'Edit a blog entry',
	'EDIT_A_REPLY'				=> 'Edit a comment',

	'HARD_DELETE'				=> 'Hard Delete',
	'HARD_DELETE_EXPLAIN'		=> 'If you select this you will never get the post back!',

	'NO_PERMISSIONS_SINGLE'		=> 'Can not read or reply to this blog entry.',

	'PERMISSIONS'				=> 'Permissions',

	'REPLY_ALREADY_APPROVED'	=> 'This comment is already approved.',
	'REPLY_APPROVE_PM'			=> 'This is an automatically dispatched message from the User Blog Mod.<br /><br />%1$s has just posted <a href="%2$s">this comment</a> and it needs approval before it is publically viewable.<br />Please take the time to read over the comment and decide what needs to be done.',
	'REPLY_APPROVE_PM_SUBJECT'	=> 'Blog Comment Approval Needed!',
	'REPLY_DELETED'				=> 'Comment has been deleted.',
	'REPLY_EDIT_LOCKED'			=> 'This comment is locked for editing.',
	'REPLY_EDIT_SUCCESS'		=> 'The comment was edited successfully!',
	'REPLY_NEED_APPROVE'		=> 'A moderator or administrator must approve your comments before they are public.',
	'REPLY_NOT_DELETED'			=> 'This comment is not deleted.  Why are you trying to un-delete it?',
	'REPLY_PERMISSIONS_SINGLE'	=> 'Can read and reply to this blog entry.',
	'REPLY_REPORT_CONFIRM'		=> 'Are you sure you want to report this comment?',
	'REPLY_REPORT_PM'			=> 'This is an automatically dispatched message from the User Blog Mod.<br /><br />%1$s has just reported <a href="%2$s">this comment</a>.<br />Please take the time to read over the comment and decide what needs to be done.',
	'REPLY_REPORT_PM_SUBJECT'	=> 'Blog Comment Reported!',
	'REPLY_SUBMIT_SUCCESS'		=> 'The comment was submitted successfully!',
	'REPLY_UNDELETED'			=> 'The comment has been un-deleted.',

	'SUBSCRIPTION_NOTICE'		=> 'Subscription notice from the User Blog Mod',

	'UNDELETE_BLOG'				=> 'Un-Delete Blog Entry',
	'UNDELETE_BLOG_CONFIRM'		=> 'Are you sure you want to un-delete this blog entry?',
	'UNDELETE_REPLY'			=> 'Un-delete Comment',
	'UNDELETE_REPLY_CONFIRM'	=> 'Are you sure you want to un-delete this comment?',
	'USER_SUBSCRIPTION_NOTICE'	=> 'This is an automatically dispatched message from the User Blog mod notifying you that a new blog entry has been posted by %1$s.  You can view the blog [url=%2$s]here[/url].<br /><br />If you would like to no longer recieve these notices click [url=%3$s]here[/url] to unsubscribe.',

	'VIEW_PERMISSIONS_SINGLE'	=> 'Can read this blog entry.',
));

?>