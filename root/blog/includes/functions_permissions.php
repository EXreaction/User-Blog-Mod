<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: functions_permissions.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Checks to see if a user has permission to either read or reply to a blog with the given blog_id
*/
function handle_user_blog_permissions($blog_id, $user_id = false, $mode = 'read')
{
	global $auth, $config, $db, $user;
	global $zebra_list, $blog_plugins;

	if (!$config['user_blog_user_permissions'])
	{
		return true;
	}

	// If we are checking blog permissions for a certain blog, get the permission data on it and set the user_id to the author's id, else get the users permissions of the requested user
	$var = false;
	if ($blog_id !== false && isset(blog_data::$blog[$blog_id]))
	{
		$var = blog_data::$blog[$blog_id];
		$user_id = blog_data::$blog[$blog_id]['user_id'];
	}
	else if ($user_id !== false)
	{
		global $user_settings;
		$var = (isset($user_settings[$user_id])) ? $user_settings[$user_id] : false;
	}

	// Anonymous users are not allowed to set per blog permissions, and if the user is viewing their own or is a mod or admin, they can see it.
	if ($user_id == ANONYMOUS || $user->data['user_id'] == $user_id || $auth->acl_gets('a_', 'm_') || !$var)
	{
		return true;
	}

	if ($user->data['user_id'] == ANONYMOUS)
	{
		switch ($mode)
		{
			case 'read' :
				if ($var['perm_guest'] > 0)
				{
					return true;
				}
				return false;
			break;
			case 'reply' :
				if ($var['perm_guest'] > 1)
				{
					return true;
				}
				return false;
			break;
		}
	}

	if ($config['user_blog_enable_zebra'])
	{
		if (!is_array($zebra_list) || !array_key_exists($user_id, $zebra_list))
		{
			get_zebra_info($user_id);
		}

		if (isset($zebra_list[$user_id]['foe']) && in_array($user->data['user_id'], $zebra_list[$user_id]['foe']))
		{
			switch ($mode)
			{
				case 'read' :
					if ($var['perm_foe'] > 0)
					{
						return true;
					}
					return false;
				break;
				case 'reply' :
					if ($var['perm_foe'] > 1)
					{
						return true;
					}
					return false;
				break;
			}
		}
		else if (isset($zebra_list[$user_id]['friend']) && in_array($user->data['user_id'], $zebra_list[$user_id]['friend']))
		{
			switch ($mode)
			{
				case 'read' :
					if ($var['perm_friend'] > 0)
					{
						return true;
					}
					return false;
				break;
				case 'reply' :
					if ($var['perm_friend'] > 1)
					{
						return true;
					}
					return false;
				break;
			}
		}
	}

	if ($user->data['user_id'] != ANONYMOUS)
	{
		switch ($mode)
		{
			case 'read' :
				if ($var['perm_registered'] > 0)
				{
					return true;
				}
				return false;
			break;
			case 'reply' :
				if ($var['perm_registered'] > 1)
				{
					return true;
				}
				return false;
			break;
		}
	}

	$return = false;
	$temp = compact('blog_id', 'user_id', 'mode', 'return');
	blog_plugins::plugin_do_ref('handle_user_blog_permissions', $temp);
	return $temp['return'];
}

