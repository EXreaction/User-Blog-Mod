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

if (isset($config['user_blog_version']))
{
	trigger_error(sprintf($user->lang['ALREADY_INSTALLED'], '<a href="' . $blog_urls['main'] . '">', '</a>'));
}

if (!defined('BLOGS_TABLE') || !defined('BLOGS_REPLY_TABLE') || !defined('BLOGS_SUBSCRIPTION_TABLE') || !defined('BLOGS_PLUGINS_TABLE'))
{
	trigger_error('INSTALL_IN_FILES_FIRST');
}

if (!@is_writable($phpbb_root_path . 'files/blog_mod/'))
{
	@chmod($phpbb_root_path . 'files/blog_mod/', 0777);
	if (!@is_writable($phpbb_root_path . 'files/blog_mod/'))
	{
		trigger_error('FILES_CANT_WRITE');
	}
}

if (confirm_box(true))
{
	$sql_array = array();

	switch ($dbms)
	{
		case 'mysql' :
		case 'mysqli' :
			if ($dbms == 'mysqli' || version_compare($db->mysql_version, '4.1.3', '>='))
			{
				$sql_array[] = 'CREATE TABLE IF NOT EXISTS ' . BLOGS_TABLE . " (
					blog_id mediumint(8) unsigned NOT NULL auto_increment,
					user_id mediumint(8) unsigned NOT NULL default '0',
					user_ip varchar(40) NOT NULL default '',
					blog_subject varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
					blog_text mediumtext character set utf8 collate utf8_unicode_ci NOT NULL,
					blog_checksum varchar(32) NOT NULL default '',
					blog_time int(11) unsigned NOT NULL default '0',
					blog_approved tinyint(1) unsigned NOT NULL default '1',
					blog_reported tinyint(1) unsigned NOT NULL default '0',
					enable_bbcode tinyint(1) unsigned NOT NULL default '1',
					enable_smilies tinyint(1) unsigned NOT NULL default '1',
					enable_magic_url tinyint(1) unsigned NOT NULL default '1',
					bbcode_bitfield varchar(255) NOT NULL default '',
					bbcode_uid varchar(5) NOT NULL default '',
					blog_edit_time int(11) unsigned NOT NULL default '0',
					blog_edit_reason varchar(255) NOT NULL,
					blog_edit_user mediumint(8) unsigned NOT NULL default '0',
					blog_edit_count smallint(4) unsigned NOT NULL default '0',
					blog_edit_locked tinyint(1) unsigned NOT NULL default '0',
					blog_deleted tinyint(1) unsigned NOT NULL default '0',
					blog_deleted_time int(11) unsigned NOT NULL default '0',
					blog_read_count mediumint(8) unsigned NOT NULL default '1',
					blog_reply_count mediumint(8) unsigned NOT NULL default '0',
					blog_real_reply_count mediumint(8) unsigned NOT NULL default '0',
					PRIMARY KEY (blog_id),
					KEY user_id (user_id),
					KEY user_ip (user_ip),
					KEY blog_approved (blog_approved),
					KEY blog_deleted (blog_deleted)
				) CHARACTER SET `utf8` COLLATE `utf8_bin`;";

				$sql_array[] = 'CREATE TABLE IF NOT EXISTS ' . BLOGS_REPLY_TABLE . " (
					reply_id mediumint(8) unsigned NOT NULL auto_increment,
					blog_id mediumint(8) unsigned NOT NULL,
					user_id mediumint(8) unsigned NOT NULL default '0',
					user_ip varchar(40) NOT NULL default '',
					reply_subject varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
					reply_text mediumtext character set utf8 collate utf8_unicode_ci NOT NULL,
					reply_checksum varchar(32) NOT NULL default '',
					reply_time int(11) unsigned NOT NULL default '0',
					reply_approved tinyint(1) unsigned NOT NULL default '1',
					reply_reported tinyint(1) unsigned NOT NULL default '0',
					enable_bbcode tinyint(1) unsigned NOT NULL default '1',
					enable_smilies tinyint(1) unsigned NOT NULL default '1',
					enable_magic_url tinyint(1) unsigned NOT NULL default '1',
					bbcode_bitfield varchar(255) NOT NULL default '',
					bbcode_uid varchar(5) NOT NULL default '',
					reply_edit_time int(11) unsigned NOT NULL default '0',
					reply_edit_reason varchar(255) collate utf8_bin NOT NULL,
					reply_edit_user mediumint(8) unsigned NOT NULL default '0',
					reply_edit_count smallint(4) unsigned NOT NULL default '0',
					reply_edit_locked tinyint(1) unsigned NOT NULL default '0',
					reply_deleted tinyint(1) unsigned NOT NULL default '0',
					reply_deleted_time int(11) unsigned NOT NULL default '0',
					PRIMARY KEY (reply_id),
					KEY blog_id (blog_id),
					KEY user_id (user_id),
					KEY user_ip (user_ip),
					KEY reply_approved (reply_approved),
					KEY reply_deleted (reply_deleted)
				) CHARACTER SET `utf8` COLLATE `utf8_bin`;";

				$sql_array[] = 'CREATE TABLE IF NOT EXISTS ' . BLOGS_SUBSCRIPTION_TABLE . " (
					sub_user_id mediumint(8) unsigned NOT NULL default '0',
					sub_type tinyint(1) unsigned NOT NULL default '0',
					blog_id mediumint(8) unsigned NOT NULL default '0',
					user_id mediumint(8) unsigned NOT NULL default '0',
					PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)
				) CHARACTER SET `utf8` COLLATE `utf8_bin`;";

				$sql_array[] = 'CREATE TABLE . ' BLOGS_PLUGINS_TABLE . " (
					plugin_id mediumint(8) UNSIGNED NOT NULL auto_increment,
					plugin_name varchar(255) NOT NULL,
					plugin_enabled tinyint(1) UNSIGNED NOT NULL default '0',
					plugin_version_db varchar(255) NOT NULL,
					PRIMARY KEY (plugin_id)
				) CHARACTER SET `utf8` COLLATE `utf8_bin`;";
			}
			else
			{
				$sql_array[] = 'CREATE TABLE IF NOT EXISTS ' . BLOGS_TABLE . " (
					blog_id mediumint(8) unsigned NOT NULL auto_increment,
					user_id mediumint(8) unsigned NOT NULL default '0',
					user_ip varchar(40) NOT NULL default '',
					blog_subject varchar(255) NOT NULL,
					blog_text mediumtext NOT NULL,
					blog_checksum varchar(32) NOT NULL default '',
					blog_time int(11) unsigned NOT NULL default '0',
					blog_approved tinyint(1) unsigned NOT NULL default '1',
					blog_reported tinyint(1) unsigned NOT NULL default '0',
					enable_bbcode tinyint(1) unsigned NOT NULL default '1',
					enable_smilies tinyint(1) unsigned NOT NULL default '1',
					enable_magic_url tinyint(1) unsigned NOT NULL default '1',
					bbcode_bitfield varchar(255) NOT NULL default '',
					bbcode_uid varchar(5) NOT NULL default '',
					blog_edit_time int(11) unsigned NOT NULL default '0',
					blog_edit_reason varchar(255) NOT NULL,
					blog_edit_user mediumint(8) unsigned NOT NULL default '0',
					blog_edit_count smallint(4) unsigned NOT NULL default '0',
					blog_edit_locked tinyint(1) unsigned NOT NULL default '0',
					blog_deleted tinyint(1) unsigned NOT NULL default '0',
					blog_deleted_time int(11) unsigned NOT NULL default '0',
					blog_read_count mediumint(8) unsigned NOT NULL default '1',
					blog_reply_count mediumint(8) unsigned NOT NULL default '0',
					blog_real_reply_count mediumint(8) unsigned NOT NULL default '0',
					PRIMARY KEY (blog_id),
					KEY user_id (user_id),
					KEY user_ip (user_ip),
					KEY blog_approved (blog_approved),
					KEY blog_deleted (blog_deleted)
				);";

				$sql_array[] = 'CREATE TABLE IF NOT EXISTS ' . BLOGS_REPLY_TABLE . " (
					reply_id mediumint(8) unsigned NOT NULL auto_increment,
					blog_id mediumint(8) unsigned NOT NULL,
					user_id mediumint(8) unsigned NOT NULL default '0',
					user_ip varchar(40) NOT NULL default '',
					reply_subject varchar(255) NOT NULL,
					reply_text mediumtext NOT NULL,
					reply_checksum varchar(32) NOT NULL default '',
					reply_time int(11) unsigned NOT NULL default '0',
					reply_approved tinyint(1) unsigned NOT NULL default '1',
					reply_reported tinyint(1) unsigned NOT NULL default '0',
					enable_bbcode tinyint(1) unsigned NOT NULL default '1',
					enable_smilies tinyint(1) unsigned NOT NULL default '1',
					enable_magic_url tinyint(1) unsigned NOT NULL default '1',
					bbcode_bitfield varchar(255) NOT NULL default '',
					bbcode_uid varchar(5) NOT NULL default '',
					reply_edit_time int(11) unsigned NOT NULL default '0',
					reply_edit_reason varchar(255) collate utf8_bin NOT NULL,
					reply_edit_user mediumint(8) unsigned NOT NULL default '0',
					reply_edit_count smallint(4) unsigned NOT NULL default '0',
					reply_edit_locked tinyint(1) unsigned NOT NULL default '0',
					reply_deleted tinyint(1) unsigned NOT NULL default '0',
					reply_deleted_time int(11) unsigned NOT NULL default '0',
					PRIMARY KEY (reply_id),
					KEY blog_id (blog_id),
					KEY user_id (user_id),
					KEY user_ip (user_ip),
					KEY reply_approved (reply_approved),
					KEY reply_deleted (reply_deleted)
				);";

				$sql_array[] = 'CREATE TABLE IF NOT EXISTS ' . BLOGS_SUBSCRIPTION_TABLE . " (
					sub_user_id mediumint(8) unsigned NOT NULL default '0',
					sub_type tinyint(1) unsigned NOT NULL default '0',
					blog_id mediumint(8) unsigned NOT NULL default '0',
					user_id mediumint(8) unsigned NOT NULL default '0',
					PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)
				);";

				$sql_array[] = 'CREATE TABLE . ' BLOGS_PLUGINS_TABLE . " (
					plugin_id mediumint(8) UNSIGNED NOT NULL auto_increment,
					plugin_name varchar(255) NOT NULL,
					plugin_enabled tinyint(1) UNSIGNED NOT NULL default '0',
					PRIMARY KEY (plugin_id)
				);";
			}

			$sql_array[] = 'ALTER TABLE ' . USERS_TABLE . " ADD blog_count MEDIUMINT(8) default '0'";
			break;
		default :
			trigger_error('Only MySQL is supported at this time.  Please wait for a future release for this to be compatible with your DB.');
	}

	// insert the data
	foreach ($sql_array as $sql)
	{
		$db->sql_query($sql);
	}

	//Setup $auth_admin class so we can add permission options
	include($phpbb_root_path . '/includes/acp/auth.' . $phpEx);
	$auth_admin = new auth_admin();

	//Lets add the required new permissions
	$blog_permissions = array(
		'local'      => array(),
		'global'   => array(
			'u_blogview',
			'u_blogpost',
			'u_blognocaptcha',
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
			)
	);
	$auth_admin->acl_add_option($blog_permissions);

	// Add config options
	set_config('user_blog_enable', 1, 0);
	set_config('user_blog_custom_profile_enable', 0, 0);
	set_config('user_blog_text_limit', '50', 0);
	set_config('user_blog_user_text_limit', '500', 0);
	set_config('user_blog_inform', '2', 0);
	set_config('user_blog_always_show_blog_url', 0, 0);
	set_config('user_blog_founder_all_perm', 1, 0);
	set_config('user_blog_force_prosilver', 0, 0);
	set_config('user_blog_subscription_enabled', 1, 0);
	set_config('user_blog_enable_zebra', 1, 0);
	set_config('user_blog_enable_feeds', 1, 0);

	//insert the modules
	$sql = 'SELECT * FROM ' . MODULES_TABLE . " WHERE module_langname = 'ACP_CAT_DOT_MODS'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	$sql_ary = array(
		'module_enabled'	=> 1,
		'module_display'	=> 1,
		'module_basename'	=> 'blogs',
		'module_class'		=> 'acp',
		'parent_id'			=> $row['module_id'],
		'left_id'			=> $row['right_id'],
		'right_id'			=> $row['right_id'] + 5,
		'module_langname'	=> 'ACP_BLOGS',
		'module_mode'		=> 'default',
		'module_auth'		=> 'acl_a_blogmanage',
	);
	
	$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
	$db->sql_query($sql);
	$module_id = $db->sql_nextid();
	
	$sql = 'UPDATE ' . MODULES_TABLE . "
	SET left_id = left_id + 6, right_id = right_id + 6
	WHERE left_id >= {$sql_ary['left_id']} AND module_id != $module_id";
	$db->sql_query($sql);
						
	$sql = 'UPDATE ' . MODULES_TABLE . "
	SET right_id = right_id + 6
	WHERE left_id < {$sql_ary['left_id']} AND right_id >= {$sql_ary['left_id']} AND module_id != $module_id";
	$db->sql_query($sql);
	
	$sql_ary = array(
		'module_enabled'	=> 1,
		'module_display'	=> 1,
		'module_basename'	=> 'blogs',
		'module_class'		=> 'acp',
		'parent_id'			=> $module_id,
		'left_id'			=> $row['right_id'] + 1,
		'right_id'			=> $row['right_id'] + 2,
		'module_langname'	=> 'ACP_BLOGS',
		'module_mode'		=> 'default',
		'module_auth'		=> 'acl_a_blogmanage',
	);
	
	$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
	$db->sql_query($sql);

	$sql_ary = array(
		'module_enabled'	=> 1,
		'module_display'	=> 1,
		'module_basename'	=> 'blog_plugins',
		'module_class'		=> 'acp',
		'parent_id'			=> $module_id,
		'left_id'			=> $row['right_id'] + 3,
		'right_id'			=> $row['right_id'] + 4,
		'module_langname'	=> 'ACP_BLOG_PLUGINS',
		'module_mode'		=> 'default',
		'module_auth'		=> 'acl_a_blogmanage',
	);
	
	$sql = 'INSERT INTO ' . MODULES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
	$db->sql_query($sql);

	set_config('user_blog_version', $user_blog_version, 0);

	$cache->purge();

	trigger_error(sprintf($user->lang['INSTALL_BLOG_DB_SUCCESS'], '<a href="' . $blog_urls['main'] . '">', '</a>'));
}
else
{
	confirm_box(false, 'INSTALL_BLOG_DB');
}
?>