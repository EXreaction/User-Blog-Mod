<?php
/**
 *
 * @package phpBB3 User Blog
 * @copyright (c) 2007 EXreaction, Lithium Studios
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 *
 */

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * User data class
 *
 * For grabbing/handling all user data
 */
class user_data
{
	// this is our large array holding all the data
	var $user = array();

	// this holds a user_queue of the user's data when requesting replies so we can cut down on queries
	var $user_queue = array();

	/**
	 * Get user data
	 *
	 * grabs the data on the user and places it in the $this->user array
	 *
	 * @param int|bool $id The user_id (or multiple user_ids if given an array) of the user we want to grab the data for
	 * @param bool $user_queue If user_queue is true then we just grab the user_ids from the user_queue, otherwise we select data from $id.
	 */
	function get_user_data($id, $user_queue = false)
	{
		global $user, $db, $phpbb_root_path, $phpEx, $config, $auth, $cp;
		global $blog_data, $reply_data, $user_founder, $blog_plugins;

		// if we are using the user_queue, set $user_id as that for consistency
		if ($user_queue)
		{
			$id = $this->user_queue;
		}

		// if the $user_id isn't an array, make it one for consistency
		if (!is_array($id))
		{
			$id = array($id);
		}

		if (!count($id))
		{
			return;
		}

		$blog_plugins->plugin_do('user_data_start');

		// this holds the user_id's we will query
		$users_to_query = array();

		foreach ($id as $i)
		{
			if ( (!array_key_exists($i, $this->user)) && (!in_array($i, $users_to_query)) )
			{
				array_push($users_to_query, $i);
			}
		}

		if (!count($users_to_query))
		{
			return;
		}

		// Grab all profile fields from users in id cache for later use - similar to the poster cache
		if ($config['user_blog_custom_profile_enable'])
		{
			$profile_fields_cache = $cp->generate_profile_fields_template('grab', $users_to_query);
		}

		// Grab user status information
		$status_data = array();
		$sql = 'SELECT session_user_id, MAX(session_time) AS online_time, MIN(session_viewonline) AS viewonline
			FROM ' . SESSIONS_TABLE . '
				WHERE ' . $db->sql_in_set('session_user_id', $users_to_query) . '
					GROUP BY session_user_id';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$status_data[$row['session_user_id']] = $row;
		}
		$db->sql_freeresult($result);
		$update_time = $config['load_online_time'] * 60;

		// Get the rest of the data on the users and parse everything we need
		$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $db->sql_in_set('user_id', $users_to_query);
		$blog_plugins->plugin_do_arg('user_data_sql', $sql);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$user_id = $row['user_id'];

			$blog_plugins->plugin_do_arg('user_data_while', $row);

			// view profile link
			$row['view_profile'] = append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=viewprofile&amp;u=" . $user_id);
	
			// Full username, with colour
			$row['username_full'] = get_username_string('full', $user_id, $row['username'], $row['user_colour']);
	
			// format the color correctly
			$row['user_colour'] = get_username_string('colour', $user_id, $row['username'], $row['user_colour']);

