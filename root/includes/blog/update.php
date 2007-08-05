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

// Was Cancel pressed? If so then redirect to the appropriate page
if ($cancel)
{
	redirect($blog_urls['main']);
}

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['UPDATE_BLOG']);

if (!isset($config['user_blog_version']))
{
	trigger_error('Either you do not have the User Blog Mod installed in your database, or you are running a very old version.<br/>If you have the mod installed already please delete the tables and information which was inserted by the version you used and reinstall the mod.');
}

if ($user_blog_version == $config['user_blog_version'])
{
	trigger_error(sprintf($user->lang['ALREADY_UPDATED'], '<a href="' . $blog_urls['main'] . '">', '</a>'));
}

if (confirm_box(true))
{
	$sql_array = array();

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

			/* The blog_rating section is not a planned feature ATM, but may be added later on (this was commented out for Alpha 11)
			$sql_array[] = 'ALTER TABLE ' . BLOGS_TABLE . '
				ADD blog_rating MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT \'0\',
				ADD blog_num_ratings MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT \'0\'';
			*/

			//Setup $auth_admin class so we can add permission options
			include($phpbb_root_path . '/includes/acp/auth.' . $phpEx);
			$auth_admin = new auth_admin();

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
	}

	if (count($sql_array))
	{
		foreach ($sql_array as $sql)
		{
			$db->sql_query($sql);
		}
	}

	// update the version
	set_config('user_blog_version', $user_blog_version);

	// clear the cache
	$cache->purge();

	$message = sprintf($user->lang['SUCCESSFULLY_UPDATED'], $user_blog_version, '<a href="' . $blog_urls['main'] . '">', '</a>');

	trigger_error($message);
}
else
{
	confirm_box(false, 'UPDATE_INSTRUCTIONS');
}

// they pressed No, so redirect them
redirect($blog_urls['main']);
?>