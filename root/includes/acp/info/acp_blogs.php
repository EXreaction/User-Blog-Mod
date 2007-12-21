<?php
/** 
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
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