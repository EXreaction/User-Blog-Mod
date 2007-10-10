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

/*
* CURRENT UPGRADE INFORMATION
*
* Since this is still in early development this script is not going to have any GUI.
*  Once the mod gets further I will come back to this page and add a GUI and some much nicer stuff for upgrades.
*
* THIS ONLY SUPPORTS UPGRADES FROM 1 PREVIOUS BLOG SYSTEM - the blog mod 0.2.4b
*
* To test it comment out the trigger_error below, and enter the DB information below that. 
*
* TO DO -
*  Add upgrade/ directory with upgrade scripts in, so a user can just drop a new upgrade script in and it will be autodetected & selectable
*  Add GUI for from blog selection & old DB info
*/

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['UPGRADE_BLOG']);

// Comment out the following like to test the upgrade
//trigger_error('This page is only FOR TESTING.  Under no circumstances should you use this for the actual upgrade.<br/>There is absolutely no support for upgrades at this time.  If you are a tester who is willing to test the upgrade on a dev server please follow the instructions in includes/blog/upgrade.php.');

// Enter in your old DB information to test the upgrade
$dbhost			= 'localhost';
$dbuser			= 'root';
$dbpassword		= '';
$dbname			= 'phpbb3_blog';
$dbtableprefix	= 'phpbb_';

// if this is set to true we do not insert any data into the database nor truncate the tables, we just test extracting the data and parsing everything.
$test = true;

$old_db = new $sql_db();
if (!@$old_db->sql_connect($dbhost, $dbuser, $dbpassword, $dbname, false, true))
{
	trigger_error('Could not connect to Database.');
}
unset($dbpassword);

$sql_array = array();

$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_TABLE;
$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_REPLY_TABLE;
$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_SUBSCRIPTION_TABLE;

foreach ($sql_array as $sql)
{
	echo 'Current Query: ' . $sql . '<br/>';
	flush();
	if (!$test)
	{
		$db->sql_query($sql);
	}
}
unset($sql_array);

$bb2_users = $bb3_users = array();
$sql = 'SELECT user_id, username FROM ' . $dbtableprefix . 'users';
echo '<br/>Current Query: ' . $sql . '<br/><br/>';
flush();
$result = $old_db->sql_query($sql);
while($row = $old_db->sql_fetchrow($result))
{
	$bb2_users[$row['user_id']] = $row['username'];
}
$old_db->sql_freeresult($result);

$sql = 'SELECT user_id, username FROM ' . USERS_TABLE;
echo '<br/>Current Query: ' . $sql . '<br/><br/>';
flush();
$result = $db->sql_query($sql);
while($row = $db->sql_fetchrow($result))
{
	$bb3_users[$row['username']] = $row['user_id'];
}
$db->sql_freeresult($result);

$sql = 'SELECT * FROM ' . $dbtableprefix . 'weblog_entries';
echo '<br/>Current Query: ' . $sql . '<br/><br/>';
flush();
$result = $old_db->sql_query($sql);
while ($row = $old_db->sql_fetchrow($result))
{
	echo 'On row ' . $row['entry_id'] . '<br/>';
	flush();

	$text = utf8_normalize_nfc($row['entry_text']);
	decode_message($text, $row['bbcode_uid']);
	$message_parser = new parse_message();
	$message_parser->message = $text;
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
		'blog_id'				=> $row['entry_id'],
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
	);

	$sql2 = 'INSERT INTO ' . BLOGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
	echo 'Current Query: ' . $sql2 . '<br/>';
	flush();

	if (!$test)
	{
		$db->sql_query($sql2);
	}

	unset($text);
	unset($message_parser);
	unset($sql_array);

	echo '<br/>';
}

$sql = 'SELECT * FROM ' . $dbtableprefix . 'weblog_replies';
echo '<br/>Current Query: ' . $sql . '<br/><br/>';
flush();
$result = $old_db->sql_query($sql);
while ($row = $old_db->sql_fetchrow($result))
{
	echo 'On row ' . $row['entry_id'] . '<br/>';
	flush();

	$text = utf8_normalize_nfc($row['reply_text']);
	decode_message($text, $row['bbcode_uid']);
	$message_parser = new parse_message();
	$message_parser->message = $text;
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
		'reply_id'				=> $row['reply_id'],
		'blog_id'				=> $row['entry_id'],
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

	$sql2 = 'INSERT INTO ' . BLOGS_REPLY_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
	echo 'Current Query: ' . $sql2 . '<br/>';
	flush();

	if (!$test)
	{
		$db->sql_query($sql2);
	}

	unset($text);
	unset($message_parser);
	unset($sql_array);

	echo '<br/>';
}

echo 'Resyncing the User Blog Mod, this may take a while.<br/><br/>';
flush();
resync_blog('all');

echo 'Done with the upgrade!';
?>