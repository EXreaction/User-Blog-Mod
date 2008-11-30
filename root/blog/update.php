<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: update.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (!isset($config['user_blog_version']))
{
	trigger_error('NOT_INSTALLED');
}

if (USER_BLOG_MOD_VERSION == $config['user_blog_version'])
{
	trigger_error(sprintf($user->lang['ALREADY_UPDATED'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx") . '">', '</a>'));
}

if (confirm_box(true))
{
	// This may help...
	@set_time_limit(120);

	$sql_array = array();

	include($phpbb_root_path . 'includes/functions_admin.' . $phpEx); // Needed for remove_comments function for some DB types
	include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
	include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
	include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/eami.' . $phpEx);
	$auth_admin = new auth_admin();
	$db_tool = new phpbb_db_tools($db);
	$dbmd = get_available_dbms($dbms);
	$eami = new eami();

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
		case 'A11' :
			set_config('user_blog_subscription_enabled', 1, 0);

			$sql_array[] = 'ALTER TABLE ' . BLOGS_SUBSCRIPTION_TABLE . ' DROP PRIMARY KEY ,
				ADD PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)';
			$sql_array[] = 'ALTER TABLE ' . BLOGS_SUBSCRIPTION_TABLE . ' DROP INDEX sub_type';
		case 'A12' :
			set_config('user_blog_enable_zebra', 1, 0);
			set_config('user_blog_enable_feeds', 1, 0);
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

			// This is the last time it is used.
			if (sizeof($sql_array))
			{
				foreach ($sql_array as $sql)
				{
					$db->sql_query($sql);
				}
			}
			unset($sql_array);
		case '0.3.25' :
		case '0.3.26' :
			switch ($dbms)
			{
				case 'mysql' :
					if (version_compare($db->mysql_version, '4.1.3', '>='))
					{
						$dbms_schema = 'mysql_41_schema.sql';
					}
					else
					{
						$dbms_schema = 'mysql_40_schema.sql';
					}
				break;
				case 'mysqli' :
					$dbms_schema = 'mysql_41_schema.sql';
				break;
				default :
					$dbms_schema = $dbms . '_schema.sql';
			}

			if (!file_exists($phpbb_root_path . 'blog/update/0326/' . $dbms_schema))
			{
				trigger_error('SCHEMA_NOT_EXIST');
			}

			$remove_remarks = $dbmd[$dbms]['COMMENTS'];
			$delimiter = $dbmd[$dbms]['DELIM'];

			$sql_query = @file_get_contents($phpbb_root_path . 'blog/update/0326/' . $dbms_schema);

			$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

			$remove_remarks($sql_query);

			$sql_query = split_sql_file($sql_query, $delimiter);

			foreach ($sql_query as $sql)
			{
				if (!$db->sql_query($sql))
				{
					$error[] = $db->sql_error();
				}
			}
			unset($sql_query);

			set_config('user_blog_search', 1);
			set_config('user_blog_user_permissions', 1);

			if (!class_exists('blog_fulltext_native'))
			{
				include($phpbb_root_path . "blog/search/fulltext_native.$phpEx");
			}

			$blog_search = new blog_fulltext_native();
			$blog_search->reindex();
		case '0.3.27' :
		case '0.3.28' :
		case '0.3.29' :
			set_config('user_blog_search_type', 'fulltext_native');
		case '0.3.30' :
			$db_tool->sql_column_change(BLOGS_TABLE, 'blog_subject', array('STEXT_UNI', '', 'true_sort'));
			$db_tool->sql_column_change(BLOGS_REPLY_TABLE, 'reply_subject', array('STEXT_UNI', '', 'true_sort'));
			$db_tool->sql_column_change(BLOGS_PLUGINS_TABLE, 'plugin_name', array('STEXT_UNI', '', 'true_sort'));
			$db_tool->sql_column_change(BLOGS_USERS_TABLE, 'title', array('STEXT_UNI', '', 'true_sort'));
		case '0.3.31' :
		case '0.3.32' :
			switch ($dbms)
			{
				case 'mysql' :
					if (version_compare($db->mysql_version, '4.1.3', '>='))
					{
						$dbms_schema = 'mysql_41_schema.sql';
					}
					else
					{
						$dbms_schema = 'mysql_40_schema.sql';
					}
				break;
				case 'mysqli' :
					$dbms_schema = 'mysql_41_schema.sql';
				break;
				default :
					$dbms_schema = $dbms . '_schema.sql';
			}

			if (!file_exists($phpbb_root_path . 'blog/update/0332/' . $dbms_schema))
			{
				trigger_error('SCHEMA_NOT_EXIST');
			}

			$remove_remarks = $dbmd[$dbms]['COMMENTS'];
			$delimiter = $dbmd[$dbms]['DELIM'];

			$sql_query = @file_get_contents($phpbb_root_path . 'blog/update/0332/' . $dbms_schema);

			$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

			$remove_remarks($sql_query);

			$sql_query = split_sql_file($sql_query, $delimiter);

			foreach ($sql_query as $sql)
			{
				if (!$db->sql_query($sql))
				{
					$error[] = $db->sql_error();
				}
			}
			unset($sql_query);

			// ACP Modules
			$sql_ary = array(
				'module_langname'	=> 'ACP_BLOGS',
			);
			$eami->add_module('acp', 'ACP_CAT_DOT_MODS', $sql_ary);

			$sql_ary = array(
				'module_basename'	=> 'blogs',
				'module_langname'	=> 'ACP_BLOG_SETTINGS',
				'module_mode'		=> 'settings',
				'module_auth'		=> 'acl_a_blogmanage',
			);
			$eami->add_module('acp', 'ACP_BLOGS', $sql_ary);

			$sql_ary = array(
				'module_basename'	=> 'blogs',
				'module_langname'	=> 'ACP_BLOG_PLUGINS',
				'module_mode'		=> 'plugins',
				'module_auth'		=> 'acl_a_blogmanage',
			);
			$eami->add_module('acp', 'ACP_BLOGS', $sql_ary);

			$sql_ary = array(
				'module_basename'	=> 'blogs',
				'module_langname'	=> 'ACP_BLOG_SEARCH',
				'module_mode'		=> 'search',
				'module_auth'		=> 'acl_a_blogmanage',
			);
			$eami->add_module('acp', 'ACP_BLOGS', $sql_ary);

			$sql_ary = array(
				'module_basename'	=> 'blogs',
				'module_langname'	=> 'ACP_BLOG_CATEGORIES',
				'module_mode'		=> 'categories',
				'module_auth'		=> 'acl_a_blogmanage',
			);
			$eami->add_module('acp', 'ACP_BLOGS', $sql_ary);
		case '0.3.33' :
			$db_tool->sql_column_change(BLOGS_TABLE, 'blog_read_count', array('UINT', 1));
		case '0.3.34' :
		case '0.3.35' :
			switch ($dbms)
			{
				case 'mysql' :
					if (version_compare($db->mysql_version, '4.1.3', '>='))
					{
						$dbms_schema = 'mysql_41_schema.sql';
					}
					else
					{
						$dbms_schema = 'mysql_40_schema.sql';
					}
				break;
				case 'mysqli' :
					$dbms_schema = 'mysql_41_schema.sql';
				break;
				default :
					$dbms_schema = $dbms . '_schema.sql';
			}

			if (!file_exists($phpbb_root_path . 'blog/update/0335/' . $dbms_schema))
			{
				trigger_error('SCHEMA_NOT_EXIST');
			}

			$remove_remarks = $dbmd[$dbms]['COMMENTS'];
			$delimiter = $dbmd[$dbms]['DELIM'];

			$sql_query = @file_get_contents($phpbb_root_path . 'blog/update/0335/' . $dbms_schema);

			$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

			$remove_remarks($sql_query);

			$sql_query = split_sql_file($sql_query, $delimiter);

			foreach ($sql_query as $sql)
			{
				if (!$db->sql_query($sql))
				{
					$error[] = $db->sql_error();
				}
			}
			unset($sql_query);

			$db_tool->sql_column_add(BLOGS_TABLE, 'rating', array('UINT', 0));
			$db_tool->sql_column_add(BLOGS_TABLE, 'num_ratings', array('UINT', 0));

			set_config('user_blog_min_rating', 1);
			set_config('user_blog_max_rating', 5);
			set_config('user_blog_enable_ratings', true);
		case '0.3.36' :
		case '0.3.37' :
			if (version_compare(PHP_VERSION, '5.1.0') < 0)
			{
				trigger_error('You are running an unsupported PHP version. Please upgrade to PHP 5.1.0 or higher.');
			}

			set_config('user_blog_enable_attachments', 1);

			$sql = 'SELECT * FROM ' . BLOGS_PLUGINS_TABLE . ' WHERE plugin_name = \'attachments\'';
			$result = $db->sql_query($sql);
			if ($db->sql_fetchrow($result))
			{
				// They have already installed the attachments plugin
				$sql = 'DELETE FROM ' . BLOGS_PLUGINS_TABLE . ' WHERE plugin_name = \'attachments\'';
				$db->sql_query($sql);
			}
			else
			{
				if ($dbms == 'mysql' || $dbms == 'mysqli')
				{
					if ($dbms == 'mysqli' || version_compare($db->mysql_version, '4.1.3', '>='))
					{
						$dbms_schema = 'mysql_41_schema.sql';
					}
					else
					{
						$dbms_schema = 'mysql_40_schema.sql';
					}
				}
				else
				{
					$dbms_schema = $dbms . '_schema.sql';
				}

				if (!file_exists($phpbb_root_path . 'blog/update/0337/attachments/' . $dbms_schema))
				{
					trigger_error('SCHEMA_NOT_EXIST');
				}

				$remove_remarks = $dbmd[$dbms]['COMMENTS'];
				$delimiter = $dbmd[$dbms]['DELIM'];

				$sql_query = @file_get_contents($phpbb_root_path . 'blog/update/0337/attachments/' . $dbms_schema);

				$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

				$remove_remarks($sql_query);

				$sql_query = split_sql_file($sql_query, $delimiter);

				foreach ($sql_query as $sql)
				{
					if (!$db->sql_query($sql))
					{
						$error[] = $db->sql_error();
					}
				}
				unset($sql_query);

				$db_tool->sql_column_add(BLOGS_TABLE, 'blog_attachment', array('BOOL', 0));
				$db_tool->sql_column_add(BLOGS_REPLY_TABLE, 'reply_attachment', array('BOOL', 0));
				$db_tool->sql_column_add(EXTENSION_GROUPS_TABLE, 'allow_in_blog', array('BOOL', 0));

				$blog_permissions = array(
					'local'      => array(),
					'global'   => array(
						'u_blogattach',
						'u_blognolimitattach',
					)
				);
				$auth_admin->acl_add_option($blog_permissions);

				set_config('user_blog_max_attachments', 3);
			}

			// This module has to be added even if they had the attachments mod installed.
			$sql_ary = array(
				'module_basename'	=> 'blogs',
				'module_langname'	=> 'ACP_EXTENSION_GROUPS',
				'module_mode'		=> 'ext_groups',
				'module_auth'		=> 'acl_a_blogmanage',
			);
			$eami->add_module('acp', 'ACP_BLOGS', $sql_ary);

			// The subscription type now goes to the bitwise type, so 1,2,4,8,16,32,64,etc.  Also, the old 2 was removed.
			$sql = 'SELECT * FROM ' . BLOGS_SUBSCRIPTION_TABLE . ' WHERE sub_type = 2'; // First we will do what is needed to get rid of the old type 2
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$row['sub_type'] = 0;
				$sql = 'INSERT INTO ' . BLOGS_SUBSCRIPTION_TABLE . ' ' . $db->sql_build_array('INSERT', $row);
				$db->sql_query($sql);
			}
			$sql = 'UPDATE ' . BLOGS_SUBSCRIPTION_TABLE . ' SET sub_type = 1 WHERE sub_type = 2';
			$db->sql_query($sql);

			// Now we update it for the bitwise stuff.  If anyone has made a custom subscription type for a plugin make sure you update yours correctly.
			$sql = 'UPDATE ' . BLOGS_SUBSCRIPTION_TABLE . ' SET sub_type = sub_type + 1';
			$db->sql_query($sql);

			// New blog subscription default for the blogs users table.  This uses the bitwise stuff like options does for posting.
			$db_tool->sql_column_add(BLOGS_USERS_TABLE, 'blog_subscription_default', array('UINT:11', 0));
			$db_tool->sql_column_change(BLOGS_SUBSCRIPTION_TABLE, 'sub_type', array('UINT:11', 0));

			// changing the ratings to decimal
			$db_tool->sql_column_change(BLOGS_TABLE, 'rating', array('DECIMAL:6', 0));

			if ($dbms == 'mysql' || $dbms == 'mysqli')
			{
				if ($dbms == 'mysqli' || version_compare($db->mysql_version, '4.1.3', '>='))
				{
					$dbms_schema = 'mysql_41_schema.sql';
				}
				else
				{
					$dbms_schema = 'mysql_40_schema.sql';
				}
			}
			else
			{
				$dbms_schema = $dbms . '_schema.sql';
			}

			if (!file_exists($phpbb_root_path . 'blog/update/0337/' . $dbms_schema))
			{
				trigger_error('SCHEMA_NOT_EXIST');
			}

			$remove_remarks = $dbmd[$dbms]['COMMENTS'];
			$delimiter = $dbmd[$dbms]['DELIM'];

			$sql_query = @file_get_contents($phpbb_root_path . 'blog/update/0337/' . $dbms_schema);

			$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

			$remove_remarks($sql_query);

			$sql_query = split_sql_file($sql_query, $delimiter);

			foreach ($sql_query as $sql)
			{
				if (!$db->sql_query($sql))
				{
					$error[] = $db->sql_error();
				}
			}
			unset($sql_query);

			// Need to add polls to the blogs table
			$db_tool->sql_column_add(BLOGS_TABLE, 'poll_title', array('STEXT_UNI', '', 'true_sort'));
			$db_tool->sql_column_add(BLOGS_TABLE, 'poll_start', array('TIMESTAMP', 0));
			$db_tool->sql_column_add(BLOGS_TABLE, 'poll_length', array('TIMESTAMP', 0));
			$db_tool->sql_column_add(BLOGS_TABLE, 'poll_max_options', array('TINT:4', 0));
			$db_tool->sql_column_add(BLOGS_TABLE, 'poll_last_vote', array('TIMESTAMP', 0));
			$db_tool->sql_column_add(BLOGS_TABLE, 'poll_vote_change', array('BOOL', 0));

			// Blog style
			$db_tool->sql_column_add(BLOGS_USERS_TABLE, 'blog_style', array('STEXT_UNI', '', 'true_sort'));

			$blog_permissions = array(
				'local'		=> array(),
				'global'	=> array(
					'u_blog_vote',
					'u_blog_vote_change',
					'u_blog_create_poll',
					'u_blog_style',
					)
			);
			$auth_admin->acl_add_option($blog_permissions);

			$sql = 'SELECT count(blog_id) AS blog_count FROM ' . BLOGS_TABLE . ' WHERE blog_deleted = 0 AND blog_approved = 1';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			set_config('num_blogs', $row['blog_count'], true);
		case '0.3.38' :
			$db_tool->sql_column_add(BLOGS_USERS_TABLE, 'blog_css', array('MTEXT_UNI', ''));

			$blog_permissions = array(
				'local'		=> array(),
				'global'	=> array(
					'u_blog_css',
					)
			);
			$auth_admin->acl_add_option($blog_permissions);
		case '0.3.39' :
		case '0.3.40' :
			// Must re-do this because the code used to increment the num_blogs was not correct.
			$sql = 'SELECT count(blog_id) AS blog_count FROM ' . BLOGS_TABLE . ' WHERE blog_deleted = 0 AND blog_approved = 1';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			set_config('num_blogs', $row['blog_count'], true);

			$sql = 'SELECT count(reply_id) AS reply_count FROM ' . BLOGS_REPLY_TABLE . ' WHERE reply_deleted = 0 AND reply_approved = 1';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			set_config('num_blog_replies', $row['reply_count'], true);
			set_config('user_blog_quick_reply', 1);
		case '0.3.41' :
		case '0.3.42' :
		case '0.7.0' :
		case '0.7.1' :
		case '0.7.2' :
			// Resync the category blog counts
			$db->sql_query('UPDATE ' . BLOGS_CATEGORIES_TABLE . ' SET blog_count = 0');
			$result = $db->sql_query('SELECT blog_id FROM ' . BLOGS_TABLE . ' WHERE blog_deleted = 0 AND blog_approved = 1');
			while ($row = $db->sql_fetchrow($result))
			{
				$to_query = $parent_list = array();
				$sql = 'SELECT category_id FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . $row['blog_id'];
				$result1 = $db->sql_query($sql);
				while ($row1 = $db->sql_fetchrow($result1))
				{
					$to_query[] = $row1['category_id'];
				}
				$db->sql_freeresult($result1);

				while (sizeof($to_query))
				{
					$sql = 'SELECT category_id, parent_id FROM ' . BLOGS_CATEGORIES_TABLE . '
						WHERE ' . $db->sql_in_set('category_id', $to_query);
					$result1 = $db->sql_query($sql);
					$to_query = array();
					while ($row1 = $db->sql_fetchrow($result1))
					{
						$parent_list[] = $row1['category_id'];
						if ($row1['parent_id'] && !in_array($row1['parent_id'], $to_query))
						{
							$to_query[] = $row1['parent_id'];
						}
					}
					$db->sql_freeresult($result1);
				}

				if (sizeof($parent_list))
				{
					$db->sql_query('UPDATE ' . BLOGS_CATEGORIES_TABLE . ' SET blog_count = blog_count + 1 WHERE ' . $db->sql_in_set('category_id', array_unique($parent_list)));
				}
			}
			$db->sql_freeresult($result);
		case '0.7.3' :
		case '0.7.4' :
		case '0.9.0' :
		case '0.9.1' :
		case '1.0.0' :
			set_config('user_blog_links_output_block', 1);
		case '1.0.1' :
			$db->sql_query('UPDATE ' . BLOGS_TABLE . ' SET poll_max_options = 1 WHERE poll_max_options < 1');
		case '1.0.2' :
		case '1.0.3' :
			$style_ids = array();
			$result = $db->sql_query('SELECT style_id FROM ' . STYLES_TABLE);
			while ($row = $db->sql_fetchrow($result))
			{
				$style_ids[] = $row['style_id'];
			}
			$db->sql_freeresult($result);

			$db->sql_query('UPDATE ' . BLOGS_USERS_TABLE . ' SET blog_style = ' . $style_ids[0] . ' WHERE ' . $db->sql_in_set('blog_style', $style_ids, true));

			set_config('user_blog_message_from', 2);
		case '1.0.4' :
		case '1.0.5' :
		case '1.0.6' :
		case '1.0.7' :
			set_config('user_blog_version', '1.0.7');
	}

	// clear the cache
	$cache->purge();

	$user->add_lang('mods/blog/umil');
	$message = sprintf($user->lang['SUCCESSFULLY_UPDATED_UMIL_RETURN'], '<a href="' . append_sid("{$phpbb_root_path}blog/database.$phpEx") . '">', '</a>');
	trigger_error($message);
}
else
{
	confirm_box(false, 'UPDATE_INSTRUCTIONS');
}

blog_meta_refresh(0, append_sid("{$phpbb_root_path}blog.$phpEx"), true);
?>