<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('UMIL_AUTO', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

define('IN_PHPBB', true);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'blog/functions.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/blog/umil', 'mods/blog/common', 'mods/blog/setup'));

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

$mod_name = 'USER_BLOG_MOD';
$version_config_name = 'user_blog_version';

/*
* Since this was implimented long after the mod was started, it is not really possible to write the install/uninstall instructions for all versions the way it was setup.
* So instead of making a mess trying to do that, 0.9.0 will be are starting point and any versions prior to that will need to use the old update script.
*/
if (isset($config[$version_config_name]) && version_compare($config[$version_config_name], '0.9.0', '<'))
{
	trigger_error(sprintf($user->lang['USE_OLD_UPDATE_SCRIPT'], append_sid("{$phpbb_root_path}blog.{$phpEx}?page=update")));
}

$versions = array(
	'0.9.0'		=> array(
		'table_add'		=> array(
			array('phpbb_blogs', array(
				'COLUMNS'		=> array(
					'blog_id'				=> array('UINT', NULL, 'auto_increment'),
					'user_id'				=> array('UINT', 0),
					'user_ip'				=> array('VCHAR:40', ''),
					'blog_subject'			=> array('STEXT_UNI', '', 'true_sort'),
					'blog_text'				=> array('MTEXT_UNI', ''),
					'blog_checksum'			=> array('VCHAR:32', ''),
					'blog_time'				=> array('TIMESTAMP', 0),
					'blog_approved'			=> array('BOOL', 1),
					'blog_reported'			=> array('BOOL', 0),
					'enable_bbcode'			=> array('BOOL', 1),
					'enable_smilies'		=> array('BOOL', 1),
					'enable_magic_url'		=> array('BOOL', 1),
					'bbcode_bitfield'		=> array('VCHAR:255', ''),
					'bbcode_uid'			=> array('VCHAR:8', ''),
					'blog_edit_time'		=> array('TIMESTAMP', 0),
					'blog_edit_reason'		=> array('STEXT_UNI', ''),
					'blog_edit_user'		=> array('UINT', 0),
					'blog_edit_count'		=> array('USINT', 0),
					'blog_edit_locked'		=> array('BOOL', 0),
					'blog_deleted'			=> array('UINT', 0),
					'blog_deleted_time'		=> array('TIMESTAMP', 0),
					'blog_read_count'		=> array('UINT', 1),
					'blog_reply_count'		=> array('UINT', 0),
					'blog_real_reply_count'	=> array('UINT', 0),
					'blog_attachment'		=> array('BOOL', 0),
					'perm_guest'			=> array('TINT:1', 1),
					'perm_registered'		=> array('TINT:1', 2),
					'perm_foe'				=> array('TINT:1', 0),
					'perm_friend'			=> array('TINT:1', 2),
					'rating'				=> array('DECIMAL:6', 0),
					'num_ratings'			=> array('UINT', 0),
					'poll_title'			=> array('STEXT_UNI', '', 'true_sort'),
					'poll_start'			=> array('TIMESTAMP', 0),
					'poll_length'			=> array('TIMESTAMP', 0),
					'poll_max_options'		=> array('TINT:4', 1),
					'poll_last_vote'		=> array('TIMESTAMP', 0),
					'poll_vote_change'		=> array('BOOL', 0),
				),
				'PRIMARY_KEY'	=> 'blog_id',
				'KEYS'			=> array(
					'user_id'				=> array('INDEX', 'user_id'),
					'user_ip'				=> array('INDEX', 'user_ip'),
					'blog_approved'			=> array('INDEX', 'blog_approved'),
					'blog_deleted'			=> array('INDEX', 'blog_deleted'),
					'perm_guest'			=> array('INDEX', 'perm_guest'),
					'perm_registered'		=> array('INDEX', 'perm_registered'),
					'perm_foe'				=> array('INDEX', 'perm_foe'),
					'perm_friend'			=> array('INDEX', 'perm_friend'),
					'rating'				=> array('INDEX', 'rating'),
				),
			)),
			array('phpbb_blogs_attachment', array(
				'COLUMNS'		=> array(
					'attach_id'				=> array('UINT', NULL, 'auto_increment'),
					'blog_id'				=> array('UINT', 0),
					'reply_id'				=> array('UINT', 0),
					'poster_id'				=> array('UINT', 0),
					'is_orphan'				=> array('BOOL', 1),
					'physical_filename'		=> array('VCHAR', ''),
					'real_filename'			=> array('VCHAR', ''),
					'download_count'		=> array('UINT', 0),
					'attach_comment'		=> array('TEXT_UNI', ''),
					'extension'				=> array('VCHAR:100', ''),
					'mimetype'				=> array('VCHAR:100', ''),
					'filesize'				=> array('UINT:20', 0),
					'filetime'				=> array('TIMESTAMP', 0),
					'thumbnail'				=> array('BOOL', 0),
				),
				'PRIMARY_KEY'	=> 'attach_id',
				'KEYS'			=> array(
					'blog_id'				=> array('INDEX', 'blog_id'),
					'reply_id'				=> array('INDEX', 'reply_id'),
					'filetime'				=> array('INDEX', 'filetime'),
					'poster_id'				=> array('INDEX', 'poster_id'),
					'is_orphan'				=> array('INDEX', 'is_orphan'),
				),
			)),
			array('phpbb_blogs_categories', array(
				'COLUMNS'		=> array(
					'category_id'					=> array('UINT', NULL, 'auto_increment'),
					'parent_id'						=> array('UINT', 0),
					'left_id'						=> array('UINT', 0),
					'right_id'						=> array('UINT', 0),
					'category_name'					=> array('STEXT_UNI', '', 'true_sort'),
					'category_description'			=> array('MTEXT_UNI', ''),
					'category_description_bitfield'	=> array('VCHAR:255', ''),
					'category_description_uid'		=> array('VCHAR:8', ''),
					'category_description_options'	=> array('UINT:11', 7),
					'rules'							=> array('MTEXT_UNI', ''),
					'rules_bitfield'				=> array('VCHAR:255', ''),
					'rules_uid'						=> array('VCHAR:8', ''),
					'rules_options'					=> array('UINT:11', 7),
					'blog_count'					=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> 'category_id',
				'KEYS'			=> array(
					'left_right_id'			=> array('INDEX', array('left_id', 'right_id')),
				),
			)),
			array('phpbb_blogs_in_categories', array(
				'COLUMNS'		=> array(
					'blog_id'						=> array('UINT', 0),
					'category_id'					=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> array('blog_id', 'category_id'),
			)),
			array('phpbb_blogs_plugins', array(
				'COLUMNS'		=> array(
					'plugin_id'				=> array('UINT', NULL, 'auto_increment'),
					'plugin_name'			=> array('STEXT_UNI', '', 'true_sort'),
					'plugin_enabled'		=> array('BOOL', 0),
					'plugin_version'		=> array('XSTEXT_UNI', '', 'true_sort'),
				),
				'PRIMARY_KEY'	=> 'plugin_id',
				'KEYS'			=> array(
					'plugin_name'			=> array('INDEX', 'plugin_name'),
					'plugin_enabled'		=> array('INDEX', 'plugin_enabled'),
				),
			)),
			array('phpbb_blogs_poll_options', array(
				'COLUMNS'		=> array(
					'poll_option_id'		=> array('TINT:4', 0),
					'blog_id'				=> array('UINT', 0),
					'poll_option_text'		=> array('TEXT_UNI', ''),
					'poll_option_total'		=> array('UINT', 0),
				),
				'KEYS'			=> array(
					'poll_opt_id'			=> array('INDEX', 'poll_option_id'),
					'blog_id'				=> array('INDEX', 'blog_id'),
				),
			)),
			array('phpbb_blogs_poll_votes', array(
				'COLUMNS'		=> array(
					'blog_id'				=> array('UINT', 0),
					'poll_option_id'		=> array('TINT:4', 0),
					'vote_user_id'			=> array('UINT', 0),
					'vote_user_ip'			=> array('VCHAR:40', ''),
				),
				'KEYS'			=> array(
					'blog_id'				=> array('INDEX', 'blog_id'),
					'vote_user_id'			=> array('INDEX', 'vote_user_id'),
					'vote_user_ip'			=> array('INDEX', 'vote_user_ip'),
				),
			)),
			array('phpbb_blogs_ratings', array(
				'COLUMNS'		=> array(
					'blog_id'						=> array('UINT', 0),
					'user_id'						=> array('UINT', 0),
					'rating'						=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> array('blog_id', 'user_id'),
			)),
			array('phpbb_blogs_reply', array(
				'COLUMNS'		=> array(
					'reply_id'				=> array('UINT', NULL, 'auto_increment'),
					'blog_id'				=> array('UINT', 0),
					'user_id'				=> array('UINT', 0),
					'user_ip'				=> array('VCHAR:40', ''),
					'reply_subject'			=> array('STEXT_UNI', '', 'true_sort'),
					'reply_text'			=> array('MTEXT_UNI', ''),
					'reply_checksum'		=> array('VCHAR:32', ''),
					'reply_time'			=> array('TIMESTAMP', 0),
					'reply_approved'		=> array('BOOL', 1),
					'reply_reported'		=> array('BOOL', 0),
					'enable_bbcode'			=> array('BOOL', 1),
					'enable_smilies'		=> array('BOOL', 1),
					'enable_magic_url'		=> array('BOOL', 1),
					'bbcode_bitfield'		=> array('VCHAR:255', ''),
					'bbcode_uid'			=> array('VCHAR:8', ''),
					'reply_edit_time'		=> array('TIMESTAMP', 0),
					'reply_edit_reason'		=> array('STEXT_UNI', ''),
					'reply_edit_user'		=> array('UINT', 0),
					'reply_edit_count'		=> array('UINT', 0),
					'reply_edit_locked'		=> array('BOOL', 0),
					'reply_deleted'			=> array('UINT', 0),
					'reply_deleted_time'	=> array('TIMESTAMP', 0),
					'reply_attachment'		=> array('BOOL', 0),
				),
				'PRIMARY_KEY'	=> 'reply_id',
				'KEYS'			=> array(
					'blog_id'				=> array('INDEX', 'blog_id'),
					'user_id'				=> array('INDEX', 'user_id'),
					'user_ip'				=> array('INDEX', 'user_ip'),
					'reply_approved'		=> array('INDEX', 'reply_approved'),
					'reply_deleted'			=> array('INDEX', 'reply_deleted'),
				),
			)),
			array('phpbb_blogs_subscription', array(
				'COLUMNS'		=> array(
					'sub_user_id'			=> array('UINT', 0),
					'sub_type'				=> array('UINT:11', 0),
					'blog_id'				=> array('UINT', 0),
					'user_id'				=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> array('sub_user_id', 'sub_type', 'blog_id', 'user_id'),
			)),
			array('phpbb_blogs_users', array(
				'COLUMNS'		=> array(
					'user_id'				=> array('UINT', 0),
					'perm_guest'			=> array('TINT:1', 1),
					'perm_registered'		=> array('TINT:1', 2),
					'perm_foe'				=> array('TINT:1', 0),
					'perm_friend'			=> array('TINT:1', 2),
					'title'					=> array('STEXT_UNI', '', 'true_sort'),
					'description'			=> array('MTEXT_UNI', ''),
					'description_bbcode_bitfield'	=> array('VCHAR:255', ''),
					'description_bbcode_uid'		=> array('VCHAR:8', ''),
					'instant_redirect'				=> array('BOOL', 1),
					'blog_subscription_default'		=> array('UINT:11', 0),
					'blog_style'					=> array('STEXT_UNI', '', 'true_sort'),
					'blog_css'						=> array('MTEXT_UNI', ''),
				),
				'PRIMARY_KEY'	=> 'user_id',
			)),
			array('phpbb_blog_search_wordlist', array(
				'COLUMNS'		=> array(
					'word_id'			=> array('UINT', NULL, 'auto_increment'),
					'word_text'			=> array('VCHAR_UNI', ''),
					'word_common'		=> array('BOOL', 0),
					'word_count'		=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> 'word_id',
				'KEYS'			=> array(
					'word_text'			=> array('UNIQUE', 'word_text'),
					'word_count'		=> array('INDEX', 'word_count'),
				),
			)),
			array('phpbb_blog_search_wordmatch', array(
				'COLUMNS'		=> array(
					'blog_id'			=> array('UINT', 0),
					'reply_id'			=> array('UINT', 0),
					'word_id'			=> array('UINT', 0),
					'title_match'		=> array('BOOL', 0),
				),
				'KEYS'			=> array(
					'unique_match'		=> array('UNIQUE', array('blog_id', 'reply_id', 'word_id', 'title_match')),
					'word_id'			=> array('INDEX', 'word_id'),
					'blog_id'			=> array('INDEX', 'blog_id'),
					'reply_id'			=> array('INDEX', 'reply_id'),
				),
			)),
		),

		'table_column_add' => array(
			array(USERS_TABLE, 'blog_count', array('UINT', 0)),
			array(EXTENSION_GROUPS_TABLE, 'allow_in_blog', array('BOOL', 0)),
		),

		'module_add' => array(
			array('acp', 'ACP_CAT_DOT_MODS', 'ACP_BLOGS'),
			array('acp', 'ACP_BLOGS', array('module_basename' => 'blogs')),

			array('mcp', '', 'MCP_BLOG'),
			array('mcp', 'MCP_BLOG', array('module_basename' => 'blog')),

			array('ucp', '', 'UCP_BLOG'),
			array('ucp', 'UCP_BLOG', array('module_basename' => 'blog')),
		),

		'permission_add' => array(
			'u_blogview',
			'u_blogpost',
			'u_blogedit',
			'u_blogdelete',
			'u_blognoapprove',
			'u_blogreport',
			'u_blogreply',
			'u_blogreplyedit',
			'u_blogreplydelete',
			'u_blogreplynoapprove',
			'u_blogbbcode',
			'u_blogsmilies',
			'u_blogimg',
			'u_blogurl',
			'u_blogflash',
			'u_blogmoderate',
			'u_blogattach',
			'u_blognolimitattach',
			'm_blogapprove',
			'm_blogedit',
			'm_bloglockedit',
			'm_blogdelete',
			'm_blogreport',
			'm_blogreplyapprove',
			'm_blogreplyedit',
			'm_blogreplylockedit',
			'm_blogreplydelete',
			'm_blogreplyreport',
			'a_blogmanage',
			'a_blogdelete',
			'a_blogreplydelete',
			'u_blog_vote',
			'u_blog_vote_change',
			'u_blog_create_poll',
			'u_blog_style',
			'u_blog_css',
		),

		'config_add'	=> array(
			array('user_blog_enable', 1),
			array('user_blog_custom_profile_enable', 0),
			array('user_blog_text_limit', 200),
			array('user_blog_user_text_limit', 1000),
			array('user_blog_inform', '2'),
			array('user_blog_always_show_blog_url', 0),
			array('user_blog_subscription_enabled', 1),
			array('user_blog_enable_zebra', 1),
			array('user_blog_enable_feeds', 1),
			array('user_blog_enable_plugins', 1),
			array('user_blog_seo', 0),
			array('user_blog_guest_captcha', 1),
			array('user_blog_user_permissions', 1),
			array('user_blog_search', 1),
			array('user_blog_search_type', 'fulltext_native'),
			array('user_blog_enable_ratings', 1),
			array('user_blog_min_rating', 1),
			array('user_blog_max_rating', 5),
			array('user_blog_enable_attachments', 1),
			array('user_blog_max_attachments', 3),
			array('num_blogs', 1, true),
			array('num_blog_replies', 0, true),
			array('user_blog_quick_reply', 1),
		),

		'custom'	=> 'ubm_custom_install',

		'cache_purge'	=> array(
			array(),
			array('imageset'),
			array('template'),
			array('theme'),
		),
	),

	'0.9.1'		=> array(),
	'1.0.0'		=> array(),
	'1.0.1'		=> array(
		'config_add'	=> array(
			array('user_blog_links_output_block', 1),
		),
	),
	'1.0.2'		=> array(
		'custom'	=> 'ubm_custom_install',
	),
	'1.0.3'		=> array(),
	'1.0.4'		=> array(
		'config_add'	=> array(
			array('user_blog_message_from', 2),
		),

		'custom'	=> 'ubm_custom_install',
	),
	'1.0.5'		=> array(),
	'1.0.6'		=> array(),
	'1.0.7'		=> array(),
	'1.0.8'		=> array(),
	'1.0.9'		=> array(),
	'1.0.10'	=> array(),
	'1.0.11'	=> array(),
	'1.0.11-pl1'	=> array(),
	'1.0.12'	=> array(),
	'1.0.13'	=> array(),
);

include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

function ubm_custom_install($action, $version)
{
	global $db, $user, $umil, $phpbb_root_path, $phpEx;

	switch ($version)
	{
		case '0.9.0' :
			switch ($action)
			{
				case 'install' :
					$umil->umil_start('INSTALLING_ARCHIVE_PLUGIN');
					$sql_ary = array(
						'plugin_name'		=> 'archive',
						'plugin_enabled'	=> 1,
						'plugin_version'	=> '1.0.0',
					);
					$sql = 'INSERT INTO ' . BLOGS_PLUGINS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
					$db->sql_query($sql);
					$umil->umil_end();

					$umil->umil_start('SETTING_DEFAULT_PERMISSIONS');
					$role_data = array(
						'ROLE_ADMIN_FULL'		=> array('a_blogmanage', 'a_blogdelete', 'a_blogreplydelete'),
						'ROLE_MOD_FULL'			=> array('m_blogapprove', 'm_blogedit', 'm_bloglockedit', 'm_blogdelete', 'm_blogreport', 'm_blogreplyapprove', 'm_blogreplyedit', 'm_blogreplylockedit', 'm_blogreplydelete', 'm_blogreplyreport'),
						'ROLE_MOD_STANDARD'		=> array('m_blogapprove', 'm_blogedit', 'm_bloglockedit', 'm_blogdelete', 'm_blogreport', 'm_blogreplyapprove', 'm_blogreplyedit', 'm_blogreplylockedit', 'm_blogreplydelete', 'm_blogreplyreport'),
						'ROLE_MOD_QUEUE'		=> array('m_blogapprove', 'm_blogedit', 'm_bloglockedit', 'm_blogreplyapprove', 'm_blogreplyedit', 'm_blogreplylockedit'),
						'ROLE_MOD_SIMPLE'		=> array('m_blogedit', 'm_bloglockedit', 'm_blogdelete', 'm_blogreplyedit', 'm_blogreplylockedit', 'm_blogreplydelete'),
						'ROLE_USER_FULL'		=> array('u_blog_css', 'u_blog_style', 'u_blog_vote', 'u_blog_vote_change', 'u_blog_create_poll', 'u_blogattach', 'u_blognolimitattach', 'u_blogview', 'u_blogpost', 'u_blogedit', 'u_blogdelete', 'u_blognoapprove', 'u_blogreport', 'u_blogreply', 'u_blogreplyedit', 'u_blogreplydelete', 'u_blogreplynoapprove', 'u_blogbbcode', 'u_blogsmilies', 'u_blogimg', 'u_blogurl', 'u_blogflash', 'u_blogmoderate'),
						'ROLE_USER_STANDARD'	=> array('u_blog_style', 'u_blog_vote', 'u_blog_vote_change', 'u_blog_create_poll', 'u_blogattach', 'u_blogview', 'u_blogpost', 'u_blogedit', 'u_blogdelete', 'u_blognoapprove', 'u_blogreport', 'u_blogreply', 'u_blogreplyedit', 'u_blogreplydelete', 'u_blogreplynoapprove', 'u_blogbbcode', 'u_blogsmilies', 'u_blogimg', 'u_blogurl', 'u_blogmoderate'),
						'ROLE_USER_LIMITED'		=> array('u_blog_vote', 'u_blog_vote_change', 'u_blogview', 'u_blogpost', 'u_blogedit', 'u_blogreport', 'u_blogreply', 'u_blogreplyedit', 'u_blogbbcode', 'u_blogsmilies', 'u_blogimg', 'u_blogurl'),
						'ROLE_USER_NOPM'		=> array('u_blog_style', 'u_blog_vote', 'u_blog_vote_change', 'u_blogview', 'u_blogpost', 'u_blogedit', 'u_blogreport', 'u_blogreply', 'u_blogreplyedit', 'u_blogbbcode', 'u_blogsmilies', 'u_blogimg', 'u_blogurl'),
						'ROLE_USER_NOAVATAR'	=> array('u_blog_style', 'u_blog_vote', 'u_blog_vote_change', 'u_blogview', 'u_blogpost', 'u_blogedit', 'u_blogreport', 'u_blogreply', 'u_blogreplyedit', 'u_blogbbcode', 'u_blogsmilies', 'u_blogimg', 'u_blogurl'),
					);

					foreach ($role_data as $role => $options)
					{
						$sql = 'SELECT role_id FROM ' . ACL_ROLES_TABLE . " WHERE role_name = '{$role}'";
						$db->sql_query($sql);
						$role_id = $db->sql_fetchfield('role_id');

						if ($role_id)
						{
							$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . ' WHERE ' . $db->sql_in_set('auth_option', $options);
							$result = $db->sql_query($sql);
							while ($row = $db->sql_fetchrow($result))
							{
								$sql_ary = array(
									'role_id'			=> $role_id,
									'auth_option_id'	=> $row['auth_option_id'],
									'auth_setting'		=> 1,
								);
								$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
								$db->sql_query($sql);
							}
						}

						$role_id = false;
					}
					$umil->umil_end();

					$umil->umil_start('ADDING_FIRST_BLOG');

					if (!class_exists('bbcode_firstpass'))
					{
						include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
					}

					$message_parser = new parse_message();
					$blog_search = setup_blog_search();

					$message_parser->message = $user->lang['WELCOME_MESSAGE'];
					$message_parser->parse(true, true, true);

					$sql_data = array(
						'user_id' 					=> $user->data['user_id'],
						'user_ip'					=> $user->data['user_ip'],
						'blog_time'					=> time(),
						'blog_subject'				=> $user->lang['WELCOME_SUBJECT'],
						'blog_text'					=> $message_parser->message,
						'blog_checksum'				=> md5($message_parser->message),
						'blog_approved' 			=> 1,
						'enable_bbcode' 			=> 1,
						'enable_smilies'			=> 1,
						'enable_magic_url'			=> 1,
						'bbcode_bitfield'			=> $message_parser->bbcode_bitfield,
						'bbcode_uid'				=> $message_parser->bbcode_uid,
						'blog_edit_reason'			=> '',
						'poll_title'				=> '',
					);
					$sql = 'INSERT INTO ' . BLOGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);
					$db->sql_query($sql);
					$blog_id = $db->sql_nextid();

					$blog_search->index('add', $blog_id, 0, $message_parser->message, $user->lang['WELCOME_SUBJECT'], $user->data['user_id']);

					$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = blog_count + 1 WHERE user_id = ' . $user->data['user_id'];
					$db->sql_query($sql);
					$umil->umil_end();
				break;
			}
		break;

		case '1.0.2' :
			if ($action == 'update')
			{
				$umil->umil_start('FIXING_MAX_POLL_OPTIONS');
				$db->sql_query('UPDATE ' . BLOGS_TABLE . ' SET poll_max_options = 1 WHERE poll_max_options < 1');
				$umil->umil_end();
			}
		break;

		case '1.0.4' :
			if ($action == 'update')
			{
				$umil->umil_start('FIXING_MISSING_STYLES');
				$style_ids = array();
				$result = $db->sql_query('SELECT style_id FROM ' . STYLES_TABLE);
				while ($row = $db->sql_fetchrow($result))
				{
					$style_ids[] = $row['style_id'];
				}
				$db->sql_freeresult($result);

				$db->sql_query('UPDATE ' . BLOGS_USERS_TABLE . ' SET blog_style = ' . $style_ids[0] . ' WHERE ' . $db->sql_in_set('blog_style', $style_ids, true));
				$umil->umil_end();
			}
		break;
	}
}
?>