<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: functions_admin.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Delete a board style
*/
function blog_remove_style($style_id, $new_id)
{
	global $db, $phpbb_root_path, $phpEx;

	include("{$phpbb_root_path}blog/includes/constants.$phpEx");

	$db->sql_query('UPDATE ' . BLOGS_USERS_TABLE . ' SET blog_style = ' . (int) $new_id . ' WHERE blog_style = ' . (int) $style_id);
}

/**
* Delete user
*
* This function deletes the needed stuff when a user is deleted
*/
function blog_delete_user($user_id)
{
	global $config, $db, $phpbb_root_path, $phpEx;

	$user_id = (int) $user_id;

	include("{$phpbb_root_path}blog/includes/constants.$phpEx");
	if (!function_exists('setup_blog_search'))
	{
		include("{$phpbb_root_path}blog/includes/functions.$phpEx");
	}
	if (!function_exists('put_blogs_in_cats'))
	{
		include("{$phpbb_root_path}blog/includes/functions_categories.$phpEx");
	}
	$blog_search = setup_blog_search();

	$num_blogs = $num_blog_replies = 0;
	$result = $db->sql_query('SELECT * FROM ' . BLOGS_TABLE . ' WHERE user_id = ' . $user_id);
	while ($row = $db->sql_fetchrow($result))
	{
		$num_blogs++;
		$num_blog_replies += $row['blog_real_reply_count'];

		$blog_search->index_remove($row['blog_id']);
		put_blogs_in_cats($row['blog_id'], array(), true, 'soft_delete');

		// Delete the Attachments
		$rids = array();
		$sql = 'SELECT reply_id FROM ' . BLOGS_REPLY_TABLE . ' WHERE blog_id = ' . $row['blog_id'];
		$result1 = $db->sql_query($sql);
		while ($row1 = $db->sql_fetchrow($result1))
		{
			$rids[] = $row1['reply_id'];
		}
		$db->sql_freeresult($result1);
		if (sizeof($rids))
		{
			$sql = 'SELECT physical_filename FROM ' . BLOGS_ATTACHMENT_TABLE . ' WHERE blog_id = ' . $row['blog_id'] . ' OR ' . $db->sql_in_set('reply_id', $rids);
		}
		else
		{
			$sql = 'SELECT physical_filename FROM ' . BLOGS_ATTACHMENT_TABLE . ' WHERE blog_id = ' . $row['blog_id'];
		}
		$result1 = $db->sql_query($sql);
		while ($row1 = $db->sql_fetchrow($result1))
		{
			@unlink($phpbb_root_path . $config['upload_path'] . '/blog_mod/' . $row1['physical_filename']);
		}
		$db->sql_freeresult($result1);

		if (sizeof($rids))
		{
			$db->sql_query('DELETE FROM ' . BLOGS_ATTACHMENT_TABLE . ' WHERE blog_id = ' . $row['blog_id'] . ' OR ' . $db->sql_in_set('reply_id', $rids));
		}
		else
		{
			$db->sql_query('DELETE FROM ' . BLOGS_ATTACHMENT_TABLE . ' WHERE blog_id = ' . $row['blog_id']);
		}

		// delete the blog
		$db->sql_query('DELETE FROM ' . BLOGS_TABLE . ' WHERE blog_id = ' . $row['blog_id']);

		// delete the replies
		$db->sql_query('DELETE FROM ' . BLOGS_REPLY_TABLE . ' WHERE blog_id = ' . $row['blog_id']);

		// delete from the blogs_in_categories
		$db->sql_query('DELETE FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . $row['blog_id']);

		// delete from the blogs_ratings
		$db->sql_query('DELETE FROM ' . BLOGS_RATINGS_TABLE . ' WHERE blog_id = ' . $row['blog_id']);

		// Delete the subscriptions
		$db->sql_query('DELETE FROM ' . BLOGS_SUBSCRIPTION_TABLE . ' WHERE blog_id = ' . $row['blog_id']);

		// Delete the Polls
		$db->sql_query('DELETE FROM ' . BLOGS_POLL_OPTIONS_TABLE . ' WHERE blog_id = ' . $row['blog_id']);
		$db->sql_query('DELETE FROM ' . BLOGS_POLL_VOTES_TABLE . ' WHERE blog_id = ' . $row['blog_id']);
	}
	$db->sql_freeresult($result);

	$result = $db->sql_query('SELECT * FROM ' . BLOGS_REPLY_TABLE . ' WHERE user_id = ' . $user_id);
	while ($row = $db->sql_fetchrow($result))
	{
		$num_blog_replies++;

		$blog_search->index_remove(false, $row['reply_id']);

		// Delete the Attachments
		$sql = 'SELECT physical_filename FROM ' . BLOGS_ATTACHMENT_TABLE . ' WHERE reply_id = ' . $row['reply_id'];
		$result1 = $db->sql_query($sql);
		while ($row1 = $db->sql_fetchrow($result1))
		{
			@unlink($phpbb_root_path . $config['upload_path'] . '/blog_mod/' . $row1['physical_filename']);
		}
		$db->sql_freeresult($result1);
		$db->sql_query('DELETE FROM ' . BLOGS_ATTACHMENT_TABLE . ' WHERE reply_id = ' . $row['reply_id']);

		// delete the reply
		$db->sql_query('DELETE FROM ' . BLOGS_REPLY_TABLE . ' WHERE reply_id = ' . $row['reply_id']);
	}
	$db->sql_freeresult($result);

	$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = 0
		WHERE user_id = ' . $user_id;
	$db->sql_query($sql);

	// Resync reply counts
	resync_blog('reply_count');
	resync_blog('real_reply_count');

	set_config('num_blogs', ($config['num_blogs'] - $num_blogs), true);
	set_config('num_blog_replies', ($config['num_blog_replies'] - $num_blog_replies), true);
}

/**
* Get the User Blog Version
*
* Gets the latest version from lithiumstudios.org (once every hour) and returns it
*/
function get_latest_user_blog_version()
{
	global $cache;

	$version = $cache->get('user_blog_version');
	if ($version === false)
	{
		if (!function_exists('get_remote_file'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}

		$errstr = $errno = '';
		$version = get_remote_file('lithiumstudios.org', '/updatecheck', 'user_blog_mod.txt', $errstr, $errno);

		$cache->put('user_blog_version', $version, 3600);
	}

	return $version;
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
			'blog_style'					=> request_var('blog_style', ''),
			'blog_css'						=> request_var('blog_css', ''),
		);
		update_user_blog_settings($user_id, $blog_data);
	}
	else
	{
		global $user_settings;
		get_user_settings($user_id);

		$available_styles = array(array('name' => $user->lang['NONE'], 'value' => 0, 'demo' => $phpbb_root_path . 'images/spacer.gif'));
		$sql = 'SELECT * FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' st WHERE style_active = 1 AND s.template_id = st.template_id';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$demo = $phpbb_root_path . 'images/spacer.gif';
			if (@file_exists($phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.png'))
			{
				$demo = $phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.png';
			}
			else if (@file_exists($phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.gif'))
			{
				$demo = $phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.gif';
			}
			else if (@file_exists($phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.jpg'))
			{
				$demo = $phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.jpg';
			}

			$available_styles[] = array('name' => $row['style_name'], 'value' => $row['style_id'], 'demo' => $demo);
		}
		$db->sql_freeresult($result);

		$dh = @opendir($phpbb_root_path . 'blog/styles/');

		if ($dh)
		{
			while (($file = readdir($dh)) !== false)
			{
				if (file_exists($phpbb_root_path . 'blog/styles/' . $file . '/style.' . $phpEx))
				{
					// Inside of the style.php file, add to the $available_styles array
					include($phpbb_root_path . 'blog/styles/' . $file . '/style.' . $phpEx);
				}
			}

			closedir($dh);
		}

		foreach ($available_styles as $row)
		{
			if (isset($user_settings[$user_id]) && $user_settings[$user_id]['blog_style'] == $row['value'] && isset($row['demo']) && $row['demo'])
			{
				$default_demo = $row['demo'];
			}

			$template->assign_block_vars('blog_styles', array(
				'VALUE'			=> $row['value'],
				'SELECTED'		=> (isset($user_settings[$user_id]) && $user_settings[$user_id]['blog_style'] == $row['value']) ? true : false,
				'NAME'			=> $row['name'],
				'BLOG_CSS'		=> (isset($row['blog_css']) && $row['blog_css']) ? true : false,
				'DEMO'			=> (isset($row['demo']) && $row['demo']) ? $row['demo'] : '',
			));
		}

		if (isset($user_settings[$user_id]))
		{
			decode_message($user_settings[$user_id]['description'], $user_settings[$user_id]['description_bbcode_uid']);
			$template->assign_vars(array(
				'BLOG_TITLE'		=> $user_settings[$user_id]['title'],
				'BLOG_DESCRIPTION'	=> $user_settings[$user_id]['description'],
				'DEFAULT_DEMO'		=> (isset($default_demo)) ? $default_demo : $phpbb_root_path . 'images/spacer.gif',
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

	if (class_exists('blog_plugins'))
	{
		blog_plugins::plugin_do_arg('function_resync_blog', $mode);
	}
}

?>