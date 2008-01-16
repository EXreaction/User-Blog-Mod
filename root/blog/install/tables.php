<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB') || !defined('IN_BLOG_INSTALL'))
{
	exit;
}

/*
* Add New Tables ----------------------------------------------------------------------------------
*/
phpbb_db_tools::$return_statements = true;
$schema_data = get_blog_schema_struct();
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

/*
* Alter Existing Tables -----------------------------------------------------------------------
*/
phpbb_db_tools::$return_statements = false;
phpbb_db_tools::sql_column_add(USERS_TABLE, 'blog_count', array('UINT', 0));
phpbb_db_tools::sql_column_add(EXTENSION_GROUPS_TABLE, 'allow_in_blog', array('BOOL', 0));

function get_blog_schema_struct()
{
	$schema_data = array();
/* Not currently used...probably won't ever be...
	$schema_data['phpbb_blog_search_results'] = array(
		'COLUMNS'		=> array(
			'search_key'			=> array('VCHAR:32', ''),
			'search_time'			=> array('TIMESTAMP', 0),
			'search_keywords'		=> array('MTEXT_UNI', ''),
			'search_authors'		=> array('MTEXT', ''),
		),
		'PRIMARY_KEY'	=> 'search_key',
	);
*/

	$schema_data['phpbb_blogs'] = array(
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
	);

	$schema_data['phpbb_blogs_attachment'] = array(
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

	$schema_data['phpbb_blogs_categories'] = array(
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
	);

	$schema_data['phpbb_blogs_in_categories'] = array(
		'COLUMNS'		=> array(
			'blog_id'						=> array('UINT', 0),
			'category_id'					=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> array('blog_id', 'category_id'),
	);

	$schema_data['phpbb_blogs_plugins'] = array(
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
	);

	$schema_data['phpbb_blogs_ratings'] = array(
		'COLUMNS'		=> array(
			'blog_id'						=> array('UINT', 0),
			'user_id'						=> array('UINT', 0),
			'rating'						=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> array('blog_id', 'user_id'),
	);

	$schema_data['phpbb_blogs_reply'] = array(
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
	);

	$schema_data['phpbb_blogs_subscription'] = array(
		'COLUMNS'		=> array(
			'sub_user_id'			=> array('UINT', 0),
			'sub_type'				=> array('UINT:11', 0),
			'blog_id'				=> array('UINT', 0),
			'user_id'				=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'sub_user_id, sub_type, blog_id, user_id',
	);

	$schema_data['phpbb_blogs_users'] = array(
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
			'instant_redirect'		=> array('BOOL', 1),
			'blog_subscription_default'		=> array('UINT:11', 0),
		),
		'PRIMARY_KEY'	=> 'user_id',
	);

	$schema_data['phpbb_blog_search_wordlist'] = array(
		'COLUMNS'		=> array(
			'word_id'			=> array('UINT', NULL, 'auto_increment'),
			'word_text'			=> array('VCHAR_UNI', ''),
			'word_common'		=> array('BOOL', 0),
			'word_count'		=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'word_id',
		'KEYS'			=> array(
			'wrd_txt'			=> array('UNIQUE', 'word_text'),
			'wrd_cnt'			=> array('INDEX', 'word_count'),
		),
	);

	$schema_data['phpbb_blog_search_wordmatch'] = array(
		'COLUMNS'		=> array(
			'blog_id'			=> array('UINT', 0),
			'reply_id'			=> array('UINT', 0),
			'word_id'			=> array('UINT', 0),
			'title_match'		=> array('BOOL', 0),
		),
		'KEYS'			=> array(
			'unq_mtch'			=> array('UNIQUE', array('blog_id', 'reply_id', 'word_id', 'title_match')),
			'word_id'			=> array('INDEX', 'word_id'),
			'blog_id'			=> array('INDEX', 'blog_id'),
			'reply_id'			=> array('INDEX', 'reply_id'),
		),
	);

	return $schema_data;
}
?>