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
* Blog Plugins Class
*/
class blog_plugins
{
	var $plugins = array();
	var $available_plugins = array();
	var $to_do = array();

	function load_plugins()
	{
		global $cache, $config, $db, $phpbb_root_path, $phpEx, $blog_plugins_path, $table_prefix, $user;

		if (!isset($config['user_blog_enable_plugins']) || !$config['user_blog_enable_plugins'])
		{
			return false;
		}

		if (!defined('BLOGS_PLUGINS_TABLE'))
		{
			include($phpbb_root_path . 'blog/data/constants.' . $phpEx);
		}

		$cache_data = $cache->get('_blog_plugins');

		if ($cache_data === false)
		{
			$sql = 'SELECT * FROM ' . BLOGS_PLUGINS_TABLE;
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result))
			{
				$this->plugins[$row['plugin_name']] = $row;
			}

			$cache->put('_blog_plugins', $this->plugins);
		}
		else
		{
			$this->plugins = $cache_data;
		}
		unset($cache_data);

		$dh = @opendir($blog_plugins_path . 'info/');

		if ($dh)
		{
			while (($file = readdir($dh)) !== false)
			{
				if (strpos($file, 'info_') === 0 && substr($file, -(strlen($phpEx) + 1)) === '.' . $phpEx)
				{
					$name = substr($file, 5, -(strlen($phpEx) + 1));

					$this->available_plugins[$name] = array();

					// this will be checked in each plugin file
					$plugin_enabled = (array_key_exists($name, $this->plugins) && $this->plugins[$name]['plugin_enabled']) ? true : false;

					include($blog_plugins_path . 'info/' . substr($file, 0, -(strlen($phpEx) + 1)) . '.' . $phpEx);
				}
			}

			closedir($dh);
		}

		return true;
	}

	function plugin_do($what)
	{
		if (isset($this->to_do[$what]))
		{
			foreach ($this->to_do[$what] as $function_name)
			{
				$function_name();
			}
		}
	}

	function plugin_do_arg($what, &$args)
	{
		if (isset($this->to_do[$what]))
		{
			foreach ($this->to_do[$what] as $function_name)
			{
				$function_name($args);
			}
		}
	}

	function plugin_install($which)
	{
		global $cache, $config, $db, $dbms, $phpbb_root_path, $phpEx, $blog_plugins_path, $table_prefix;

		if (!array_key_exists($which, $this->available_plugins))
		{
			trigger_error('PLUGIN_NOT_EXIST');
		}

		if (array_key_exists($which, $this->plugins))
		{
			trigger_error('PLUGIN_ALREADY_INSTALLED');
		}

		include($phpbb_root_path . '/includes/acp/auth.' . $phpEx);
		$auth_admin = new auth_admin();

		include($blog_plugins_path . $which . '/install.' . $phpEx);

		$sql_data = array(
			'plugin_name'		=> $which,
			'plugin_enabled'	=> 1,
			'plugin_version'	=> $this->available_plugins[$which]['plugin_version'],
		);

		$sql = 'INSERT INTO ' . BLOGS_PLUGINS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);
		$db->sql_query($sql);
		unset($sql_data);

		$this->plugins[$which] = $sql_data;

		$cache->purge();
	}

	function plugin_uninstall($which)
	{
		global $cache, $config, $db, $dbms, $phpbb_root_path, $phpEx, $blog_plugins_path, $table_prefix;
		if (!array_key_exists($which, $this->plugins))
		{
			trigger_error('PLUGIN_NOT_INSTALLED');
		}

		include($blog_plugins_path . $which . '/uninstall.' . $phpEx);

		$sql = 'DELETE FROM ' . BLOGS_PLUGINS_TABLE . ' WHERE plugin_name = \'' . $db->sql_escape($which) . '\'';
		$db->sql_query($sql);

		unset($this->plugins[$which]);

		$cache->purge();
	}

	function plugin_update($which)
	{
		global $config, $db, $dbms, $phpbb_root_path, $phpEx, $blog_plugins_path, $table_prefix;
		if (!array_key_exists($which, $this->plugins))
		{
			trigger_error('PLUGIN_NOT_INSTALLED');
		}

		$newer_files = false;
		if ($this->available_plugins[$which]['plugin_version'] != $this->plugins[$which]['plugin_version'])
		{
			$version = array('files' => explode('.', $this->available_plugins[$which]['plugin_version']), 'db' => explode('.', $this->plugins[$which]['plugin_version']));

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
			include($blog_plugins_path . $which . '/update.' . $phpEx);

			$sql = 'UPDATE ' . BLOGS_PLUGINS_TABLE . ' SET plugin_version = \'' . $this->available_plugins[$which]['plugin_version'] . '\' WHERE plugin_name = \'' . $db->sql_escape($which) . '\'';
			$db->sql_query($sql);

			$this->plugins[$which]['plugin_version'] = $this->available_plugins[$which]['plugin_version'];

			handle_blog_cache('plugins');
		}
	}

	function plugin_enable($which)
	{
		global $db;

		if (!array_key_exists($which, $this->plugins))
		{
			$this->plugin_install($which);
			return;
		}

		$sql = 'UPDATE ' . BLOGS_PLUGINS_TABLE . ' SET plugin_enabled = \'1\' WHERE plugin_name = \'' . $db->sql_escape($which) . '\'';
		$db->sql_query($sql);

		$this->plugins[$which]['plugin_enabled'] = 1;

		handle_blog_cache('plugins');
	}

	function plugin_disable($which)
	{
		global $db;

		if (!array_key_exists($which, $this->plugins))
		{
			$this->plugin_install($which);
		}

		$sql = 'UPDATE ' . BLOGS_PLUGINS_TABLE . ' SET plugin_enabled = \'0\' WHERE plugin_name = \'' . $db->sql_escape($which) . '\'';
		$db->sql_query($sql);

		$this->plugins[$which]['plugin_enabled'] = 0;

		handle_blog_cache('plugins');
	}
}
?>