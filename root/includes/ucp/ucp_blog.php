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

class ucp_blog
{
	var $u_action;

	function main($id, $mode)
	{
		global $cache, $template, $user, $db, $config, $phpEx, $phpbb_root_path;
		global $blog_plugins, $blog_plugins_path;

		$submit = (isset($_POST['submit'])) ? true : false;
		$preview = (isset($_POST['preview'])) ? true : false;
		$error = array();

		define('IN_BLOG', true); // So the header does not try to reload these files
		include($phpbb_root_path . 'blog/functions.' . $phpEx);
		include($phpbb_root_path . 'blog/permissions.' . $phpEx);
		include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);

		$blog_plugins = new blog_plugins();
		$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';
		$blog_plugins->load_plugins();

		$blog_plugins->plugin_do('blog_ucp_start');

		switch ($mode)
		{
			case 'ucp_blog_permissions' :
				if ($submit)
				{
					$sql_ary = array(
						'perm_guest'		=> request_var('guest_permissions', 1),
						'perm_registered'	=> request_var('registered_permissions', 2),
						'perm_foe'			=> request_var('foe_permissions', 0),
						'perm_friend'		=> request_var('friend_permissions', 2),
					);

					update_user_blog_settings($user->data['user_id'], $sql_ary);
				}
				else
				{
					global $user_settings;
					get_user_settings($user->data['user_id']);

					$template->assign_vars(array(
						'SET_PERMISSIONS'				=> true,
					));
					$permission_settings = array(
						array(
							'TITLE'			=> $user->lang['GUEST_PERMISSIONS'],
							'NAME'			=> 'guest_permissions',
							'DEFAULT'		=> ($user_settings) ? $user_settings['perm_guest'] : 1,
						),
						array(
							'TITLE'			=> $user->lang['REGISTERED_PERMISSIONS'],
							'NAME'			=> 'registered_permissions',
							'DEFAULT'		=> ($user_settings) ? $user_settings['perm_registered'] : 2,
						),
					);

					if ($config['user_blog_enable_zebra'])
					{
						$permission_settings[] = array(
								'TITLE'			=> $user->lang['FOE_PERMISSIONS'],
								'NAME'			=> 'foe_permissions',
								'DEFAULT'		=> ($user_settings) ? $user_settings['perm_foe'] : 0,
							);
						$permission_settings[] = array(
								'TITLE'			=> $user->lang['FRIEND_PERMISSIONS'],
								'NAME'			=> 'friend_permissions',
								'DEFAULT'		=> ($user_settings) ? $user_settings['perm_friend'] : 2,
							);
					}

					$blog_plugins->plugin_do_arg_ref('blog_ucp_permissions', $permission_settings);

					foreach ($permission_settings as $row)
					{
						$template->assign_block_vars('permissions', $row);
					}
				}
			break;
			case 'ucp_blog_title_description' :
				include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
				include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
				include($phpbb_root_path . 'blog/post_options.' . $phpEx);

				global $user_settings;
				get_user_settings($user->data['user_id']);

				$user->add_lang('posting');

				$post_options = new post_options;
				$post_options->set_status(true, true, true);
				$post_options->set_in_template();

				if ($submit || $preview)
				{
					// see if they tried submitting a message or suject(if they hit preview or submit) put it in an array for consistency with the edit mode
					$blog_title = utf8_normalize_nfc(request_var('title', '', true));
					$blog_description = utf8_normalize_nfc(request_var('message', '', true));

					// set up the message parser to parse BBCode, Smilies, etc
					$message_parser = new parse_message();
					$message_parser->message = $blog_description;
					$message_parser->parse($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, $post_options->img_status, $post_options->flash_status, $post_options->bbcode_status, $post_options->url_status);
				}
				else
				{
					if ($user_settings)
					{
						$blog_title = $user_settings['title'];
						$blog_description = $user_settings['description'];
						decode_message($blog_description, $user_settings['description_bbcode_uid']);
					}
					else
					{
						$blog_title = $blog_description = '';
					}
				}
				
				if (!$submit || sizeof($error))
				{
					if ($preview && !sizeof($error))
					{
						$preview_message = $message_parser->format_display($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, false);

						// output some data to the template parser
						$template->assign_vars(array(
							'S_DISPLAY_PREVIEW'			=> true,
							'PREVIEW_SUBJECT'			=> censor_text($blog_title),
							'PREVIEW_MESSAGE'			=> $preview_message,
							'POST_DATE'					=> $user->format_date(time()),
						));
					}

					// Generate smiley listing
					generate_smilies('inline', false);

					// Build custom bbcodes array
					display_custom_bbcodes();

					$template->assign_vars(array(
						'S_PREVIEW_BUTTON'		=> true,
						'TITLE'					=> $blog_title,
						'MESSAGE'				=> $blog_description,
					));
				}
				else if ($submit)
				{
					$sql_ary = array(
						'user_id'						=> $user->data['user_id'],
						'title'							=> $blog_title,
						'description'					=> $message_parser->message,
						'description_bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
						'description_bbcode_uid'		=> $message_parser->bbcode_uid,
					);
					unset($message_parser);

					update_user_blog_settings($user->data['user_id'], $sql_ary);
				}
			break;
			default;
				$temp = array('mode' => $mode, 'submit' => $submit, 'error' => $error, 'default' => true);
				$blog_plugins->plugin_do_arg_ref('blog_ucp_mode', $temp);
				if (!$temp['default'])
				{
					trigger_error('NO_MODE');
				}
				$error = $temp['error'];
		}

		if ($submit && !count($error))
		{
			$cache->destroy('_blog_settings_' . $user->data['user_id']);

			meta_refresh(3, $this->u_action);
			$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
			trigger_error($message);
		}

		$template->assign_vars(array(
			'L_TITLE'				=> $user->lang[strtoupper($mode)],
			'L_TITLE_EXPLAIN'		=> $user->lang[strtoupper($mode) . '_EXPLAIN'],
			'ERROR'					=> (count($error)) ? implode($error, '<br/>') : false,
			'MODE'					=> $mode,
		));

		$this->tpl_name = 'ucp_blog';
		$this->page_title = strtoupper($mode);
	}
}

?>