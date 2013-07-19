<?php
/**
*
* @package phpBB3 User Blog Anti-Spam
* @version $Id$
* @copyright (c) 2008 EXreaction
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

function antispam_blog_add_after_setup(&$data)
{
	global $config, $user;

	if (!class_exists('antispam'))
	{
		return;
	}

	if ($config['asacp_spam_words_posting_action'] == 1 && antispam::spam_words(array($data['blog_subject'], $data['blog_text'])))
	{
		$user->add_lang('mods/asacp');
		antispam::add_log('LOG_SPAM_POST_DENIED', array($data['blog_subject'], $data['blog_text']));
		$data['error'][] = $user->lang['SPAM_DENIED'];
	}
}

function antispam_blog_add_sql(&$data)
{
	global $config, $user;

	if (!class_exists('antispam'))
	{
		return;
	}

	if ($config['asacp_spam_words_posting_action'] == 2 && antispam::spam_words(array($data['blog_subject'], $data['blog_text'])))
	{
		$data['blog_approved'] = 0;
	}
}

function antispam_blog_add_after_sql($blog_id)
{
	global $config, $user;

	if (!class_exists('antispam'))
	{
		return;
	}

	if ($user->data['user_flagged'])
	{
		antispam::add_log('LOG_ADDED_BLOG', blog_url($user->data['user_id'], $blog_id), 'flag');
	}
}

function antispam_blog_edit_after_sql($blog_id)
{
	global $config, $user;

	if (!class_exists('antispam'))
	{
		return;
	}

	if ($user->data['user_flagged'])
	{
		antispam::add_log('LOG_EDITED_BLOG', blog_url($user->data['user_id'], $blog_id), 'flag');
	}
}

function antispam_reply_add_after_setup(&$data)
{
	global $config, $user;

	if (!class_exists('antispam'))
	{
		return;
	}

	if ($config['asacp_spam_words_posting_action'] == 1 && antispam::spam_words(array($data['reply_subject'], $data['reply_text'])))
	{
		$user->add_lang('mods/asacp');
		antispam::add_log('LOG_SPAM_POST_DENIED', array($data['reply_subject'], $data['reply_text']));
		$data['error'][] = $user->lang['SPAM_DENIED'];
	}
}

function antispam_reply_add_sql(&$data)
{
	global $config, $user;

	if (!class_exists('antispam'))
	{
		return;
	}

	if ($config['asacp_spam_words_posting_action'] == 2 && antispam::spam_words(array($data['reply_subject'], $data['reply_text'])))
	{
		$data['reply_approved'] = 0;
	}
}

function antispam_reply_add_after_sql($reply_id)
{
	global $config, $user;

	if (!class_exists('antispam'))
	{
		return;
	}

	if ($user->data['user_flagged'])
	{
		global $blog_id;
		antispam::add_log('LOG_ADDED_BLOG_REPLY', blog_url($user->data['user_id'], $blog_id, $reply_id), 'flag');
	}
}

function antispam_reply_edit_after_sql($reply_id)
{
	global $config, $user;

	if (!class_exists('antispam'))
	{
		return;
	}

	if ($user->data['user_flagged'])
	{
		global $blog_id;
		antispam::add_log('LOG_EDITED_BLOG_REPLY', blog_url($user->data['user_id'], $blog_id, $reply_id), 'flag');
	}
}