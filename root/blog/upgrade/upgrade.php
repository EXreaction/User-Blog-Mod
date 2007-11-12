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
		if (!count($this->selected_options))
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

		connect_check_db(true, $error, array('DRIVER' => substr($sql_db, 5)), $this->selected_options['db_prefix'], $this->selected_options['db_host'],$this->selected_options['db_user'], $this->selected_options['db_password'], $this->selected_options['db_name'], $this->selected_options['db_port'], false, false);

		if (count($error) == 1 && $error[0] == '')
		{
			$error = array();
		}

		if (!count($error))
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

		if (!count($error))
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
	function run_blog_upgrade($name)
	{
		global $phpbb_root_path, $phpEx, $old_db, $db, $config, $user, $auth;

		if (!isset($this->available_upgrades[$name]))
		{
			trigger_error('NO_MODE');
		}

		if ($this->selected_options['blogs'])
		{
			$run_blog_upgrade = true;
			include($phpbb_root_path . 'blog/upgrade/' . $name . '.' .  $phpEx);
		}
	}

	/**
	* Run the requested upgrade script
	*
	* @param string $name - The name of the upgrade script we want to show the custom upgrade options for
	*/
	function run_remaining_upgrade($name)
	{
		global $phpbb_root_path, $phpEx, $old_db, $db, $config, $user, $auth;

		if (!isset($this->available_upgrades[$name]))
		{
			trigger_error('NO_MODE');
		}

		$run_remaining_upgrade = true;
		include($phpbb_root_path . 'blog/upgrade/' . $name . '.' .  $phpEx);
	}
}
?>