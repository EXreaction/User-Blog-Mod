<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: search.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['SEARCH_BLOGS'], blog_url(false, false, false, array('page' => 'search')));
page_header($user->lang['SEARCH_BLOGS']);

$user->add_lang(array('search', 'mods/blog/view'));

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

blog_plugins::plugin_do('search');

if (($keywords && $keywords != $user->lang['SEARCH_MINI']) || $author)
{
	$blog_search = setup_blog_search();

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

	if ($author && $keywords)
	{
		$uid = $blog_data->get_id_by_username($author);
		$blog_search->split_keywords($keywords, $terms);
		$ids = $blog_search->keyword_search($sf, $terms, 0);
	}
	else if ($author)
	{
		$uid = $blog_data->get_id_by_username($author);
		$ids = $blog_search->author_search($uid);
	}
	else
	{
		$blog_search->split_keywords($keywords, $terms);
		$ids = $blog_search->keyword_search($sf, $terms, 0);
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

		$temp = array();
		if (sizeof($blog_ids))
		{
			$sql = 'SELECT blog_id, blog_time FROM ' . BLOGS_TABLE . '
				WHERE ' . $db->sql_in_set('blog_id', $blog_ids);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($temp[$row['blog_time']]))
				{
					$temp[$row['blog_time']] = array();
				}
				$temp[$row['blog_time']][$row['blog_id']] = 'b';
			}
		}

		if (sizeof($reply_ids))
		{
			$sql = 'SELECT reply_id, reply_time FROM ' . BLOGS_REPLY_TABLE . '
				WHERE ' . $db->sql_in_set('reply_id', $reply_ids);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($temp[$row['reply_time']]))
				{
					$temp[$row['reply_time']] = array();
				}
				$temp[$row['reply_time']][$row['reply_id']] = 'r';
			}
		}
		krsort($temp);

		$i = 0;
		$matches = (sizeof($ids));
		$blog_ids = $reply_ids = $ids = array();
		foreach ($temp as $time => $data)
		{
			if ($i < $start)
			{
				$i++;
				continue;
			}
			if ($i >= ($start + $limit))
			{
				break;
			}

			foreach ($data as $id => $type)
			{
				if ($type == 'b')
				{
					$blog_ids[] = $id;
					$ids[] = array('blog_id' => $id);
				}
				else
				{
					$reply_ids[] = $id;
					$ids[] = array('reply_id' => $id);
				}
				$i++;
			}
		}

		if (sizeof($blog_ids))
		{
			$blog_data->get_blog_data('blog', $blog_ids);
		}
		if (sizeof($reply_ids))
		{
			$blog_data->get_reply_data('reply', $reply_ids);
		}
		$blog_data->get_user_data(false, true);
		update_edit_delete();

		foreach ($ids as $id)
		{
			if (isset($id['reply_id']))
			{
				if (!$author || ($uid == blog_data::$reply[$id['reply_id']]['user_id']))
				{
					$template->assign_block_vars('searchrow', $blog_data->handle_reply_data($id['reply_id']) + $blog_data->handle_user_data(blog_data::$reply[$id['reply_id']]['user_id']));
				}
				else
				{
					// they are selecting from a specific author and this is from a different author
					$matches--;
				}
			}
			else
			{
				if (isset(blog_data::$blog[$id['blog_id']]) && (!$author || ($uid == blog_data::$blog[$id['blog_id']]['user_id'])))
				{
					$template->assign_block_vars('searchrow', $blog_data->handle_blog_data($id['blog_id']) + $blog_data->handle_user_data(blog_data::$blog[$id['blog_id']]['user_id']));
				}
				else
				{
					// they don't have permission to view this blog, or they are selecting from a specific author and this is from a different author
					$matches--;
				}
			}
		}
	}
	else
	{
		$matches = 0;
	}

	$pagination = generate_blog_pagination(blog_url(false, false, false, array('page' => 'search', 'author' => $author, 'keywords' => $keywords, 'terms' => $terms, 'sf' => $sf, 'start' => '*start*', 'limit' => $limit), array(), true), $matches, $limit, $start, false);

	$template->assign_vars(array(
		'PAGINATION'		=> $pagination,
		'PAGE_NUMBER' 		=> on_page($matches, $limit, $start),
		'TOTAL_POSTS'		=> ($matches == 1) ? $user->lang['ONE_REPLY'] : sprintf($user->lang['CNT_REPLIES'], $matches),
		'SEARCH_MATCHES'	=> ($matches == 1) ? sprintf($user->lang['FOUND_SEARCH_MATCH'], $matches) : sprintf($user->lang['FOUND_SEARCH_MATCHES'], $matches),
		'U_SEARCH_WORDS'	=> $search_url,
		'SEARCH_WORDS'		=> $author . ' &bull; ' . $keywords,
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

blog_plugins::plugin_do('search_end');
?>