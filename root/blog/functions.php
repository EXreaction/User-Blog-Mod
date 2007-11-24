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

// Make sure that if this file is accidently included more than once we don't get errors
if (!defined('BLOG_FUNCTIONS_INCLUDED'))
{
	define('BLOG_FUNCTIONS_INCLUDED', true);

	// Include the constants.php and sql_functions.php files
	include($phpbb_root_path . 'blog/data/constants.' . $phpEx);
	include($phpbb_root_path . 'blog/functions_sql.' . $phpEx);

	/**
	* Setup the blog search system
	*/
	function setup_blog_search()
	{
		global $config, $user, $phpbb_root_path, $phpEx;

		if (file_exists($phpbb_root_path . 'blog/search/' . $config['user_blog_search_type'] . '.' . $phpEx))
		{
			include($phpbb_root_path . 'blog/search/' . $config['user_blog_search_type'] . '.' . $phpEx);
			$class = 'blog_' . $config['user_blog_search_type'];
			return new $class();
		}
		else
		{
			trigger_error('BLOG_SEARCH_BACKEND_NOT_EXIST');
		}
	}

	/**
	* Generates the left side menu
	*
	* @param int $user_id If we are building it for a certain user, send the uid here
	*/
	function generate_menu($user_id = false)
	{
		global $config, $db, $template;
		global $user_data, $blog_plugins;

		$extra = $user_menu_extra = '';
		$temp = compact('user_id', 'user_menu_extra', 'extra');
		$blog_plugins->plugin_do_arg_ref('function_generate_menu', $temp);
		extract($temp);

		if ($user_id)
		{
			$template->assign_vars($user_data->handle_user_data($user_id));
			$user_data->handle_user_data($user_id, 'custom_fields');

			$template->assign_vars(array(
				'S_USER_MENU'		=> true,
				'USER_MENU_EXTRA'	=> $user_menu_extra,
			));
		}

		if ($config['user_blog_search'])
		{
			$template->assign_vars(array(
				'S_DISPLAY_BLOG_SEARCH'	=> true,
				'U_BLOG_SEARCH'			=> blog_url(false, false, false, array('page' => 'search')),
			));
		}
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

		if ($mode == 'edit' && isset($blog_data->blog[$blog_id]))
		{
			$perm_guest = (request_var('perm_guest', -1) != -1) ? request_var('perm_guest', -1) : $blog_data->blog[$blog_id]['perm_guest'];
			$perm_registered = (request_var('perm_registered', -1) != -1) ? request_var('perm_registered', -1) : $blog_data->blog[$blog_id]['perm_registered'];
			$perm_foe = (request_var('perm_foe', -1) != -1) ? request_var('perm_foe', -1) : $blog_data->blog[$blog_id]['perm_foe'];
			$perm_friend = (request_var('perm_friend', -1) != -1) ? request_var('perm_friend', -1) : $blog_data->blog[$blog_id]['perm_friend'];
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
	* URL handler
	*/
	function blog_url($user_id, $blog_id = false, $reply_id = false, $url_data = array(), $extra_data = array(), $force_no_seo = false)
	{
		global $config, $phpbb_root_path, $phpEx, $user, $_SID;
		global $blog_data, $reply_data, $user_data;

		// don't call the generate_board_url function a whole bunch of times, get it once and keep using it!
		static $start_url = '';
		$start_url = ($start_url == '') ? generate_board_url() . '/' : $start_url;
		$extras = $anchor = '';

		if ($config['user_blog_seo'] && !$force_no_seo)
		{
			// We will be replacing spaces and dashes in the url with an underscore.  Just so things don't get screwed up if the user has something like " start-10" in the title. :P
			$match = array(' ', '-');
			$title_match ='/(&amp;|&lt;|&gt;|&quot;|[^a-zA-Z0-9\s_])/'; // Replace HTML Entities, and non alphanumeric/space/underscore characters
			$replace_page = true; // match everything except the page if this is set to false

			if (!isset($url_data['page']))
			{
				if ($user_id == $user->data['user_id'])
				{
					$url_data['page'] = $user->data['username'];
				}
				else if ($user_id != false && isset($extra_data['username']))
				{
					$url_data['page'] = $extra_data['username'];
				}
				else if ($user_id != false && !empty($user_data))
				{
					if (!isset($user_data->user[$user_id]))
					{
						$user_data->get_user_data($user_id);
					}
					$url_data['page'] = $user_data->user[$user_id]['username'];
				}

				// Do not do the str_replace for the username!  It would break it! :P
				$replace_page = false;
			}
			else
			{
				$url_data['u'] = ($user_id) ? $user_id : '*skip*';
				$url_data['b'] = ($blog_id) ? $blog_id : '*skip*';
				$url_data['r'] = ($reply_id) ? $reply_id : '*skip*';
			}

			if ($reply_id)
			{
				$url_data['r'] = $reply_id;
				$url_data['anchor'] = 'r' . $reply_id;
				if (!isset($url_data['mode']))
				{
					if (!empty($reply_data) && array_key_exists($reply_id, $reply_data->reply))
					{
						$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', $reply_data->reply[$reply_id]['reply_subject']));
					}
					else if (array_key_exists('reply_subject', $extra_data))
					{
						$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', $extra_data['reply_subject']));
					}
				}
			}
			else if ($blog_id)
			{
				$url_data['b'] = $blog_id;
				if (!isset($url_data['mode']))
				{
					if (!empty($blog_data) && array_key_exists($blog_id, $blog_data->blog))
					{
						$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', $blog_data->blog[$blog_id]['blog_subject']));
					}
					else if (array_key_exists('blog_subject', $extra_data))
					{
						$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', $extra_data['blog_subject']));
					}
				}
			}

			if (isset($url_data['anchor']))
			{
				$anchor = '#' . $url_data['anchor'];
			}

			if (count($url_data))
			{
				foreach ($url_data as $name => $value)
				{
					if ($name == 'page' || $name == 'mode' || $name == 'anchor' || $value == '*skip*')
					{
						continue;
					}
					$extras .= '_' . str_replace($match, '_', $name) . '-' . str_replace($match, '_', $value);
				}
			}

			// Add the Session ID if required
			if ($_SID)
			{
				$extras .= "_sid-{$_SID}";
			}

			if (isset($url_data['page']))
			{
				if ($replace_page)
				{
					$url_data['page'] = str_replace($match, '_', $url_data['page']);
				}

				if (isset($url_data['mode']))
				{
					$url_data['mode'] = str_replace($match, '_', $url_data['mode']);
					$return = "blog/{$url_data['page']}/{$url_data['mode']}{$extras}.html{$anchor}";
				}
				else
				{
					if ($extras != '')
					{
						$return = "blog/{$url_data['page']}/index{$extras}.html{$anchor}";
					}
					else
					{
						$return = "blog/{$url_data['page']}/";
					}
				}
			}
			else
			{
				$return = "blog/index{$extras}.html{$anchor}";
			}

			if (isset($return))
			{
				return $start_url . $return;
			}
		}

		if (count($url_data))
		{
			foreach ($url_data as $name => $var)
			{
				// Do not add the blog/reply/user id to the url string, they get added later
				if ($name == 'b' || $name == 'u' || $name == 'r' || $var == '*skip*')
				{
					continue;
				}

				$extras .= '&amp;' . $name . '=' . $var;
			}
		}

		$extras .= (($user_id) ? '&amp;u=' . $user_id : '');
		$extras .= (($blog_id) ? '&amp;b=' . $blog_id : '');
		$extras .= (($reply_id) ? '&amp;r=' . $reply_id . '#r' . $reply_id: '');
		$extras = substr($extras, 5);
		$url = $phpbb_root_path . 'blog.' . $phpEx;
		return append_sid($url, $extras);
	}

	/**
	* generates the basic URL's used by this mod
	*/
	function generate_blog_urls()
	{
		global $phpbb_root_path, $phpEx, $config, $user;
		global $blog_id, $reply_id, $user_id, $start;
		global $blog_data, $reply_data, $user_data, $blog_urls, $blog_plugins;

		$self_data = $_GET;

		$blog_urls = array(
			'main'				=> blog_url(false),
			'self'				=> blog_url($user_id, $blog_id, $reply_id, $self_data),
			'self_print'		=> blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('view' => 'print'))),
			'subscribe'			=> ($config['user_blog_subscription_enabled'] && ($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != ANONYMOUS) ? blog_url($user_id, $blog_id, false, array('page' => 'subscribe')) : '',
			'unsubscribe'		=> ($config['user_blog_subscription_enabled'] && ($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != ANONYMOUS) ? blog_url($user_id, $blog_id, false, array('page' => 'unsubscribe')) : '',

			'add_blog'			=> blog_url(false, false, false, array('page' => 'blog', 'mode' => 'add')),
			'add_reply'			=> ($blog_id) ? blog_url($user_id, $blog_id, false, array('page' => 'reply', 'mode' => 'add')) : '',

			'view_blog'			=> ($blog_id != 0) ? blog_url($user_id, $blog_id) : '',
			'view_reply'		=> ($reply_id != 0) ? blog_url($user_id, $blog_id, $reply_id) : '',
			'view_user'			=> ($user_id != 0) ? blog_url($user_id) : false,
			'view_user_deleted'	=> ($user_id != 0) ? blog_url($user_id, false, false, array('mode' => 'deleted')) : false,
			'view_user_self'	=> blog_url($user->data['user_id']),
		);

		$blog_urls['self_minus_print'] = blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('view' => '*skip*')));
		$blog_urls['self_minus_start'] = blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('view' => '*skip*')));
		$blog_urls['start_zero'] = blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('start' => '*start*')));

		$blog_plugins->plugin_do('function_generate_blog_urls');
	}

	/**
	* Updates user settings
	*/
	function update_user_blog_settings($user_id, $data, $resync = false)
	{
		global $db, $user_settings;

		get_user_settings($user_id);

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
			);

			$sql = 'INSERT INTO ' . BLOGS_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
			$db->sql_query($sql);
		}
		else
		{
			$sql = 'UPDATE ' . BLOGS_USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE user_id = \'' . intval($user_id) . '\'';
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
	}

	/**
	* Create the breadcrumbs
	*
	* @param string $crumb_lang The last language option in the breadcrumbs
	* @param string $crumb_url The last url option in the breadcrumbs (this will be set to the current URL if this is blank)
	*/
	function generate_blog_breadcrumbs($crumb_lang = '', $crumb_url = '')
	{
		global $template, $user;
		global $page, $username, $blog_id, $reply_id;
		global $blog_data, $reply_data, $user_data, $blog_urls;

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'		=> $user->lang['USER_BLOGS'],
			'U_VIEW_FORUM'		=> $blog_urls['main'],
		));

		if ($username != '')
		{
			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'		=> sprintf($user->lang['USERNAMES_BLOGS'], $username),
				'U_VIEW_FORUM'		=> $blog_urls['view_user'],
			));

			if ($blog_id != 0)
			{
				$template->assign_block_vars('navlinks', array(
					'FORUM_NAME'		=> censor_text($blog_data->blog[$blog_id]['blog_subject']),
					'U_VIEW_FORUM'		=> $blog_urls['view_blog'],
				));

				if ($reply_id != 0 && $page == 'reply')
				{
					$template->assign_block_vars('navlinks', array(
						'FORUM_NAME'		=> (censor_text($reply_data->reply[$reply_id]['reply_subject']) != '') ? censor_text($reply_data->reply[$reply_id]['reply_subject']) : $user->lang['UNTITLED_REPLY'],
						'U_VIEW_FORUM'		=> $blog_urls['view_reply'],
					));
				}
			}
		}

		if ($crumb_lang != '')
		{
			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'		=> $crumb_lang,
				'U_VIEW_FORUM'		=> ($crumb_url) ? $crumb_url : $blog_urls['self'],
			));
		}
	}

	/**
	* Gets Zebra (friend/foe)  info
	*
	* @param int|bool $uid The user_id we will grab the zebra data for.  If this is false we will use $user->data['user_id']
	*/
	function get_zebra_info($user_ids, $reverse_lookup = false)
	{
		global $config, $user, $db;
		global $zebra_list, $reverse_zebra_list;

		if (!$config['user_blog_enable_zebra'])
		{
			return;
		}

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

	/**
	* Add the links in the custom profile fields to view the users' blog
	*
	* @param int $user_id The users id.
	* @param string $block The name of the custom profile block we insert it into
	* @param mixed $user_data Extra data on the user.  If blog_count is supplied in $user_data we can skip 1 sql query (if $grab_from_db is true)
	* @param bool $grab_from_db If it is true we will run the query to find out how many blogs the user has if the data isn't supplied in $user_data, otherwise we won't and just display the link alone.
	* @param bool $force_output is if you would like to force the output of the links for the single requested section
	*/
	function add_blog_links($user_id, $block, $user_data = false, $grab_from_db = false, $force_output = false)
	{
		global $db, $template, $user, $phpbb_root_path, $phpEx, $config;
		global $reverse_zebra_list, $user_settings;

		if (!$config['user_blog_enable'] || $user_id == ANONYMOUS)
		{
			return;
		}

		if (!isset($user->lang['BLOG']))
		{
			$user->add_lang('mods/blog');
		}

		if (isset($user_settings[$user_id]))
		{
			$no_perm = false;

			if ($user->data['user_id'] == ANONYMOUS)
			{
				if ($user_settings[$user_id]['perm_guest'] == 0)
				{
					$no_perm = true;
				}
			}
			else
			{
				if ($config['user_blog_enable_zebra'])
				{
					if (isset($reverse_zebra_list[$user->data['user_id']]['foe']) && in_array($user_id, $reverse_zebra_list[$user->data['user_id']]['foe']))
					{
						if ($user_settings[$user_id]['perm_foe'] == 0)
						{
							$no_perm = true;
						}
					}
					else if (isset($reverse_zebra_list[$user->data['user_id']]['friend']) && in_array($user_id, $reverse_zebra_list[$user->data['user_id']]['friend']))
					{
						if ($user_settings[$user_id]['perm_friend'] == 0)
						{
							$no_perm = true;
						}
					}
					else
					{
						if ($user_settings[$user_id]['perm_registered'] == 0)
						{
							$no_perm = true;
						}
					}
				}
				else
				{
					if ($user_settings[$user_id]['perm_registered'] == 0)
					{
						$no_perm = true;
					}
				}
			}

			if ($no_perm)
			{
				if ($config['user_blog_always_show_blog_url'] || $force_output)
				{
					$template->assign_block_vars($block, array(
						'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
						'PROFILE_FIELD_VALUE'		=> '<a href="' . blog_url($user_id, false, false, array(), array('username' => $user_data['username'])) . '">' . $user->lang['VIEW_BLOGS'] . ' (0)</a>',
					));

					return;
				}
				else
				{
					return;
				}
			}
		}

		// if they are not an anon user, and they blog_count row isn't set grab that data from the db.
		if ($user_id > 1 && (!isset($user_data['blog_count']) || !isset($user_data['username'])) && $grab_from_db)
		{
			$sql = 'SELECT username, blog_count FROM ' . USERS_TABLE . ' WHERE user_id = \'' . intval($user_id) . '\'';
			$result = $db->sql_query($sql);
			$user_data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}
		else if (!isset($user_data['blog_count']))
		{
			$user_data['blog_count'] = -1;
		}
		
		if ($user_data['blog_count'] > 0 || (($config['user_blog_always_show_blog_url'] || $force_output) && $user_data['blog_count'] >= 0))
		{
			$template->assign_block_vars($block, array(
				'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
				'PROFILE_FIELD_VALUE'		=> '<a href="' . blog_url($user_id, false, false, array(), array('username' => $user_data['username'])) . '">' . $user->lang['VIEW_BLOGS'] . ' (' .$user_data['blog_count'] . ')</a>',
			));
		}
		else if (!$grab_from_db && $user_data['blog_count'] == -1)
		{
			$template->assign_block_vars($block, array(
				'PROFILE_FIELD_NAME'		=> $user->lang['BLOG'],
				'PROFILE_FIELD_VALUE'		=> '<a href="' . blog_url($user_id, false, false, array(), array('username' => $user_data['username'])) . '">' . $user->lang['VIEW_BLOGS'] . '</a>',
			));
		}
	}

	/**
	* Gets user settings
	*
	* @param int $user_ids array of user_ids to get the settings for
	*/
	function get_user_settings($user_ids)
	{
		global $cache, $db, $user_settings;

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
			$sql = 'SELECT * FROM ' . BLOGS_USERS_TABLE . ' WHERE ' . $db->sql_in_set('user_id', $to_query);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$cache->put('_blog_settings_' . $row['user_id'], $row);

				$user_settings[$row['user_id']] = $row;
			}
			$db->sql_freeresult($result);
		}
	}

	/**
	* Handles blog view/reply permissions (those set by users)
	*/
	function handle_user_blog_permissions($blog_id, $user_id = false, $mode = 'read')
	{
		global $auth, $cache, $config, $db, $user;
		global $blog_data, $zebra_list, $blog_plugins, $user_settings;

		if (!$config['user_blog_user_permissions'])
		{
			return true;
		}

		if ($blog_id !== false && isset($blog_data->blog[$blog_id]))
		{
			$var = $blog_data->blog[$blog_id];
			$user_id = $blog_data->blog[$blog_id]['user_id'];
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
	* Handles updates to the cache
	*
	* @param string $mode
	* @param int $user_id
	*/
	function handle_blog_cache($mode, $user_id = 0)
	{
		global $cache, $auth, $user, $db, $blog_plugins;

		$blog_plugins->plugin_do('function_handle_blog_cache');

		if ($mode == 'blog' || strpos($mode, 'blog'))
		{
			$cache->destroy('sql', BLOGS_TABLE);

			if ($user_id === false)
			{
				$sql = 'SELECT user_id FROM ' . USERS_TABLE;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$cache->destroy('_blog_archive' . $row['user_id']);
					$cache->destroy('_blog_settings_' . $row['user_id']);
					$cache->destroy('_blog_subscription' . $row['user_id']);
				}
			}
			else
			{
				$cache->destroy("_blog_archive{$user_id}");
				$cache->destroy('_blog_settings_' . $user_id);
				$cache->destroy("_blog_subscription{$user_id}");
			}
		}

		switch ($mode)
		{
			case 'new_blog' :
				if ($auth->acl_get('u_blognoapprove'))
				{
					$cache->destroy('all_blog_ids');
				}
				else
				{
					$cache->destroy('all_unapproved_blog_ids');
				}
			break;
			case 'approve_blog' :
				$cache->destroy('all_blog_ids');
				$cache->destroy('all_unapproved_blog_ids');
			break;
			case 'delete_blog' :
				$cache->destroy('all_blog_ids');
				$cache->destroy('all_deleted_blog_ids');
			break;
			case 'blog' :
				$cache->destroy('all_blog_ids');
				$cache->destroy('all_unapproved_blog_ids');
				$cache->destroy('all_deleted_blog_ids');
			break;
			case 'subscription' :
				$cache->destroy("_blog_subscription{$user_id}");
			break;
			case 'plugins' :
				$cache->destroy('_blog_plugins');
			default :
				$blog_plugins->plugin_do_arg('function_handle_blog_cache_mode', $mode);
		}
	}

	/**
	* Get subscription info
	*
	* Grabs subscription info from the DB if not already in the cache and finds out if the user is subscribed to the blog/user.
	*
	* @param int|bool $blog_id The blog_id to check, set to false if we are checking a user_id.
	* @param int|bool $user_id The user_id to check, set to false if we are checking a blog_id.
	*
	* @return Returns true if the user is subscribed to the blog or user, false if not.
	*/
	function get_subscription_info($blog_id, $user_id = false)
	{
		global $db, $user, $cache, $config, $blog_plugins;

		if (!$config['user_blog_subscription_enabled'])
		{
			return false;
		}

		// attempt to get the data from the cache
		$subscription_data = $cache->get('_blog_subscription' . $user->data['user_id']);

		// grab data from the db if it isn't cached
		if ($subscription_data === false)
		{
			$sql = 'SELECT * FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
					WHERE sub_user_id = \'' . $user->data['user_id'] . '\'';
			$result = $db->sql_query($sql);
			$subscription_data = $db->sql_fetchrowset($result);
			$cache->put('_blog_subscription' . $user->data['user_id'], $subscription_data);
		}

		if (count($subscription_data))
		{
			$blog_plugins->plugin_do('function_get_subscription_info');

			if ($user_id !== false)
			{
				foreach ($subscription_data as $row)
				{
					if ($row['user_id'] == $user_id)
					{
						unset($subscription_data);
						return true;
					}
				}
			}
			else if ($blog_id !== false)
			{
				foreach ($subscription_data as $row)
				{
					if ($row['blog_id'] == $blog_id)
					{
						unset($subscription_data);
						return true;
					}
				}
			}
		}

		unset($subscription_data);
		return false;
	}

	/**
	* Pagination routine, generates page number sequence
	* tpl_prefix is for using different pagination blocks at one page
	*/
	function generate_blog_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = false, $tpl_prefix = '')
	{
		global $config, $template, $user;

		if (strpos($base_url, '#'))
		{
			$base_url = substr($base_url, 0, strpos($base_url, '#'));
		}

		if ($config['user_blog_seo'])
		{
			// Make sure $per_page is a valid value
			$per_page = ($per_page <= 0) ? 1 : $per_page;

			$seperator = '<span class="page-sep">' . $user->lang['COMMA_SEPARATOR'] . '</span>';
			$total_pages = ceil($num_items / $per_page);

			if ($total_pages == 1 || !$num_items)
			{
				return false;
			}

			$on_page = floor($start_item / $per_page) + 1;
			$page_string = ($on_page == 1) ? '<strong>1</strong>' : '<a href="' . str_replace('*start*', '0', $base_url) . '">1</a>';

			if ($total_pages > 5)
			{
				$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
				$end_cnt = max(min($total_pages, $on_page + 4), 6);

				$page_string .= ($start_cnt > 1) ? ' ... ' : $seperator;

				for ($i = $start_cnt + 1; $i < $end_cnt; $i++)
				{
					$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . str_replace('*start*', (($i - 1) * $per_page), $base_url) . '">' . $i . '</a>';
					if ($i < $end_cnt - 1)
					{
						$page_string .= $seperator;
					}
				}

				$page_string .= ($end_cnt < $total_pages) ? ' ... ' : $seperator;
			}
			else
			{
				$page_string .= $seperator;

				for ($i = 2; $i < $total_pages; $i++)
				{
					$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . str_replace('*start*', (($i - 1) * $per_page), $base_url) . '">' . $i . '</a>';
					if ($i < $total_pages)
					{
						$page_string .= $seperator;
					}
				}
			}

			$page_string .= ($on_page == $total_pages) ? '<strong>' . $total_pages . '</strong>' : '<a href="' . str_replace('*start*', (($i - 1) * $per_page), $base_url) . '">' . $total_pages . '</a>';

			if ($add_prevnext_text)
			{
				if ($on_page != 1) 
				{
					$page_string = '<a href="' . str_replace('*start*', (($on_page - 2) * $per_page), $base_url) . '">' . $user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
				}

				if ($on_page != $total_pages)
				{
					$page_string .= '&nbsp;&nbsp;<a href="' . str_replace('*start*', ($on_page * $per_page), $base_url) . '">' . $user->lang['NEXT'] . '</a>';
				}
			}

			$template->assign_vars(array(
				$tpl_prefix . 'BASE_URL'	=> $base_url,
				$tpl_prefix . 'PER_PAGE'	=> $per_page,

				$tpl_prefix . 'PREVIOUS_PAGE'	=> ($on_page == 1) ? '' : str_replace('*start*', (($on_page - 2) * $per_page), $base_url),
				$tpl_prefix . 'NEXT_PAGE'		=> ($on_page == $total_pages) ? '' : str_replace('*start*', ($on_page  * $per_page), $base_url),
				$tpl_prefix . 'TOTAL_PAGES'		=> $total_pages)
			);

			return $page_string;
		}
		else
		{
			return generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text, $tpl_prefix);
		}
	}

	/**
	* Blog Meta Refresh (the normal one does not work with the SEO Url's
	*/
	function blog_meta_refresh($time, $url, $instant = false)
	{
		global $config, $template, $user, $user_settings;

		if ($instant || (isset($user_settings[$user->data['user_id']]['instant_redirect']) && $user_settings[$user->data['user_id']]['instant_redirect']))
		{
			$time = 0;
			header('Location: ' . str_replace('&amp;', '&', $url));
		}

		if ($config['user_blog_seo'])
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '" />')
			);
		}
		else
		{
			meta_refresh($time, $url);
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

		$blog_plugins->plugin_do('permissions_start');

		switch ($page)
		{
			case 'blog' :
				switch ($mode)
				{
					case 'add' :
						$is_auth = ($auth->acl_get('u_blogpost')) ? true : false;
						break;
					case 'edit' :
						$is_auth = ($user->data['user_id'] != ANONYMOUS && ($auth->acl_get('u_blogedit') && ($user->data['user_id'] == $blog_data->blog[$blog_id]['user_id']) || $auth->acl_get('m_blogedit'))) ? true : false;
						break;
					case 'delete' :
						if ($blog_data->blog[$blog_id]['blog_deleted'] == 0 || $auth->acl_get('a_blogdelete'))
						{
							$is_auth = ($user->data['user_id'] != ANONYMOUS && (($auth->acl_get('u_blogdelete') && $user->data['user_id'] == $blog_data->blog[$blog_id]['user_id']) || $auth->acl_get('m_blogdelete') || $auth->acl_get('a_blogdelete'))) ? true : false;
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
						$is_auth = ($user->data['user_id'] != ANONYMOUS && (($auth->acl_get('u_blogreplyedit') && $user->data['user_id'] == $reply_data->reply[$reply_id]['user_id']) || ($auth->acl_get('u_blogmoderate') && $user->data['user_id'] == $blog_data->blog[$blog_id]['user_id']) || $auth->acl_get('m_blogreplyedit'))) ? true : false;
						break;
					case 'delete' :
						if ($reply_data->reply[$reply_id]['reply_deleted'] == 0 || $auth->acl_get('a_blogreplydelete'))
						{
							$is_auth = ($user->data['user_id'] != ANONYMOUS && (($auth->acl_get('u_blogreplydelete') && $user->data['user_id'] == $reply_data->reply[$reply_id]['user_id']) || ($auth->acl_get('u_blogmoderate') && $user->data['user_id'] == $blog_data->blog[$blog_id]['user_id']) || $auth->acl_gets('a_blogreplydelete', 'm_blogreplydelete'))) ? true : false;
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
						break;
				}
				break;
			case 'mcp' :
				$is_auth = ($auth->acl_gets('m_blogapprove', 'acl_m_blogreport')) ? true : false;
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

		$blog_plugins->plugin_do_arg_ref('permissions_end', $is_auth);

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
}
?>