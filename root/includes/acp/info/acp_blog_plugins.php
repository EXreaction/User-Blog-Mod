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
class acp_blog_plugins_info
{
	function module()
	{
		global $user;

		return array(
			'filename'	=> 'acp_blog_plugins',
			'title'		=> 'ACP_BLOG_PLUGINS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'default'	=> array('title' => 'ACP_BLOG_PLUGINS', 'auth' => 'acl_a_blogmanage', 'cat' => array('ACP_BLOGS')),
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