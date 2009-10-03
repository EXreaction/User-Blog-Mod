<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: ucp_blog.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
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
		global $auth, $cache, $template, $user, $db, $config, $phpEx, $phpbb_root_path;
		global $blog_plugins, $blog_plugins_path, $user_settings;

		$preview = (isset($_POST['preview'])) ? true : false;
		$submit = (isset($_POST['submit'])) ? true : false;
		$error = array();

		$user->add_lang(array('mods/blog/common', 'mods/blog/ucp'));

		include($phpbb_root_path . 'blog/functions.' . $phpEx);

		blog_plugins::plugin_do('ucp_start');

		get_user_settings($user->data['user_id']);

		switch ($mode)
		{
			case 'ucp_blog_settings' :
				$subscription_types = get_blog_subscription_types();

				if ($submit)
				{
					$sql_ary = array(
						'instant_redirect'				=> request_var('instant_redirect', 0),
						'blog_subscription_default'		=> 0,
						'blog_style'					=> ($auth->acl_get('u_blog_style')) ? request_var('blog_style', '') : '',
						'blog_css'						=> ($auth->acl_get('u_blog_css')) ? request_var('blog_css', '') : '',
					);

					if ($config['user_blog_subscription_enabled'])
					{
						foreach ($subscription_types as $type => $name)
						{
							if (request_var('subscription_' . $type, false))
							{
								$sql_ary['blog_subscription_default'] += $type;
							}
						}
					}

					update_user_blog_settings($user->data['user_id'], $sql_ary);
				}
				else
				{
					if ($config['user_blog_subscription_enabled'])
					{
						$subscribed = array();
						if (isset($user_settings[$user->data['user_id']]))
						{
							foreach ($subscription_types as $type => $name)
							{
								// Bitwise check
								if ($user_settings[$user->data['user_id']]['blog_subscription_default'] & $type)
								{
									$subscribed[$type] = true;
								}
							}
						}
						foreach ($subscription_types as $type => $name)
						{
							$template->assign_block_vars('subscriptions', array(
								'TYPE'		=> 'subscription_' . $type,
								'NAME'		=> ((isset($user->lang[$name])) ? $user->lang[$name] : $name),
								'S_CHECKED'	=> ((isset($subscribed[$type])) ? true : false),
							));
						}
					}

					if ($auth->acl_get('u_blog_style'))
					{
						$available_styles = array(array('name' => $user->lang['NONE'], 'value' => 0, 'demo' => $phpbb_root_path . 'images/spacer.gif'));
						$sql = 'SELECT * FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' st WHERE style_active = 1 AND s.template_id = st.template_id';
						$result = $db->sql_query($sql);
						while ($row = $db->sql_fetchrow($result))
						{
							$demo = $phpbb_root_path . 'images/spacer.gif';
							if (@file_exists($phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.png'))
							{
								$demo = $phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.png';
							}
							else if (@file_exists($phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.gif'))
							{
								$demo = $phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.gif';
							}
							else if (@file_exists($phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.jpg'))
							{
								$demo = $phpbb_root_path . 'styles/' . $row['template_path'] . '/template/blog/demo.jpg';
							}

							$available_styles[] = array('name' => $row['style_name'], 'value' => $row['style_id'], 'demo' => $demo);
						}
						$db->sql_freeresult($result);

						$dh = @opendir($phpbb_root_path . 'blog/styles/');

						if ($dh)
						{
							while (($file = readdir($dh)) !== false)
							{
								if (file_exists($phpbb_root_path . 'blog/styles/' . $file . '/style.' . $phpEx))
								{
									// Inside of the style.php file, add to the $available_styles array
									include($phpbb_root_path . 'blog/styles/' . $file . '/style.' . $phpEx);
								}
							}

							closedir($dh);
						}

						foreach ($available_styles as $row)
						{
							if (isset($user_settings[$user->data['user_id']]) && $user_settings[$user->data['user_id']]['blog_style'] == $row['value'] && isset($row['demo']) && $row['demo'])
							{
								$default_demo = $row['demo'];
							}

							$template->assign_block_vars('blog_styles', array(
								'VALUE'			=> $row['value'],
								'SELECTED'		=> (isset($user_settings[$user->data['user_id']]) && $user_settings[$user->data['user_id']]['blog_style'] == $row['value']) ? true : false,
								'NAME'			=> $row['name'],
								'BLOG_CSS'		=> (isset($row['blog_css']) && $row['blog_css']) ? true : false,
								'DEMO'			=> (isset($row['demo']) && $row['demo']) ? $row['demo'] : '',
							));
						}
					}

					$template->assign_vars(array(
						'S_BLOG_INSTANT_REDIRECT'	=> (isset($user_settings[$user->data['user_id']])) ? $user_settings[$user->data['user_id']]['instant_redirect'] : 0,
						'S_SUBSCRIPTIONS'			=> ($config['user_blog_subscription_enabled']) ? true : false,
						'S_BLOG_STYLE'				=> (isset($available_styles) && sizeof($available_styles) > 1) ? true : false,
						'S_BLOG_CSS'				=> ($auth->acl_get('u_blog_css')) ? true : false,
						'DEFAULT_DEMO'				=> (isset($default_demo)) ? $default_demo : $phpbb_root_path . 'images/spacer.gif',
						'BLOG_CSS'					=> (isset($user_settings[$user->data['user_id']])) ? $user_settings[$user->data['user_id']]['blog_css'] : '',
					));
				}
			break;
			case 'ucp_blog_permissions' :
				if (!$config['user_blog_user_permissions'])
				{
					$error[] = $user->lang['USER_PERMISSIONS_DISABLED'];

					$template->assign_vars(array(
						'PERMISSIONS_DISABLED'	=> true,
					));
				}
				else
				{
					if ($submit)
					{
						$sql_ary = array(
							'perm_guest'		=> request_var('perm_guest', 1),
							'perm_registered'	=> request_var('perm_registered', 2),
							'perm_foe'			=> request_var('perm_foe', 0),
							'perm_friend'		=> request_var('perm_friend', 2),
						);

						update_user_blog_settings($user->data['user_id'], $sql_ary, ((isset($_POST['resync'])) ? true : false));
					}
					else
					{
						permission_settings_builder();
					}
				}
			break;
			case 'ucp_blog_title_description' :
				include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
				include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
				include($phpbb_root_path . 'blog/includes/functions_posting.' . $phpEx);
				if (!function_exists('display_custom_bbcodes'))
				{
					include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
				}

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
					if (isset($user_settings[$user->data['user_id']]))
					{
						$blog_title = $user_settings[$user->data['user_id']]['title'];
						$blog_description = $user_settings[$user->data['user_id']]['description'];
						decode_message($blog_description, $user_settings[$user->data['user_id']]['description_bbcode_uid']);
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
				$default = true;
				$temp = compact('mode', 'error', 'default');
				blog_plugins::plugin_do_ref('ucp_default', $temp); // make sure you set default to false if you use your own page
				extract($temp);
				if ($default)
				{
					trigger_error('NO_MODE');
				}
		}

		blog_plugins::plugin_do('ucp_end');

		if ($submit && !sizeof($error))
		{
			$cache->destroy('_blog_settings_' . $user->data['user_id']);

			meta_refresh(3, $this->u_action);
			$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
			trigger_error($message);
		}

		$template->assign_vars(array(
			'L_TITLE'				=> $user->lang[strtoupper($mode)],
			'L_TITLE_EXPLAIN'		=> $user->lang[strtoupper($mode) . '_EXPLAIN'],

			'ERROR'					=> (sizeof($error)) ? implode($error, '<br />') : false,
			'MODE'					=> $mode,

			'S_UCP_ACTION'			=> $this->u_action,
		));

		$this->tpl_name = 'blog/ucp_blog';
		$this->page_title = strtoupper($mode);
	}
}

?>