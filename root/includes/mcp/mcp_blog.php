<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class mcp_blog
{
	var $p_master;
	var $u_action;

	function main($id, $mode)
	{
		global $auth, $db, $user, $template, $cache;
		global $config, $phpbb_root_path, $phpEx, $action;
		global $blog_data, $reply_data, $user_data, $blog_plugins, $blog_urls;

		$user->add_lang('mods/blog/common');

		// include some files
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		include($phpbb_root_path . 'blog/functions.' . $phpEx);
		include($phpbb_root_path . 'blog/data/blog_data.' . $phpEx);
		include($phpbb_root_path . 'blog/data/reply_data.' . $phpEx);
		include($phpbb_root_path . 'blog/data/user_data.' . $phpEx);
		include($phpbb_root_path . 'blog/data/handle_data.' . $phpEx);

		// set some initial variables that we will use
		$blog_data = new blog_data();
		$reply_data = new reply_data();
		$user_data = new user_data();

		// Start loading the plugins
		include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);
		$blog_plugins = new blog_plugins();
		$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';
		$blog_plugins->load_plugins();

		$blog = (strpos($mode, 'blogs')) ? true : false;
		$start = request_var('start', 0);
		$limit = request_var('limit', 10);
		$sort_days = request_var('st', ((!empty($user->data['user_post_show_days'])) ? $user->data['user_post_show_days'] : 0));
		$sort_key = request_var('sk', 't');
		$sort_dir = request_var('sd', 'd');
		$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		$order_dir = ($sort_dir == 'a') ? 'ASC' : 'DESC';

		if ($blog)
		{
			$sort_by_text = array('t' => $user->lang['POST_TIME']);
			$sort_by_sql = array('t' => 'blog_time');
		}
		else
		{
			$sort_by_text = array('t' => $user->lang['POST_TIME']);
			$sort_by_sql = array('t' => 'reply_time');
		}

		define('IN_BLOG', true);

		generate_blog_urls();

		$this->tpl_name = 'blog/mcp_blog';
		$this->page_title = $user->lang['MCP_BLOG_' . strtoupper($mode)];

		$template->assign_vars(array(
			'L_TITLE'		=> $user->lang['MCP_BLOG_' . strtoupper($mode)],
			'L_EXPLAIN'		=> $user->lang['MCP_BLOG_' . strtoupper($mode) . '_EXPLAIN'],

			'S_BLOGS'		=> $blog,
			'S_REPLIES'		=> !$blog,
		));

		$extra_data = array('start' => $start, 'limit' => $limit, 'order_by' => $sort_by_sql[$sort_key], 'order_dir' => $order_dir, 'sort_days' => $sort_days);
		switch ($mode)
		{
			case 'reported_blogs' :
				$ids = $blog_data->get_blog_data('reported', false, $extra_data);
			break;
			case 'reported_replies' :
				$ids = $reply_data->get_reply_data('reported', false, $extra_data);
			break;
			case 'disapproved_blogs' :
				$ids = $blog_data->get_blog_data('disapproved', false, $extra_data);
			break;
			case 'disapproved_replies' :
				$ids = $reply_data->get_reply_data('reported', false, $extra_data);
			break;
		}

		if ($ids === false)
		{
			$ids = array();
		}

		$user_data->get_user_data(false, true);

		if ($blog)
		{
			$total_posts = (count($ids) == 1) ? $user->lang['ONE_BLOG'] : sprintf($user->lang['CNT_BLOGS'], count($ids));

			foreach ($ids as $id)
			{
				$user_id = $blog_data->blog[$id]['user_id'];
				$template->assign_block_vars('postrow', array(
					'U_VIEW'		=> blog_url($user_id, $id),
					'SUBJECT'		=> $blog_data->blog[$id]['blog_subject'],
					'AUTHOR'		=> $user_data->user[$user_id]['username_full'],
					'TIME'			=> $user->format_date($blog_data->blog[$id]['blog_time']),
				));
			}
		}
		else
		{
			$total_posts = (count($ids) == 1) ? $user->lang['ONE_REPLY'] : sprintf($user->lang['CNT_REPLIES'], count($ids));

			foreach ($ids as $id)
			{
				$user_id = $reply_data->reply[$id]['user_id'];
				$blog_id = $reply_data->reply[$id]['blog_id'];
				$template->assign_block_vars('postrow', array(
					'U_VIEW'		=> blog_url($user_id, $blog_id, $id),
					'SUBJECT'		=> $reply_data->reply[$id]['reply_subject'],
					'AUTHOR'		=> $user_data->user[$user_id]['username_full'],
					'TIME'			=> $user->format_date($reply_data->reply[$id]['reply_time']),
				));
			}
		}

		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
		$pagination = generate_blog_pagination($blog_urls['start_zero'], count($ids), $limit, $start, false);

		$template->assign_vars(array(
			'PAGINATION'			=> $pagination,
			'PAGE_NUMBER' 			=> on_page(count($ids), $limit, $start),
			'TOTAL_POSTS'			=> $total_posts,
			'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
			'S_SELECT_SORT_KEY' 	=> $s_sort_key,
			'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
		));
	}
}

?>