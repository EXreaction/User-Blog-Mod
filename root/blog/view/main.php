<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

// Add the language Variables for viewtopic
$user->add_lang('viewtopic');

generate_blog_breadcrumbs();
generate_menu();

if ($category_id)
{
	$category_list = get_blog_categories('category_id');

	if (isset($category_list[$category_id]))
	{
		page_header($category_list[$category_id]['category_name']);
	}
	else
	{
		trigger_error('NO_CATEGORY');
	}
	unset($category_list);
}
else
{
	page_header($user->lang['USER_BLOGS']);
}

blog_plugins::plugin_do('view_main_start');

// Handle the categories and output them
handle_categories($category_id);

switch ($mode)
{
	// If you send one of the following options via mode you will be able to just view a list of them.
	case 'random' :
	case 'recent' :
	case 'popular' :
		// for sorting and pagination
		$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		$total_blogs = $blog_data->get_blog_info('count', 0, array('sort_days' => $sort_days, 'category_id' => $category_id));
		$sort_by_text = array('t' => $user->lang['POST_TIME'], 'c' => $user->lang['REPLY_COUNT'], 'bt' => $user->lang['BLOG_SUBJECT']);
		$sort_by_sql = array('t' => 'blog_time', 'c' => 'blog_reply_count', 'bt' => 'blog_subject');
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
		$pagination = generate_blog_pagination($blog_urls['start_zero'], $total_blogs, $limit, $start, false);

		$blog_ids = $blog_data->get_blog_data($mode, 0, array('start' => $start, 'limit' => $limit, 'category_id' => $category_id, 'order_by' => $sort_by_sql[$sort_key], 'order_dir' => $order_dir, 'sort_days' => $sort_days));
		$blog_data->get_user_data(false, true);
		update_edit_delete('blog');

		if ($feed === false)
		{
			// Output some data
			$template->assign_vars(array(
				'PAGINATION'			=> $pagination,
				'PAGE_NUMBER' 			=> on_page($total_blogs, $limit, $start),
				'TOTAL_POSTS'			=> ($total_blogs == 1) ? $user->lang['ONE_BLOG'] : sprintf($user->lang['CNT_BLOGS'], $total_blogs),

				'S_SORT'				=> ($mode == 'random') ? false : true,
				'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
				'S_SELECT_SORT_KEY' 	=> $s_sort_key,
				'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
			));
			unset($pagination);

			// parse and output the blogs
			$template->assign_block_vars('column', array(
				'SECTION_WIDTH'		=> '100',
				'U_FEED'			=> ($config['user_blog_enable_feeds']) ? blog_url(false, false, false, array('mode' => $mode, 'feed' => 'explain')) : '',
				'U_VIEW'			=> blog_url(false, false, false, array('mode' => $mode)),
				'TITLE'				=> $user->lang[strtoupper($mode) . '_BLOGS'],
			));
			if ($blog_ids !== false)
			{
				foreach($blog_ids as $id)
				{
					$template->assign_block_vars('column.row', array_merge($blog_data->handle_user_data(blog_data::$blog[$id]['user_id']), $blog_data->handle_blog_data($id, $config['user_blog_user_text_limit'])));
				}
			}

			$template->set_filenames(array(
				'body'		=> 'blog/view_blog_main.html',
			));
		}
		else
		{
			feed_output($blog_ids, $feed);
		}
	break;

	// This is the default page
	default :
		// Get the random blog(s) and the recent blogs
		$random_blog_ids = $blog_data->get_blog_data('random', 0, array('limit' => 1, 'category_id' => $category_id));
		$recent_blog_ids = $blog_data->get_blog_data('recent', 0, array('limit' => $limit, 'category_id' => $category_id));

		$blog_data->get_user_data(false, true);
		update_edit_delete('blog');

		// Output the random blog(s)
		if ($random_blog_ids !== false)
		{
			$template->assign_vars(array(
				'S_RANDOM_BLOG'		=> true,
			));

			// I've decided to use a foreach to display the random blogs so it is easier to change the limit if the board owner would like...
			foreach ($random_blog_ids as $id)
			{
				$template->assign_block_vars('random', array_merge($blog_data->handle_user_data(blog_data::$blog[$id]['user_id']), $blog_data->handle_blog_data($id, $config['user_blog_user_text_limit'])));
			}
		}

		// Output the recent blogs
		$template->assign_block_vars('column', array(
			'SECTION_WIDTH'		=> '50',
			'U_FEED'			=> ($config['user_blog_enable_feeds']) ? blog_url(false, false, false, array('mode' => 'recent', 'feed' => 'explain')) : '',
			'U_VIEW'			=> blog_url(false, false, false, array('mode' => 'recent')),
			'TITLE'				=> $user->lang['RECENT_BLOGS'],
		));
		foreach ($recent_blog_ids as $id)
		{
			$template->assign_block_vars('column.row', array_merge($blog_data->handle_user_data(blog_data::$blog[$id]['user_id']), $blog_data->handle_blog_data($id, $config['user_blog_text_limit'])));
		}


		$template->set_filenames(array(
			'body'		=> 'blog/view_blog_main.html',
		));
}