/**
* Builds permission settings
*
* @param bool $send_to_template - Automatically put the data in the template, otherwise it returns it.
*/
function permission_settings_builder($send_to_template = true, $mode = 'add')
{
	global $blog_plugins, $config, $template, $user, $user_settings, $blog_id;

	if (!$config['user_blog_user_permissions'])
	{
		return;
	}

	if ($mode == 'edit' && isset(blog_data::$blog[$blog_id]))
	{
		$perm_guest = (request_var('perm_guest', -1) != -1) ? request_var('perm_guest', -1) : blog_data::$blog[$blog_id]['perm_guest'];
		$perm_registered = (request_var('perm_registered', -1) != -1) ? request_var('perm_registered', -1) : blog_data::$blog[$blog_id]['perm_registered'];
		$perm_foe = (request_var('perm_foe', -1) != -1) ? request_var('perm_foe', -1) : blog_data::$blog[$blog_id]['perm_foe'];
		$perm_friend = (request_var('perm_friend', -1) != -1) ? request_var('perm_friend', -1) : blog_data::$blog[$blog_id]['perm_friend'];
	}
	else if (isset($user_settings[$user->data['user_id']]))
	{
		$perm_guest = (request_var('perm_guest', -1) != -1) ? request_var('perm_guest', -1) : $user_settings[$user->data['user_id']]['perm_guest'];
		$perm_registered = (request_var('perm_registered', -1) != -1) ? request_var('perm_registered', -1) : $user_settings[$user->data['user_id']]['perm_registered'];
		$perm_foe = (request_var('perm_foe', -1) != -1) ? request_var('perm_foe', -1) : $user_settings[$user->data['user_id']]['perm_foe'];
		$perm_friend = (request_var('perm_friend', -1) != -1) ? request_var('perm_friend', -1) : $user_settings[$user->data['user_id']]['perm_friend'];
	}
	else
	{
		$perm_guest = 1;
		$perm_registered = 2;
		$perm_foe = 0;
		$perm_friend = 2;
	}

	$permission_settings = array(
		array(
			'TITLE'			=> $user->lang['GUEST_PERMISSIONS'],
			'NAME'			=> 'perm_guest',
			'DEFAULT'		=> $perm_guest,
		),
		array(
			'TITLE'			=> $user->lang['REGISTERED_PERMISSIONS'],
			'NAME'			=> 'perm_registered',
			'DEFAULT'		=> $perm_registered,
		),
	);

	if ($config['user_blog_enable_zebra'])
	{
		$permission_settings[] = array(
			'TITLE'			=> $user->lang['FOE_PERMISSIONS'],
			'NAME'			=> 'perm_foe',
			'DEFAULT'		=> $perm_foe,
		);
		$permission_settings[] = array(
			'TITLE'			=> $user->lang['FRIEND_PERMISSIONS'],
			'NAME'			=> 'perm_friend',
			'DEFAULT'		=> $perm_friend,
		);
	}

	$temp = compact('permission_settings', 'mode');
	blog_plugins::plugin_do_ref('function_permission_settings_builder', $temp);
	extract($temp);

	if ($send_to_template)
	{
		foreach ($permission_settings as $row)
		{
			$template->assign_block_vars('permissions', $row);
		}
	}
	else
	{
		return $permission_settings;
	}
}

