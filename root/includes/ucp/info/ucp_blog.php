<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: ucp_blog.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class ucp_blog_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_blog',
			'title'		=> 'UCP_BLOG',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'ucp_blog_settings'				=> array('title' => 'UCP_BLOG_SETTINGS', 'auth' => 'acl_u_blogpost', 'cat' => array('BLOG')),
				'ucp_blog_title_description'	=> array('title' => 'UCP_BLOG_TITLE_DESCRIPTION', 'auth' => 'acl_u_blogpost', 'cat' => array('BLOG')),
				'ucp_blog_permissions'			=> array('title' => 'UCP_BLOG_PERMISSIONS', 'auth' => 'acl_u_blogpost', 'cat' => array('BLOG')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>