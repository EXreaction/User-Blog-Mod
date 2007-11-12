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

$user->add_lang('mods/blog/upgrade');
$user->add_lang('install');
$user->add_lang('posting');

generate_blog_urls();
generate_blog_breadcrumbs($user->lang['UPGRADE_BLOG'], append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade'));
page_header($user->lang['UPGRADE_BLOG']);

include($phpbb_root_path . 'blog/upgrade/upgrade.' . $phpEx);
include($phpbb_root_path . 'blog/upgrade/functions.' .$phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
$blog_upgrade = new blog_upgrade();

$stage = request_var('stage', 0);
$start = request_var('start', 0);
$error = array();
$message = '';

$stages = array($user->lang['UPGRADE_LIST']);
if ($stage > 0)
{
	$stages = array($user->lang['UPGRADE_LIST'], $user->lang['OPTIONS'], $user->lang['CONFIRM'], $user->lang['CLEANUP'], $user->lang['CONVERT_BLOGS']);
	$stages = array_merge($stages, $blog_upgrade->available_upgrades[$mode]['custom_stages']);
	$stages = array_merge($stages, array($user->lang['REINDEX_BLOGS'], $user->lang['REINDEX_REPLIES'], $user->lang['RESYNC'], $user->lang['FINAL']));

	if ($stage > 2)
	{
		$blog_upgrade->confirm_upgrade_options($mode, $error);
		if (count($error))
		{
			$error = array();
			$stage = 2;
		}
	}
}

$stage_cnt = count($stages);
switch ($stage)
{
	case 0:
		$blog_upgrade->output_available_list();
	break;
	case 1:
		$blog_upgrade->output_upgrade_options($mode);
	break;
	case 2:
		$blog_upgrade->confirm_upgrade_options($mode, $error);
		$message = $user->lang['BLOG_PRE_CONVERT_COMPLETE'];
	break;
	case 3:
		$blog_upgrade->clean_tables();
		$blog_upgrade->reindex('delete');
		$message = $user->lang['BLOG_CLEANUP_CONVERT_COMPLETE'];
		break;
	case 4:
		$blog_upgrade->old_db_connect();
		$blog_upgrade->run_upgrade($mode, $stage);
		$message = $user->lang['BLOG_CONVERT_COMPLETE'];
	break;
	case ($stage_cnt - 4):
		$blog_upgrade->reindex('blog');
		$message = $user->lang['INDEX_BLOG_CONVERT_COMPLETE'];
	break;
	case ($stage_cnt - 3):
		$blog_upgrade->reindex('reply');
		$message = $user->lang['INDEX_REPLY_CONVERT_COMPLETE'];
	break;
	case ($stage_cnt - 2):
		$blog_upgrade->resync();
		$message = $user->lang['RESYNC_CONVERT_COMPLETE'];
	break;
	case ($stage_cnt - 1):
		$cache->purge();
		$message = $user->lang['CONVERT_COMPLETE'];
	break;
	default :
		if ($stage > 4 && $stage < ($stage_cnt - 3))
		{
			$blog_upgrade->confirm_upgrade_options($mode, $error);
			if (!count($error))
			{
				$blog_upgrade->old_db_connect();
				$blog_upgrade->run_upgrade($mode, $stage);
			}
			else
			{
				$stage == 2;
			}
		}
		else
		{
			trigger_error('NO_STAGE');
		}
}

if (isset($part_message))
{
	meta_refresh(1, append_sid("{$phpbb_root_path}blog.$phpEx", "page=upgrade&amp;stage={$stage}&amp;mode={$mode}&amp;start={$start}"));
	$message = $part_message;
}

$template->assign_vars(array(
	'STAGE'			=> $stage,
	'S_LAST_STAGE'	=> ($stage == ($stage_cnt - 1)) ? true : false,
	'S_NEXT_PART'	=> (isset($part_message)) ? true : false,
	'U_ACTION'		=> (isset($part_message)) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=upgrade&amp;stage={$stage}&amp;mode={$mode}&amp;start={$start}") : append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade&amp;stage=' . ($stage + 1) . "&amp;mode={$mode}"),
	'U_BACK'		=> append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade&amp;stage=' . ($stage - 1) . '&amp;mode=' . $mode),
	'ERROR'			=> (count($error)) ? implode('<br/>', $error) : '',
	'MESSAGE'		=> $message,
));

$i = 0;
foreach($stages as $st)
{
	$template->assign_block_vars('l_block1', array(
		'S_SELECTED'		=> ($i == $stage) ? true : false,
		'U_TITLE'			=> ($i <= $stage) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=upgrade&amp;stage={$i}&amp;mode={$mode}") : '#',
		'L_TITLE'			=> $st,
	));

	$i++;
}

$template->set_filenames(array(
	'body' => 'blog/upgrade.html'
));
?>