<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: single.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

// Are we viewing a blog after a category?
if ($category_id)
{
	$category_list = get_blog_categories('category_id');

	if (!isset($category_list[$category_id]))
	{
		trigger_error('NO_CATEGORY');
	}
}
else
{
	// Generate the left menu
	generate_menu($user_id);
}

// if the blog was deleted and the person trying to view the blog is not a moderator that can view deleted blogs, give them a nice error. :P
if (blog_data::$blog[$blog_id]['blog_deleted'] != 0 && blog_data::$blog[$blog_id]['blog_deleted'] != $user->data['user_id'] && !$auth->acl_get('m_blogdelete') && !$auth->acl_get('a_blogdelete'))
{
	trigger_error('BLOG_NOT_EXIST');
}

// Add the language Variables for viewtopic
$user->add_lang('viewtopic');

// Generate the breadcrumbs, setup the page header, and setup some variables we will use...
generate_blog_breadcrumbs();
page_header(blog_data::$blog[$blog_id]['blog_subject']);

$sort_days = request_var('st', ((!empty($user->data['user_post_show_days'])) ? $user->data['user_post_show_days'] : 0));
$sort_key = request_var('sk', 't');
$sort_dir = request_var('sd', 'a');
$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
$order_dir = ($sort_dir == 'a') ? 'ASC' : 'DESC';
$sort_by_text = array('t' => $user->lang['POST_TIME']);
$sort_by_sql = array('t' => 'blog_time');

$total_replies = $blog_data->get_reply_data('reply_count', $blog_id, array('sort_days' => $sort_days));

// Get the reply data if we need to
if ($total_replies > 0)
{
	$reply_ids = $blog_data->get_reply_data('blog', $blog_id, array('start' => $start, 'limit' => $limit, 'order_dir' => $order_dir, 'sort_days' => $sort_days));
	$blog_data->get_user_data(false, true);
	update_edit_delete('reply');
}
else
{
	$reply_ids = false;
}

// Get the Poll Data
$blog_data->get_polls($blog_id);

// Get the Attachment Data
get_attachment_data($blog_id, $reply_ids);

blog_plugins::plugin_do('view_blog_start');

// Output some data
$template->assign_vars(array(
	// Canonical URL
	'META'				=> '<link rel="canonical" href="' . blog_url($user_id, $blog_id, false, (($start > 0) ? array('start' => $start) : array())) . '" />',

	'BLOG_CSS'			=> (isset($user_settings[$user_id]['blog_css'])) ? $user_settings[$user_id]['blog_css'] : '',

	'U_PRINT_TOPIC'		=> (!$user->data['is_bot']) ? $blog_urls['self_print'] : '',
	'U_VIEW'			=> $blog_urls['self'],

	'S_CATEGORY_MODE'	=> ($category_id) ? true : false,
	'S_SINGLE'			=> true,

	// Quick reply
	'U_QUICK_REPLY'		=> blog_url($user_id, $blog_id, false, array('page' => 'reply', 'mode' => 'add')),
	'S_QUICK_REPLY'		=> ($user->data['is_registered'] && $config['user_blog_quick_reply']) ? true : false,
));

// Quick Reply
add_form_key('postform');

// Parse the blog data and output it to the template
$template->assign_block_vars('blogrow', array_merge($blog_data->handle_blog_data($blog_id), $blog_data->handle_user_data($user_id)));

blog_plugins::plugin_do('view_blog_after_blogrow');

// to update the read count, we are only doing this if the user is not the owner, and the user doesn't view the shortened version, and we are not viewing the deleted blogs page
if ($user->data['user_id'] != $user_id)
{
	$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_read_count = blog_read_count + 1 WHERE blog_id = ' . intval($blog_id);
	$db->sql_query($sql);
}

if ($total_replies > 0 || $sort_days != 0)
{
	// for sorting and pagination
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
	$pagination = generate_blog_pagination($blog_urls['start_zero'], $total_replies, $limit, $start, false);

	$template->assign_vars(array(
		'PAGINATION'			=> $pagination,
		'PAGE_NUMBER' 			=> on_page($total_replies, $limit, $start),
		'TOTAL_POSTS'			=> ($total_replies == 1) ? $user->lang['ONE_REPLY'] : sprintf($user->lang['CNT_REPLIES'], $total_replies),
		'S_REPLIES'				=> true,
		'S_SORT_REPLY'			=> true,
		'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
		'S_SELECT_SORT_KEY' 	=> $s_sort_key,
		'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
	));
	unset($pagination);

	// For the replies
	if ($reply_ids !== false)
	{
		// use a foreach to easily output the data
		foreach($reply_ids as $id)
		{
			// send the data to the template
			$template->assign_block_vars('replyrow', array_merge($blog_data->handle_reply_data($id), $blog_data->handle_user_data(blog_data::$reply[$id]['user_id'])));
		}
	}
}

blog_plugins::plugin_do('view_blog_end');

$template->set_filenames(array(
	'body'		=> 'blog/view_blog.html',
));

?>