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

// if the blog was deleted and the person trying to view the blog is not a moderator that can view deleted blogs, give them a nice error. :P
if ($blog_data->blog[$blog_id]['blog_deleted'] != 0 && !$auth->acl_get('m_blogdelete') && !$auth->acl_get('a_blogdelete'))
{
	trigger_error('BLOG_DELETED');
}

// Add the language Variables for viewtopic
$user->add_lang('viewtopic');

// Generate the left menu
generate_menu($user_id);

// Generate the breadcrumbs, setup the page header, and setup some variables we will use...
$breadcrumbs[sprintf($user->lang['USERNAMES_BLOGS'], $user_data->user[$user_id]['username'])] = $blog_urls['view_user'];

generate_blog_breadcrumbs();
page_header($user->lang['VIEW_BLOG'] .' - ' . $blog_data->blog[$blog_id]['blog_subject']);

// Output some data
$template->assign_vars(array(
	'POST_SUBJECT'		=> $blog_data->blog[$blog_id]['blog_subject'],
	'TITLE'				=> $blog_data->blog[$blog_id]['blog_subject'],

	'U_PRINT_TOPIC'		=> (!$user->data['is_bot']) ? $blog_urls['self_print'] : '',
	'U_VIEW'			=> $blog_urls['self'],

	'S_SHOW_SIGNATURE'	=> true,
));

// Parse the blog data and output it to the template
$template->assign_block_vars('blogrow', $blog_data->handle_blog_data($blog_id) + $user_data->handle_user_data($user_id));

// to update the read count, we are only doing this if the user is not the owner, and the user doesn't view the shortened version, and we are not viewing the deleted blogs page
if ($user->data['user_id'] != $user_id)
{
	$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_read_count = blog_read_count + 1 WHERE blog_id = \'' . $blog_id . '\'';
	$db->sql_query($sql);
}

$total_replies = $reply_data->get_reply_data('reply_count', $blog_id);

// Get the reply data if we need to
if ($total_replies > 0)
{
	// for sorting and pagination
	$sort_by_text = array('t' => $user->lang['POST_TIME']);
	$sort_by_sql = array('t' => 'blog_time');
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
	$pagination = generate_pagination($blog_urls['self_minus_start'], $total_replies, $limit, $start, false);

	// Get the data on all of the replies
	$reply_ids = $reply_data->get_reply_data('blog', $blog_id, array('start' => $start, 'limit' => $limit, 'order_dir' => $order_dir, 'sort_days' => $sort_days));
	$user_data->get_user_data(false, true);
	update_edit_delete('reply');

	$template->assign_vars(array(
		'PAGINATION'			=> $pagination,
		'PAGE_NUMBER' 			=> on_page($total_replies, $limit, $start),
		'TOTAL_POSTS'			=> ($total_replies == 1) ? $user->lang['REPLY_COUNT'] : sprintf($user->lang['REPLIES_COUNT'], $total_replies),
		'S_SORT_REPLY'			=> true,
		'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
		'S_SELECT_SORT_KEY' 	=> $s_sort_key,
		'S_SELECT_SORT_DAYS' 	=> $s_limit_days,

		'S_REPLIES'				=> true,
	));

	// For the replies
	if ($reply_ids !== false)
	{
		// use a foreach to easily output the data
		foreach($reply_ids as $id)
		{
			// handle the user and reply data
			$user_replyrow = $user_data->handle_user_data($reply_data->reply[$id]['user_id']);
			$replyrow = $reply_data->handle_reply_data($id);

			// send the data to the template
			$template->assign_block_vars('replyrow', $user_replyrow + $replyrow);

			// output the custom fields
			$user_data->handle_user_data($reply_data->reply[$id]['user_id'], 'replyrow.custom_fields');
		}
	}
}

// tell the template parser what template file to use
$template->set_filenames(array(
	'body' => 'view_blog.html'
));

?>