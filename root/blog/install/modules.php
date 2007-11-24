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

// ACP Modules ----------------------------------
$sql = 'SELECT * FROM ' . MODULES_TABLE . " WHERE module_langname = 'ACP_CAT_DOT_MODS'";
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blogs',
	'module_class'		=> 'acp',
	'parent_id'			=> $row['module_id'],
	'left_id'			=> $row['right_id'],
	'right_id'			=> $row['right_id'] + 7,
	'module_langname'	=> 'ACP_BLOGS',
	'module_mode'		=> 'default',
	'module_auth'		=> 'acl_a_blogmanage',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);
$module_id = $db->sql_nextid();

$sql = 'UPDATE ' . MODULES_TABLE . "
SET left_id = left_id + 6, right_id = right_id + 6
WHERE left_id >= {$sql_ary['left_id']} AND module_id != $module_id";
$db->sql_query($sql);
					
$sql = 'UPDATE ' . MODULES_TABLE . "
SET right_id = right_id + 6
WHERE left_id < {$sql_ary['left_id']} AND right_id >= {$sql_ary['left_id']} AND module_id != $module_id";
$db->sql_query($sql);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blogs',
	'module_class'		=> 'acp',
	'parent_id'			=> $module_id,
	'left_id'			=> $row['right_id'] + 1,
	'right_id'			=> $row['right_id'] + 2,
	'module_langname'	=> 'ACP_BLOGS',
	'module_mode'		=> 'default',
	'module_auth'		=> 'acl_a_blogmanage',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blog_plugins',
	'module_class'		=> 'acp',
	'parent_id'			=> $module_id,
	'left_id'			=> $row['right_id'] + 3,
	'right_id'			=> $row['right_id'] + 4,
	'module_langname'	=> 'ACP_BLOG_PLUGINS',
	'module_mode'		=> 'default',
	'module_auth'		=> 'acl_a_blogmanage',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blog_search',
	'module_class'		=> 'acp',
	'parent_id'			=> $module_id,
	'left_id'			=> $row['right_id'] + 5,
	'right_id'			=> $row['right_id'] + 6,
	'module_langname'	=> 'ACP_BLOG_SEARCH',
	'module_mode'		=> 'default',
	'module_auth'		=> 'acl_a_blogmanage',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

// MCP Modules ----------------------------------
$sql = 'SELECT MAX(right_id) AS top FROM ' . MODULES_TABLE . ' WHERE module_class = \'mcp\'';
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> '',
	'module_class'		=> 'mcp',
	'parent_id'			=> 0,
	'left_id'			=> $row['top'] + 1,
	'right_id'			=> $row['top'] + 10,
	'module_langname'	=> 'BLOG',
	'module_mode'		=> '',
	'module_auth'		=> '',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);
$parent_id = $db->sql_nextid();

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blog',
	'module_class'		=> 'mcp',
	'parent_id'			=> $parent_id,
	'left_id'			=> $row['top'] + 2,
	'right_id'			=> $row['top'] + 3,
	'module_langname'	=> 'MCP_BLOG_REPORTED_BLOGS',
	'module_mode'		=> 'reported_blogs',
	'module_auth'		=> 'acl_m_blogreport',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blog',
	'module_class'		=> 'mcp',
	'parent_id'			=> $parent_id,
	'left_id'			=> $row['top'] + 4,
	'right_id'			=> $row['top'] + 5,
	'module_langname'	=> 'MCP_BLOG_REPORTED_REPLIES',
	'module_mode'		=> 'reported_replies',
	'module_auth'		=> 'acl_m_blogreplyreport',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blog',
	'module_class'		=> 'mcp',
	'parent_id'			=> $parent_id,
	'left_id'			=> $row['top'] + 6,
	'right_id'			=> $row['top'] + 7,
	'module_langname'	=> 'MCP_BLOG_DISAPPROVED_BLOGS',
	'module_mode'		=> 'disapproved_blogs',
	'module_auth'		=> 'acl_m_blogapprove',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blog',
	'module_class'		=> 'mcp',
	'parent_id'			=> $parent_id,
	'left_id'			=> $row['top'] + 8,
	'right_id'			=> $row['top'] + 9,
	'module_langname'	=> 'MCP_BLOG_DISAPPROVED_REPLIES',
	'module_mode'		=> 'disapproved_replies',
	'module_auth'		=> 'acl_m_blogreplyapprove',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

// UCP Modules ----------------------------------
$sql = 'SELECT MAX(right_id) AS top FROM ' . MODULES_TABLE . ' WHERE module_class = \'ucp\'';
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> '',
	'module_class'		=> 'ucp',
	'parent_id'			=> 0,
	'left_id'			=> $row['top'] + 1,
	'right_id'			=> $row['top'] + 8,
	'module_langname'	=> 'BLOG',
	'module_mode'		=> '',
	'module_auth'		=> '',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);
$parent_id = $db->sql_nextid();

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blog',
	'module_class'		=> 'ucp',
	'parent_id'			=> $parent_id,
	'left_id'			=> $row['top'] + 2,
	'right_id'			=> $row['top'] + 3,
	'module_langname'	=> 'UCP_BLOG_SETTINGS',
	'module_mode'		=> 'ucp_blog_settings',
	'module_auth'		=> 'acl_u_blogpost',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blog',
	'module_class'		=> 'ucp',
	'parent_id'			=> $parent_id,
	'left_id'			=> $row['top'] + 4,
	'right_id'			=> $row['top'] + 5,
	'module_langname'	=> 'UCP_BLOG_TITLE_DESCRIPTION',
	'module_mode'		=> 'ucp_blog_title_description',
	'module_auth'		=> 'acl_u_blogpost',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);

$sql_ary = array(
	'module_enabled'	=> 1,
	'module_display'	=> 1,
	'module_basename'	=> 'blog',
	'module_class'		=> 'ucp',
	'parent_id'			=> $parent_id,
	'left_id'			=> $row['top'] + 6,
	'right_id'			=> $row['top'] + 7,
	'module_langname'	=> 'UCP_BLOG_PERMISSIONS',
	'module_mode'		=> 'ucp_blog_permissions',
	'module_auth'		=> 'acl_u_blogpost',
);

$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
$db->sql_query($sql);
?>