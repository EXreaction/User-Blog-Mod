<?php
/**
*
* @package phpBB3 User Blog Attachments
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

if (!@is_writable($phpbb_root_path . 'files/blog_mod/'))
{
	@chmod($phpbb_root_path . 'files/blog_mod/', 0777);
	if (!@is_writable($phpbb_root_path . 'files/blog_mod/'))
	{
		trigger_error('FILES_CANT_WRITE');
	}
}

$sql_array = array();
switch ($dbms)
{
	case 'mysql' :
	case 'mysqli' :
		if ($dbms == 'mysqli' || version_compare($db->mysql_version, '4.1.3', '>='))
		{
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
			) CHARACTER SET `utf8` COLLATE `utf8_bin`;";
		}
		else
		{
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
		}

		$sql_array[] = 'ALTER TABLE ' . BLOGS_TABLE . " ADD blog_attachment tinyint(1) unsigned NOT NULL default '0'";
		$sql_array[] = 'ALTER TABLE ' . BLOGS_REPLY_TABLE . " ADD reply_attachment tinyint(1) unsigned NOT NULL default '0'";
		$sql_array[] = 'ALTER TABLE ' . EXTENSION_GROUPS_TABLE . " ADD allow_in_blog TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
	break;
	default :
		trigger_error('Only MySQL is supported at this time.  Please wait for a future release for this to be compatible with your DB.');
}

foreach ($sql_array as $sql)
{
	$db->sql_query($sql);
}

$blog_permissions = array(
	'local'      => array(),
	'global'   => array(
		'u_blogattach',
		'u_blognolimitattach',
	)
);
$auth_admin->acl_add_option($blog_permissions);

set_config('user_blog_max_attachments', 3, 0);
?>