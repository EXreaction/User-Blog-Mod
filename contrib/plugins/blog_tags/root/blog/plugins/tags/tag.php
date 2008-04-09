<?php
/**
*
* @package phpBB3 User Blog Tags
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$tag = request_var('tag', '', true);

if ($tag == '')
{
	trigger_error('NO_TAG');
}

$blog_ids = get_blogs_with_tag($tag);

if (!sizeof($blog_ids))
{
	trigger_error('NO_TAGS');
}

page_header($user->lang['BLOG_TAGS_TITLE'], false);

$template->assign_vars(array(
	'S_CATEGORY_MODE'		=> true,
));

$blog_data->get_blog_data('blog', $blog_ids);
$blog_data->get_user_data(false, true);
update_edit_delete('blog');

foreach ($blog_ids as $id)
{
	$blogrow = array_merge($blog_data->handle_user_data(blog_data::$blog[$id]['user_id']), $blog_data->handle_blog_data($id, $config['user_blog_user_text_limit']));
	$template->assign_block_vars('blogrow', $blogrow);
}

$template->set_filenames(array(
	'body'		=> 'blog/view_blog.html',
));
?>