blog_plugins::plugin_do('view_main_end');

/*
$text_limit = ($all) ? $config['user_blog_text_limit'] : $config['user_blog_user_text_limit'];

if ($feed == false || $all)
{
	// Output the random blogs
	if ($random || $all)
	{
		$template->assign_block_vars('column', array(
			'SECTION_WIDTH'		=> ($all) ? '33' : '100',
			'U_FEED'			=> ($config['user_blog_enable_feeds']) ? blog_url(false, false, false, array('page' => 'view', 'mode' => 'random', 'feed' => 'explain')) : '',
			'U_VIEW'			=> blog_url(false, false, false, array('page' => 'view', 'mode' => 'random')),
			'TITLE'				=> $user->lang['RANDOM_BLOGS'],
		));

		if ($random_blog_ids !== false)
		{
			foreach ($random_blog_ids as $id)
			{
				// handle user and blog data
				$user_row = $blog_data->handle_user_data(blog_data::$blog[$id]['user_id']);
				$blog_row = $blog_data->handle_blog_data($id, $text_limit);
			
				$template->assign_block_vars('column.row', $user_row + $blog_row);
			}
			unset($user_row, $blog_row);
		}
	}

	// Output the recent blogs
	if ($recent || $all)
	{
		$template->assign_block_vars('column', array(
			'SECTION_WIDTH'		=> ($all) ? '33' : '100',
			'U_FEED'			=> ($config['user_blog_enable_feeds']) ? blog_url(false, false, false, array('page' => 'view', 'mode' => 'recent', 'feed' => 'explain')) : '',
			'U_VIEW'			=> blog_url(false, false, false, array('page' => 'view', 'mode' => 'recent')),
			'TITLE'				=> $user->lang['RECENT_BLOGS'],
		));

		if ($recent_blog_ids !== false)
		{
			foreach ($recent_blog_ids as $id)
			{
				// handle user and blog data
				$user_row = $blog_data->handle_user_data(blog_data::$blog[$id]['user_id']);
				$blog_row = $blog_data->handle_blog_data($id, $text_limit);

				$template->assign_block_vars('column.row', $user_row + $blog_row);
			}
			unset($user_row, $blog_row);
		}
	}

	// Output the popular blogs
	if ($popular || $all)
	{
		$template->assign_block_vars('column', array(
			'SECTION_WIDTH'		=> ($all) ? '33' : '100',
			'U_FEED'			=> ($config['user_blog_enable_feeds']) ? blog_url(false, false, false, array('page' => 'view', 'mode' => 'popular', 'feed' => 'explain')) : '',
			'U_VIEW'			=> blog_url(false, false, false, array('page' => 'view', 'mode' => 'popular')),
			'TITLE'				=> $user->lang['POPULAR_BLOGS'],
		));

		if ($popular_blog_ids !== false)
		{
			foreach ($popular_blog_ids as $id)
			{
				// handle user and blog data
				$user_row = $blog_data->handle_user_data(blog_data::$blog[$id]['user_id']);
				$blog_row = $blog_data->handle_blog_data($id, $text_limit);

				$template->assign_block_vars('column.row', $user_row + $blog_row);
			}
			unset($user_row, $blog_row);
		}
	}

	blog_plugins::plugin_do('view_main_end');

	$template->set_filenames(array(
		'body'		=> 'blog/view_blog_main.html',
	));
}
else
{
	if ($random)
	{
		feed_output($random_blog_ids, $feed);
	}
	else if ($recent)
	{
		feed_output($recent_blog_ids, $feed);
	}
	else if ($popular)
	{
		feed_output($popular_blog_ids, $feed);
	}

	blog_plugins::plugin_do('view_main_feed_end');
}
*/
?>