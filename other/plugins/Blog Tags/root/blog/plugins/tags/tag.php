<?php
/**
*
* @package phpBB3 User Blog Tags
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$tag = request_var('tag', '', true);
$limit = 20;

if ($tag == '')
{
	trigger_error('NO_TAG');
}

$blog_ids = get_blogs_with_tag($tag);

if (!sizeof($blog_ids))
{
	trigger_error('NO_TAGS');
}

$user->add_lang('mods/blog/view');
page_header($user->lang['BLOG_TAGS_TITLE'], false);

$blog_data->get_blog_data('blog', $blog_ids);
$blog_data->get_user_data(false, true);
update_edit_delete('blog');

$i = -1;
foreach ($blog_ids as $id)
{
	$i++;
	if ($i < $start || !isset(blog_data::$blog[$id]['user_id']))
	{
		// It is before the start or they do not have permission to view
		continue;
	}
	else if ($i >= $start + $limit)
	{
		break;
	}

	$blogrow = array_merge($blog_data->handle_user_data(blog_data::$blog[$id]['user_id']), $blog_data->handle_blog_data($id, $config['user_blog_user_text_limit']));
	$template->assign_block_vars('searchrow', $blogrow);
}

$total = sizeof(blog_data::$blog);

$template->assign_vars(array(
	'SEARCH_TITLE'			=> $tag,
	'PAGINATION' 			=> generate_pagination(append_sid("{$phpbb_root_path}blog.$phpEx", 'page=tag&amp;tag=' . $tag), $total, $limit, $start),
	'PAGE_NUMBER' 			=> on_page($total, $limit, $start),
	'SEARCH_MATCHES'		=> ($total == 1) ? $user->lang['ONE_BLOG'] : sprintf($user->lang['CNT_BLOGS'], $total),
));

$template->set_filenames(array(
	'body'		=> 'blog/search_results.html',
));
?>