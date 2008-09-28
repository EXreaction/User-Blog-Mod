<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: permissions.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB') || !defined('IN_BLOG_INSTALL'))
{
	exit;
}

/*
* Add the new permissions
*/
$blog_permissions = array(
	'local'		=> array(),
	'global'	=> array(
		'u_blogview',
		'u_blogpost',
		'u_blogedit',
		'u_blogdelete',
		'u_blognoapprove',
		'u_blogreport',
		'u_blogreply',
		'u_blogreplyedit',
		'u_blogreplydelete',
		'u_blogreplynoapprove',
		'u_blogbbcode',
		'u_blogsmilies',
		'u_blogimg',
		'u_blogurl',
		'u_blogflash',
		'u_blogmoderate',
		'u_blogattach',
		'u_blognolimitattach',
		'm_blogapprove',
		'm_blogedit',
		'm_bloglockedit',
		'm_blogdelete',
		'm_blogreport',
		'm_blogreplyapprove',
		'm_blogreplyedit',
		'm_blogreplylockedit',
		'm_blogreplydelete',
		'm_blogreplyreport',
		'a_blogmanage',
		'a_blogdelete',
		'a_blogreplydelete',

		'u_blog_vote',
		'u_blog_vote_change',
		'u_blog_create_poll',
		'u_blog_style',
		'u_blog_css',
	)
);
$auth_admin->acl_add_option($blog_permissions);
?>