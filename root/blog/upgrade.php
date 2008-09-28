<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: upgrade.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

$user->add_lang(array('install', 'posting'));

generate_blog_urls();
generate_blog_breadcrumbs($user->lang['UPGRADE_BLOGS'], append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade'));
page_header($user->lang['UPGRADE_BLOGS']);

include($phpbb_root_path . 'blog/upgrade/upgrade.' . $phpEx);
include($phpbb_root_path . 'blog/upgrade/functions.' .$phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
$blog_upgrade = new blog_upgrade();

$stage = request_var('stage', 0);
$section = request_var('section', 0);
$part = request_var('part', 0);
$error = array();
$part_cnt = $section_cnt = 0;
$message = '';

$stages = array($user->lang['UPGRADE_LIST'], $user->lang['OPTIONS'], $user->lang['CONFIRM'], $user->lang['CLEANUP'], $user->lang['CONVERT'], $user->lang['REINDEX'], $user->lang['RESYNC'], $user->lang['FINAL']);

if ($stage >= 2)
{
	$blog_upgrade->confirm_upgrade_options($mode, $error);
	if (sizeof($error))
	{
		$stage = 2;
	}
}

switch ($stage)
{
	case 0:
		$blog_upgrade->output_available_list();
		$section++;
	break;
	case 1:
		$section++;
		$blog_upgrade->output_upgrade_options($mode);
	break;
	case 2:
		// This is checked above
		$section++;
		$message = $user->lang['PRE_UPGRADE_COMPLETE'];
	break;
	case 3:
		$blog_upgrade->clean_tables();
		$blog_upgrade->reindex('delete');
		$section++;
		$message = $user->lang['CLEANUP_COMPLETE'];
		break;
	case 4:
		$blog_upgrade->old_db_connect();
		$blog_upgrade->run_upgrade($mode);
		$section_cnt = $blog_upgrade->available_upgrades[$mode]['section_cnt'];
		$message = $user->lang['CONVERT_COMPLETE'];
	break;
	case 5:
		$blog_upgrade->reindex();
		$message = $user->lang['INDEX_COMPLETE'];
	break;
	case 6:
		$blog_upgrade->resync();
		$message = $user->lang['RESYNC_COMPLETE'];
	break;
	case 7:
		$cache->purge();
		$message = $user->lang['UPGRADE_COMPLETE'];
	break;
	default :
		trigger_error('NO_STAGE');
}

if ($section <= ($section_cnt - 1) || $part <= ($part_cnt - 1))
{
	$redirect_url = append_sid("{$phpbb_root_path}blog.$phpEx", "page=upgrade&amp;mode={$mode}&amp;stage={$stage}&amp;section={$section}&amp;part={$part}");
	meta_refresh(1, $redirect_url);
	$message = sprintf($user->lang['UPGRADE_BREAK_CONTINUE_NOTICE'], $stage, $section, $section_cnt, $part, $part_cnt);
}
else
{
	$redirect_url = append_sid("{$phpbb_root_path}blog.$phpEx", "page=upgrade&amp;mode={$mode}&amp;stage=" . ($stage + 1));

	if ($stage > 2 && $stage != 7)
	{
		meta_refresh(3, $redirect_url);
	}
}

$template->assign_vars(array(
	'STAGE'			=> $stage,
	'S_NEXT_PART'	=> ($section <= ($section_cnt - 1) || $part <= ($part_cnt - 1)) ? true : false,
	'U_ACTION'		=> $redirect_url,
	'U_BACK'		=> append_sid("{$phpbb_root_path}blog.$phpEx", "page=upgrade&amp;mode={$mode}&amp;stage=" . ($stage - 1)),
	'ERROR'			=> (sizeof($error)) ? implode('<br />', $error) : '',
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