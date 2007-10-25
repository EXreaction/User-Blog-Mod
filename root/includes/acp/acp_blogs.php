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
class acp_blogs
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;
		global $blog_plugins_path;

		include($phpbb_root_path . 'blog/functions.' . $phpEx);
		include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);

		$blog_plugins = new blog_plugins();
		$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';
		$blog_plugins->load_plugins();

		$submit = (isset($_POST['submit'])) ? true : false;
		$action = request_var('action', '');

		$this->tpl_name = 'acp_board';
		$this->page_title = $user->lang['ACP_BLOGS'];

		$blog_plugins->plugin_do('acp_main_start');

		$settings = array(
			'legend0'							=> 'VERSION',
			'user_blog_version'					=> array('lang' => 'VERSION',						'type' => 'custom',		'method' => 'blog_version',				'explain' => false),

			'legend1'							=> 'BLOG_SETTINGS',
			'user_blog_enable'					=> array('lang' => 'ENABLE_USER_BLOG',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => true),
			'user_blog_enable_plugins'			=> array('lang' => 'ENABLE_USER_BLOG_PLUGINS',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => true),
			'user_blog_subscription_enabled'	=> array('lang'	=> 'ENABLE_SUBSCRIPTIONS',			'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => true),
			'user_blog_enable_zebra'			=> array('lang' => 'BLOG_ENABLE_ZEBRA',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => true),
			'user_blog_founder_all_perm'		=> array('lang'	=> 'FOUNDER_ALL_PERMISSION',		'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => false),
			'user_blog_force_prosilver'			=> array('lang' => 'BLOG_FORCE_PROSILVER',			'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => true),
			'user_blog_seo'						=> array('lang' => 'BLOG_ENABLE_SEO',				'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => true),

			'legend2'							=> 'BLOG_POST_VIEW_SETTINGS',
			'user_blog_guest_captcha'			=> array('lang' => 'BLOG_GUEST_CAPTCHA',			'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => false),
			'user_blog_custom_profile_enable'	=> array('lang' => 'ENABLE_BLOG_CUSTOM_PROFILES',	'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => false),
//			'user_blog_enable_feeds'			=> array('lang' => 'BLOG_ENABLE_FEEDS',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => false),
			'user_blog_always_show_blog_url'	=> array('lang' => 'BLOG_ALWAYS_SHOW_URL', 			'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => true),
			'user_blog_text_limit'				=> array('lang' => 'DEFAULT_TEXT_LIMIT', 			'validate' => 'int',	'type' => 'text:5:5',					'explain' => true),
			'user_blog_user_text_limit'			=> array('lang' => 'USER_TEXT_LIMIT', 				'validate' => 'int',	'type' => 'text:5:5',					'explain' => true),
			'user_blog_inform'					=> array('lang' => 'BLOG_INFORM', 					'validate' => 'string',	'type' => 'text:25:100',				'explain' => true),
		);

		$blog_plugins->plugin_do_arg('acp_main_settings', $settings);

		// check to see if prosilver is installed and style_id 1.  If it isn't we won't display the user_blog_force_prosilver option.
		$sql = 'SELECT style_name FROM ' . STYLES_TABLE . '
			WHERE style_id = \'1\'';
		$result = $db->sql_query($sql);
		$style_1 = $db->sql_fetchrow($result);
		$prosilver_1 = ($style_1['style_name'] == 'prosilver') ? true : false;
		$db->sql_freeresult($result);

		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($settings, $cfg_array, $error);

		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang['BLOG_SETTINGS'],
			'L_TITLE_EXPLAIN'	=> $user->lang['BLOG_SETTINGS_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action,
		));
		foreach ($settings as $config_key => $vars)
		{
			if ($config_key == 'user_blog_force_prosilver' && !$prosilver_1)
			{
				set_config($config_key, 0);
				continue;
			}

			if ($submit)
			{
				if (!isset($cfg_array[$config_key]) || strpos($config_key, 'legend') !== false)
				{
					continue;
				}

				$this->new_config[$config_key] = $config_value = $cfg_array[$config_key];

				set_config($config_key, $config_value);
			}
			else
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

				$template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
					'S_EXPLAIN'		=> $vars['explain'],
					'TITLE_EXPLAIN'	=> $l_explain,
					'CONTENT'		=> build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars),
					)
				);
			}
		}

		if ($submit)
		{
			add_log('admin', 'LOG_CONFIG_BLOG');

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}
	}

	/**
	* Select default dateformat
	*/
	function blog_version($value, $key)
	{
		global $user, $config, $phpbb_root_path, $phpEx;

		$handle = @fopen($phpbb_root_path . 'blog.' . $phpEx, "r");
		if ($handle)
		{
			while (!feof($handle))
			{
				$line = fgets($handle, 4096);

				if (strpos($line, 'user_blog_version'))
				{
					// If we are using the Windows line ending, we need to remove 1 more character...
					if (strpos($line, "\r\n"))
					{
						$file_version = substr($line, (strpos($line, "'") + 1), -4);
					}
					else
					{
						$file_version = substr($line, (strpos($line, "'") + 1), -3);
					}
					break;
				}
			}
			fclose($handle);
		}

		$version = $user->lang['DATABASE_VERSION'] . ': ' . $value . '<br/>';
		$version .= $user->lang['FILE_VERSION'] . ': ' . $file_version . '<br/><br/>';

		if ($file_version != $value)
		{
			$version .= sprintf($user->lang['CLICK_UPDATE'], '<a href="' . blog_url(false, false, false, array('page' => 'update', 'mode' => 'update')) . '">', '</a>') . '<br/>';
		}

		$version .= sprintf($user->lang['CLICK_CHECK_NEW_VERSION'], '<a href="http://www.lithiumstudios.org/phpBB3/viewtopic.php?f=31&t=200">', '</a>');
		return $version;
	}
}

?>