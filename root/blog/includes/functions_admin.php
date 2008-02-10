<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Perform actions on a user's profile from the acp_users file
*/
function blog_acp_profile($user_id, $submit)
{
	global $db, $phpbb_root_path, $phpEx, $template, $user;

	$user->add_lang(array('mods/blog/common', 'mods/blog/ucp'));
	include("{$phpbb_root_path}blog/includes/functions.$phpEx");
	include("{$phpbb_root_path}blog/includes/constants.$phpEx");
	include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);
	new blog_plugins();

	if ($submit)
	{
		$blog_description = utf8_normalize_nfc(request_var('blog_description', '', true));
		$blog_description_uid = $blog_description_bitfield = $blog_description_options = '';
		generate_text_for_storage($blog_description, $blog_description_uid, $blog_description_bitfield, $blog_description_options, true, true, true);

		$blog_data = array(
			'title'							=> utf8_normalize_nfc(request_var('blog_title', '', true)),
			'description'					=> $blog_description,
			'description_bbcode_bitfield'	=> $blog_description_bitfield,
			'description_bbcode_uid'		=> $blog_description_uid,
			'blog_css'						=> request_var('blog_css', ''),
		);
		update_user_blog_settings($user_id, $blog_data);
	}
	else
	{
		global $user_settings;
		get_user_settings($user_id);

		if (isset($user_settings[$user_id]))
		{
			decode_message($user_settings[$user_id]['description'], $user_settings[$user_id]['description_bbcode_uid']);
			$template->assign_vars(array(
				'BLOG_TITLE'		=> $user_settings[$user_id]['title'],
				'BLOG_DESCRIPTION'	=> $user_settings[$user_id]['description'],
				'BLOG_CSS'			=> $user_settings[$user_id]['blog_css'],
			));
		}

		blog_plugins::plugin_do_arg('function_blog_acp_profile', compact('blog_data', 'user_id'));
	}
}

/**
* Syncronise Blog Data
*
* This should never need to be used unless someone manually deletes blogs or replies from the database
* It is not used by the User Blog mod anywhere, except for updates/upgrades and the resync page.
* To any potential users: Make sure you do not set this in a page where it gets ran often.  Resyncing data is a long process, especially when the number of blogs that you have is large
*
* @param string $mode can be all, reply_count, real_reply_count, delete_orphan_replies, or user_blog_count
*/
function resync_blog($mode)
{
	global $cache, $db;

	$blog_data = array();

	// Start by selecting all blog data that we will use
	$sql = 'SELECT blog_id, blog_reply_count, blog_real_reply_count FROM ' . BLOGS_TABLE . ' ORDER BY blog_id ASC';
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
		$blog_data[$row['blog_id']] = $row;
	}
	$db->sql_freeresult($result);

	/*
	* Update & Resync the reply counts
	*/
	if ( ($mode == 'reply_count') || ($mode == 'all') )
	{
		foreach($blog_data as $row)
		{
			// count all the replies (an SQL query seems the easiest way to do it)
			$sql = 'SELECT count(reply_id) AS total 
				FROM ' . BLOGS_REPLY_TABLE . ' 
					WHERE blog_id = ' . $row['blog_id'] . '
						AND reply_deleted = 0
						AND reply_approved = 1';
			$result = $db->sql_query($sql);
			$total = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($total['total'] != $row['blog_reply_count'])
			{
				// Update the reply count
				$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_reply_count = ' . $total['total'] . ' WHERE blog_id = ' . $row['blog_id'];
				$db->sql_query($sql);
			}
		}
	}

	/*
	* Update & Resync the real reply counts
	*/
	if ( ($mode == 'real_reply_count') || ($mode == 'all') )
	{
		foreach($blog_data as $row)
		{
			// count all the replies (an SQL query seems the easiest way to do it)
			$sql = 'SELECT count(reply_id) AS total 
				FROM ' . BLOGS_REPLY_TABLE . ' 
					WHERE blog_id = ' . $row['blog_id'];
			$result = $db->sql_query($sql);
			$total = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($total['total'] != $row['blog_real_reply_count'])
			{
				// Update the reply count
				$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_real_reply_count = ' . $total['total'] . ' WHERE blog_id = ' . $row['blog_id'];
				$db->sql_query($sql);
			}
		}
	}

	/*
	* Delete's all oprhaned replies (replies where the blogs they should go under have been deleted).
	*/
	if ( ($mode == 'delete_orphan_replies') || ($mode == 'all') )
	{
		// Now get all reply data we will use
		$sql = 'SELECT reply_id, blog_id FROM ' . BLOGS_REPLY_TABLE . ' ORDER BY reply_id ASC';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			// if the blog_id it attached to is not in $blog_data
			if (!(array_key_exists($row['blog_id'], $blog_data)))
			{
				$sql2 = 'DELETE FROM ' . BLOGS_REPLY_TABLE . ' WHERE reply_id = ' . $row['reply_id'];
				$db->sql_query($sql2);
			}
		}
		$db->sql_freeresult($result);
	}

	/*
	* Updates the blog_count for each user
	*/
	if ( ($mode == 'user_blog_count') || ($mode == 'all') )
	{
		// select the users data we will need
		$sql = 'SELECT user_id, blog_count FROM ' . USERS_TABLE;
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			// count all the replies (an SQL query seems the easiest way to do it)
			$sql2 = 'SELECT count(blog_id) AS total 
				FROM ' . BLOGS_TABLE . ' 
					WHERE user_id = \'' . $row['user_id'] . '\' 
						AND blog_deleted = 0
						AND blog_approved = 1';
			$result2 = $db->sql_query($sql2);
			$total = $db->sql_fetchrow($result2);
			$db->sql_freeresult($result2);

			if ($total['total'] != $row['blog_count'])
			{
				// Update the reply count
				$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = ' . $total['total'] . ' WHERE user_id = ' . $row['user_id'];
				$db->sql_query($sql);
			}
		}
		$db->sql_freeresult($result);
	}

	/**
	* Updates the user permissions for each blog
	*/
	if ( ($mode == 'user_permissions' ) || ($mode == 'all') )
	{
		$sql = 'SELECT * FROM ' . BLOGS_USERS_TABLE;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_ary = array(
				'perm_guest'		=> $row['perm_guest'],
				'perm_registered'	=> $row['perm_registered'],
				'perm_foe'			=> $row['perm_foe'],
				'perm_friend'		=> $row['perm_friend'],
			);

			$sql = 'UPDATE ' . BLOGS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE user_id = ' . $row['user_id'];
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);
	}

	// clear the cache
	$cache->purge();

	blog_plugins::plugin_do_arg('function_resync_blog', $mode);
}

?>