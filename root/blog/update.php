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

// Generate the breadcrumbs
generate_blog_urls();
generate_blog_breadcrumbs($user->lang['UPDATE_BLOG']);

if (!isset($config['user_blog_version']))
{
	trigger_error('Either you do not have the User Blog Mod installed in your database, or you are running a very old version.<br/>If you have the mod installed already please delete the tables and information which was inserted by the version you used and reinstall the mod.');
}

if (!defined('BLOGS_TABLE') || !defined('BLOGS_REPLY_TABLE') || !defined('BLOGS_SUBSCRIPTION_TABLE') || !defined('BLOGS_USERS_TABLE'))
{
	trigger_error('UPDATE_IN_FILES_FIRST');
}

if ($user_blog_version == $config['user_blog_version'])
{
	trigger_error(sprintf($user->lang['ALREADY_UPDATED'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx") . '">', '</a>'));
}

if (strpos($user_blog_version, 'dev'))
{
	trigger_error('Automatic Updating is disabled for Dev versions.');
}

if (confirm_box(true))
{
	$sql_array = array();

	//Setup $auth_admin class so we can add permission options
	include($phpbb_root_path . '/includes/acp/auth.' . $phpEx);
	$auth_admin = new auth_admin();

	switch ($config['user_blog_version'])
	{
		case 'A6' :
		case 'A7' :
			$sql = 'ALTER TABLE ' . BLOGS_TABLE . ' ADD blog_real_reply_count MEDIUMINT( 8 ) NOT NULL DEFAULT \'0\'';
			$db->sql_query($sql);
		case 'A8' :
			resync_blog('real_reply_count');
			resync_blog('reply_count');

			$sql_array[] = 'CREATE TABLE ' . BLOGS_SUBSCRIPTION_TABLE . ' (
				sub_user_id mediumint(8) UNSIGNED DEFAULT \'0\' NOT NULL,
				sub_type tinyint(1) UNSIGNED DEFAULT \'0\' NOT NULL,
				blog_id mediumint(8) UNSIGNED DEFAULT \'0\' NOT NULL,
				user_id mediumint(8) UNSIGNED DEFAULT \'0\' NOT NULL,
				PRIMARY KEY (sub_user_id)
			)';

			$blog_permissions = array(
				'local'      => array(),
				'global'   => array(
					'm_blogreplyapprove',
					'm_blogreplyedit',
					'm_blogreplylockedit',
					'm_blogreplydelete',
					'm_blogreplyreport',
					)
			);
			$auth_admin->acl_add_option($blog_permissions);
		case 'A9' :
			set_config('user_blog_enable', 1, 0);
			set_config('user_blog_custom_profile_enable', 0, 0);
			set_config('user_blog_text_limit', '50', 0);
			set_config('user_blog_user_text_limit', '500', 0);
			set_config('user_blog_inform', '2', 0);
			set_config('user_blog_always_show_blog_url', 0, 0);
		case 'A10' :
			$sql_array[] = 'ALTER TABLE ' . BLOGS_TABLE . ' DROP blog_rating, DROP blog_num_ratings';

			$blog_permissions = array(
				'local'      => array(),
				'global'   => array(
					'u_blognocaptcha')
			);
			$auth_admin->acl_add_option($blog_permissions);

			set_config('user_blog_founder_all_perm', 1, 0);
		case 'A11' :
			set_config('user_blog_force_prosilver', 0, 0);
			set_config('user_blog_subscription_enabled', 1, 0);

			$sql_array[] = 'ALTER TABLE ' . BLOGS_SUBSCRIPTION_TABLE . ' DROP PRIMARY KEY ,
				ADD PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)';
			$sql_array[] = 'ALTER TABLE ' . BLOGS_SUBSCRIPTION_TABLE . ' DROP INDEX sub_type';
		case 'A12' :
			set_config('user_blog_enable_zebra', 1, 0);
			set_config('user_blog_enable_feeds', 1, 0);
			set_config('user_blog_max_attachments', 3, 0);
			set_config('user_blog_enable_attachments', 1, 0);

			$sql_array[] = 'CREATE TABLE IF NOT EXISTS ' . BLOGS_ATTACHMENT_TABLE . " (
				attach_id mediumint(8) UNSIGNED NOT NULL auto_increment,
				blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
				reply_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
				poster_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
				is_orphan tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
				physical_filename varchar(255) DEFAULT '' NOT NULL,
				real_filename varchar(255) DEFAULT '' NOT NULL,
				download_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
				attach_comment text NOT NULL,
				extension varchar(100) DEFAULT '' NOT NULL,
				mimetype varchar(100) DEFAULT '' NOT NULL,
				filesize int(20) UNSIGNED DEFAULT '0' NOT NULL,
				filetime int(11) UNSIGNED DEFAULT '0' NOT NULL,
				thumbnail tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
				PRIMARY KEY (attach_id),
				KEY filetime (filetime),
				KEY blog_id (blog_id),
				KEY reply_id (reply_id),
				KEY poster_id (poster_id),
				KEY is_orphan (is_orphan)
			);";

			$sql_array[] = 'ALTER TABLE ' . EXTENSION_GROUPS_TABLE . " ADD allow_in_blog TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
			$sql_array[] = 'ALTER TABLE ' . BLOGS_TABLE . " ADD blog_attachment TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
			$sql_array[] = 'ALTER TABLE ' . BLOGS_REPLY_TABLE . " ADD reply_attachment TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";

			$blog_permissions = array(
				'local'      => array(),
				'global'   => array(
					'u_blogattach',
					'u_blognolimitattach',
					)
			);
			$auth_admin->acl_add_option($blog_permissions);
		case 'A13' :
		case 'A14' :
			$sql_array[] = 'CREATE TABLE IF NOT EXISTS ' . BLOGS_PLUGINS_TABLE . " (
				plugin_id mediumint(8) UNSIGNED NOT NULL auto_increment,
				plugin_name varchar(255) NOT NULL,
				plugin_enabled tinyint(1) UNSIGNED NOT NULL default '0',
				plugin_version varchar(255) NOT NULL,
				PRIMARY KEY (plugin_id)
			);";
			set_config('user_blog_enable_plugins', 1);
		case 'A15' :
			$sql_array[] = 'ALTER TABLE ' . BLOGS_TABLE . ' CHANGE blog_deleted blog_deleted MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT \'0\'';
			$sql_array[] = 'ALTER TABLE ' . BLOGS_REPLY_TABLE . ' CHANGE reply_deleted reply_deleted MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT \'0\'';

			set_config('user_blog_seo', false);
		case 'A16' :
			$sql_array[] = 'ALTER TABLE ' . BLOGS_TABLE . ' CHANGE bbcode_uid bbcode_uid VARCHAR( 8 ) NOT NULL';
			$sql_array[] = 'ALTER TABLE ' . BLOGS_REPLY_TABLE . ' CHANGE bbcode_uid bbcode_uid VARCHAR( 8 ) NOT NULL';
			$sql_array[] = 'DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option = \'u_blognocaptcha\'';
			set_config('user_blog_guest_captcha', true);

			$blog_permissions = array(
				'local'      => array(),
				'global'   => array(
					'u_blogmoderate',
					)
			);
			$auth_admin->acl_add_option($blog_permissions);
		case 'A17' :
		case 'A18' :
			$sql_array[] = 'CREATE TABLE IF NOT EXISTS ' . BLOGS_USERS_TABLE . " (
				user_id MEDIUMINT( 8 ) UNSIGNED NOT NULL,
				guest TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '2',
				registered TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '2',
				foe TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '2',
				friend TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '2',
				title VARCHAR ( 255 ) NOT NULL DEFAULT '',
				description MEDIUMTEXT NOT NULL,
				description_bbcode_bitfield varchar(255) NOT NULL default '',
				description_bbcode_uid varchar(8) NOT NULL default '',
				PRIMARY KEY ( user_id )
			);";

			/**
			* Insert UCP Modules
			*/
			$sql = 'SELECT MAX(right_id) AS top FROM ' . MODULES_TABLE . ' WHERE module_class = \'ucp\'';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			$sql_ary = array(
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'module_basename'	=> '',
				'module_class'		=> 'ucp',
				'parent_id'			=> 0,
				'left_id'			=> $row['top'] + 1,
				'right_id'			=> $row['top'] + 6,
				'module_langname'	=> 'BLOG',
				'module_mode'		=> '',
				'module_auth'		=> '',
			);

			$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
			$parent_id = $db->sql_nextid();

			$sql_ary = array(
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'module_basename'	=> 'blog',
				'module_class'		=> 'ucp',
				'parent_id'			=> $parent_id,
				'left_id'			=> $row['top'] + 2,
				'right_id'			=> $row['top'] + 3,
				'module_langname'	=> 'UCP_BLOG_PERMISSIONS',
				'module_mode'		=> 'ucp_blog_permissions',
				'module_auth'		=> 'acl_u_blogpost',
			);

			$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);

			$sql_ary = array(
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'module_basename'	=> 'blog',
				'module_class'		=> 'ucp',
				'parent_id'			=> $parent_id,
				'left_id'			=> $row['top'] + 4,
				'right_id'			=> $row['top'] + 5,
				'module_langname'	=> 'UCP_BLOG_TITLE_DESCRIPTION',
				'module_mode'		=> 'ucp_blog_title_description',
				'module_auth'		=> 'acl_u_blogpost',
			);

			$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		case 'A19' :
			$sql_array[] = 'ALTER TABLE ' . BLOGS_TABLE . " ADD perm_guest TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '2',
				ADD perm_registered TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '2',
				ADD perm_foe TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '2',
				ADD perm_friend TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '2';";
			$sql_array[] = 'ALTER TABLE ' . BLOGS_USERS_TABLE . " CHANGE guest perm_guest TINYINT( 1 ) UNSIGNED NOT NULL ,
				CHANGE registered perm_registered TINYINT( 1 ) UNSIGNED NOT NULL ,
				CHANGE foe perm_foe TINYINT( 1 ) UNSIGNED NOT NULL ,
				CHANGE friend perm_friend TINYINT( 1 ) UNSIGNED NOT NULL;";

			// Must do all the SQL changes before we resync.
			foreach ($sql_array as $sql)
			{
				$db->sql_query($sql);
			}
			$sql_array = array();
			resync_blog('user_permissions');
		case 'A20' :
			$sql_array[] = 'ALTER TABLE ' . BLOGS_USERS_TABLE . " CHANGE perm_guest perm_guest TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1',
				CHANGE perm_foe perm_foe TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';";
			$sql_array[] = 'ALTER TABLE ' . BLOGS_TABLE . " CHANGE perm_guest perm_guest TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1',
				CHANGE perm_foe perm_foe TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';";
		case '0.3.21' : // Changing the version number scheme
			$sql_array[] = 'ALTER TABLE ' . BLOGS_USERS_TABLE . " ADD instant_redirect TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
			$sql_array[] = 'DELETE FROM ' . CONFIG_TABLE . ' WHERE config_name = \'user_blog_founder_all_perm\'';

			// Insert the new UCP Module
			$sql = 'SELECT module_id, right_id FROM ' . MODULES_TABLE . '
				WHERE module_class = \'ucp\'
				AND module_langname = \'BLOG\'';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$sql = 'UPDATE ' . MODULES_TABLE . ' SET right_id = right_id + 2
				WHERE module_class = \'ucp\'
				AND module_langname = \'BLOG\'';
			$db->sql_query($sql);

			$sql_ary = array(
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'module_basename'	=> 'blog',
				'module_class'		=> 'ucp',
				'parent_id'			=> $row['module_id'],
				'left_id'			=> $row['right_id'],
				'right_id'			=> $row['right_id'] + 1,
				'module_langname'	=> 'UCP_BLOG_SETTINGS',
				'module_mode'		=> 'ucp_blog_settings',
				'module_auth'		=> 'acl_u_blogpost',
			);

			$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		case '0.3.22' :
		case '0.3.23' :
		case '0.3.24' :
			if ($config['user_blog_force_prosilver'])
			{
				set_config('user_blog_force_style', 1);
			}
			else
			{
				set_config('user_blog_force_style', 0);
			}
			$sql_array[] = 'DELETE FROM ' . CONFIG_TABLE . ' WHERE config_name = \'user_blog_force_prosilver\'';
		case '0.3.25' :
	}

	if (count($sql_array))
	{
		foreach ($sql_array as $sql)
		{
			$db->sql_query($sql);
		}
	}

	// update the version
	if (!strpos($user_blog_version, 'dev'))
	{
		set_config('user_blog_version', $user_blog_version);
	}

	// clear the cache
	$cache->purge();

	$message = sprintf($user->lang['SUCCESSFULLY_UPDATED'], $user_blog_version, '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx") . '">', '</a>');
	trigger_error($message);
}
else
{
	confirm_box(false, 'UPDATE_INSTRUCTIONS');
}

blog_meta_refresh(0, append_sid("{$phpbb_root_path}blog.$phpEx"), true);
?>