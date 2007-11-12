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

$this->available_upgrades[$name]['upgrade_title'] = 'The Blog Mod 0.2.4b';
$this->available_upgrades[$name]['upgrade_copyright'] = 'EXreaction';
$this->available_upgrades[$name]['upgrade_version'] = '0.7.0';

$this->available_upgrades[$name]['custom_options'] = array(
	'replies'			=> array('lang' => 'UPGRADE_REPLIES',	'type' => 'radio:yes_no',	'explain' => false,		'default' => true),
	'convert_friend'	=> array('lang' => 'CONVERT_FRIEND',	'type' => 'radio:yes_no',	'explain' => true,		'default' => true),
	'convert_foe'		=> array('lang' => 'CONVERT_FOE',		'type' => 'radio:yes_no',	'explain' => true,		'default' => true),
);

$this->available_upgrades[$name]['requred_tables'] = array('users', 'weblog_entries', 'weblog_replies', 'weblog_blocked', 'weblog_friends');
$this->available_upgrades[$name]['custom_stages'] = array($user->lang['CONVERT_REPLIES'], $user->lang['CONVERT_FRIENDS'], $user->lang['CONVERT_FOES']);

if (isset($stage) && $stage == 4 && $this->selected_options['blogs'])
{
	$bb2_users = $bb3_users = array();
	$sql = 'SELECT user_id, username FROM ' . $this->selected_options['db_prefix'] . 'users';
	$result = $old_db->sql_query($sql);
	while($row = $old_db->sql_fetchrow($result))
	{
		$bb2_users[$row['user_id']] = $row['username'];
	}
	$old_db->sql_freeresult($result);

	$sql = 'SELECT user_id, username FROM ' . USERS_TABLE;
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
		$bb3_users[$row['username']] = $row['user_id'];
	}
	$db->sql_freeresult($result);

	$new_ids = array(); // this will be filled with the new blog ids if we did not truncate the tables

	$sql = 'SELECT * FROM ' . $this->selected_options['db_prefix'] . 'weblog_entries
		ORDER BY entry_time ASC
			LIMIT ' . $start . ', ' . $this->selected_options['limit'];
	$result = $old_db->sql_query($sql);
	while ($row = $old_db->sql_fetchrow($result))
	{
		$row['entry_text'] = str_replace(':' . $row['bbcode_uid'], '', $row['entry_text']);
		$row['entry_text'] = utf8_normalize_nfc($row['entry_text']);
		$message_parser = new parse_message();
		$message_parser->message = $row['entry_text'];
		$message_parser->parse($row['enable_bbcode'], 1, $row['enable_smilies']);

		if ($row['entry_poster_id'] == -1)
		{
			$user_id = 1;
		}
		else
		{
			if (array_key_exists($bb2_users[$row['entry_poster_id']], $bb3_users))
			{
				$user_id = $bb3_users[$bb2_users[$row['entry_poster_id']]];
			}
			else
			{
				$user_id = 1;
			}
		}

		$sql_array = array(
			'user_id'				=> $user_id,
			'user_ip'				=> '0.0.0.0',
			'blog_subject'			=> utf8_normalize_nfc($row['entry_subject']),
			'blog_text'				=> $message_parser->message,
			'blog_checksum'			=> md5($message_parser->message),
			'blog_time'				=> $row['entry_time'],
			'enable_bbcode'			=> $row['enable_bbcode'],
			'enable_smilies'		=> $row['enable_smilies'],
			'enable_magic_url'		=> 1,
			'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
			'bbcode_uid'			=> $message_parser->bbcode_uid,
			'blog_deleted'			=> ($row['entry_deleted']) ? $row['entry_poster_id'] : 0,
			'blog_read_count'		=> $row['entry_views'],
			'blog_edit_reason'		=> '',
			'perm_guest'			=> (($row['entry_access'] == 0) ? 1 : 0),
			'perm_registered'		=> (($row['entry_access'] == 0 || $row['entry_access'] == 1) ? 2 : 0),
			'perm_foe'				=> 0,
			'perm_friend'			=> (($row['entry_access'] == 0 || $row['entry_access'] == 1 || $row['entry_access'] == 2) ? 2 : 0),
		);

		if ($this->selected_options['truncate'])
		{
			$sql_array['blog_id'] = $row['entry_id'];
		}

		$sql = 'INSERT IGNORE INTO ' . BLOGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
		$db->sql_query($sql);

		if (!$this->selected_options['truncate'])
		{
			$new_id = $db->sql_nextid();
			$new_ids[$row['entry_id']] = $new_id;
		}

		unset($message_parser, $sql_array);
	}

	if (!$this->selected_options['truncate'])
	{
		$old_new_ids = $cache->get('_blog_upgrade_blog_ids');
		if ($old_new_ids !== false)
		{
			$new_ids = array_merge($old_new_ids, $new_ids);
		}
		$cache->put('_blog_upgrade_blog_ids', $new_ids);
	}

	$sql = 'SELECT count(entry_id) AS cnt FROM ' . $this->selected_options['db_prefix'] . 'weblog_entries';
	$result = $old_db->sql_query($sql);
	$cnt = $db->sql_fetchrow($result);

	if ($cnt['cnt'] >= $start + $this->selected_options['limit'])
	{
		global $part_message, $stage, $start;
		$start += $this->selected_options['limit'];
		$part_message = sprintf($user->lang['BREAK_CONTINUE_NOTICE'], $stage, ($start / $this->selected_options['limit']));
	}
}


