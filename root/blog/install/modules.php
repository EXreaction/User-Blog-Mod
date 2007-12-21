<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB') || !defined('IN_BLOG_INSTALL'))
{
	exit;
}

/*
* insert the modules
*/
// ACP Modules
$sql_ary = array(
	'module_langname'	=> 'ACP_BLOGS',
);
$eami->add_module('acp', 'ACP_CAT_DOT_MODS', $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blogs',
	'module_langname'	=> 'ACP_BLOG_SETTINGS',
	'module_mode'		=> 'settings',
	'module_auth'		=> 'acl_a_blogmanage',
);
$eami->add_module('acp', 'ACP_BLOGS', $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blogs',
	'module_langname'	=> 'ACP_BLOG_PLUGINS',
	'module_mode'		=> 'plugins',
	'module_auth'		=> 'acl_a_blogmanage',
);
$eami->add_module('acp', 'ACP_BLOGS', $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blogs',
	'module_langname'	=> 'ACP_BLOG_SEARCH',
	'module_mode'		=> 'search',
	'module_auth'		=> 'acl_a_blogmanage',
);
$eami->add_module('acp', 'ACP_BLOGS', $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blogs',
	'module_langname'	=> 'ACP_BLOG_CATEGORIES',
	'module_mode'		=> 'categories',
	'module_auth'		=> 'acl_a_blogmanage',
);
$eami->add_module('acp', 'ACP_BLOGS', $sql_ary);

// MCP Modules
$sql_ary = array(
	'module_langname'	=> 'MCP_BLOG',
);
$eami->add_module('mcp', 0, $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blog',
	'module_langname'	=> 'MCP_BLOG_REPORTED_BLOGS',
	'module_mode'		=> 'reported_blogs',
	'module_auth'		=> 'acl_m_blogreport',
);
$eami->add_module('mcp', 'MCP_BLOG', $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blog',
	'module_langname'	=> 'MCP_BLOG_DISAPPROVED_BLOGS',
	'module_mode'		=> 'disapproved_blogs',
	'module_auth'		=> 'acl_m_blogapprove',
);
$eami->add_module('mcp', 'MCP_BLOG', $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blog',
	'module_langname'	=> 'MCP_BLOG_REPORTED_REPLIES',
	'module_mode'		=> 'reported_replies',
	'module_auth'		=> 'acl_m_blogreplyreport',
);
$eami->add_module('mcp', 'MCP_BLOG', $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blog',
	'module_langname'	=> 'MCP_BLOG_DISAPPROVED_REPLIES',
	'module_mode'		=> 'disapproved_replies',
	'module_auth'		=> 'acl_m_blogreplyapprove',
);
$eami->add_module('mcp', 'MCP_BLOG', $sql_ary);

// UCP Modules
$sql_ary = array(
	'module_langname'	=> 'UCP_BLOG',
);
$eami->add_module('ucp', 0, $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blog',
	'module_langname'	=> 'UCP_BLOG_SETTINGS',
	'module_mode'		=> 'ucp_blog_settings',
	'module_auth'		=> 'acl_u_blogpost',
);
$eami->add_module('ucp', 'UCP_BLOG', $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blog',
	'module_langname'	=> 'UCP_BLOG_TITLE_DESCRIPTION',
	'module_mode'		=> 'ucp_blog_title_description',
	'module_auth'		=> 'acl_u_blogpost',
);
$eami->add_module('ucp', 'UCP_BLOG', $sql_ary);

$sql_ary = array(
	'module_basename'	=> 'blog',
	'module_langname'	=> 'UCP_BLOG_PERMISSIONS',
	'module_mode'		=> 'ucp_blog_permissions',
	'module_auth'		=> 'acl_u_blogpost',
);
$eami->add_module('ucp', 'UCP_BLOG', $sql_ary);
?>