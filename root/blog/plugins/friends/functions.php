<?php
/**
*
* @package phpBB3 User Blog Friends
* @version $Id: functions.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

function friends_function_generate_menu(&$arg)
{
	global $auth, $cache, $config, $db, $user, $template, $phpbb_root_path;
	global $user_id, $blog_template, $blog_images_path;

	$limit = 4;

	$template->assign_vars(array(
		'ZEBRA_LIST_LIMIT'			=> $limit,
		'IMG_PORTAL_MEMBER'			=> $blog_images_path . 'icon_friend.gif',
		'S_CONTENT_FLOW_BEGIN'		=> ($user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
		'S_CONTENT_FLOW_END'		=> ($user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
	));

	if ($user_id == ANONYMOUS || $user->data['is_bot'])
	{
		return;
	}

	// Output listing of friends online
	$menu_friends_online = $menu_friends_offline = $user_friends = 0;
	$update_time = (time() - (intval($config['load_online_time']) * 60));

	// lets use the cache...as this query is quite intensive
	$cache_data = $cache->get("_zebra{$user_id}");
	
	if ($cache_data === false)
	{
		$sql = $db->sql_build_query('SELECT_DISTINCT', array(
			'SELECT'	=> 'u.user_id, u.username, u.username_clean, u.user_colour, MAX(s.session_time) as online_time, MIN(s.session_viewonline) AS viewonline',

			'FROM'		=> array(
				USERS_TABLE		=> 'u',
				ZEBRA_TABLE		=> 'z'
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(SESSIONS_TABLE => 's'),
					'ON'	=> 's.session_user_id = z.zebra_id'
				)
			),

			'WHERE'		=> 'z.user_id = ' . intval($user_id) . '
				AND z.friend = 1
				AND u.user_id = z.zebra_id',

			'GROUP_BY'	=> 'z.zebra_id, u.user_id, u.username_clean, u.user_colour, u.username',

			'ORDER_BY'	=> 'u.username_clean ASC',
		));
		$result = $db->sql_query($sql);
		$rowset = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$cache->put("_zebra{$user_id}", $rowset, 60); // cache for 1 minute
		$cache_data = $rowset;
	}

	foreach ($cache_data as $row)
	{
		$which = ($update_time < $row['online_time'] && ($row['viewonline'] || $auth->acl_get('u_viewonline'))) ? 'menu_friends_online' : 'menu_friends_offline';

		$$which++;

		$template->assign_block_vars("{$which}", array(
			'USER_ID'		=> $row['user_id'],

			'U_PROFILE'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),
			'U_VIEW_BLOG'	=> blog_url($row['user_id']),
			'USER_COLOUR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
			'USERNAME'		=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
			'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
			'HIDE'			=> true,
		));
	}

	$template->assign_vars(array(
		'S_SHOW_NEXT_ONLINE'		=> (($menu_friends_online > $limit) ? true : false),
		'S_SHOW_NEXT_OFFLINE'		=> (($menu_friends_offline > $limit) ? true : false),
		'S_MENU_ZEBRA_ENABLED'		=> ($menu_friends_online || $menu_friends_offline) ? true : false,
	));

	$arg['user_menu_extra'] .= blog_plugins::parse_template('blog/plugins/friends/friends_body.html');
}
?>