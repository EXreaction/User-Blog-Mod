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

generate_blog_urls();
generate_blog_breadcrumbs($user->lang['UPGRADE_BLOG'], append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade'));
page_header($user->lang['UPGRADE_BLOG']);

include($phpbb_root_path . 'blog/upgrade/upgrade.' . $phpEx);
include($phpbb_root_path . 'blog/upgrade/functions.' .$phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
$blog_upgrade = new blog_upgrade();

$stage = request_var('stage', 0);
$stages = array($user->lang['UPGRADE_LIST'], $user->lang['OPTIONS'], $user->lang['CONFIRM'], $user->lang['CONVERT_BLOGS'], $user->lang['CONVERT_REMAINING'], $user->lang['REINDEX'], $user->lang['RESYNC'], $user->lang['FINAL']);
$error = array();
$message = '';
$limit = 250;

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
		$blog_upgrade->confirm_upgrade_options($mode, $error);
		if (!count($error))
		{
			if ($blog_upgrade->selected_options['truncate'])
			{
				$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_TABLE;
				$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_REPLY_TABLE;
				$sql_array[] = 'TRUNCATE TABLE ' . BLOGS_SUBSCRIPTION_TABLE;
				$sql_array[] = 'TRUNCATE TABLE ' . BLOG_SEARCH_WORDLIST_TABLE;
				$sql_array[] = 'TRUNCATE TABLE ' . BLOG_SEARCH_WORDMATCH_TABLE;
				$sql_array[] = 'TRUNCATE TABLE ' . BLOG_SEARCH_RESULTS_TABLE;

				foreach ($sql_array as $sql)
				{
					$db->sql_query($sql);
				}
				unset($sql_array);
			}

			$blog_upgrade->old_db_connect();
			$blog_upgrade->run_blog_upgrade($mode);

			if (isset($part_message))
			{
				meta_refresh(2, append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade&amp;stage=' . ($stage + 1) . "&amp;mode={$mode}&amp;start={$start}"));
				$message = $part_message;
			}
			else
			{
				$message = $user->lang['BLOG_CONVERT_COMPLETE'];
			}
		}
		else
		{
			$stage == 2;
		}
	break;
	case 4:
		$blog_upgrade->confirm_upgrade_options($mode, $error);
		if (!count($error))
		{
			$blog_upgrade->old_db_connect();
			$blog_upgrade->run_remaining_upgrade($mode);

			$cache->destroy('_blog_upgrade');
			if (isset($part_message))
			{
				meta_refresh(2, append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade&amp;stage=' . ($stage + 1) . "&amp;mode={$mode}&amp;start={$start}"));
				$message = $part_message;
			}
			else
			{
				$message = $user->lang['REMAINING_CONVERT_COMPLETE'];
			}
		}
		else
		{
			$stage == 2;
		}
	break;
	case 5:
		include($phpbb_root_path . 'blog/search/fulltext_native.' . $phpEx);
		$blog_search = new blog_fulltext_native();
		$blog_search->reindex();
		if (isset($part_message))
		{
			meta_refresh(2, append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade&amp;stage=' . ($stage + 1) . "&amp;mode={$mode}&amp;start={$start}"));
			$message = $part_message;
		}
		else
		{
			$message = $user->lang['INDEX_CONVERT_COMPLETE'];
		}
	break;
	case 6:
		resync_blog('all');
		$cache->purge();
		$message = $user->lang['RESYNC_CONVERT_COMPLETE'];
	break;
	case 7:
		$message = $user->lang['CONVERT_COMPLETE'];
	break;
	default :
		trigger_error('NO_STAGE');
}

$template->assign_vars(array(
	'STAGE'		=> $stage,
	'U_ACTION'	=> append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade&amp;stage=' . ($stage + 1) . "&amp;mode={$mode}&amp;start={$start}"),
	'U_BACK'	=> append_sid("{$phpbb_root_path}blog.$phpEx", 'page=upgrade&amp;stage=' . ($stage - 1) . '&amp;mode=' . $mode),
	'ERROR'		=> (count($error)) ? implode('<br/>', $error) : '',
	'MESSAGE'	=> $message,
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