<?php
/**
*
* @package phpBB3 User Blog Archives
* @version $Id: functions.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

function archive_function_generate_menu(&$arg)
{
	global $auth, $db, $user, $template, $phpbb_root_path, $blog_images_path;

	if (!$arg['user_id'])
	{
		return;
	}

	$last_mon = 0;

	$archive_rows = array();

	$sql = 'SELECT blog_id, blog_time, blog_subject FROM ' . BLOGS_TABLE . '
		WHERE user_id = ' . intval($arg['user_id']) .
		(($auth->acl_get('m_blogapprove')) ? '' : ' AND blog_approved = 1') .
		((!$auth->acl_gets('m_blogdelete', 'a_blogdelete')) ? ' AND (blog_deleted = 0 OR blog_deleted = ' . $user->data['user_id'] . ')' : '') .
		build_permission_sql($user->data['user_id']) . '
			ORDER BY blog_time DESC';
	$result = $db->sql_query($sql);

	while($row = $db->sql_fetchrow($result))
	{
		$date = getdate($row['blog_time']);

		// If we are starting a new month
		if ($date['mon'] != $last_mon)
		{
			$archive_row = array(
				'MONTH'			=> $user->lang['datetime'][$date['month']], // make sure to use the correct language
				'YEAR'			=> $date['year'],

				'monthrow'		=> array(),
			);

			$archive_rows[] = $archive_row;
		}

		$archive_row_month = array(
			'TITLE'			=> censor_text($row['blog_subject']),
			'U_VIEW'		=> blog_url($arg['user_id'], $row['blog_id'], false, array(), array('blog_subject' => $row['blog_subject'])),
			'DATE'			=> $user->format_date($row['blog_time']),
		);

		$archive_rows[sizeof($archive_rows) - 1]['monthrow'][] = $archive_row_month;

		// set the last month variable as the current month
		$last_mon = $date['mon'];
	}
	$db->sql_freeresult($result);

	foreach($archive_rows as $row)
	{
		$template->assign_block_vars('archiverow', $row);
	}

	$template->assign_vars(array(
		'S_ARCHIVES'	=> (sizeof($archive_rows)) ? true : false,
		'T_THEME_PATH'	=> "{$phpbb_root_path}styles/" . $user->theme['theme_path'] . '/theme',

		'IMG_PLUS'		=> $blog_images_path . 'plus.gif',
		'IMG_MINUS'		=> $blog_images_path . 'minus.gif',
	));

	$arg['user_menu_extra'] .= blog_plugins::parse_template('blog/plugins/archive/archive_body.html');

	unset($template->_tpldata['archiverow']);
}
?>