/**
* Build the permissions sql
*
* Call this function to have it automatically build the user permissions check part of the SQL query
*
* @param int $user_id The user id of the user we will build the permissions sql query for
* @param bool $add_where Puts a WHERE at the beginning instead of AND
* @param string $prefix is to add a prefix to the column (for example, when used in a join and you need b.user_id)
*/
function build_permission_sql($user_id, $add_where = false, $prefix = '')
{
	global $auth, $config, $db;

	// If user permissions are not allowed or the viewing user has moderator or administrator permissions, nothing will be checked.
	if (!$config['user_blog_user_permissions'] || $auth->acl_gets('a_', 'm_'))
	{
		return '';
	}

	// Matches and replacements.  Make sure to add any field used below here.  It must be done this way to work with our static...otherwise the static is useless.
	$matches = array('user_id', 'perm_guest', 'perm_registered', 'perm_friend', 'perm_foe');
	$replacements = array($prefix . 'user_id', $prefix . 'perm_guest', $prefix . 'perm_registered', $prefix . 'perm_friend', $prefix . 'perm_foe');

	// We only want to build this query once per session...so if it is built already, don't do it again!
	static $sql = '';
	if ($sql)
	{
		return str_replace($matches, $replacements, (($add_where) ? fix_where_sql($sql) : $sql));
	}

	$user_id = (int) $user_id;

	if ($user_id == ANONYMOUS)
	{
		$sql = ' AND perm_guest > 0';
		return str_replace($matches, $replacements, (($add_where) ? fix_where_sql($sql) : $sql));
	}

	$sql = " AND (user_id = {$user_id}";

	// Here is where things get complicated with friend/foe permissions.
	$zebra_list = array();
	if ($config['user_blog_enable_zebra'])
	{
		global $reverse_zebra_list;
		get_zebra_info($user_id, true);

		if (isset($reverse_zebra_list[$user_id]['foe']) && sizeof($reverse_zebra_list[$user_id]['foe']))
		{
			foreach ($reverse_zebra_list[$user_id]['foe'] as $zid)
			{
				$sql .= " OR (user_id = {$zid} AND perm_foe > 0)";
				$zebra_list[] = $zid;
			}
		}

		if (isset($reverse_zebra_list[$user_id]['friend']) && sizeof($reverse_zebra_list[$user_id]['friend']))
		{
			foreach ($reverse_zebra_list[$user_id]['friend'] as $zid)
			{
				$sql .= " OR (user_id = {$zid} AND perm_friend > 0)";
				$zebra_list[] = $zid;
			}
		}
	}

	if (sizeof($zebra_list))
	{
		// Inverted sql_in_set.  For any user NOT in the zebra list.
		$sql .= ' OR (' . $db->sql_in_set('user_id', $zebra_list, true) . " AND perm_registered > 0)";
	}
	else
	{
		$sql .= " OR (perm_registered > 0)";
	}

	$sql .= ')';

	blog_plugins::plugin_do_ref('function_build_permission_sql', $sql);

	return str_replace($matches, $replacements, (($add_where) ? fix_where_sql($sql) : $sql));
}

/**
 *  Check blog permissions
 *
 * @param string $page The page requested - blog, reply, mcp, install, upgrade, update, dev, resync
 * @param string $mode The mode requested - depends on the $page requested
 * @param bool $return If you would like this function to return true or false (if they have permission or not).  If it is false we give them a login box if they are not logged in, or give them the NO_AUTH error message
 * @param int $blog_id The blog_id requested (needed for some things, like blog edit, delete, etc
 * @param int $reply_id The reply_id requested, used for the same reason as $blog_id
 *
 * @return Returns
 *	- true if the user is authorized to do the requested action
 *	- false if the user is not authorized to do the requested action
 */
