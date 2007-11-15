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
generate_blog_breadcrumbs($user->lang['SEARCH_BLOGS'], blog_url(false, false, false, array('page' => 'search')));
page_header($user->lang['SEARCH_BLOGS']);

$user->add_lang('search');

// Is user able to search? Has search been disabled?
if (!$auth->acl_get('u_search') || !$auth->acl_getf_global('f_search') || !$config['load_search'])
{
	$template->assign_var('S_NO_SEARCH', true);
	trigger_error('NO_SEARCH');
}

// Check search load limit
if ($user->load && $config['limit_search_load'] && ($user->load > doubleval($config['limit_search_load'])))
{
	$template->assign_var('S_NO_SEARCH', true);
	trigger_error('NO_SEARCH_TIME');
}

$keywords = request_var('keywords', '', true);
$author = request_var('author', '', true);
$terms = request_var('terms', 'all');
$sf = request_var('sf', '');

$search_url = blog_url(false, false, false, array('page' => 'search', 'author' => $author, 'keywords' => $keywords, 'terms' => $terms, 'sf' => $sf), array(), true);

if ($keywords || $author)
{
	include($phpbb_root_path . 'blog/search/fulltext_native.' . $phpEx);
	$blog_search = new blog_fulltext_native();

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
	$highlight = urlencode($highlight_words);

	if ($author)
	{
		$uid = $user_data->get_id_by_username($author);
		$ids = $blog_search->author_search($uid);
	}
	else
	{
		$blog_search->split_keywords($keywords, $terms, false, $start, $limit);
		$ids = $blog_search->keyword_search($sf, $terms, 0, $start, $limit);
	}

	if ($ids !== false)
	{
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

		if (count($blog_ids))
		{
			$blog_data->get_blog_data('blog', $blog_ids);
		}
		if (count($reply_ids))
		{
			$reply_data->get_reply_data('reply', $reply_ids);
		}
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
				if (isset($blog_data->blog[$id['blog_id']]))
				{
					$template->assign_block_vars('searchrow', $blog_data->handle_blog_data($id['blog_id']) + $user_data->handle_user_data($blog_data->blog[$id['blog_id']]['user_id']));
				}
				else
				{
					// they don't have permission to view this blog...
					$matches--;
				}
			}
			else 
			{
				$template->assign_block_vars('searchrow', $reply_data->handle_reply_data($id['reply_id']) + $user_data->handle_user_data($reply_data->reply[$id['reply_id']]['user_id']));
			}

			$i++;
		}
		$matches = (count($blog_ids) + count($reply_ids));
	}
	else
	{
		$matches = 0;
	}

	$pagination = generate_blog_pagination(blog_url(false, false, false, array('page' => 'search', 'author' => $author, 'keywords' => $keywords, 'terms' => $terms, 'sf' => $sf, 'start' => '*start*'), array(), true), $matches, $limit, $start, false);

	$template->assign_vars(array(
		'PAGINATION'		=> $pagination,
		'PAGE_NUMBER' 		=> on_page($matches, $limit, $start),
		'TOTAL_POSTS'		=> ($matches == 1) ? $user->lang['REPLY_COUNT'] : sprintf($user->lang['REPLIES_COUNT'], $matches),
		'SEARCH_MATCHES'	=> ($matches == 1) ? sprintf($user->lang['FOUND_SEARCH_MATCH'], $matches) : sprintf($user->lang['FOUND_SEARCH_MATCHES'], $matches),
		'U_SEARCH_WORDS'	=> $search_url,
		'SEARCH_WORDS'		=> (($author) ? $author : $keywords),
	));

	$template->set_filenames(array(
		'body' => 'blog/search_results.html'
	));
}
else
{
	$template->assign_vars(array(
		'U_BLOG_SEARCH'	=> blog_url(false, false, false, array('page' => 'search'), array(), true),
	));

	$template->set_filenames(array(
		'body' => 'blog/search_body.html'
	));
}
?>