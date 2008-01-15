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
* Updates user settings
*/
function update_user_blog_settings($user_id, $data, $resync = false)
{
	global $cache, $db, $user_settings, $blog_plugins;

	if (!isset($user_settings[$user_id]))
	{
		get_user_settings($user_id);
	}

	if (!isset($user_settings[$user_id]))
	{
		$sql_array = array(
			'user_id'							=> $user_id,
			'perm_guest'						=> (isset($data['perm_guest'])) ? $data['perm_guest'] : 1,
			'perm_registered'					=> (isset($data['perm_registered'])) ? $data['perm_registered'] : 2,
			'perm_foe'							=> (isset($data['perm_foe'])) ? $data['perm_foe'] : 0,
			'perm_friend'						=> (isset($data['perm_friend'])) ? $data['perm_friend'] : 2,
			'title'								=> (isset($data['title'])) ? $data['title'] : '',
			'description'						=> (isset($data['description'])) ? $data['description'] : '',
			'description_bbcode_bitfield'		=> (isset($data['description_bbcode_bitfield'])) ? $data['description_bbcode_bitfield'] : '',
			'description_bbcode_uid'			=> (isset($data['description_bbcode_uid'])) ? $data['description_bbcode_uid'] : '',
			'instant_redirect'					=> (isset($data['instant_redirect'])) ? $data['instant_redirect'] : 0,
			'blog_subscription_default'			=> (isset($data['blog_subscription_default'])) ? $data['blog_subscription_default'] : 0,
		);

		$temp = compact('sql_array', 'user_id', 'data');
		$blog_plugins->plugin_do_ref('function_get_user_settings_insert', $temp);
		extract($temp);

		$sql = 'INSERT INTO ' . BLOGS_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
		$db->sql_query($sql);
	}
	else
	{
		$blog_plugins->plugin_do_ref('function_get_user_settings_update', $data);

		$sql = 'UPDATE ' . BLOGS_USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE user_id = ' . intval($user_id);
		$db->sql_query($sql);
	}

	if ($resync && (array_key_exists('perm_guest', $data) || array_key_exists('perm_registered', $data) || array_key_exists('perm_foe', $data) || array_key_exists('perm_friend', $data)))
	{
		$sql_array = array(
			'perm_guest'						=> (isset($data['perm_guest'])) ? $data['perm_guest'] : 1,
			'perm_registered'					=> (isset($data['perm_registered'])) ? $data['perm_registered'] : 2,
			'perm_foe'							=> (isset($data['perm_foe'])) ? $data['perm_foe'] : 0,
			'perm_friend'						=> (isset($data['perm_friend'])) ? $data['perm_friend'] : 2,
		);

		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id = \'' . intval($user_id) . '\'';
		$db->sql_query($sql);
	}

	$blog_plugins->plugin_do('function_get_user_settings', compact('data', 'user_id', 'resync'));

	$cache->destroy('_blog_settings_' . $user_id);
}

/**
* Gets user settings
*
* @param int $user_ids array of user_ids to get the settings for
*/
function get_user_settings($user_ids)
{
	global $cache, $config, $user_settings, $blog_plugins;

	if (!isset($config['user_blog_enable']) || !$config['user_blog_enable'])
	{
		return;
	}

	if (!is_array($user_settings))
	{
		$user_settings = array();
	}

	if (!is_array($user_ids))
	{
		$user_ids = array($user_ids);
	}

	$to_query = array();
	foreach ($user_ids as $id)
	{
		if (!array_key_exists($id, $user_settings))
		{
			$cache_data = $cache->get('_blog_settings_' . intval($id));
			if ($cache_data === false)
			{
				$to_query[] = (int) $id;
			}
			else
			{
				$user_settings[$id] = $cache_data;
			}
		}
	}

	if (count($to_query))
	{
		global $db;
		$sql = 'SELECT * FROM ' . BLOGS_USERS_TABLE . ' WHERE ' . $db->sql_in_set('user_id', $to_query);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$cache->put('_blog_settings_' . $row['user_id'], $row);

			$user_settings[$row['user_id']] = $row;
		}
		$db->sql_freeresult($result);
	}

	$blog_plugins->plugin_do('function_get_user_settings');
}

/**
* Gets Zebra (friend/foe)  info
*
* @param int|bool $uid The user_id we will grab the zebra data for.  If this is false we will use $user->data['user_id']
*/
function get_zebra_info($user_ids, $reverse_lookup = false)
{
	global $config, $db, $zebra_list, $reverse_zebra_list, $blog_plugins;

	if (!isset($config['user_blog_enable_zebra']) || !$config['user_blog_enable_zebra'])
	{
		return;
	}

	$blog_plugins->plugin_do('function_get_zebra_info', compact('user_ids', 'reverse_lookup'));

	$to_query = array();

	if (!is_array($user_ids))
	{
		$user_ids = array($user_ids);
	}

	if (!$reverse_lookup)
	{
		foreach ($user_ids as $user_id)
		{
			if (!is_array($zebra_list) || !array_key_exists($user_id, $zebra_list))
			{
				$to_query[] = $user_id;
			}
		}

		if (!count($to_query))
		{
			return;
		}
	}
	else
	{
		foreach ($user_ids as $user_id)
		{
			if (!is_array($reverse_zebra_list) || !array_key_exists($user_id, $reverse_zebra_list))
			{
				$to_query[] = $user_id;
			}
		}

		if (!count($to_query))
		{
			return;
		}
	}

	$sql = 'SELECT * FROM ' . ZEBRA_TABLE . '
		WHERE ' . $db->sql_in_set((($reverse_lookup) ? 'zebra_id' : 'user_id'), $to_query);
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		if ($reverse_lookup)
		{
			if ($row['foe'])
			{
				$reverse_zebra_list[$row['zebra_id']]['foe'][] = $row['user_id'];
				$zebra_list[$row['user_id']]['foe'][] = $row['zebra_id'];
			}
			else if ($row['friend'])
			{
				$reverse_zebra_list[$row['zebra_id']]['friend'][] = $row['user_id'];
				$zebra_list[$row['user_id']]['friend'][] = $row['zebra_id'];
			}
		}
		else
		{
			if ($row['foe'])
			{
				$zebra_list[$row['user_id']]['foe'][] = $row['zebra_id'];
			}
			else if ($row['friend'])
			{
				$zebra_list[$row['user_id']]['friend'][] = $row['zebra_id'];
			}
		}
	}
	$db->sql_freeresult($result);	
}
?>