function check_blog_permissions($page, $mode, $return = false, $blog_id = 0, $reply_id = 0)
{
	global $user, $config, $auth, $blog_plugins;

	blog_plugins::plugin_do('function_check_blog_permissions');

	switch ($page)
	{
		case 'blog' :
			switch ($mode)
			{
				case 'add' :
					$is_auth = ($auth->acl_get('u_blogpost')) ? true : false;
				break;
				case 'edit' :
					$is_auth = ($user->data['user_id'] != ANONYMOUS && ($auth->acl_get('u_blogedit') && ($user->data['user_id'] == blog_data::$blog[$blog_id]['user_id']) || $auth->acl_get('m_blogedit'))) ? true : false;
				break;
				case 'delete' :
					if (blog_data::$blog[$blog_id]['blog_deleted'] == 0 || $auth->acl_get('a_blogdelete'))
					{
						$is_auth = ($user->data['user_id'] != ANONYMOUS && (($auth->acl_get('u_blogdelete') && $user->data['user_id'] == blog_data::$blog[$blog_id]['user_id']) || $auth->acl_get('m_blogdelete') || $auth->acl_get('a_blogdelete'))) ? true : false;
					}
					else
					{
						$is_auth = false;
					}
				break;
				case 'undelete' :
					$is_auth = ($auth->acl_gets('m_blogdelete', 'a_blogdelete') || blog_data::$blog[$blog_id]['blog_deleted'] == $user->data['user_id']) ? true : false;
				break;
				case 'report' :
					$is_auth = ($auth->acl_get('u_blogreport')) ? true : false;
				break;
				case 'approve' :
					$is_auth = ($auth->acl_get('m_blogapprove')) ? true : false;
				break;
				case 'vote' :
					$is_auth = ($auth->acl_get('u_blog_vote') && handle_user_blog_permissions($blog_id)) ? true : false;
				break;
			}
		break;
		case 'reply' :
			switch ($mode)
			{
				case 'add' :
				case 'quote' :
						$is_auth = ($auth->acl_get('u_blogreply') && handle_user_blog_permissions($blog_id, false, 'reply')) ? true : false;
				break;
				case 'edit' :
					$is_auth = ($user->data['user_id'] != ANONYMOUS && (($auth->acl_get('u_blogreplyedit') && $user->data['user_id'] == blog_data::$reply[$reply_id]['user_id']) || (isset(blog_data::$blog[$blog_id]['user_id']) && $auth->acl_get('u_blogmoderate') && $user->data['user_id'] == blog_data::$blog[$blog_id]['user_id']) || $auth->acl_get('m_blogreplyedit'))) ? true : false;
				break;
				case 'delete' :
					if (blog_data::$reply[$reply_id]['reply_deleted'] == 0 || $auth->acl_get('a_blogreplydelete'))
					{
						$is_auth = ($user->data['user_id'] != ANONYMOUS && (($auth->acl_get('u_blogreplydelete') && $user->data['user_id'] == blog_data::$reply[$reply_id]['user_id']) || (isset(blog_data::$blog[$blog_id]['user_id']) && $auth->acl_get('u_blogmoderate') && $user->data['user_id'] == blog_data::$blog[$blog_id]['user_id']) || $auth->acl_gets('a_blogreplydelete', 'm_blogreplydelete'))) ? true : false;
					}
					else
					{
						$is_auth = false;
					}
				break;
				case 'undelete' :
					$is_auth = ($auth->acl_gets('m_blogreplydelete', 'a_blogreplydelete') || blog_data::$reply[$reply_id]['reply_deleted'] == $user->data['user_id']) ? true : false;
				break;
				case 'report' :
					$is_auth = ($auth->acl_get('u_blogreport')) ? true : false;
				break;
				case 'approve' :
					$is_auth = ($auth->acl_get('m_blogreplyapprove')) ? true : false;
				break;
			}
			break;
		case 'mcp' :
			$is_auth = ($auth->acl_gets('m_blogapprove', 'acl_m_blogreport')) ? true : false;
		break;
		case 'rate' :
			$is_auth = ($user->data['is_registered']) ? true : false;
		break;
		case 'install' :
		case 'update' :
		case 'upgrade' :
		case 'dev' :
		case 'resync' :
			$is_auth = ($user->data['user_type'] == USER_FOUNDER) ? true : false;
			$founder = true;
		break;
	}

	$temp = compact('is_auth', 'page', 'mode', 'blog_id', 'reply_id');
	blog_plugins::plugin_do_ref('permissions_end', $temp);
	extract($temp);

	// if $is_auth hasn't been set yet they are just viewing a blog/user/etc, if it has been set also check to make sure they can view blogs
	if (!isset($is_auth))
	{
		$is_auth = ($auth->acl_get('u_blogview')) ? true : false;
	}
	else
	{
		// if it is the install page they will not have viewing permissions, but they already need to be a founder :P
		$is_auth = (!$auth->acl_get('u_blogview') && $page != 'install') ? false : $is_auth;
	}

	if (!$return)
	{
		if (!$is_auth)
		{
			if (!$user->data['is_registered'])
			{
				global $template;
				$template->set_template(); // reset the template.  Required because of user styles.
				login_box();
			}
			else
			{
				if (isset($founder) && $founder)
				{
					trigger_error('MUST_BE_FOUNDER');
				}
				else
				{
					trigger_error('NO_AUTH_OPERATION');
				}
			}
		}
	}
	else
	{
		return $is_auth;
	}
}
?>