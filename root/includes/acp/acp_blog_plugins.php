<?php
/**
 *
 * @package phpBB3 User Blog
 * @copyright (c) 2007 EXreaction, Lithium Studios
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 *
 */

/**
 * @package acp
 */
class acp_blog_plugins
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;
		global $blog_plugins_path, $blog_plugins;

		include($phpbb_root_path . 'includes/blog/functions.' . $phpEx);
		include($phpbb_root_path . 'includes/blog/plugins/plugins.' . $phpEx);

		$blog_plugins = new blog_plugins();
		$blog_plugins_path = $phpbb_root_path . 'includes/blog/plugins/';
		if ($blog_plugins->load_plugins() === false)
		{
			trigger_error('PLUGINS_DISABLED');
		}

		$submit = (isset($_POST['submit'])) ? true : false;
		$action = request_var('action', '');
		$action_to = request_var('name', '');

		$this->tpl_name = 'acp_blog_plugins';
		$this->page_title = 'ACP_BLOG_PLUGINS';

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
		));

		switch ($action)
		{
			case 'activate' :
				$blog_plugins->plugin_enable($action_to);
			break;
			case 'deactivate' :
				$blog_plugins->plugin_disable($action_to);
			break;
			case 'install' :
				$blog_plugins->plugin_install($action_to);
			break;
			case 'uninstall' :
				if (confirm_box(true))
				{
					$blog_plugins->plugin_uninstall($action_to);
				}
				else
				{
					confirm_box(false, 'PLUGIN_UNINSTALL');
				}
			break;
			case 'update' :
				$blog_plugins->plugin_update($action_to);
			break;
		}

		foreach ($blog_plugins->available_plugins as $name => $data)
		{
			$installed = (array_key_exists($name, $blog_plugins->plugins)) ? true : false;
			$active = ($installed && $blog_plugins->plugins[$name]['plugin_enabled']) ? true : false;

			$s_actions = array();
			if ($installed)
			{
				if ($active)
				{
					$s_actions[] = '<a href="' . $this->u_action . "&amp;action=deactivate&amp;name=" . $name . '">' . $user->lang['PLUGIN_DEACTIVATE'] . '</a>';
					$s_actions[] = '<a href="' . $this->u_action . "&amp;action=uninstall&amp;name=" . $name . '">' . $user->lang['PLUGIN_UNINSTALL'] . '</a>';
				}
				else
				{
					$s_actions[] = '<a href="' . $this->u_action . "&amp;action=activate&amp;name=" . $name . '">' . $user->lang['PLUGIN_ACTIVATE'] . '</a>';
					$s_actions[] = '<a href="' . $this->u_action . "&amp;action=uninstall&amp;name=" . $name . '">' . $user->lang['PLUGIN_UNINSTALL'] . '</a>';
				}

				if ($data['plugin_version'] != $blog_plugins->plugins[$name]['plugin_version'])
				{
					$version = array('files' => explode('.', $data['plugin_version']), 'db' => explode('.', $blog_plugins->plugins[$name]['plugin_version']));

					$i = 0;
					$newer_files = false;
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
					if ($newer_files)
					{
						$s_actions[] = '<a href="' . $this->u_action . "&amp;action=update&amp;name=" . $name . '">' . $user->lang['PLUGIN_UPDATE'] . '</a>';
					}
				}
			}
			else
			{
				$s_actions[] = '<a href="' . $this->u_action . "&amp;action=install&amp;name=" . $name . '">' . $user->lang['PLUGIN_INSTALL'] . '</a>';
			}

			$template->assign_block_vars((($installed) ? 'installed' : 'uninstalled'), array(
				'NAME'				=> (isset($data['plugin_title'])) ? $data['plugin_title'] : $name,
				'DESCRIPTION'		=> (isset($data['plugin_description'])) ? $data['plugin_description'] : '',
				'S_ACTIONS'			=> implode(' | ', $s_actions),
				'COPYRIGHT'			=> (isset($data['plugin_copyright'])) ? $data['plugin_copyright'] : '',
				'DATABASE_VERSION'	=> ($installed) ? $blog_plugins->plugins[$name]['plugin_version'] : false,
				'FILES_VERSION'		=> (isset($data['plugin_version'])) ? $data['plugin_version'] : '',
			));
		}
	}
}

?>