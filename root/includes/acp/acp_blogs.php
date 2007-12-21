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
	var $state;
	var $search;
	var $max_post_id;
	var $batch_size = 100;
	var $parent_id = 0;

	function main($id, $mode)
	{
		global $phpbb_root_path, $phpEx, $user;
		global $blog_plugins, $blog_plugins_path;

		include($phpbb_root_path . 'blog/functions.' . $phpEx);
		include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);

		if ($mode != 'plugins')
		{
			$blog_plugins = new blog_plugins();
			$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';
			$blog_plugins->load_plugins();
		}

		$user->add_lang(array('mods/blog/common', 'mods/blog/setup'));

		switch($mode)
		{
			case 'plugins' :
				$this->plugins($id, $mode);
			break;
			case 'search' :
				$this->search($id, $mode);
			break;
			case 'categories' :
				$this->categories($id, $mode);
			break;
			default :
				$this->settings($id, $mode);
		}
	}

	function settings($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;
		global $blog_plugins, $blog_plugins_path;

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
			'user_blog_seo'						=> array('lang' => 'BLOG_ENABLE_SEO',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => true),
			'user_blog_search'					=> array('lang' => 'BLOG_ENABLE_SEARCH',			'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => true),
			'user_blog_user_permissions'		=> array('lang' => 'BLOG_ENABLE_USER_PERMISSIONS',	'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => true),

			'legend2'							=> 'BLOG_POST_VIEW_SETTINGS',
			'user_blog_force_style'				=> array('lang' => 'BLOG_FORCE_STYLE',				'validate' => 'int',	'type' => 'text:5:5',					'explain' => true),
			'user_blog_guest_captcha'			=> array('lang' => 'BLOG_GUEST_CAPTCHA',			'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => false),
			'user_blog_custom_profile_enable'	=> array('lang' => 'ENABLE_BLOG_CUSTOM_PROFILES',	'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => false),
//			'user_blog_enable_feeds'			=> array('lang' => 'BLOG_ENABLE_FEEDS',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',		'explain' => false),
			'user_blog_always_show_blog_url'	=> array('lang' => 'BLOG_ALWAYS_SHOW_URL', 			'validate' => 'bool',	'type' => 'radio:yes_no',				'explain' => true),
			'user_blog_text_limit'				=> array('lang' => 'DEFAULT_TEXT_LIMIT', 			'validate' => 'int',	'type' => 'text:5:5',					'explain' => true),
			'user_blog_user_text_limit'			=> array('lang' => 'USER_TEXT_LIMIT', 				'validate' => 'int',	'type' => 'text:5:5',					'explain' => true),
			'user_blog_inform'					=> array('lang' => 'BLOG_INFORM', 					'validate' => 'string',	'type' => 'text:25:100',				'explain' => true),
		);

		$blog_plugins->plugin_do_arg('acp_main_settings', $settings);

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

				// Make sure the style_id they selected for the force style exists
				if ($config_key == 'user_blog_force_style' && $config_value != 0)
				{
					$sql = 'SELECT style_name FROM ' . STYLES_TABLE . '
						WHERE style_id = \'' . intval($config_value) . '\'';
					$result = $db->sql_query($sql);
					$exists = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
					if (!$exists)
					{
						continue;
					}
				}

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

		$version .= sprintf($user->lang['CLICK_CHECK_NEW_VERSION'], '<a href="http://www.lithiumstudios.org/phpBB3/viewtopic.php?f=41&t=433">', '</a>');
		return $version;
	}

	// Code taken from acp_category
	function categories($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$this->tpl_name = 'acp_blog_categories';
		$this->page_title = 'ACP_BLOG_CATEGORIES';

		$form_key = 'acp_blog';
		add_form_key($form_key);

		$action		= request_var('action', '');
		$update		= (isset($_POST['update'])) ? true : false;
		$category_id	= request_var('c', 0);

		$this->parent_id	= request_var('parent_id', 0);
		$category_data = $errors = array();
		if ($update && !check_form_key($form_key))
		{
			$update = false;
			$errors[] = $user->lang['FORM_INVALID'];
		}

		// Clear the categories cache
		$cache->destroy('_blog_categories');

		// Major routines
		if ($update)
		{
			switch ($action)
			{
				case 'delete':
					$action_subcategories	= request_var('action_subcategories', '');
					$subcategories_to_id	= request_var('subcategories_to_id', 0);
					$action_blogs		= request_var('action_blogs', '');
					$blogs_to_id		= request_var('blogs_to_id', 0);

					$errors = $this->delete_category($category_id, $action_blogs, $action_subcategories, $blogs_to_id, $subcategories_to_id);

					if (sizeof($errors))
					{
						break;
					}

					trigger_error($user->lang['CATEGORY_DELETED'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
	
				break;

				case 'edit':
					$category_data = array(
						'category_id'		=>	$category_id
					);

				// No break here

				case 'add':

					$category_data += array(
						'parent_id'						=> request_var('category_parent_id', $this->parent_id),
						'category_name'					=> utf8_normalize_nfc(request_var('category_name', '', true)),
						'category_description'			=> utf8_normalize_nfc(request_var('category_description', '', true)),
						'category_description_bitfield'	=> '',
						'category_description_uid'		=> '',
						'category_description_options'	=> 7,
						'rules'							=> utf8_normalize_nfc(request_var('rules', '', true)),
						'rules_bitfield'				=> '',
						'rules_uid'						=> '',
						'rules_options'					=> 7,
					);

					// Get data for category rules if specified...
					if ($category_data['rules'])
					{
						generate_text_for_storage($category_data['rules'], $category_data['rules_uid'], $category_data['rules_bitfield'], $category_data['rules_options'], request_var('rules_parse_bbcode', false), request_var('rules_parse_urls', false), request_var('rules_parse_smilies', false));
					}

					// Get data for category description if specified
					if ($category_data['category_description'])
					{
						generate_text_for_storage($category_data['category_description'], $category_data['category_description_uid'], $category_data['category_description_bitfield'], $category_data['category_description_options'], request_var('desc_parse_bbcode', false), request_var('desc_parse_urls', false), request_var('desc_parse_smilies', false));
					}

					$errors = $this->update_category_data($category_data);

					if (!sizeof($errors))
					{
						$message = ($action == 'add') ? $user->lang['CATEGORY_CREATED'] : $user->lang['CATEGORY_UPDATED'];

						trigger_error($message . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
					}

				break;
			}
		}

		switch ($action)
		{
			case 'move_up':
			case 'move_down':

				if (!$category_id)
				{
					trigger_error($user->lang['NO_CATEGORY'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . BLOGS_CATEGORIES_TABLE . "
					WHERE category_id = $category_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['NO_CATEGORY'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				$move_category_name = $this->move_category_by($row, $action, 1);
			break;

			case 'add':
			case 'edit':
				// Show form to create/modify a category
				if ($action == 'edit')
				{
					$this->page_title = 'EDIT_CATEGORY';
					$row = $this->get_category_info($category_id);

					if (!$update)
					{
						$category_data = $row;
					}
					else
					{
						$category_data['left_id'] = $row['left_id'];
						$category_data['right_id'] = $row['right_id'];
					}

					// Make sure no direct child categories are able to be selected as parents.
					$exclude_categories = array();
					foreach (get_category_branch($category_id, 'children') as $row)
					{
						$exclude_categories[] = $row['category_id'];
					}

					$parents_list = make_category_select($category_data['parent_id'], $exclude_categories);
				}
				else
				{
					$this->page_title = 'CREATE_CATEGORY';

					$category_id = $this->parent_id;
					$parents_list = make_category_select($this->parent_id);

					// Fill category data with default values
					if (!$update)
					{
						$category_data = array(
							'parent_id'				=> $this->parent_id,
							'category_name'			=> utf8_normalize_nfc(request_var('category_name', '', true)),
							'category_description'	=> '',
							'rules'					=> '',
						);
					}
				}

				$rules_data = array(
					'text'			=> $category_data['rules'],
					'allow_bbcode'	=> true,
					'allow_smilies'	=> true,
					'allow_urls'	=> true
				);

				$category_description_data = array(
					'text'			=> $category_data['category_description'],
					'allow_bbcode'	=> true,
					'allow_smilies'	=> true,
					'allow_urls'	=> true
				);

				$rules_preview = '';

				// Parse rules if specified
				if ($category_data['rules'])
				{
					if (!isset($category_data['rules_uid']))
					{
						// Before we are able to display the preview and plane text, we need to parse our request_var()'d value...
						$category_data['rules_uid'] = '';
						$category_data['rules_bitfield'] = '';
						$category_data['rules_options'] = 0;

						generate_text_for_storage($category_data['rules'], $category_data['rules_uid'], $category_data['rules_bitfield'], $category_data['rules_options'], request_var('rules_allow_bbcode', false), request_var('rules_allow_urls', false), request_var('rules_allow_smilies', false));
					}

					// Generate preview content
					$rules_preview = generate_text_for_display($category_data['rules'], $category_data['rules_uid'], $category_data['rules_bitfield'], $category_data['rules_options']);

					// decode...
					$rules_data = generate_text_for_edit($category_data['rules'], $category_data['rules_uid'], $category_data['rules_options']);
				}

				// Parse desciption if specified
				if ($category_data['category_description'])
				{
					if (!isset($category_data['category_description_uid']))
					{
						// Before we are able to display the preview and plane text, we need to parse our request_var()'d value...
						$category_data['category_description_uid'] = '';
						$category_data['category_description_bitfield'] = '';
						$category_data['category_description_options'] = 0;

						generate_text_for_storage($category_data['category_description'], $category_data['category_description_uid'], $category_data['category_description_bitfield'], $category_data['category_description_options'], request_var('desc_allow_bbcode', false), request_var('desc_allow_urls', false), request_var('desc_allow_smilies', false));
					}

					// decode...
					$category_description_data = generate_text_for_edit($category_data['category_description'], $category_data['category_description_uid'], $category_data['category_description_options']);
				}

				$sql = 'SELECT category_id
					FROM ' . BLOGS_CATEGORIES_TABLE . "
						WHERE category_id <> $category_id";
				$result = $db->sql_query($sql);

				if ($db->sql_fetchrow($result))
				{
					$template->assign_vars(array(
						'S_MOVE_CATEGORY_OPTIONS'		=> make_category_select($category_data['parent_id'], $category_id))
					);
				}
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'S_ADD_ACTION'				=> ($mode == 'add') ? true : false,
					'S_EDIT_CATEGORY'			=> true,
					'S_ERROR'					=> (sizeof($errors)) ? true : false,
					'S_PARENT_ID'				=> $this->parent_id,
					'S_CATEGORY_PARENT_ID'		=> $category_data['parent_id'],
					'S_PARENT_OPTIONS'			=> $parents_list,

					'U_BACK'					=> $this->u_action . '&amp;parent_id=' . $this->parent_id,
					'U_EDIT_ACTION'				=> $this->u_action . "&amp;parent_id={$this->parent_id}&amp;action=$action&amp;c=$category_id",

					'L_TITLE'					=> $user->lang[$this->page_title],
					'ERROR_MSG'					=> (sizeof($errors)) ? implode('<br />', $errors) : '',

					'CATEGORY_NAME'				=> $category_data['category_name'],
					'RULES'						=> $category_data['rules'],
					'RULES_PREVIEW'				=> $rules_preview,
					'RULES_PLAIN'				=> $rules_data['text'],
					'S_BBCODE_CHECKED'			=> ($rules_data['allow_bbcode']) ? true : false,
					'S_SMILIES_CHECKED'			=> ($rules_data['allow_smilies']) ? true : false,
					'S_URLS_CHECKED'			=> ($rules_data['allow_urls']) ? true : false,

					'CATEGORY_DESCRIPTION'		=> $category_description_data['text'],
					'S_DESC_BBCODE_CHECKED'		=> ($category_description_data['allow_bbcode']) ? true : false,
					'S_DESC_SMILIES_CHECKED'	=> ($category_description_data['allow_smilies']) ? true : false,
					'S_DESC_URLS_CHECKED'		=> ($category_description_data['allow_urls']) ? true : false,

					'S_CATEGORY_OPTIONS'			=> make_category_select(($action == 'add') ? $category_data['parent_id'] : false, ($action == 'edit') ? $category_data['category_id'] : false),
				));

				return;

			break;

			case 'delete':
				if (!$category_id)
				{
					trigger_error($user->lang['NO_CATEGORY'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				$category_data = $this->get_category_info($category_id);

				$subcategories_id = array();
				$subcategories = get_category_branch($category_id, 'children');

				foreach ($subcategories as $row)
				{
					$subcategories_id[] = $row['category_id'];
				}

				$categories_list = make_category_select($category_data['parent_id'], $subcategories_id);

				$sql = 'SELECT category_id
					FROM ' . BLOGS_CATEGORIES_TABLE . "
						WHERE category_id <> $category_id";
				$result = $db->sql_query($sql);

				if ($db->sql_fetchrow($result))
				{
					$template->assign_vars(array(
						'S_MOVE_CATEGORY_OPTIONS'		=> make_category_select($category_data['parent_id'], $subcategories_id))
					);
				}
				$db->sql_freeresult($result);

				$parent_id = ($this->parent_id == $category_id) ? 0 : $this->parent_id;

				$template->assign_vars(array(
					'S_DELETE_CATEGORY'		=> true,
					'U_ACTION'				=> $this->u_action . "&amp;parent_id={$parent_id}&amp;action=delete&amp;c=$category_id",
					'U_BACK'				=> $this->u_action . '&amp;parent_id=' . $this->parent_id,

					'CATEGORY_NAME'			=> $category_data['category_name'],
					'S_HAS_SUBCATEGORYS'	=> ($category_data['right_id'] - $category_data['left_id'] > 1) ? true : false,
					'S_CATEGORIES_LIST'		=> $categories_list,
					'S_ERROR'				=> (sizeof($errors)) ? true : false,
					'ERROR_MSG'				=> (sizeof($errors)) ? implode('<br />', $errors) : '')
				);

				return;
			break;
		}

		// Default management page
		if (!$this->parent_id)
		{
			$navigation = $user->lang['CATEGORY_INDEX'];
		}
		else
		{
			$navigation = '<a href="' . $this->u_action . '">' . $user->lang['CATEGORY_INDEX'] . '</a>';

			$category_nav = get_category_branch($this->parent_id, 'parents', 'descending');
			foreach ($category_nav as $row)
			{
				if ($row['category_id'] == $this->parent_id)
				{
					$navigation .= ' -&gt; ' . $row['category_name'];
				}
				else
				{
					$navigation .= ' -&gt; <a href="' . $this->u_action . '&amp;parent_id=' . $row['category_id'] . '">' . $row['category_name'] . '</a>';
				}
			}
		}

		// Jumpbox
		$category_box = make_category_select($this->parent_id);

		$sql = 'SELECT *
			FROM ' . BLOGS_CATEGORIES_TABLE . "
			WHERE parent_id = $this->parent_id
			ORDER BY left_id";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$url = $this->u_action . "&amp;parent_id=$this->parent_id&amp;c={$row['category_id']}";

				$category_title = $row['category_name'];

				$template->assign_block_vars('categories', array(
					'CATEGORY_NAME'			=> $row['category_name'],
					'CATEGORY_DESCRIPTION'	=> generate_text_for_display($row['category_description'], $row['category_description_uid'], $row['category_description_bitfield'], $row['category_description_options']),

					'U_CATEGORY'			=> $this->u_action . '&amp;parent_id=' . $row['category_id'],
					'U_MOVE_UP'				=> $url . '&amp;action=move_up',
					'U_MOVE_DOWN'			=> $url . '&amp;action=move_down',
					'U_EDIT'				=> $url . '&amp;action=edit',
					'U_DELETE'				=> $url . '&amp;action=delete',
				));
			}
			while ($row = $db->sql_fetchrow($result));
		}
		else if ($this->parent_id)
		{
			$row = $this->get_category_info($this->parent_id);

			$url = $this->u_action . '&amp;parent_id=' . $this->parent_id . '&amp;c=' . $row['category_id'];

			$template->assign_vars(array(
				'S_NO_CATEGORIES'		=> true,

				'U_EDIT'			=> $url . '&amp;action=edit',
				'U_DELETE'			=> $url . '&amp;action=delete',
			));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'ERROR_MSG'		=> (sizeof($errors)) ? implode('<br />', $errors) : '',
			'NAVIGATION'	=> $navigation,
			'CATEGORY_BOX'	=> $category_box,
			'U_SEL_ACTION'	=> $this->u_action,
			'U_ACTION'		=> $this->u_action . '&amp;parent_id=' . $this->parent_id,
		));
	}

	function plugins($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;
		global $blog_plugins_path, $blog_plugins;

		$blog_plugins = new blog_plugins();
		$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';
		if ($blog_plugins->load_plugins(true) === false)
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

	function search($id, $mode)
	{
		global $user, $phpbb_root_path, $phpEx;

		$user->add_lang('acp/search');

		// For some this may be of help...
		@ini_set('memory_limit', '128M');

		$this->search_settings($id, $mode);
		$this->search_index($id, $mode);
	}

	function search_settings($id, $mode)
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

	function search_index($id, $mode)
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
						trigger_error(sprintf($user->lang['SEARCH_BREAK_CONTINUE_NOTICE'], $section, 3, $part, $part_cnt) . '<br/><br/><a href="' . $redirect_url . '">' . $user->lang['CONTINUE'] . '</a>');
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

	/**
	* Get category details
	*/
	function get_category_info($category_id)
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . BLOGS_CATEGORIES_TABLE . "
			WHERE category_id = $category_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error("Forum #$category_id does not exist", E_USER_ERROR);
		}

		return $row;
	}

	/**
	* Update category data
	*/
	function update_category_data(&$category_data)
	{
		global $db, $user, $cache;

		$errors = array();

		if (!$category_data['category_name'])
		{
			$errors[] = $user->lang['CATEGORY_NAME_EMPTY'];
		}

		// Unset data that are not database fields
		$category_data_sql = $category_data;

		// What are we going to do tonight Brain? The same thing we do everynight,
		// try to take over the world ... or decide whether to continue update
		// and if so, whether it's a new category/cat/link or an existing one
		if (sizeof($errors))
		{
			return $errors;
		}

		if (!isset($category_data_sql['category_id']))
		{
			// no category_id means we're creating a new category
			if ($category_data_sql['parent_id'])
			{
				$sql = 'SELECT left_id, right_id
					FROM ' . BLOGS_CATEGORIES_TABLE . '
						WHERE category_id = ' . $category_data_sql['parent_id'];
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['PARENT_NOT_EXIST'] . adm_back_link($this->u_action . '&amp;' . $this->parent_id), E_USER_WARNING);
				}

				$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . '
					SET left_id = left_id + 2, right_id = right_id + 2
					WHERE left_id > ' . $row['right_id'];
				$db->sql_query($sql);

				$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . '
					SET right_id = right_id + 2
					WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id';
				$db->sql_query($sql);

				$category_data_sql['left_id'] = $row['right_id'];
				$category_data_sql['right_id'] = $row['right_id'] + 1;
			}
			else
			{
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . BLOGS_CATEGORIES_TABLE;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$category_data_sql['left_id'] = $row['right_id'] + 1;
				$category_data_sql['right_id'] = $row['right_id'] + 2;
			}

			$sql = 'INSERT INTO ' . BLOGS_CATEGORIES_TABLE . ' ' . $db->sql_build_array('INSERT', $category_data_sql);
			$db->sql_query($sql);

			$category_data['category_id'] = $db->sql_nextid();

			add_log('admin', 'LOG_CATEGORY_ADD', $category_data['category_name']);
		}
		else
		{
			$row = $this->get_category_info($category_data_sql['category_id']);

			if (sizeof($errors))
			{
				return $errors;
			}

			if ($row['parent_id'] != $category_data_sql['parent_id'])
			{
				$errors = $this->move_category($category_data_sql['category_id'], $category_data_sql['parent_id']);
			}

			if (sizeof($errors))
			{
				return $errors;
			}

			unset($category_data_sql['type_action']);

			// Setting the category id to the category id is not really received well by some dbs. ;)
			$category_id = $category_data_sql['category_id'];
			unset($category_data_sql['category_id']);

			$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $category_data_sql) . '
				WHERE category_id = ' . $category_id;
			$db->sql_query($sql);

			// Add it back
			$category_data['category_id'] = $category_id;

			add_log('admin', 'LOG_CATEGORY_EDIT', $category_data['category_name']);
		}

		return $errors;
	}

	/**
	* Move category
	*/
	function move_category($from_id, $to_id)
	{
		global $db, $user;

		$to_data = $moved_ids = $errors = array();

		// Check if we want to move to a parent with link type
		if ($to_id > 0)
		{
			$to_data = $this->get_category_info($to_id);
		}

		$moved_categories = get_category_branch($from_id, 'children', 'descending');
		$from_data = $moved_categories[0];
		$diff = sizeof($moved_categories) * 2;

		$moved_ids = array();
		for ($i = 0; $i < sizeof($moved_categories); ++$i)
		{
			$moved_ids[] = $moved_categories[$i]['category_id'];
		}

		// Resync parents
		$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . "
			SET right_id = right_id - $diff
			WHERE left_id < " . $from_data['right_id'] . "
				AND right_id > " . $from_data['right_id'];
		$db->sql_query($sql);

		// Resync righthand side of tree
		$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . "
			SET left_id = left_id - $diff, right_id = right_id - $diff
			WHERE left_id > " . $from_data['right_id'];
		$db->sql_query($sql);

		if ($to_id > 0)
		{
			// Retrieve $to_data again, it may have been changed...
			$to_data = $this->get_category_info($to_id);

			// Resync new parents
			$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . "
				SET right_id = right_id + $diff
				WHERE " . $to_data['right_id'] . ' BETWEEN left_id AND right_id
					AND ' . $db->sql_in_set('category_id', $moved_ids, true);
			$db->sql_query($sql);

			// Resync the righthand side of the tree
			$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . "
				SET left_id = left_id + $diff, right_id = right_id + $diff
				WHERE left_id > " . $to_data['right_id'] . '
					AND ' . $db->sql_in_set('category_id', $moved_ids, true);
			$db->sql_query($sql);

			// Resync moved branch
			$to_data['right_id'] += $diff;

			if ($to_data['right_id'] > $from_data['right_id'])
			{
				$diff = '+ ' . ($to_data['right_id'] - $from_data['right_id'] - 1);
			}
			else
			{
				$diff = '- ' . abs($to_data['right_id'] - $from_data['right_id'] - 1);
			}
		}
		else
		{
			$sql = 'SELECT MAX(right_id) AS right_id
				FROM ' . BLOGS_CATEGORIES_TABLE . '
				WHERE ' . $db->sql_in_set('category_id', $moved_ids, true);
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$diff = '+ ' . ($row['right_id'] - $from_data['left_id'] + 1);
		}

		$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . "
			SET left_id = left_id $diff, right_id = right_id $diff
			WHERE " . $db->sql_in_set('category_id', $moved_ids);
		$db->sql_query($sql);

		return $errors;
	}

	/**
	* Move category content from one to another category
	*/
	function move_category_content($from_id, $to_id, $sync = true)
	{
		global $db;

		$sql = 'SELECT count(blog_id) AS total FROM ' . BLOGS_IN_CATEGORIES_TABLE . '
			WHERE category_id = \'' . $from_id . '\'';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		if ($row !== false)
		{
			$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . '
				SET blog_count = blog_count + ' . $row['total'] . '
					WHERE category_id = \'' . $to_id . '\'';
			$db->sql_query($sql);
		}

		$sql = 'UPDATE ' . BLOGS_IN_CATEGORIES_TABLE . "
			SET category_id = '$to_id'
			WHERE category_id = '$from_id'";
		$db->sql_query($sql);

		return array();
	}

	/**
	* Delete category content
	*/
	function delete_category_content($category_id)
	{
		global $db;

		$sql = 'DELETE FROM ' . BLOGS_IN_CATEGORIES_TABLE . "
			WHERE category_id = $category_id";
		$db->sql_query($sql);

		return array();
	}

	/**
	* Remove complete category
	*/
	function delete_category($category_id, $action_blogs = 'delete', $action_subcategories = 'delete', $blogs_to_id = 0, $subcategories_to_id = 0)
	{
		global $db, $user, $cache;

		$category_data = $this->get_category_info($category_id);

		$errors = array();
		$log_action_posts = $log_action_categories = $posts_to_name = $subcategories_to_name = '';
		$category_ids = array($category_id);

		if (sizeof($errors))
		{
			return $errors;
		}

		if ($action_blogs == 'delete')
		{
			$errors = array_merge($errors, $this->delete_category_content($category_id));
		}
		else if ($action_blogs == 'move')
		{
			if (!$blogs_to_id)
			{
				$errors[] = $user->lang['NO_DESTINATION_CATEGORY'];
			}
			else
			{
				$sql = 'SELECT category_name
					FROM ' . BLOGS_CATEGORIES_TABLE . '
					WHERE category_id = ' . $blogs_to_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					$errors[] = $user->lang['NO_CATEGORY'];
				}
				else
				{
					$blogs_to_name = $row['category_name'];
					$errors = array_merge($errors, $this->move_category_content($category_id, $blogs_to_id));
				}
			}
		}

		if (sizeof($errors))
		{
			return $errors;
		}

		if ($action_subcategories == 'delete')
		{
			$rows = get_category_branch($category_id, 'children', 'descending', false);

			foreach ($rows as $row)
			{
				$category_ids[] = $row['category_id'];
				$errors = array_merge($errors, $this->delete_category_content($row['category_id']));
			}

			if (sizeof($errors))
			{
				return $errors;
			}

			$diff = sizeof($category_ids) * 2;

			$sql = 'DELETE FROM ' . BLOGS_CATEGORIES_TABLE . '
				WHERE ' . $db->sql_in_set('category_id', $category_ids);
			$db->sql_query($sql);
		}
		else if ($action_subcategories == 'move')
		{
			if (!$subcategories_to_id)
			{
				$errors[] = $user->lang['NO_DESTINATION_FORUM'];
			}
			else
			{
				$log_action_categories = 'MOVE_CATEGORIES';

				$sql = 'SELECT category_name
					FROM ' . BLOGS_CATEGORIES_TABLE . '
					WHERE category_id = ' . $subcategories_to_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					$errors[] = $user->lang['NO_CATEGORY'];
				}
				else
				{
					$subcategories_to_name = $row['category_name'];

					$sql = 'SELECT category_id
						FROM ' . BLOGS_CATEGORIES_TABLE . "
						WHERE parent_id = $category_id";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$this->move_category($row['category_id'], $subcategories_to_id);
					}
					$db->sql_freeresult($result);

					// Grab new category data for correct tree updating later
					$category_data = $this->get_category_info($category_id);

					$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . "
						SET parent_id = $subcategories_to_id
						WHERE parent_id = $category_id";
					$db->sql_query($sql);

					$diff = 2;
					$sql = 'DELETE FROM ' . BLOGS_CATEGORIES_TABLE . "
						WHERE category_id = $category_id";
					$db->sql_query($sql);
				}
			}

			if (sizeof($errors))
			{
				return $errors;
			}
		}
		else
		{
			$diff = 2;
			$sql = 'DELETE FROM ' . BLOGS_CATEGORIES_TABLE . "
				WHERE category_id = $category_id";
			$db->sql_query($sql);
		}

		// Resync tree
		$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . "
			SET right_id = right_id - $diff
			WHERE left_id < {$category_data['right_id']} AND right_id > {$category_data['right_id']}";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . "
			SET left_id = left_id - $diff, right_id = right_id - $diff
			WHERE left_id > {$category_data['right_id']}";
		$db->sql_query($sql);

		add_log('admin', 'LOG_CATEGORY_DELETE');

		return $errors;
	}

	/**
	* Move category position by $steps up/down
	*/
	function move_category_by($category_row, $action = 'move_up', $steps = 1)
	{
		global $db;

		/**
		* Fetch all the siblings between the module's current spot
		* and where we want to move it to. If there are less than $steps
		* siblings between the current spot and the target then the
		* module will move as far as possible
		*/
		$sql = 'SELECT category_id, category_name, left_id, right_id
			FROM ' . BLOGS_CATEGORIES_TABLE . "
			WHERE parent_id = {$category_row['parent_id']}
				AND " . (($action == 'move_up') ? "right_id < {$category_row['right_id']} ORDER BY right_id DESC" : "left_id > {$category_row['left_id']} ORDER BY left_id ASC");
		$result = $db->sql_query_limit($sql, $steps);

		$target = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$target = $row;
		}
		$db->sql_freeresult($result);

		if (!sizeof($target))
		{
			// The category is already on top or bottom
			return false;
		}

		/**
		* $left_id and $right_id define the scope of the nodes that are affected by the move.
		* $diff_up and $diff_down are the values to substract or add to each node's left_id
		* and right_id in order to move them up or down.
		* $move_up_left and $move_up_right define the scope of the nodes that are moving
		* up. Other nodes in the scope of ($left_id, $right_id) are considered to move down.
		*/
		if ($action == 'move_up')
		{
			$left_id = $target['left_id'];
			$right_id = $category_row['right_id'];

			$diff_up = $category_row['left_id'] - $target['left_id'];
			$diff_down = $category_row['right_id'] + 1 - $category_row['left_id'];

			$move_up_left = $category_row['left_id'];
			$move_up_right = $category_row['right_id'];
		}
		else
		{
			$left_id = $category_row['left_id'];
			$right_id = $target['right_id'];

			$diff_up = $category_row['right_id'] + 1 - $category_row['left_id'];
			$diff_down = $target['right_id'] - $category_row['right_id'];

			$move_up_left = $category_row['right_id'] + 1;
			$move_up_right = $target['right_id'];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . "
			SET left_id = left_id + CASE
				WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			right_id = right_id + CASE
				WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END
			WHERE
				left_id BETWEEN {$left_id} AND {$right_id}
				AND right_id BETWEEN {$left_id} AND {$right_id}";
		$db->sql_query($sql);

		return $target['category_name'];
	}
}
?>