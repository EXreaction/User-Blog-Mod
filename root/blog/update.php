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

if ($user_blog_version == $config['user_blog_version'])
{
	trigger_error(sprintf($user->lang['ALREADY_UPDATED'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx") . '">', '</a>'));
}

$template->set_template();
if (confirm_box(true))
{
	$sql_array = array();

	include($phpbb_root_path . '/includes/acp/auth.' . $phpEx);
	include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/db_tools.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/eami.' . $phpEx);
	$auth_admin = new auth_admin();
	$dbmd = get_available_dbms($dbms);
	$eami = new eami();

	switch ($config['user_blog_version'])
	{
		case '0.3.33' :
			phpbb_db_tools::sql_column_change(BLOGS_TABLE, 'blog_read_count', array('UINT', 1));
		case '0.3.34' :
		case '0.3.35' :
			$table_data = array(
				'COLUMNS'		=> array(
					'blog_id'						=> array('UINT', 0),
					'user_id'						=> array('UINT', 0),
					'rating'						=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> array('blog_id', 'user_id'),
			);

			phpbb_db_tools::$return_statements = true;
			$table_name = preg_replace('#phpbb_#i', $table_prefix, 'phpbb_blogs_ratings');
			$statements = phpbb_db_tools::sql_create_table($table_name, $table_data);
			foreach ($statements as $sql)
			{
				$db->sql_query($sql);
			}
			unset($table_name, $table_data);

			phpbb_db_tools::$return_statements = false;
			phpbb_db_tools::sql_column_add(BLOGS_TABLE, 'rating', array('UINT', 0));
			phpbb_db_tools::sql_column_add(BLOGS_TABLE, 'num_ratings', array('UINT', 0));

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
				$table_data = array(
					'COLUMNS'		=> array(
						'attach_id'				=> array('UINT', NULL, 'auto_increment'),
						'blog_id'				=> array('UINT', 0),
						'reply_id'				=> array('UINT', 0),
						'poster_id'				=> array('UINT', 0),
						'is_orphan'				=> array('BOOL', 1),
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
				);

				phpbb_db_tools::$return_statements = true;
				$table_name = preg_replace('#phpbb_#i', $table_prefix, 'phpbb_blogs_attachment');
				$statements = phpbb_db_tools::sql_create_table($table_name, $table_data);
				foreach ($statements as $sql)
				{
					$db->sql_query($sql);
				}
				unset($table_name, $table_data);

				phpbb_db_tools::$return_statements = false;
				phpbb_db_tools::sql_column_add(BLOGS_TABLE, 'blog_attachment', array('BOOL', 0));
				phpbb_db_tools::sql_column_add(BLOGS_REPLY_TABLE, 'reply_attachment', array('BOOL', 0));
				phpbb_db_tools::sql_column_add(EXTENSION_GROUPS_TABLE, 'allow_in_blog', array('BOOL', 0));

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
			phpbb_db_tools::$return_statements = false;
			phpbb_db_tools::sql_column_add(BLOGS_USERS_TABLE, 'blog_subscription_default', array('UINT:11', 0));
			phpbb_db_tools::sql_column_change(BLOGS_SUBSCRIPTION_TABLE, 'sub_type', array('UINT:11', 0));

			// changing the ratings to decimal
			phpbb_db_tools::sql_column_change(BLOGS_TABLE, 'rating', array('DECIMAL:6', 0));

			// New poll tables
			$schema_data = array();
			$schema_data['phpbb_blogs_poll_options'] = array(
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
			);

			$schema_data['phpbb_blogs_poll_votes'] = array(
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
			);

			phpbb_db_tools::$return_statements = true;
			foreach ($schema_data as $table_name => $table_data)
			{
				// Change prefix
				$table_name = preg_replace('#phpbb_#i', $table_prefix, $table_name);

				$statements = phpbb_db_tools::sql_create_table($table_name, $table_data);

				foreach ($statements as $sql)
				{
					$db->sql_query($sql);
				}
			}

			// Need to add polls to the blogs table
			phpbb_db_tools::$return_statements = false;
			phpbb_db_tools::sql_column_add(BLOGS_TABLE, 'poll_title', array('STEXT_UNI', '', 'true_sort'));
			phpbb_db_tools::sql_column_add(BLOGS_TABLE, 'poll_start', array('TIMESTAMP', 0));
			phpbb_db_tools::sql_column_add(BLOGS_TABLE, 'poll_length', array('TIMESTAMP', 0));
			phpbb_db_tools::sql_column_add(BLOGS_TABLE, 'poll_max_options', array('TINT:4', 1));
			phpbb_db_tools::sql_column_add(BLOGS_TABLE, 'poll_last_vote', array('TIMESTAMP', 0));
			phpbb_db_tools::sql_column_add(BLOGS_TABLE, 'poll_vote_change', array('BOOL', 0));

			// Blog style
			phpbb_db_tools::sql_column_add(BLOGS_USERS_TABLE, 'blog_style', array('STEXT_UNI', '', 'true_sort'));

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