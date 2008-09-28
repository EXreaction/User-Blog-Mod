<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: upgrade.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Blog Upgrade Class
*/
class blog_upgrade
{
	var $available_upgrades = array();
	var $selected_options = array();

	/**
	* Load the available upgrade options
	*/
	function blog_upgrade()
	{
		global $cache, $config, $phpbb_root_path, $phpEx, $user;

		if (!isset($config['user_blog_version']))
		{
			trigger_error(sprintf($user->lang['CLICK_INSTALL_BLOG'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", 'page=install') . '">', '</a>'));
		}

		$this->selected_options = request_var('config', array('' => ''), true);
		if (!sizeof($this->selected_options))
		{
			$cache_data = $cache->get('_blog_upgrade');
			if ($cache_data !== false)
			{
				$this->selected_options = $cache_data;
			}
		}

		$dh = @opendir($phpbb_root_path . 'blog/upgrade/');

		if ($dh)
		{
			while (($file = readdir($dh)) !== false)
			{
				if ($file != "upgrade.$phpEx" && $file != "functions.$phpEx" && substr($file, -(strlen($phpEx) + 1)) === '.' . $phpEx)
				{
					$name = substr($file, 0, -(strlen($phpEx) + 1));

					$this->available_upgrades[$name] = array();

					include($phpbb_root_path . 'blog/upgrade/' . $file);
				}
			}

			closedir($dh);
		}

		return true;
	}

	/**
	* Cleans the blog tables
	*/
	function clean_tables()
	{
		global $db;

		if ($this->selected_options['truncate'])
		{
			$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_TABLE;
			$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_ATTACHMENT_TABLE;
			$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_IN_CATEGORIES_TABLE;
			$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_POLL_OPTIONS_TABLE;
			$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_POLL_VOTES_TABLE;
			$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_RATINGS_TABLE;
			$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_REPLY_TABLE;
			$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_SUBSCRIPTION_TABLE;
			$sql_array[] = 'TRUNCATE TABLE ' . BLOG_SEARCH_WORDLIST_TABLE;
			$sql_array[] = 'TRUNCATE TABLE ' . BLOG_SEARCH_WORDMATCH_TABLE;
			//$sql_array[] = 'TRUNCATE TABLE ' . BLOG_SEARCH_RESULTS_TABLE;

			foreach ($sql_array as $sql)
			{
				$db->sql_query($sql);
			}
			unset($sql_array);
		}
	}

	/**
	* Outputs the list of available upgrade options
	*/
	function output_available_list()
	{
		global $template, $phpbb_root_path, $phpEx, $user;

		foreach($this->available_upgrades as $name => $data)
		{
			$template->assign_block_vars('convertors', array(
				'SOFTWARE'		=> $data['upgrade_title'],
				'VERSION'		=> $data['upgrade_version'],
				'AUTHOR'		=> $data['upgrade_copyright'],
				'U_CONVERT'		=> append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade&amp;stage=1&amp;mode=' . $name),
				)
			);
		}
	}

	/**
	* Get Options
	*/
	function get_options($name)
	{
		global $dbhost, $dbport, $dbname, $table_prefix;

		$options = array(
			'legend0'		=> 'DB_CONFIG',
			'db_host'		=> array('lang' => 'DB_HOST',			'type' => 'text:25:100',		'explain' => true,		'default' => $dbhost),
			'db_port'		=> array('lang' => 'DB_PORT',			'type' => 'text:25:100',		'explain' => true,		'default' => $dbport),
			'db_name'		=> array('lang' => 'DB_NAME',			'type' => 'text:25:100',		'explain' => false,		'default' => $dbname),
			'db_user'		=> array('lang' => 'DB_USERNAME',		'type' => 'text:25:100',		'explain' => false,		'default' => ''),
			'db_password'	=> array('lang' => 'DB_PASSWORD',		'type' => 'password:25:100',	'explain' => false,		'default' => ''),
			'db_prefix'		=> array('lang' => 'TABLE_PREFIX',		'type' => 'text:25:100',		'explain' => false,		'default' => $table_prefix),
			'limit'			=> array('lang' => 'LIMIT',				'type' => 'text:25:100',		'explain' => true,		'default' => 250),
			'legend1'		=> 'STAGE_ADVANCED',
			'truncate'		=> array('lang' => 'TRUNCATE_TABLES',	'type' => 'radio:yes_no',		'explain' => true,		'default' => true),
			'blogs'			=> array('lang' => 'UPGRADE_BLOGS',		'type' => 'radio:yes_no',		'explain' => false,		'default' => true),
		);

		if (isset($this->available_upgrades[$name]['custom_options']))
		{
			$options = array_merge($options, $this->available_upgrades[$name]['custom_options']);
		}

		return $options;
	}

	/**
	* Output Upgrade Options
	*
	* @param string $name - The name of the upgrade script we want to show the custom upgrade options for
	*/
	function output_upgrade_options($name)
	{
		global $user, $template;

		if (!isset($this->available_upgrades[$name]))
		{
			trigger_error('NO_MODE');
		}

		$options = $this->get_options($name);

		// this code is mostly from acp_board.php
		$new_config = array();
		foreach ($options as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$new_config[$config_key] = (isset($vars['default'])) ? $vars['default'] : '';
			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> build_cfg_template($type, $config_key, $new_config, $config_key, $vars),
				)
			);
		}
	}

	/**
	* Connect to the old database
	*/
	function old_db_connect()
	{
		global $old_db, $sql_db, $user;

		$old_db = new $sql_db();
		$old_db->sql_connect($this->selected_options['db_host'], $this->selected_options['db_user'], $this->selected_options['db_password'], $this->selected_options['db_name'], $this->selected_options['db_port']);
	}

	/**
	* Confirm Upgrade Options
	*/
	function confirm_upgrade_options($name, &$error)
	{
		global $cache, $sql_db, $user, $old_db;

		if (!isset($this->available_upgrades[$name]))
		{
			trigger_error('NO_MODE');
		}

		$options = $this->get_options($name);
		$default_options = array();
		foreach ($options as $config_key => $vars)
		{
			$default_options[$config_key] = (isset($vars['default'])) ? $vars['default'] : '';
		}

		$this->selected_options = array_merge($default_options, $this->selected_options);

		$this->selected_options['limit'] = intval($this->selected_options['limit']);
		if ($this->selected_options['limit'] < 1)
		{
			$error[] = $user->lang['LIMIT_INCORRECT'];
		}

		connect_check_db(true, $error, array('DRIVER' => substr($sql_db, 5)), $this->selected_options['db_prefix'], $this->selected_options['db_host'],$this->selected_options['db_user'], $this->selected_options['db_password'], $this->selected_options['db_name'], $this->selected_options['db_port'], false, false);

		if (sizeof($error) == 1 && $error[0] == '')
		{
			$error = array();
		}

		if (!sizeof($error))
		{
			$this->old_db_connect();
			$old_db->sql_return_on_error(true);

			foreach ($this->available_upgrades[$name]['requred_tables'] as $table)
			{
				$sql = 'SELECT * FROM ' . $this->selected_options['db_prefix'] . $table . ' LIMIT 1';
				if (!$old_db->sql_query($sql))
				{
					$error[] = sprintf($user->lang['DB_TABLE_NOT_EXIST'], $this->selected_options['db_prefix'] . $table);
				}
			}
		}

		if (!sizeof($error))
		{
			// put the options in the cache for the upgrade
			$cache->put('_blog_upgrade', $this->selected_options);
			return true;
		}

		return false;
	}

	/**
	* Run the requested blog upgrade script
	*
	* @param string $name - The name of the upgrade script we want to show the custom upgrade options for
	*/
	function run_upgrade($name)
	{
		global $phpbb_root_path, $phpEx, $old_db, $db, $config, $user, $auth, $cache;
		global $part, $part_cnt, $section, $section_cnt;

		if (!isset($this->available_upgrades[$name]))
		{
			trigger_error('NO_MODE');
		}

		$run_upgrade = true; // checked in the file
		include($phpbb_root_path . 'blog/upgrade/' . $name . '.' .  $phpEx);
	}

	/**
	* Reindex the blogs/replies
	*/
	function reindex($mode = '')
	{
		global $db, $user, $phpbb_root_path, $phpEx;
		global $part, $part_cnt, $section, $section_cnt;

		$section_cnt = 1;

		$blog_search = setup_blog_search();

		if ($mode == 'delete')
		{
			$blog_search->delete_index();
		}
		else
		{
			if ($section == 0)
			{
				$sql = 'SELECT * FROM ' . BLOGS_TABLE . '
					WHERE blog_deleted = 0
					AND blog_approved = 1
						ORDER BY blog_id DESC';
				$result = $db->sql_query_limit($sql, $this->selected_options['limit'], ($part * $this->selected_options['limit']));
				while ($row = $db->sql_fetchrow($result))
				{
					$blog_search->index('add', $row['blog_id'], 0, $row['blog_text'], $row['blog_subject'], $row['user_id']);
				}

				$sql = 'SELECT count(blog_id) AS cnt FROM ' . BLOGS_TABLE . '
					WHERE blog_deleted = 0
					AND blog_approved = 1';;
				$result = $db->sql_query($sql);
				$cnt = $db->sql_fetchrow($result);

				if ($cnt['cnt'] >= (($part + 1) * $this->selected_options['limit']))
				{
					$part++;
					$part_cnt = ceil($cnt['cnt'] / $this->selected_options['limit']);
				}
				else
				{
					$part = 0;
					$section++;
				}
			}
			else
			{
				$sql = 'SELECT * FROM ' . BLOGS_REPLY_TABLE . '
					WHERE reply_deleted = 0
					AND reply_approved = 1
						ORDER BY reply_id DESC';
				$result = $db->sql_query_limit($sql, $this->selected_options['limit'], ($part * $this->selected_options['limit']));
				while ($row = $db->sql_fetchrow($result))
				{
					$blog_search->index('add', $row['blog_id'], $row['reply_id'], $row['reply_text'], $row['reply_subject'], $row['user_id']);
				}

				$sql = 'SELECT count(reply_id) AS cnt FROM ' . BLOGS_REPLY_TABLE . '
					WHERE reply_deleted = 0
					AND reply_approved = 1';
				$result = $db->sql_query($sql);
				$cnt = $db->sql_fetchrow($result);

				if ($cnt['cnt'] >= (($part + 1) * $this->selected_options['limit']))
				{
					$part++;
					$part_cnt = ceil($cnt['cnt'] / $this->selected_options['limit']);
				}
				else
				{
					$part = 0;
					$section++;
				}
			}
		}
	}

	/**
	* Resync the data
	*/
	function resync()
	{
		global $config, $db, $user;
		global $part, $part_cnt, $section, $section_cnt;

		$blog_data = array();
		$start = ($part * $this->selected_options['limit']);
		$limit = $this->selected_options['limit'];
		$section_cnt = 3;

		// Start by selecting all blog data that we will use
		$sql = 'SELECT blog_id, blog_reply_count, blog_real_reply_count FROM ' . BLOGS_TABLE . ' ORDER BY blog_id ASC';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$blog_data[$row['blog_id']] = $row;
		}
		$db->sql_freeresult($result);
		$blog_count = sizeof($blog_data);

		$i=0;
		switch ($section)
		{
			case 0 :
				foreach($blog_data as $row)
				{
					if ($i < $start)
					{
						continue;
					}

					if ($i > ($start + $limit))
					{
						break;
					}

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
			break;
			case 1 :
				foreach($blog_data as $row)
				{
					if ($i < $start)
					{
						continue;
					}

					if ($i > ($start + $limit))
					{
						break;
					}

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
			break;
			case 2 :
				// select the users data we will need
				$sql = 'SELECT user_id, blog_count FROM ' . USERS_TABLE . ' ORDER BY user_id DESC';
				$result = $db->sql_query($sql);
				while($row = $db->sql_fetchrow($result))
				{
					if ($i < $start)
					{
						continue;
					}

					if ($i > ($start + $limit))
					{
						break;
					}

					// count all the replies (an SQL query seems the easiest way to do it)
					$sql2 = 'SELECT count(blog_id) AS total
						FROM ' . BLOGS_TABLE . '
							WHERE user_id = ' . $row['user_id'] . '
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
			break;
		}

		if ($blog_count >= (($part + 1) * $this->selected_options['limit']))
		{
			$part++;
			$part_cnt = ceil($blog_count / $this->selected_options['limit']);
		}
		else
		{
			$sql = 'SELECT blog_id  FROM ' . BLOGS_TABLE . ' WHERE blog_deleted = 0 AND blog_approved = 1';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$ids[] = $row['blog_id'];
			}
			set_config('num_blogs', sizeof($ids), true);

			if (sizeof($ids))
			{
				$sql = 'SELECT count(reply_id) AS reply_count FROM ' . BLOGS_REPLY_TABLE . '
					WHERE reply_deleted = 0
					AND reply_approved = 1
					AND ' . $db->sql_in_set('blog_id', $ids); // Make sure we only count the # of replies from non-deleted blogs.
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				set_config('num_blog_replies', $row['reply_count'], true);
			}

			$part = 0;
			$section++;
		}
	}
}
?>