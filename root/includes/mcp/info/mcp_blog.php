<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: mcp_blog.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class mcp_blog_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_blog',
			'title'		=> 'MCP_BLOG',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'reported_blogs'			=> array('title' => 'MCP_BLOG_REPORTED_BLOGS',		'auth' => 'acl_m_blogreport',		'cat' => array('MCP_BLOG')),
				'reported_replies'			=> array('title' => 'MCP_BLOG_REPORTED_REPLIES',	'auth' => 'acl_m_blogreplyreport',	'cat' => array('MCP_BLOG')),
				'disapproved_blogs'			=> array('title' => 'MCP_BLOG_DISAPPROVED_BLOGS',	'auth' => 'acl_m_blogapprove',		'cat' => array('MCP_BLOG')),
				'disapproved_replies'		=> array('title' => 'MCP_BLOG_DISAPPROVED_REPLIES',	'auth' => 'acl_m_blogreplyapprove',	'cat' => array('MCP_BLOG')),
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