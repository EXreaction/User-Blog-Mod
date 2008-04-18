<?php
/**
*
* @package phpBB3 User Blog Simple Points
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

function sp_acp_main_settings(&$settings)
{
	global $user;

	$user->add_lang('mods/blog/plugins/simple_points');

	$settings['legend_sp'] = 'SIMPLE_POINTS_PLUGIN';
	$settings['user_blog_sp_blog_points'] = array('lang' => 'SIMPLE_POINTS_BLOG_POINTS', 'validate' => 'int', 'type' => 'text:5:5', 'explain' => true);
	$settings['user_blog_sp_reply_points'] = array('lang' => 'SIMPLE_POINTS_REPLY_POINTS', 'validate' => 'int', 'type' => 'text:5:5', 'explain' => true);
}

function sp_blog_add_after_sql()
{
	global $auth, $config, $db, $user;

	if ($auth->acl_get('u_blognoapprove'))
	{
		$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_points = user_points + ' . $config['user_blog_sp_blog_points'] . ' WHERE user_id = ' . $user->data['user_id']);
	}
}

function sp_blog_approve_confirm()
{
	global $config, $db, $user_id;

	$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_points = user_points + ' . $config['user_blog_sp_blog_points'] . ' WHERE user_id = ' . intval($user_id));
}

function sp_reply_add_after_sql()
{
	global $auth, $config, $db, $user;

	if ($auth->acl_get('u_blogreplynoapprove'))
	{
		$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_points = user_points + ' . $config['user_blog_sp_reply_points'] . ' WHERE user_id = ' . $user->data['user_id']);
	}
}

function sp_reply_approve_confirm()
{
	global $config, $db, $reply_user_id;

	$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_points = user_points + ' . $config['user_blog_sp_reply_points'] . ' WHERE user_id = ' . intval($reply_user_id));
}

?>