<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: mcp_blog.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
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
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $action;
		global $blog_data, $blog_plugins, $blog_urls;

		$user->add_lang(array('mods/blog/common', 'mods/blog/mcp'));

		// include some files
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		include($phpbb_root_path . 'blog/functions.' . $phpEx);

		// set some initial variables that we will use
		$blog_data = new blog_data();

		blog_plugins::plugin_do('mcp_start');

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
			// Need to add counts here...
			case 'reported_blogs' :
				$ids = $blog_data->get_blog_data('reported', false, $extra_data);
			break;
			case 'reported_replies' :
				$ids = $blog_data->get_reply_data('reported', false, $extra_data);
			break;
			case 'disapproved_blogs' :
				$ids = $blog_data->get_blog_data('disapproved', false, $extra_data);
			break;
			case 'disapproved_replies' :
				$ids = $blog_data->get_reply_data('disapproved', false, $extra_data);
			break;
			default :
				blog_plugins::plugin_do_arg('mcp_default', $mode);
		}

		if ($blog)
		{
			$cnt_sql = 'SELECT count(blog_id) AS total FROM ' . BLOGS_TABLE . ' WHERE blog_' . ((strpos($mode, 'reported') !== false) ? 'reported = 1' : 'approved = 0');
		}
		else
		{
			$cnt_sql = 'SELECT count(reply_id) AS total FROM ' . BLOGS_REPLY_TABLE . ' WHERE ' . 'reply_' . ((strpos($mode, 'reported') !== false) ? 'reported = 1' : 'approved = 0');
		}
		$result = $db->sql_query($cnt_sql);
		$row = $db->sql_fetchrow($result);
		if ($row)
		{
			$count = $row['total'];
		}
		$db->sql_freeresult($result);
		unset($row, $cnt_sql);

		if ($ids === false)
		{
			$ids = array();
		}

		$blog_data->get_user_data(false, true);

		if ($blog)
		{
			$total_posts = ($count == 1) ? $user->lang['ONE_BLOG'] : sprintf($user->lang['CNT_BLOGS'], $count);

			foreach ($ids as $id)
			{
				$user_id = blog_data::$blog[$id]['user_id'];
				$template->assign_block_vars('postrow', array(
					'U_VIEW'		=> blog_url($user_id, $id),
					'SUBJECT'		=> blog_data::$blog[$id]['blog_subject'],
					'AUTHOR'		=> blog_data::$user[$user_id]['username_full'],
					'TIME'			=> $user->format_date(blog_data::$blog[$id]['blog_time']),
				));
			}
		}
		else
		{
			$total_posts = ($count == 1) ? $user->lang['ONE_REPLY'] : sprintf($user->lang['CNT_REPLIES'], $count);

			foreach ($ids as $id)
			{
				$user_id = blog_data::$reply[$id]['user_id'];
				$blog_id = blog_data::$reply[$id]['blog_id'];
				$template->assign_block_vars('postrow', array(
					'U_VIEW'		=> blog_url($user_id, $blog_id, $id),
					'SUBJECT'		=> blog_data::$reply[$id]['reply_subject'],
					'AUTHOR'		=> blog_data::$user[$user_id]['username_full'],
					'TIME'			=> $user->format_date(blog_data::$reply[$id]['reply_time']),
				));
			}
		}

		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
		$pagination = generate_pagination($this->u_action . "&amp;limit={$limit}&amp;st={$sort_days}&amp;sk={$sort_key}&amp;sd={$sort_dir}", $count, $limit, $start, false);

		$template->assign_vars(array(
			'PAGINATION'			=> $pagination,
			'PAGE_NUMBER' 			=> on_page($count, $limit, $start),
			'TOTAL_POSTS'			=> $total_posts,
			'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
			'S_SELECT_SORT_KEY' 	=> $s_sort_key,
			'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
		));

		blog_plugins::plugin_do('mcp_end');
	}
}

?>