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

// Add the language Variables for viewtopic
$user->add_lang('viewtopic');

// page variables
$random = ($mode == 'random') ? true : false;
$recent = ($mode == 'recent') ? true : false;
$popular = ($mode == 'popular') ? true : false;
$all = (!$random && !$recent && !$popular) ? true : false;

generate_blog_breadcrumbs();

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

// Handle the categories and output them
handle_categories($category_id);

// Random Blogs
$random_blog_ids = ($random || $all) ? $blog_data->get_blog_data('random', 0, array('limit' => $limit, 'category_id' => $category_id)) : false;

// Recent Blogs
$recent_blog_ids = ($recent || $all) ? $blog_data->get_blog_data('recent', 0, array('limit' => $limit, 'category_id' => $category_id)) : false;

// Popular blogs
$popular_blog_ids =($popular || $all) ? $blog_data->get_blog_data('popular', 0, array('limit' => $limit, 'category_id' => $category_id)) : false;

blog_plugins::plugin_do('view_main_start');

$blog_data->get_user_data(false, true);
update_edit_delete('blog');

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
?>