<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: plugins.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Blog Plugins Class
*/
class blog_plugins
{
	public static $plugins = array();
	public static $available_plugins = array();
	private static $to_do = array();

	/**
	* Constructor
	*
	* Load all installed and enabled plugins
	*/
	public function __construct()
	{
		global $cache, $config, $db, $phpbb_root_path, $phpEx, $blog_plugins_path, $table_prefix, $user;

		if (!isset($config['user_blog_enable_plugins']) || !$config['user_blog_enable_plugins'])
		{
			return false;
		}

		// Just in case it is not set we will use the default.
		if (!$blog_plugins_path)
		{
			$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';
		}

		if (($cache_data = $cache->get('_blog_plugins')) === false)
		{
			if (!defined('BLOGS_PLUGINS_TABLE'))
			{
				include($phpbb_root_path . 'blog/includes/constants.' . $phpEx);
			}
			$sql = 'SELECT * FROM ' . BLOGS_PLUGINS_TABLE . ' ORDER BY plugin_id ASC';
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result))
			{
				self::$plugins[$row['plugin_name']] = $row;
			}

			$cache->put('_blog_plugins', self::$plugins);
		}
		else
		{
			self::$plugins = $cache_data;
		}
		unset($cache_data);

		foreach (self::$plugins as $row)
		{
			$name = $row['plugin_name']; // this is checked in the plugin file

			if ($row['plugin_enabled'] && file_exists($blog_plugins_path . 'info/info_' . $name . '.' . $phpEx))
			{
				include($blog_plugins_path . 'info/info_' . $name . '.' . $phpEx);
			}
		}

		return true;
	}

	/**
	* Load all available plugins
	*/
	public static function load_all_plugins()
	{
		global $cache, $config, $db, $phpbb_root_path, $phpEx, $blog_plugins_path, $table_prefix, $user;

		if (!isset($config['user_blog_enable_plugins']) || !$config['user_blog_enable_plugins'])
		{
			return false;
		}

		$dh = @opendir($blog_plugins_path . 'info/');

		if ($dh)
		{
			while (($file = readdir($dh)) !== false)
			{
				if (strpos($file, 'info_') === 0 && substr($file, -(strlen($phpEx) + 1)) === '.' . $phpEx)
				{
					$name = substr($file, 5, -(strlen($phpEx) + 1));

					if (!array_key_exists($name, self::$available_plugins))
					{
						self::$available_plugins[$name] = array();

						include($blog_plugins_path . 'info/' . substr($file, 0, -(strlen($phpEx) + 1)) . '.' . $phpEx);
					}
				}
			}

			closedir($dh);
		}

		return true;
	}

	/**
	* Parse a template and return the parsed text
	*
	* This function should be used for ALL plugins that normally use $template->assign_display to output data with the plugin system.
	* This checks to verify that the template file exists in the current template path, and, if it does not, it uses the default one from prosilver.
	*/
	public static function parse_template($template_file)
	{
		global $blog_style, $blog_template, $phpbb_root_path, $template, $user;
		static $tpl_id = 0;

		// What's the current template path again?
		if ($blog_style)
		{
			$tpl_path = $phpbb_root_path . 'blog/styles/' . $blog_template . '/';
		}
		else
		{
			$tpl_path = $phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/template/';
		}

		// If the template file does not exist, we will have the system fall back and use the one in the prosilver folder
		if (!file_exists($tpl_path . $template_file))
		{
			$template_path = '../../../styles/prosilver/template/';

			if (!file_exists($tpl_path . $template_path . $template_file))
			{
				trigger_error('PLUGIN_TEMPLATE_MISSING');
			}
		}
		else
		{
			$template_path = '';
		}

		// $tpl_id is just used to keep a unique filename for the template.
		$tpl_id++;
		$template->set_filenames(array(
			$tpl_id		=> $template_path . $template_file,
		));

		// return the output
		return $template->assign_display($tpl_id);
	}

	public static function add_to_do($to_do)
	{
		foreach($to_do as $do => $what)
		{
			if (!array_key_exists($do, self::$to_do))
			{
				self::$to_do[$do] = $what;
			}
			else
			{
				self::$to_do[$do] = array_merge(self::$to_do[$do], $what);
			}
		}
	}

	public static function plugin_do($what)
	{
		if (isset(self::$to_do[$what]))
		{
			foreach (self::$to_do[$what] as $function_name)
			{
				$function_name();
			}
		}
	}

	public static function plugin_do_arg($what, $args)
	{
		if (isset(self::$to_do[$what]))
		{
			foreach (self::$to_do[$what] as $function_name)
			{
				$function_name($args);
			}
		}
	}

	public static function plugin_do_ref($what, &$args)
	{
		if (isset(self::$to_do[$what]))
		{
			foreach (self::$to_do[$what] as $function_name)
			{
				$function_name($args);
			}
		}
	}

	/**
	* Install a plugin
	*/
	public static function plugin_install($which)
	{
		global $auth, $auth_admin, $blog_plugins_path, $cache, $config, $db, $dbmd, $dbms, $db_tool, $phpbb_root_path, $phpEx, $table_prefix, $user;

		if (!array_key_exists($which, self::$available_plugins))
		{
			trigger_error('PLUGIN_NOT_EXIST');
		}

		if (array_key_exists($which, self::$plugins))
		{
			trigger_error('PLUGIN_ALREADY_INSTALLED');
		}

		include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
		include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		include($phpbb_root_path . '/includes/acp/auth.' . $phpEx);
		$auth_admin = new auth_admin();
		$db_tool = new phpbb_db_tools($db);
		$dbmd = get_available_dbms($dbms);
		define('PLUGIN_INSTALL', true);

		if (file_exists($blog_plugins_path . $which . '/install.' . $phpEx))
		{
			include($blog_plugins_path . $which . '/install.' . $phpEx);
		}

		$sql_data = array(
			'plugin_name'		=> $which,
			'plugin_enabled'	=> 1,
			'plugin_version'	=> self::$available_plugins[$which]['plugin_version'],
		);

		$sql = 'INSERT INTO ' . BLOGS_PLUGINS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);
		$db->sql_query($sql);
		self::$plugins[$which] = $sql_data;

		add_log('admin', 'LOG_BLOG_PLUGIN_INSTALLED', $which);

		$cache->purge();
	}

	/**
	* Uninstall a plugin
	*/
	public static function plugin_uninstall($which)
	{
		global $auth, $auth_admin, $blog_plugins_path, $cache, $config, $db, $dbmd, $dbms, $db_tool, $phpbb_root_path, $phpEx, $table_prefix, $user;
		if (!array_key_exists($which, self::$plugins))
		{
			trigger_error('PLUGIN_NOT_INSTALLED');
		}

		include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
		include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		include($phpbb_root_path . '/includes/acp/auth.' . $phpEx);
		$auth_admin = new auth_admin();
		$db_tool = new phpbb_db_tools($db);
		$dbmd = get_available_dbms($dbms);
		define('PLUGIN_UNINSTALL', true);

		if (file_exists($blog_plugins_path . $which . '/uninstall.' . $phpEx))
		{
			include($blog_plugins_path . $which . '/uninstall.' . $phpEx);
		}

		$sql = 'DELETE FROM ' . BLOGS_PLUGINS_TABLE . ' WHERE plugin_name = \'' . $db->sql_escape($which) . '\'';
		$db->sql_query($sql);

		unset(self::$plugins[$which]);

		add_log('admin', 'LOG_BLOG_PLUGIN_UNINSTALLED', $which);

		$cache->purge();
	}

	/**
	* Update a plugin
	*/
	public static function plugin_update($which)
	{
		global $auth, $auth_admin, $blog_plugins_path, $cache, $config, $db, $dbmd, $dbms, $db_tool, $phpbb_root_path, $phpEx, $table_prefix, $user;
		if (!array_key_exists($which, self::$plugins))
		{
			trigger_error('PLUGIN_NOT_INSTALLED');
		}

		$newer_files = false;
		if (self::$available_plugins[$which]['plugin_version'] != self::$plugins[$which]['plugin_version'])
		{
			$version = array('files' => explode('.', self::$available_plugins[$which]['plugin_version']), 'db' => explode('.', self::$plugins[$which]['plugin_version']));

			$i = 0;
			foreach ($version['files'] as $v)
			{
				if ($v > $version['db'][$i])
				{
					$newer_files = true;
					break;
				}
				else if ($v < $version['db'][$i])
				{
					break;
				}
				$i++;
			}
		}

		if ($newer_files)
		{
			include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
			include($phpbb_root_path . '/includes/acp/auth.' . $phpEx);
			$auth_admin = new auth_admin();
			$db_tool = new phpbb_db_tools($db);
			$dbmd = get_available_dbms($dbms);
			define('PLUGIN_UPDATE', true);

			$current_version = self::$plugins[$which]['plugin_version'];

			if (file_exists($blog_plugins_path . $which . '/update.' . $phpEx))
			{
				include($blog_plugins_path . $which . '/update.' . $phpEx);
			}

			$sql = 'UPDATE ' . BLOGS_PLUGINS_TABLE . ' SET plugin_version = \'' . self::$available_plugins[$which]['plugin_version'] . '\' WHERE plugin_name = \'' . $db->sql_escape($which) . '\'';
			$db->sql_query($sql);

			self::$plugins[$which]['plugin_version'] = self::$available_plugins[$which]['plugin_version'];

			add_log('admin', 'LOG_BLOG_PLUGIN_UPDATED', $which);

			handle_blog_cache('plugins');
		}
	}

	/**
	* Enable a plugin
	*/
	public static function plugin_enable($which)
	{
		global $db;

		if (!array_key_exists($which, self::$plugins))
		{
			self::plugin_install($which);
			return;
		}

		$sql = 'UPDATE ' . BLOGS_PLUGINS_TABLE . ' SET plugin_enabled = 1 WHERE plugin_name = \'' . $db->sql_escape($which) . '\'';
		$db->sql_query($sql);

		self::$plugins[$which]['plugin_enabled'] = 1;

		add_log('admin', 'LOG_BLOG_PLUGIN_ENABLED', $which);

		handle_blog_cache('plugins');
	}

	/**
	* Disable a plugin
	*/
	public static function plugin_disable($which)
	{
		global $db;

		if (!array_key_exists($which, self::$plugins))
		{
			self::plugin_install($which);
		}

		$sql = 'UPDATE ' . BLOGS_PLUGINS_TABLE . ' SET plugin_enabled = 0 WHERE plugin_name = \'' . $db->sql_escape($which) . '\'';
		$db->sql_query($sql);

		self::$plugins[$which]['plugin_enabled'] = 0;

		add_log('admin', 'LOG_BLOG_PLUGIN_DISABLED', $which);

		handle_blog_cache('plugins');
	}

	/**
	* Move a plugin
	*
	* This is used for the menu order on the User's blog page.
	*/
	public static function plugin_move($which, $action)
	{
		global $cache, $db, $blog_plugins_path, $phpEx;

		if (!array_key_exists($which, self::$plugins))
		{
			trigger_error('PLUGIN_NOT_INSTALLED');
		}

		$temp = self::$plugins;
		if ($action == 'move_down')
		{
			$temp = array_reverse($temp);
		}

		$to = $to_id = false;
		foreach ($temp as $plugin_name => $data)
		{
			if ($plugin_name == $which)
			{
				break;
			}
			$to_id = $data['plugin_id'];
		}

		if ($to_id)
		{
			$sql = 'UPDATE ' . BLOGS_PLUGINS_TABLE . ' SET plugin_id = 0 WHERE plugin_name = \'' . $db->sql_escape($which) . '\'';
			$db->sql_query($sql);

			$sql = 'UPDATE ' . BLOGS_PLUGINS_TABLE . ' SET plugin_id = ' . self::$plugins[$which]['plugin_id'] . ' WHERE plugin_id = ' . $to_id;
			$db->sql_query($sql);

			$sql = 'UPDATE ' . BLOGS_PLUGINS_TABLE . ' SET plugin_id = ' . $to_id . ' WHERE plugin_id = 0';
			$db->sql_query($sql);

			handle_blog_cache('plugins');
		}
		unset($temp);
	}
}
?>