<?php
/**
 *
 * @package phpBB3 User Blog
 * @copyright (c) 2007 EXreaction, Lithium Studios
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 *
 */

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_blog_search
{
	var $u_action;
	var $state;
	var $search;
	var $max_post_id;
	var $batch_size = 100;

	function main($id, $mode)
	{
		global $user, $phpbb_root_path, $phpEx;

		$user->add_lang('acp/search');

		// For some this may be of help...
		@ini_set('memory_limit', '128M');

		include($phpbb_root_path . 'blog/functions.' . $phpEx);
		include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);

		$blog_plugins = new blog_plugins();
		$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';
		$blog_plugins->load_plugins();

		$this->settings($id, $mode);
		$this->index($id, $mode);
	}

	function settings($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$submit = (isset($_POST['submit'])) ? true : false;

		$search_types = $this->get_search_types();

		$settings = array(
			'user_blog_search'				=> 'bool',
		);

		$search = null;
		$error = false;
		$search_options = '';
		foreach ($search_types as $type)
		{
			if ($this->init_search($type, $search, $error))
			{
				continue;
			}

			$name = ucfirst(strtolower(str_replace('_', ' ', $type)));
			$selected = ($config['search_type'] == $type) ? ' selected="selected"' : '';
			$search_options .= '<option value="' . $type . '"' . $selected . '>' . $name . '</option>';

			if (method_exists($search, 'acp'))
			{
				$vars = $search->acp();

				if (!$submit)
				{
					$template->assign_block_vars('backend', array(
						'NAME'		=> $name,
						'SETTINGS'	=> $vars['tpl'])
					);
				}
				else if (is_array($vars['config']))
				{
					$settings = array_merge($settings, $vars['config']);
				}
			}
		}
		unset($search);
		unset($error);

		$cfg_array = (isset($_REQUEST['config'])) ? request_var('config', array('' => ''), true) : array();
		$updated = request_var('updated', false);

		foreach ($settings as $config_name => $var_type)
		{
			if (!isset($cfg_array[$config_name]))
			{
				continue;
			}

			// e.g. integer:4:12 (min 4, max 12)
			$var_type = explode(':', $var_type);

			$config_value = $cfg_array[$config_name];
			settype($config_value, $var_type[0]);

			if (isset($var_type[1]))
			{
				$config_value = max($var_type[1], $config_value);
			}

			if (isset($var_type[2]))
			{
				$config_value = min($var_type[2], $config_value);
			}

			// only change config if anything was actually changed
			if ($submit && ($config[$config_name] != $config_value))
			{
				set_config($config_name, $config_value);
				$updated = true;
			}
		}

		if ($submit)
		{
			$extra_message = '';
			if ($updated)
			{
				add_log('admin', 'LOG_CONFIG_SEARCH');
			}

			if (isset($cfg_array['search_type']) && in_array($cfg_array['search_type'], $search_types, true) && ($cfg_array['search_type'] != $config['search_type']))
			{
				$search = null;
				$error = false;

				if (!$this->init_search($cfg_array['search_type'], $search, $error))
				{
					if (confirm_box(true))
					{
						if (!method_exists($search, 'init') || !($error = $search->init()))
						{
							set_config('user_blog_search_type', $cfg_array['search_type']);

							if (!$updated)
							{
								add_log('admin', 'LOG_CONFIG_SEARCH');
							}
							$extra_message = '<br />' . $user->lang['SWITCHED_SEARCH_BACKEND'] . '<br /><a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=search&amp;mode=index') . '">&raquo; ' . $user->lang['GO_TO_SEARCH_INDEX'] . '</a>';
						}
						else
						{
							trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}
					else
					{
						confirm_box(false, $user->lang['CONFIRM_SEARCH_BACKEND'], build_hidden_fields(array(
							'i'			=> $id,
							'mode'		=> $mode,
							'submit'	=> true,
							'updated'	=> $updated,
							'config'	=> array('user_blog_search_type' => $cfg_array['search_type']),
						)));
					}
				}
				else
				{
					trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			trigger_error($user->lang['CONFIG_UPDATED'] . $extra_message . adm_back_link($this->u_action));
		}
		unset($cfg_array);

		$this->tpl_name = 'acp_blog_search';
		$this->page_title = 'ACP_SEARCH_SETTINGS';

		$template->assign_vars(array(
			'S_SEARCH_TYPES'		=> $search_options,
			'S_YES_SEARCH'			=> (bool) $config['user_blog_search'],
			'S_SETTINGS'			=> true,

			'U_ACTION'				=> $this->u_action)
		);
	}

	function index($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		if (isset($_REQUEST['action']) && is_array($_REQUEST['action']))
		{
			$action = request_var('action', array('' => false));
			$action = key($action);
		}
		else
		{
			$action = request_var('action', '');
		}
		$this->state = explode(',', $config['search_indexing_state']);

		if (isset($_POST['cancel']))
		{
			$action = '';
			$this->state = array();
			$this->save_state();
		}

		if ($action)
		{
			$this->state[0] = request_var('search_type', '');
			$this->search = null;
			$error = false;
			if ($this->init_search($this->state[0], $this->search, $error))
			{
				trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
			}
			$name = ucfirst(strtolower(str_replace('_', ' ', $this->state[0])));

			switch ($action)
			{
				case 'delete':
					$this->search->delete_index();

					add_log('admin', 'LOG_SEARCH_INDEX_REMOVED', $name);
					trigger_error($user->lang['SEARCH_INDEX_REMOVED'] . adm_back_link($this->u_action));
				break;

				case 'create':
					$section = request_var('section', 0);
					$part = request_var('part', 0);
					$limit = 250;
					$part_cnt = 0;

					if ($section == 0)
					{
						$this->search->delete_index();
						$section++;
					}
					else if ($section == 1)
					{
						$sql = 'SELECT * FROM ' . BLOGS_TABLE . '
							WHERE blog_deleted = \'0\'
							AND blog_approved = \'1\'
								ORDER BY blog_id DESC
									LIMIT ' . ($part * $limit) . ', ' . $limit;
						$result = $db->sql_query($sql);
						while ($row = $db->sql_fetchrow($result))
						{
							$this->search->index('add', $row['blog_id'], 0, $row['blog_text'], $row['blog_subject'], $row['user_id']);
						}

						$sql = 'SELECT count(blog_id) AS cnt FROM ' . BLOGS_TABLE . '
							WHERE blog_deleted = \'0\'
							AND blog_approved = \'1\'';
						$result = $db->sql_query($sql);
						$cnt = $db->sql_fetchrow($result);

						if ($cnt['cnt'] >= (($part + 1) * $limit))
						{
							$part++;
							$part_cnt = ceil($cnt['cnt'] / $limit);
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
							WHERE reply_deleted = \'0\'
							AND reply_approved = \'1\'
								ORDER BY reply_id DESC
									LIMIT ' . ($part * $limit) . ', ' . $limit;
						$result = $db->sql_query($sql);
						while ($row = $db->sql_fetchrow($result))
						{
							$this->search->index('add', $row['blog_id'], $row['reply_id'], $row['reply_text'], $row['reply_subject'], $row['user_id']);
						}

						$sql = 'SELECT count(reply_id) AS cnt FROM ' . BLOGS_REPLY_TABLE . '
							WHERE reply_deleted = \'0\'
							AND reply_approved = \'1\'';
						$result = $db->sql_query($sql);
						$cnt = $db->sql_fetchrow($result);

						if ($cnt['cnt'] >= (($part + 1) * $limit))
						{
							$part++;
							$part_cnt = ceil($cnt['cnt'] / $limit);
						}
						else
						{
							$part = 0;
							$section++;
						}
					}

					if ($section > 2)
					{
						add_log('admin', 'LOG_SEARCH_INDEX_CREATED', $name);
						trigger_error($user->lang['SEARCH_INDEX_CREATED'] . adm_back_link($this->u_action));
					}
					else
					{
						$redirect_url = $this->u_action . "&amp;search_type={$this->state[0]}&amp;action={$action}&amp;section={$section}&amp;part={$part}";
						meta_refresh(1, $redirect_url);
						trigger_error(sprintf($user->lang['BREAK_CONTINUE_NOTICE'], $section, 3, $part, $part_cnt) . '<br/><br/><a href="' . $redirect_url . '">' . $user->lang['CONTINUE'] . '</a>');
					}
				break;
			}
		}

		$search_types = $this->get_search_types();

		$search = null;
		$error = false;
		$search_options = '';
		foreach ($search_types as $type)
		{
			if ($this->init_search($type, $search, $error) || !method_exists($search, 'index_created'))
			{
				continue;
			}

			$name = ucfirst(strtolower(str_replace('_', ' ', $type)));

			$data = array();
			if (method_exists($search, 'index_stats'))
			{
				$data = $search->index_stats();
			}

			$statistics = array();
			foreach ($data as $statistic => $value)
			{
				$n = sizeof($statistics);
				if ($n && sizeof($statistics[$n - 1]) < 3)
				{
					$statistics[$n - 1] += array('statistic_2' => $statistic, 'value_2' => $value);
				}
				else
				{
					$statistics[] = array('statistic_1' => $statistic, 'value_1' => $value);
				}
			}

			$template->assign_block_vars('backend', array(
				'L_NAME'			=> $name,
				'NAME'				=> $type,

				'S_ACTIVE'			=> ($type == $config['search_type']) ? true : false,
				'S_HIDDEN_FIELDS'	=> build_hidden_fields(array('search_type' => $type)),
				'S_INDEXED'			=> (bool) $search->index_created(),
				'S_STATS'			=> (bool) sizeof($statistics))
			);

			foreach ($statistics as $statistic)
			{
				$template->assign_block_vars('backend.data', array(
					'STATISTIC_1'	=> $statistic['statistic_1'],
					'VALUE_1'		=> $statistic['value_1'],
					'STATISTIC_2'	=> (isset($statistic['statistic_2'])) ? $statistic['statistic_2'] : '',
					'VALUE_2'		=> (isset($statistic['value_2'])) ? $statistic['value_2'] : '')
				);
			}
		}
		unset($search);
		unset($error);
		unset($statistics);
		unset($data);

		$this->tpl_name = 'acp_blog_search';
		$this->page_title = 'ACP_SEARCH_INDEX';

		$template->assign_vars(array(
			'S_INDEX'				=> true,
			'U_ACTION'				=> $this->u_action,
			'U_PROGRESS_BAR'		=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&amp;mode=$mode&amp;action=progress_bar"),
			'UA_PROGRESS_BAR'		=> addslashes(append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&amp;mode=$mode&amp;action=progress_bar")),
		));

		if (isset($this->state[1]))
		{
			$template->assign_vars(array(
				'S_CONTINUE_INDEXING'	=> $this->state[1],
				'U_CONTINUE_INDEXING'	=> $this->u_action . '&amp;action=' . $this->state[1],
				'L_CONTINUE'			=> ($this->state[1] == 'create') ? $user->lang['CONTINUE_INDEXING'] : $user->lang['CONTINUE_DELETING_INDEX'],
				'L_CONTINUE_EXPLAIN'	=> ($this->state[1] == 'create') ? $user->lang['CONTINUE_INDEXING_EXPLAIN'] : $user->lang['CONTINUE_DELETING_INDEX_EXPLAIN'])
			);
		}
	}

	function get_search_types()
	{
		global $phpbb_root_path, $phpEx;

		$search_types = array();

		$dp = @opendir($phpbb_root_path . 'blog/search');

		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				if ((preg_match('#\.' . $phpEx . '$#', $file)) && ($file != "search.$phpEx"))
				{
					$search_types[] = preg_replace('#^(.*?)\.' . $phpEx . '$#', '\1', $file);
				}
			}
			closedir($dp);

			sort($search_types);
		}

		return $search_types;
	}

	function get_max_post_id()
	{
		global $db;

		$sql = 'SELECT MAX(post_id) as max_post_id
			FROM '. POSTS_TABLE;
		$result = $db->sql_query($sql);
		$max_post_id = (int) $db->sql_fetchfield('max_post_id');
		$db->sql_freeresult($result);

		return $max_post_id;
	}

	function save_state($state = false)
	{
		if ($state)
		{
			$this->state = $state;
		}

		ksort($this->state);

		set_config('search_indexing_state', implode(',', $this->state));
	}

	/**
	* Initialises a search backend object
	*
	* @return false if no error occurred else an error message
	*/
	function init_search($type, &$search, &$error)
	{
		global $phpbb_root_path, $phpEx, $user;

		if (!preg_match('#^\w+$#', $type) || !file_exists("{$phpbb_root_path}blog/search/$type.$phpEx"))
		{
			$error = $user->lang['NO_SUCH_SEARCH_MODULE'];
			return $error;
		}

		include_once("{$phpbb_root_path}blog/search/$type.$phpEx");

		$class = 'blog_' . $type;
		if (!class_exists($class))
		{
			$error = $user->lang['NO_SUCH_SEARCH_MODULE'];
			return $error;
		}

		$error = false;
		$search = new $class($error);

		return $error;
	}
}

?>