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
		
		$submit = (isset($_POST['submit'])) ? true : false;
		$action = request_var('action', '');

		$this->tpl_name = 'acp_board';
		$this->page_title = $user->lang['ACP_BLOGS'];

		$settings = array(
			'legend1'							=> 'BLOG_SETTINGS',
			'user_blog_enable'					=> array('lang' => 'ENABLE_USER_BLOG',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => true),
			'user_blog_custom_profile_enable'	=> array('lang' => 'ENABLE_BLOG_CUSTOM_PROFILES',	'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => false),
			'user_blog_inform'					=> array('lang' => 'BLOG_INFORM', 					'validate' => 'string',	'type' => 'text:25:100',				'explain' => true),
			'user_blog_always_show_blog_url'	=> array('lang' => 'BLOG_ALWAYS_SHOW_URL', 			'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => true),
			'user_blog_text_limit'				=> array('lang' => 'DEFAULT_TEXT_LIMIT', 			'validate' => 'int',	'type' => 'text:5:5',					'explain' => true),
			'user_blog_user_text_limit'			=> array('lang' => 'USER_TEXT_LIMIT', 				'validate' => 'int',	'type' => 'text:5:5',					'explain' => true),
			'user_blog_founder_all_perm'		=> array('lang'	=> 'FOUNDER_ALL_PERMISSION',		'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => false),
		);

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
}

?>