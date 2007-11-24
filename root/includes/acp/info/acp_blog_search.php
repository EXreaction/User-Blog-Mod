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
class acp_blog_search_info
{
	function module()
	{
		global $user;

		return array(
			'filename'	=> 'acp_blog_search',
			'title'		=> 'ACP_BLOG_SEARCH',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'default'	=> array('title' => 'ACP_BLOG_SEARCH', 'auth' => 'acl_a_blogmanage', 'cat' => array('ACP_BLOGS')),
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