if (isset($stage) && $stage == 5 && $this->selected_options['replies'])
{
	if (!$this->selected_options['truncate'])
	{
		$new_ids = $cache->get('_blog_upgrade_blog_ids');
		if ($new_ids === false)
		{
			trigger_error('CONVERTED_BLOG_IDS_MISSING');
		}
	}
	
	$bb2_users = $bb3_users = array();
	$sql = 'SELECT user_id, username FROM ' . $this->selected_options['db_prefix'] . 'users';
	$result = $old_db->sql_query($sql);
	while($row = $old_db->sql_fetchrow($result))
	{
		$bb2_users[$row['user_id']] = $row['username'];
	}
	$old_db->sql_freeresult($result);

	$sql = 'SELECT user_id, username FROM ' . USERS_TABLE;
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
		$bb3_users[$row['username']] = $row['user_id'];
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT * FROM ' . $this->selected_options['db_prefix'] . 'weblog_replies
		ORDER BY post_time ASC
			LIMIT ' . $start . ', ' . $this->selected_options['limit'];
	$result = $old_db->sql_query($sql);
	while ($row = $old_db->sql_fetchrow($result))
	{
		$row['reply_text'] = str_replace(':' . $row['bbcode_uid'], '', $row['reply_text']);
		$row['reply_text'] = utf8_normalize_nfc($row['reply_text']);
		$message_parser = new parse_message();
		$message_parser->message = $row['reply_text'];
		$message_parser->parse($row['enable_bbcode'], 1, $row['enable_smilies']);

		if ($row['poster_id'] == -1)
		{
			$user_id = 1;
		}
		else
		{
			if (array_key_exists($bb2_users[$row['poster_id']], $bb3_users))
			{
				$user_id = $bb3_users[$bb2_users[$row['poster_id']]];
			}
			else
			{
				$user_id = 1;
			}
		}

		$sql_array = array(
			'blog_id'				=> ($this->selected_options['truncate']) ? $row['entry_id'] : $new_ids[$row['entry_id']],
			'user_id'				=> $user_id,
			'user_ip'				=> '0.0.0.0',
			'reply_subject'			=> utf8_normalize_nfc($row['post_subject']),
			'reply_text'			=> $message_parser->message,
			'reply_checksum'		=> md5($message_parser->message),
			'reply_time'			=> $row['post_time'],
			'enable_bbcode'			=> $row['enable_bbcode'],
			'enable_smilies'		=> $row['enable_smilies'],
			'enable_magic_url'		=> 1,
			'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
			'bbcode_uid'			=> $message_parser->bbcode_uid,
			'reply_edit_reason'		=> '',
		);

		if ($this->selected_options['truncate'])
		{
			$sql_array['reply_id'] = $row['reply_id'];
		}

		$sql = 'INSERT IGNORE INTO ' . BLOGS_REPLY_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
		$db->sql_query($sql);

		unset($message_parser, $sql_array);
	}

	$sql = 'SELECT count(reply_id) AS cnt FROM ' . $this->selected_options['db_prefix'] . 'weblog_replies';
	$result = $old_db->sql_query($sql);
	$cnt = $db->sql_fetchrow($result);

	if ($cnt['cnt'] >= $start + $this->selected_options['limit'])
	{
		global $part_message, $stage, $start;
		$start += $this->selected_options['limit'];
		$part_message = sprintf($user->lang['BREAK_CONTINUE_NOTICE'], $stage, ($start / $this->selected_options['limit']));
	}
	else
	{
		global $message;
		$message = $user->lang['REPLY_CONVERT_COMPLETE'];
	}
}

