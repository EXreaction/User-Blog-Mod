<?php
/** 
* @package language(permissions)
* @copyright (c) 2007 EXreaction, Lithium Studios
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
	'acl_u_blogview'			=> array('lang' => 'Can view blogs', 'cat' => 'blog'),
	'acl_u_blogpost'			=> array('lang' => 'Can post blogs', 'cat' => 'blog'),
	'acl_u_blognocaptcha'		=> array('lang' => 'Does not need to enter CAPTCHA code to post new blogs or replies', 'cat' => 'blog'),
	'acl_u_blogedit'			=> array('lang' => 'Can edit own blogs', 'cat' => 'blog'),
	'acl_u_blogdelete'			=> array('lang' => 'Can delete own blogs', 'cat' => 'blog'),
	'acl_u_blognoapprove'		=> array('lang' => 'Blogs do not need approval before public viewing', 'cat' => 'blog'),
	'acl_u_blogreport'			=> array('lang' => 'Can report blogs and replies', 'cat' => 'blog'),
	'acl_u_blogreply'			=> array('lang' => 'Can reply to blogs', 'cat' => 'blog'),
	'acl_u_blogreplyedit'		=> array('lang' => 'Can edit own replies', 'cat' => 'blog'),
	'acl_u_blogreplydelete'		=> array('lang' => 'Can delete own replies', 'cat' => 'blog'),
	'acl_u_blogreplynoapprove'	=> array('lang' => 'Replies do not need approval before public viewing', 'cat' => 'blog'),
	'acl_u_blogbbcode'			=> array('lang' => 'Can use BBCode in blogs and replies', 'cat' => 'blog'),
	'acl_u_blogsmilies'			=> array('lang' => 'Can use smilies in blogs and replies', 'cat' => 'blog'),
	'acl_u_blogimg'				=> array('lang' => 'Can post images in blogs and replies', 'cat' => 'blog'),
	'acl_u_blogurl'				=> array('lang' => 'Can post URL\'s in blogs and replies', 'cat' => 'blog'),
	'acl_u_blogflash'			=> array('lang' => 'Can post flash in blogs and replies', 'cat' => 'blog'),
));

// Moderator Permissions
$lang = array_merge($lang, array(
	'acl_m_blogapprove'			=> array('lang' => 'Can view unapproved blogs and approve blogs for public viewing', 'cat' => 'blog'),
	'acl_m_blogedit'			=> array('lang' => 'Can edit blogs', 'cat' => 'blog'),
	'acl_m_bloglockedit'		=> array('lang' => 'Can lock editing of blogs', 'cat' => 'blog'),
	'acl_m_blogdelete'			=> array('lang' => 'Can delete and un-delete blogs', 'cat' => 'blog'),
	'acl_m_blogreport'			=> array('lang' => 'Can close and delete reports.', 'cat' => 'blog'),
	'acl_m_blogreplyapprove'	=> array('lang' => 'Can view unapproved replies and approve replies for public viewing', 'cat' => 'blog'),
	'acl_m_blogreplyedit'		=> array('lang' => 'Can edit replies', 'cat' => 'blog'),
	'acl_m_blogreplylockedit'	=> array('lang' => 'Can lock editing of replies', 'cat' => 'blog'),
	'acl_m_blogreplydelete'		=> array('lang' => 'Can delete and un-delete replies', 'cat' => 'blog'),
	'acl_m_blogreplyreport'		=> array('lang' => 'Can close and delete reports.', 'cat' => 'blog'),
));

// Administrator Permissions
$lang = array_merge($lang, array(
	'acl_a_blogmanage'			=> array('lang' => 'Can change blog settings', 'cat' => 'blog'),
	'acl_a_blogdelete'			=> array('lang' => 'Can permanently delete blogs', 'cat' => 'blog'),
	'acl_a_blogreplydelete'		=> array('lang' => 'Can permanently delete replies to blogs', 'cat' => 'blog'),
));
?>