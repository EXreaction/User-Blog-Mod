<?php
/**
* @package language(permissions)
* @version $Id: permissions_blog.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Create a new category named Blog
$lang['permission_cat']['blog'] = 'Blog';

// User Permissions
$lang = array_merge($lang, array(
	'acl_u_blogview'			=> array('lang' => 'Can view blog entries', 'cat' => 'blog'),
	'acl_u_blogpost'			=> array('lang' => 'Can post blog entries', 'cat' => 'blog'),
	'acl_u_blogedit'			=> array('lang' => 'Can edit own blog entries', 'cat' => 'blog'),
	'acl_u_blogdelete'			=> array('lang' => 'Can delete own blog entries', 'cat' => 'blog'),
	'acl_u_blognoapprove'		=> array('lang' => 'Blog entries do not need approval before public viewing', 'cat' => 'blog'),
	'acl_u_blogreport'			=> array('lang' => 'Can report blog entries and replies', 'cat' => 'blog'),
	'acl_u_blogreply'			=> array('lang' => 'Can comment on blog entries', 'cat' => 'blog'),
	'acl_u_blogreplyedit'		=> array('lang' => 'Can edit own comments', 'cat' => 'blog'),
	'acl_u_blogreplydelete'		=> array('lang' => 'Can delete own comments', 'cat' => 'blog'),
	'acl_u_blogreplynoapprove'	=> array('lang' => 'Comments do not need approval before public viewing', 'cat' => 'blog'),
	'acl_u_blogbbcode'			=> array('lang' => 'Can use BBCode in blog entries and comments', 'cat' => 'blog'),
	'acl_u_blogsmilies'			=> array('lang' => 'Can use smilies in blog entries and comments', 'cat' => 'blog'),
	'acl_u_blogimg'				=> array('lang' => 'Can post images in blog entries and comments', 'cat' => 'blog'),
	'acl_u_blogurl'				=> array('lang' => 'Can post URLs in blogs entrie and comments', 'cat' => 'blog'),
	'acl_u_blogflash'			=> array('lang' => 'Can post flash in blog entries and comments', 'cat' => 'blog'),
	'acl_u_blogmoderate'		=> array('lang' => 'Can moderate (edit and delete) comments in own blog.', 'cat' => 'blog'),
	'acl_u_blogattach'			=> array('lang' => 'Can post attachments in blog entries and comments', 'cat' => 'blog'),
	'acl_u_blognolimitattach'	=> array('lang' => 'Can ignore attachment size and amount limits', 'cat' => 'blog'),

	'acl_u_blog_create_poll'	=> array('lang' => 'Can create polls', 'cat' => 'blog'),
	'acl_u_blog_vote'			=> array('lang' => 'Can vote in polls', 'cat' => 'blog'),
	'acl_u_blog_vote_change'	=> array('lang' => 'Can change existing vote', 'cat' => 'blog'),
	'acl_u_blog_style'			=> array('lang' => 'Can select a style to use for their own blog', 'cat' => 'blog'),
	'acl_u_blog_css'			=> array('lang' => 'Can enter in their own CSS code to customize their blog style the way they want.', 'cat' => 'blog'),
));

// Moderator Permissions
$lang = array_merge($lang, array(
	'acl_m_blogapprove'			=> array('lang' => 'Can view unapproved blog entries and approve blog entries for public viewing', 'cat' => 'blog'),
	'acl_m_blogedit'			=> array('lang' => 'Can edit blog entries', 'cat' => 'blog'),
	'acl_m_bloglockedit'		=> array('lang' => 'Can lock editing of blog entries', 'cat' => 'blog'),
	'acl_m_blogdelete'			=> array('lang' => 'Can delete and un-delete blog entries', 'cat' => 'blog'),
	'acl_m_blogreport'			=> array('lang' => 'Can close and delete blog entry reports.', 'cat' => 'blog'),
	'acl_m_blogreplyapprove'	=> array('lang' => 'Can view unapproved comments and approve comments for public viewing', 'cat' => 'blog'),
	'acl_m_blogreplyedit'		=> array('lang' => 'Can edit comments', 'cat' => 'blog'),
	'acl_m_blogreplylockedit'	=> array('lang' => 'Can lock editing of comments', 'cat' => 'blog'),
	'acl_m_blogreplydelete'		=> array('lang' => 'Can delete and un-delete comments', 'cat' => 'blog'),
	'acl_m_blogreplyreport'		=> array('lang' => 'Can close and delete comment reports.', 'cat' => 'blog'),
));

// Administrator Permissions
$lang = array_merge($lang, array(
	'acl_a_blogmanage'			=> array('lang' => 'Can change Blog settings', 'cat' => 'blog'),
	'acl_a_blogdelete'			=> array('lang' => 'Can permanently delete blog entries', 'cat' => 'blog'),
	'acl_a_blogreplydelete'		=> array('lang' => 'Can permanently delete comments on blog entries', 'cat' => 'blog'),
));
?>