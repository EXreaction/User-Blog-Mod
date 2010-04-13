<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: user.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

// Add the language Variables for viewtopic
$user->add_lang('viewtopic');

// for sorting and pagination
$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
$total_blogs = $blog_data->get_blog_data('count', $user_id, array('sort_days' => $sort_days, 'deleted' => ($mode == 'deleted') ? true : false, 'custom_sql' => 'b.user_id = ' . $user_id));
$sort_by_text = array('t' => $user->lang['POST_TIME'], 'c' => $user->lang['REPLY_COUNT'], 'bt' => $user->lang['BLOG_SUBJECT']);
$sort_by_sql = array('t' => 'blog_time', 'c' => 'blog_reply_count', 'bt' => 'blog_subject');
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
$pagination = generate_blog_pagination($blog_urls['start_zero'], $total_blogs, $limit, $start, false);

// Get the blogs
if ($mode == 'deleted')
{
	$blog_ids = $blog_data->get_blog_data('user_deleted', $user_id, array('start' => $start, 'limit' => $limit, 'order_by' => $sort_by_sql[$sort_key], 'order_dir' => $order_dir, 'sort_days' => $sort_days));
}
else
{
	$blog_ids = $blog_data->get_blog_data('user', $user_id, array('start' => $start, 'limit' => $limit, 'order_by' => $sort_by_sql[$sort_key], 'order_dir' => $order_dir, 'sort_days' => $sort_days));
}

blog_plugins::plugin_do('view_user_start');

$blog_data->get_user_data(false, true);
update_edit_delete('blog');

generate_blog_breadcrumbs();
if (!$feed)
{
	// Generate the left menu
	generate_menu($user_id);

	if ($mode == 'deleted')
	{
		page_header(sprintf($user->lang['USERNAMES_DELETED_BLOGS'], blog_data::$user[$user_id]['username']));
	}
	else
	{
		if ($user->data['username'] == blog_data::$user[$user_id]['username'])
		{
			page_header($user->lang['MY_BLOG']);
		}
		else
		{
			page_header(sprintf($user->lang['USERNAMES_BLOGS'], blog_data::$user[$user_id]['username']));
		}
	}

	// Output some data
	$template->assign_vars(array(
		// Canonical URL
		'META'					=> '<link rel="canonical" href="' . blog_url($user_id, false, false, (($start > 0) ? array('start' => $start) : array())) . '" />',

		'PAGINATION'			=> $pagination,
		'PAGE_NUMBER' 			=> on_page($total_blogs, $limit, $start),
		'TOTAL_POSTS'			=> ($total_blogs == 1) ? $user->lang['ONE_BLOG'] : sprintf($user->lang['CNT_BLOGS'], $total_blogs),

		'U_BLOG_FEED'			=> ($config['user_blog_enable_feeds']) ? blog_url($user_id, false, false, array('feed' => 'explain')) : '',
		'U_PRINT_TOPIC'			=> (!$user->data['is_bot']) ? $blog_urls['self_print'] : '',
		'U_VIEW'				=> $blog_urls['self'],

		'S_SORT'				=> true,
		'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
		'S_SELECT_SORT_KEY' 	=> $s_sort_key,
		'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
		'S_VIEW_REPLY_COUNT'	=> true,

		'L_NO_DELETED_BLOGS'	=> ($sort_days == 0) ? $user->lang['NO_DELETED_BLOGS'] : sprintf($user->lang['NO_DELETED_BLOGS_SORT_DAYS'], $limit_days[$sort_days]),
		'L_NO_BLOGS_USER'		=> ($sort_days == 0) ? $user->lang['NO_BLOGS_USER'] : sprintf($user->lang['NO_BLOGS_USER_SORT_DAYS'], $limit_days[$sort_days]),

		'BLOG_CSS'				=> (isset($user_settings[$user_id]['blog_css'])) ? $user_settings[$user_id]['blog_css'] : '',
	));
	unset($pagination);

	// parse and output the blogs
	if ($blog_ids !== false)
	{
		// Get the Attachment Data
		get_attachment_data($blog_ids, false);

		// read blogs, for updating the read count
		$read_blogs = array();

		foreach($blog_ids as $id)
		{
			$blogrow = array_merge($blog_data->handle_user_data(blog_data::$blog[$id]['user_id']), $blog_data->handle_blog_data($id, $config['user_blog_user_text_limit']));
			$template->assign_block_vars('blogrow', $blogrow);

			if (!$blogrow['S_SHORTENED'])
			{
				// for updating the read count later
				array_push($read_blogs, $id);
			}
		}

		// to update the read count, we are only doing this if the user is not the owner, and the user doesn't view the shortened version, and we are not viewing the deleted blogs page
		if ($user->data['user_id'] != $user_id && $mode != 'deleted' && sizeof($read_blogs))
		{
			$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_read_count = blog_read_count + 1 WHERE ' . $db->sql_in_set('blog_id', $read_blogs);
			$db->sql_query($sql);
		}
		unset($read_blogs);
	}

	blog_plugins::plugin_do('view_user_end');

	$template->set_filenames(array(
		'body'		=> 'blog/view_blog.html',
	));
}
else // if $feed
{
	feed_output($blog_ids, $feed);
}
?>