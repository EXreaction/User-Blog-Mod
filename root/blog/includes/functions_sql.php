<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Build the permissions sql
*
* Call this function to have it automatically build the user permissions check part of the SQL query
*
* @param int $user_id The user id of the user we will build the permissions sql query for
*/
function build_permission_sql($user_id, $add_where = false)
{
	global $auth, $config, $db;

	// If user permissions are not allowed or the viewing user has moderator or administrator permissions, nothing will be checked.
	if (!$config['user_blog_user_permissions'] || $auth->acl_gets('a_', 'm_'))
	{
		return '';
	}

	// We only want to build this query once per session...so if it is build already, don't do it again!
	static $sql = '';
	if ($sql != '')
	{
		return (($add_where) ? fix_where_sql($sql) : $sql);
	}

	$user_id = (int) $user_id;

	if ($user_id == ANONYMOUS)
	{
		$sql = ' AND perm_guest > 0';
		return (($add_where) ? fix_where_sql($sql) : $sql);
	}

	$sql = " AND (user_id = {$user_id}";

	$zebra_list = array();
	if ($config['user_blog_enable_zebra'])
	{
		global $reverse_zebra_list;
		get_zebra_info($user_id, true);

		if (isset($reverse_zebra_list[$user_id]['foe']) && count($reverse_zebra_list[$user_id]['foe']))
		{
			foreach ($reverse_zebra_list[$user_id]['foe'] as $zid)
			{
				$sql .= " OR (user_id = {$zid} AND perm_foe > 0)";
				$zebra_list[] = $zid;
			}
		}

		if (isset($reverse_zebra_list[$user_id]['friend']) && count($reverse_zebra_list[$user_id]['friend']))
		{
			foreach ($reverse_zebra_list[$user_id]['friend'] as $zid)
			{
				$sql .= " OR (user_id = {$zid} AND perm_friend > 0)";
				$zebra_list[] = $zid;
			}
		}

		if (count($zebra_list))
		{
			$sql .= ' OR (' . $db->sql_in_set('user_id', $zebra_list, true) . " AND perm_registered > 0)";
		}
		else
		{
			$sql .= " OR (perm_registered > 0)";
		}
	}
	else
	{
		$sql .= " OR (perm_registered > 0)";
	}

	$sql .= ')';

	global $blog_plugins;
	$blog_plugins->plugin_do_ref('function_build_permission_sql', $sql);

	return (($add_where) ? fix_where_sql($sql) : $sql);
}

/**
* Fix Where SQL function
*
* Checks to make sure there is a WHERE if there are any AND sections in the SQL and fixes them if needed
*
* @param string $sql The (possibly) broken SQL query to check
* @return The fixed SQL query.
*/
function fix_where_sql($sql)
{
	if (!strpos($sql, 'WHERE') && strpos($sql, 'AND'))
	{
		return substr($sql, 0, strpos($sql, 'AND')) . 'WHERE' . substr($sql, strpos($sql, 'AND') + 3);
	}

	return $sql;
}
?>