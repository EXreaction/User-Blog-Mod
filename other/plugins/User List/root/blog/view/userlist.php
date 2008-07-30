<?php
if (!defined('IN_PHPBB'))
{
	exit;
}

$limit = request_var('limit', 10);
$sort_dir = request_var('sd', 'a');
$order_dir = ($sort_dir == 'a') ? 'ASC' : 'DESC';

$user->add_lang(array('mods/blog/view', 'viewtopic'));

$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
$sort_by_text = array('t' => $user->lang['USERNAME'], 'pt' => $user->lang['LAST_BLOG_TIME']);
$sort_by_sql = array('t' => 'username', 'pt' => 'blog_time');

$users = array();
$blog_ids = array();
$sql = 'SELECT COUNT(b.blog_id) AS blog_count, MAX(b.blog_id) AS blog_id, u.user_id, u.user_colour, u.username, bu.title
	FROM ' . BLOGS_TABLE . ' b, ' . USERS_TABLE . ' u
	LEFT JOIN ' . BLOGS_USERS_TABLE . ' bu
		ON bu.user_id = u.user_id
	WHERE u.user_id = b.user_id' .
	(($sort_days) ? ' AND b.blog_time >= ' . (time() - $sort_days * 86400) : '') .
	build_permission_sql($user->data['user_id'], false, 'b.') . '
	GROUP BY b.user_id
	ORDER BY ' . $sort_by_sql[$sort_key] . ' ' . $order_dir;
$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result))
{
	$users[$row['user_id']] = $row;
	$blog_ids[] = $row['blog_id'];
}
$db->sql_freeresult($result);
$total = sizeof($users);

$last_blogs = array();
if (sizeof($blog_ids))
{
	$sql = 'SELECT * FROM ' . BLOGS_TABLE . ' WHERE ' . $db->sql_in_set('blog_id', $blog_ids);
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$last_blogs[$row['blog_id']] = $row;
	}
	$db->sql_freeresult($result);
}

gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
$pagination = generate_blog_pagination($blog_urls['start_zero'], $total, $limit, $start, false);

generate_blog_breadcrumbs($user->lang['USERLIST']);

// Generate the left menu
generate_menu();

page_header($user->lang['BLOG'] . ' ' . $user->lang['USERLIST']);

// Output some data
$template->assign_vars(array(
	'FOLDER_IMG'			=> $user->img('forum_read', ''),
	'FORUM_FOLDER_IMG_SRC'	=> $user->img('forum_read', '', false, '', 'src'),

	'PAGINATION'			=> $pagination,
	'PAGE_NUMBER' 			=> on_page($total, $limit, $start),
	'TOTAL_POSTS'			=> ($total == 1) ? $user->lang['ONE_BLOG'] : sprintf($user->lang['CNT_BLOGS'], $total),

	'S_SORT'				=> true,
	'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
	'S_SELECT_SORT_KEY' 	=> $s_sort_key,
	'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
));
unset($pagination);

$i = -1;
foreach ($users as $user_id => $row)
{
	$i++;
	if ($i < $start)
	{
		continue;
	}
	else if ($i >= $start + $limit)
	{
		break;
	}

	$last_blog = $last_blogs[$row['blog_id']];
	$blog_text = trim_text($last_blog['blog_text'], $last_blog['bbcode_uid'], $config['user_blog_text_limit'], $last_blog['bbcode_bitfield'], $last_blog['enable_bbcode']);
	$bbcode_options = (($last_blog['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($last_blog['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($last_blog['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
	$blog_text = generate_text_for_display($blog_text, $last_blog['bbcode_uid'], $last_blog['bbcode_bitfield'], $bbcode_options);

	$template->assign_block_vars('userrow', array(
		'BLOG_COUNT'	=> $row['blog_count'],
		'BLOG_TITLE'	=> ($row['title']) ? censor_text($row['title']) : sprintf($user->lang['USERNAMES_BLOGS'], $row['username']),
		'USERNAME'		=> get_username_string('full', $user_id, $row['username'], $row['user_colour']),

		'LAST_BLOG'			=> $blog_text,
		'LAST_BLOG_SUBJECT'	=> censor_text($last_blog['blog_subject']),
		'LAST_BLOG_TIME'	=> $user->format_date($last_blog['blog_time']),

		'U_VIEW_BLOG'		=> blog_url($user_id),
		'U_VIEW_LAST_BLOG'	=> blog_url($user_id, $last_blog['blog_id']),
	));
}

$template->set_filenames(array(
	'body'		=> 'blog/userlist.html',
));

?>