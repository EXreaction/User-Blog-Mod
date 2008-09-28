<?php
/** 
*
* @package phpBB3 User Blog
* @version $Id: acp_blogs.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class acp_blogs_info
{
	function module()
	{
		global $user;

		return array(
			'filename'	=> 'acp_blogs',
			'title'		=> 'ACP_BLOGS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'settings'		=> array('title' => 'ACP_BLOG_SETTINGS', 'auth' => 'acl_a_blogmanage', 'cat' => array('ACP_BLOGS')),
				'categories'	=> array('title' => 'ACP_BLOG_CATEGORIES', 'auth' => 'acl_a_blogmanage', 'cat' => array('ACP_BLOGS')),
				'plugins'		=> array('title' => 'ACP_BLOG_PLUGINS', 'auth' => 'acl_a_blogmanage', 'cat' => array('ACP_BLOGS')),
				'search'		=> array('title' => 'ACP_BLOG_SEARCH', 'auth' => 'acl_a_blogmanage', 'cat' => array('ACP_BLOGS')),
				'ext_groups'	=> array('title' => 'ACP_EXTENSION_GROUPS', 'auth' => 'acl_a_blogmanage', 'cat' => array('ACP_BLOGS')),
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