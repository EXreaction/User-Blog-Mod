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

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['SEARCH_BLOGS']);
page_header($user->lang['SEARCH_BLOGS']);

$user->add_lang('search');

$keywords = request_var('keywords', '', true);
$author = request_var('author', '', true);

if ($keywords || $author)
{
	include($phpbb_root_path . 'blog/search/fulltext_native.' . $phpEx);
	$blog_search = new blog_fulltext_native();

	$terms = request_var('terms', 'all');
	$sf = request_var('sf', '');

	$blog_ids = $reply_ids = array();

	$highlight_match = $highlight = '';
	$matches = array('(', ')', '|', '+', '-');
	$highlight_words = str_replace($matches, ' ', $keywords);
	foreach (explode(' ', trim($highlight_words)) as $word)
	{
		if (trim($word))
		{
			$highlight_match .= (($highlight_match != '') ? '|' : '') . str_replace('*', '\w*?', preg_quote($word, '#'));
		}
	}
	$highlight = urlencode($hilit_words);

	if ($author)
	{
		$uid = $user_data->get_id_by_username($author);
		$ids = $blog_search->author_search($uid);
	}
	else
	{
		$blog_search->split_keywords($keywords, $terms);
		$ids = $blog_search->keyword_search();
	}

	foreach ($ids as $id)
	{
		if ($id['reply_id'] == 0)
		{
			$blog_ids[] = $id['blog_id'];
		}
		else
		{
			$reply_ids[] = $id['reply_id'];
		}
	}

	$blog_data->get_blog_data('blog', $blog_ids, array('limit' => 0));
	$reply_data->get_reply_data('reply', $reply_ids, array('limit' => 0));
	$user_data->get_user_data(false, true);
	update_edit_delete();

	$i = 0;
	foreach ($ids as $id)
	{
		if ($i < $start)
		{
			$i++;
			continue;
		}
		else if ($i >= ($start + $limit))
		{
			break;
		}

		if ($id['reply_id'] == 0)
		{
			$template->assign_block_vars('searchrow', $blog_data->handle_blog_data($id['blog_id']) + $user_data->handle_user_data($blog_data->blog[$id['blog_id']]['user_id']));
		}
		else 
		{
			$template->assign_block_vars('searchrow', $reply_data->handle_reply_data($id['reply_id']) + $user_data->handle_user_data($reply_data->reply[$id['reply_id']]['user_id']));
		}

		$i++;
	}

	$matches = (count($blog_ids) + count($reply_ids));
	$pagination = generate_blog_pagination(blog_url(false, false, false, array('page' => 'search', 'author' => $author, 'keywords' => $keywords, 'terms' => $terms, 'sf' => $sf, 'start' => '*start*'), array(), true), $matches, $limit, $start, false);

	$template->assign_vars(array(
		'PAGINATION'		=> $pagination,
		'PAGE_NUMBER' 		=> on_page($matches, $limit, $start),
		'TOTAL_POSTS'		=> ($matches == 1) ? $user->lang['REPLY_COUNT'] : sprintf($user->lang['REPLIES_COUNT'], $matches),
		'SEARCH_MATCHES'	=> ($matches == 1) ? sprintf($user->lang['FOUND_SEARCH_MATCH'], $matches) : sprintf($user->lang['FOUND_SEARCH_MATCHES'], $matches),
		'U_SEARCH_WORDS'	=> blog_url(false, false, false, array('page' => 'search', 'author' => $author, 'keywords' => $keywords), array(), true),
		'SEARCH_WORDS'		=> (($author) ? $author : $keywords),
	));

	$template->set_filenames(array(
		'body' => 'blog/search_results.html'
	));
}
else
{
	$template->assign_vars(array(
		'U_BLOG_SEARCH'	=> blog_url(false, false, false, array('page' => 'search')),
	));

	$template->set_filenames(array(
		'body' => 'blog/search_body.html'
	));
}
?>