if (isset($stage) && $stage == 6 && $this->selected_options['convert_friend'])
{
	$bb2_users = $bb3_users = array();
	$sql = 'SELECT user_id, username FROM ' . $this->selected_options['db_prefix'] . 'users';
	$result = $old_db->sql_query($sql);
	while($row = $old_db->sql_fetchrow($result))
	{
		$bb2_users[$row['user_id']] = $row['username'];
	}
	$old_db->sql_freeresult($result);

	$sql = 'SELECT user_id, username FROM ' . USERS_TABLE;
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
		$bb3_users[$row['username']] = $row['user_id'];
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT * FROM ' . $this->selected_options['db_prefix'] . 'weblog_friends
		ORDER BY owner_id ASC, friend_id ASC
			LIMIT ' . $start . ', ' . $this->selected_options['limit'];
	$result = $old_db->sql_query($sql);
	while ($row = $old_db->sql_fetchrow($result))
	{
		if (array_key_exists($row['owner_id'], $bb2_users) && array_key_exists($bb2_users[$row['owner_id']], $bb3_users) && array_key_exists($row['friend_id'], $bb2_users) && array_key_exists($bb2_users[$row['friend_id']], $bb3_users))
		{
			$user_id = $bb3_users[$bb2_users[$row['owner_id']]];
			$zebra_id = $bb3_users[$bb2_users[$row['friend_id']]];
		}
		else
		{
			continue;
		}

		if (isset($user_id) && isset($zebra_id))
		{
			$sql_ary = array(
				'user_id'	=> $user_id,
				'zebra_id'	=> $zebra_id,
				'friend'	=> 1,
				'foe'		=> 0,
			);

			$sql = 'INSERT IGNORE INTO ' . ZEBRA_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}

		unset($user_id, $zebra_id);
	}

	$sql = 'SELECT count(owner_id) AS cnt FROM ' . $this->selected_options['db_prefix'] . 'weblog_friends';
	$result = $old_db->sql_query($sql);
	$cnt = $db->sql_fetchrow($result);

	if ($cnt['cnt'] >= $start + $this->selected_options['limit'])
	{
		global $part_message, $stage, $start;
		$start += $this->selected_options['limit'];
		$part_message = sprintf($user->lang['BREAK_CONTINUE_NOTICE'], $stage, ($start / $this->selected_options['limit']));
	}
	else
	{
		global $message;
		$message = $user->lang['FRIEND_CONVERT_COMPLETE'];
	}
}

if (isset($stage) && $stage == 7 && $this->selected_options['convert_foe'])
{
	$bb2_users = $bb3_users = array();
	$sql = 'SELECT user_id, username FROM ' . $this->selected_options['db_prefix'] . 'users';
	$result = $old_db->sql_query($sql);
	while($row = $old_db->sql_fetchrow($result))
	{
		$bb2_users[$row['user_id']] = $row['username'];
	}
	$old_db->sql_freeresult($result);

	$sql = 'SELECT user_id, username FROM ' . USERS_TABLE;
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
		$bb3_users[$row['username']] = $row['user_id'];
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT * FROM ' . $this->selected_options['db_prefix'] . 'weblog_blocked
		ORDER BY owner_id ASC, blocked_id ASC
			LIMIT ' . $start . ', ' . $this->selected_options['limit'];
	$result = $old_db->sql_query($sql);
	while ($row = $old_db->sql_fetchrow($result))
	{
		if (array_key_exists($row['owner_id'], $bb2_users) && array_key_exists($bb2_users[$row['owner_id']], $bb3_users) && array_key_exists($row['blocked_id'], $bb2_users) && array_key_exists($bb2_users[$row['blocked_id']], $bb3_users))
		{
			$user_id = $bb3_users[$bb2_users[$row['owner_id']]];
			$zebra_id = $bb3_users[$bb2_users[$row['blocked_id']]];
		}
		else
		{
			continue;
		}

		if (isset($user_id) && isset($zebra_id))
		{
			$sql_ary = array(
				'user_id'	=> $user_id,
				'zebra_id'	=> $zebra_id,
				'friend'	=> 0,
				'foe'		=> 1,
			);

			$sql = 'INSERT IGNORE INTO ' . ZEBRA_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}

		unset($user_id, $zebra_id);
	}

	$sql = 'SELECT count(owner_id) AS cnt FROM ' . $this->selected_options['db_prefix'] . 'weblog_blocked';
	$result = $old_db->sql_query($sql);
	$cnt = $db->sql_fetchrow($result);

	if ($cnt['cnt'] >= $start + $this->selected_options['limit'])
	{
		global $part_message, $stage, $start;
		$start += $this->selected_options['limit'];
		$part_message = sprintf($user->lang['BREAK_CONTINUE_NOTICE'], $stage, ($start / $this->selected_options['limit']));
	}
	else
	{
		global $message;
		$stage_end_message = $user->lang['FOE_CONVERT_COMPLETE'];
	}
}
?>