			// Status
			$row['status'] = (isset($status_data[$user_id]) && time() - $update_time < $status_data[$user_id]['online_time'] && (($status_data[$user_id]['viewonline'] && $row['user_allow_viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
	
			// Avatar
			$row['avatar'] = get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']);
	
			// Rank
			get_user_rank($row['user_rank'], $row['user_posts'], $row['rank_title'], $row['rank_img'], $row['rank_img_src']);
	
			// IM Links
			$row['aim_url'] = ($row['user_aim']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=aim&amp;u=$user_id") : '';
			$row['icq_url'] = ($row['user_icq']) ? 'http://www.icq.com/people/webmsg.php?to=' . $row['user_icq'] : '';
			$row['jabber_url'] = ($row['user_jabber']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=jabber&amp;u=$user_id") : '';
			$row['msn_url'] = ($row['user_msnm']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=msnm&amp;u=$user_id") : '';
			$row['yim_url'] = ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg' : '';
	
			// PM and email links
			$row['email_url'] = ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;u=$user_id")  : (($config['board_hide_emails'] && !$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']);
			$row['pm_url'] = ($row['user_id'] != ANONYMOUS && $config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($row['user_allow_viewemail'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))) ? append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&amp;mode=compose&amp;u=$user_id") : '';

			// Signature
			if ($config['allow_sig'] && $user->optionget('viewsigs') && $row['user_sig'] != '')
			{
				$row['user_sig'] = generate_text_for_display($row['user_sig'], $row['user_sig_bbcode_uid'], $row['user_sig_bbcode_bitfield'], 7);
			}
			else
			{
				$row['user_sig'] = '';
			}

			// get the custom profile fields if the admin wants them
			if ($config['user_blog_custom_profile_enable'])
			{
				$row['cp_row'] = (isset($profile_fields_cache[$user_id])) ? $cp->generate_profile_fields_template('show', false, $profile_fields_cache[$user_id]) : array();
			}

			// now lets put everything in the user array
			$this->user[$user_id] = $row;
		}
		$db->sql_freeresult($result);

		// if we did use the user_queue, reset it
		if ($user_queue)
		{
			unset($this->user_queue);
			$this->user_queue = array();
		}
	}

	// Gets the user_id from the username
	function get_id_by_username($username)
	{
		global $db;

		$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE username_clean = \'' . $db->sql_escape(utf8_clean_string($username)) . '\'';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return $row['user_id'];
	}
	
	// prepares the user data for output to the template, and outputs the custom profile rows when requested
	// Mostly for shortenting up code
	function handle_user_data($user_id, $output_custom = false)
	{
		global $phpbb_root_path, $phpEx, $user, $auth, $config, $template;
		global $blog_data, $reply_data, $user_founder, $foe_list, $blog_plugins;

		if ($output_custom == false)
		{
			$output_data = array(
				'USER_ID'			=> $user_id,

				'AVATAR'			=> $this->user[$user_id]['avatar'],
				'POSTER_FROM'		=> $this->user[$user_id]['user_from'],
				'POSTER_JOINED'		=> $user->format_date($this->user[$user_id]['user_regdate']),
				'POSTER_POSTS'		=> $this->user[$user_id]['user_posts'],
				'RANK_IMG'			=> str_replace('img src="', 'img src="' . $phpbb_root_path, $this->user[$user_id]['rank_img']),
				'RANK_IMG_SRC'		=> $this->user[$user_id]['rank_img_src'],
				'RANK_TITLE'		=> $this->user[$user_id]['rank_title'],
				'SIGNATURE'			=> $this->user[$user_id]['user_sig'],
				'STATUS_IMG'		=> (($this->user[$user_id]['status']) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
				'USERNAME'			=> $this->user[$user_id]['username'],
				'USER_COLOUR'		=> $this->user[$user_id]['user_colour'],
				'USER_FULL'			=> $this->user[$user_id]['username_full'],
				'USER_FOE'			=> (in_array($user_id, $foe_list)) ? true : false,

				'L_USER_FOE'		=> sprintf($user->lang['POST_FOE'], '<a href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=viewprofile&amp;u=$user_id") . '">' . $this->user[$user_id]['username_full'] . '</a>'),

				'U_AIM'				=> $this->user[$user_id]['aim_url'],
				'U_DELETED_LINK'	=> ($auth->acl_get('m_blogreplydelete') || $user_founder) ? '<a href="' . blog_url($user_id, false, false, array('mode' => 'deleted')) . '">' . $user->lang['VIEW_DELETED_BLOGS'] . '</a>' : '',
				'U_EMAIL'			=> $this->user[$user_id]['email_url'],
				'U_ICQ'				=> $this->user[$user_id]['icq_url'],
				'U_JABBER'			=> $this->user[$user_id]['jabber_url'],
				'U_MSN'				=> $this->user[$user_id]['msn_url'],
				'U_PM'				=> $this->user[$user_id]['pm_url'],
				'U_VIEW_PROFILE'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=viewprofile&amp;u=$user_id"),
				'U_WWW'				=> $this->user[$user_id]['user_website'],
				'U_YIM'				=> $this->user[$user_id]['yim_url'],

				'S_CUSTOM_FIELDS'	=> (isset($this->user[$user_id]['cp_row']['blockrow'])) ? true : false,
				'S_ONLINE'			=> $this->user[$user_id]['status'],

				'USER_EXTRA'		=> '',
			);

			$blog_plugins->plugin_do_arg('user_handle_data', $output_data);

			return ($output_data);
		}
		else 
		{
			$args = array('output_custom' => $output_custom, 'user_id' => $user_id);
			$blog_plugins->plugin_do_arg('user_handle_data_cp', $args);

			if ($config['user_blog_custom_profile_enable'])
			{	
				// output the custom profile fields
				if (isset($this->user[$user_id]['cp_row']['blockrow']))
				{
					foreach ($this->user[$user_id]['cp_row']['blockrow'] as $row)
					{
						$template->assign_block_vars($output_custom, array(
							'PROFILE_FIELD_NAME'	=> $row['PROFILE_FIELD_NAME'],
							'PROFILE_FIELD_VALUE'	=> $row['PROFILE_FIELD_VALUE'],
						));
					}
				}
			}

			// add the blog links in the custom fields
			add_blog_links($user_id, $output_custom, $this->user[$user_id]);
		}
	}
}
?>