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
* Handles blog view/reply permissions (those set by users)
*/
function handle_user_blog_permissions($blog_id, $user_id = false, $mode = 'read')
{
	global $auth, $config, $db, $user;
	global $blog_data, $zebra_list, $blog_plugins, $user_settings;

	if (!$config['user_blog_user_permissions'])
	{
		return true;
	}

	if ($blog_id !== false && isset(blog_data::$blog[$blog_id]))
	{
		$var = blog_data::$blog[$blog_id];
		$user_id = blog_data::$blog[$blog_id]['user_id'];
	}
	else if ($user_id !== false)
	{
		$var = (isset($user_settings[$user_id])) ? $user_settings[$user_id] : '';
	}

	if ($user_id == ANONYMOUS || $user->data['user_id'] == $user_id || !isset($user_settings[$user_id]) || $auth->acl_gets('a_', 'm_'))
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
		if (!array_key_exists($user_id, $zebra_list))
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

	$temp = array('blog_id' => $blog_id, 'user_id' => $user_id, 'mode' => $mode, 'return' => false);
	$blog_plugins->plugin_do_arg_ref('handle_user_blog_permissions', $temp);
	return $temp['return'];
}

/**
* Builds permission settings
*
* @param bool $send_to_template - Automatically put the data in the template, otherwise it returns it.
*/
function permission_settings_builder($send_to_template = true, $mode = 'add')
{
	global $blog_plugins, $config, $template, $user, $user_settings;
	global $blog_data, $blog_id;

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

	$blog_plugins->plugin_do_arg_ref('function_permission_settings_builder', $permission_settings);

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
	global $user, $config, $auth;
	global $blog_data, $reply_data, $user_data, $blog_plugins;

	if (method_exists($blog_plugins, 'plugin_do'))
	{
		$blog_plugins->plugin_do('permissions_start');
	}

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
				break;
				case 'undelete' :
					$is_auth = ($auth->acl_gets('m_blogdelete', 'a_blogdelete')) ? true : false;
				break;
				case 'report' :
					$is_auth = ($auth->acl_get('u_blogreport')) ? true : false;
				break;
				case 'approve' :
					$is_auth = ($auth->acl_get('m_blogapprove')) ? true : false;
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
					$is_auth = ($user->data['user_id'] != ANONYMOUS && (($auth->acl_get('u_blogreplyedit') && $user->data['user_id'] == reply_data::$reply[$reply_id]['user_id']) || ($auth->acl_get('u_blogmoderate') && $user->data['user_id'] == blog_data::$blog[$blog_id]['user_id']) || $auth->acl_get('m_blogreplyedit'))) ? true : false;
				break;
				case 'delete' :
					if (reply_data::$reply[$reply_id]['reply_deleted'] == 0 || $auth->acl_get('a_blogreplydelete'))
					{
						$is_auth = ($user->data['user_id'] != ANONYMOUS && (($auth->acl_get('u_blogreplydelete') && $user->data['user_id'] == reply_data::$reply[$reply_id]['user_id']) || ($auth->acl_get('u_blogmoderate') && $user->data['user_id'] == blog_data::$blog[$blog_id]['user_id']) || $auth->acl_gets('a_blogreplydelete', 'm_blogreplydelete'))) ? true : false;
					}
				break;
				case 'undelete' :
					$is_auth = ($auth->acl_get('m_blogreplydelete') || $auth->acl_get('a_blogreplydelete')) ? true : false;
				break;
				case 'report' :
					$is_auth = ($auth->acl_get('u_blogreport')) ? true : false;
				break;
				case 'approve' :
					$is_auth = ($auth->acl_get('m_blogreplyapprove')) ? true : false;
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
	}

	// if $is_auth hasn't been set yet they are just viewing a blog/user/etc, if it has been set also check to make sure they can view blogs
	if (!isset($is_auth))
	{
		$is_auth = ($auth->acl_get('u_blogview')) ? true : false;
	}
	else
	{
		// if it is the install page they will not have viewing permissions :P
		$is_auth = (!$auth->acl_get('u_blogview') && $page != 'install') ? false : $is_auth;
	}

	if (method_exists($blog_plugins, 'plugin_do'))
	{
		$blog_plugins->plugin_do_arg_ref('permissions_end', $is_auth);
	}

	if (!$return)
	{
		if (!$is_auth)
		{
			if (!$user->data['is_registered'])
			{
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