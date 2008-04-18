<?php
/**
*
* @package phpBB3 User Blog Tags
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Get all blog ids with the containing tag in it and return them.
*/
function get_blogs_with_tag($tag)
{
	global $db;

	$blog_ids = array();
	$sql = 'SELECT blog_id FROM ' . BLOGS_TABLE . '
		WHERE blog_tags LIKE \'%[tag_delim]' . $db->sql_escape($tag) . '[tag_delim]%\'';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$blog_ids[] = $row['blog_id'];
	}
	$db->sql_freeresult($result);

	return $blog_ids;
}

/**
* Get all used blog tags and return an array filled with them.
*/
function get_blog_tags()
{
	global $cache, $db;

	$all_tags = $cache->get('_blog_tags');

	if ($all_tags === false)
	{
		$all_tags = array();

		$sql = 'SELECT * FROM ' . BLOGS_TAGS_TABLE;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$all_tags[$row['tag_name']] = $row;
		}
		$db->sql_freeresult($result);

		$cache->put('_blog_tags', $all_tags);
	}

	return $all_tags;
}

/**
* Gets an array filled with tags from both the tags in the database under the blog_tags field and from raw post input.
*/
function get_tags_from_text($text)
{
	$tag_ary = array();
	foreach (explode("\n", trim(str_replace('[tag_delim]', "\n", $text))) as $tag)
	{
		$tag = trim($tag);
		if ($tag != '')
		{
			$tag_ary[] = $tag;
		}
	}

	return array_unique($tag_ary);
}

/*
* Plugin Functions -------------------------------------
*/

function tags_function_generate_menu(&$args)
{
	global $db, $phpbb_root_path, $phpEx, $template;

	$tag_list = array();
	$total = 0;

	$sql = 'SELECT blog_tags FROM ' . BLOGS_TABLE .
		(($args['user_id']) ? ' WHERE user_id = ' . intval($args['user_id']) . build_permission_sql(intval($args['user_id'])) : '');

	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		foreach (get_tags_from_text($row['blog_tags']) as $tag)
		{
			if (!isset($tag_list[$tag]))
			{
				$tag_list[$tag] = 1;
			}
			else
			{
				$tag_list[$tag]++;
			}
			$total++;
		}
	}
	$db->sql_freeresult($result);
	ksort($tag_list);

	$med = (int) $total / sizeof($tag_list);
	foreach ($tag_list as $tag => $cnt)
	{
		$template->assign_block_vars('tags', array(
			'TAG'		=> $tag,
			'CNT'		=> $cnt,
			'SIZE'		=> min(26, 10 + (4 * $cnt / $med)),

			'U_VIEW'	=> append_sid("{$phpbb_root_path}blog.$phpEx", 'page=tag&amp;tag=' . $tag),
		));
	}

	$tag_output = blog_plugins::parse_template('blog/plugins/tags/tags_menu.html');
	$args['user_menu_extra'] .= $tag_output;
	$args['extra'] .= $tag_output;
}

function tags_blog_page_switch(&$args)
{
	if ($args['page'] == 'tag')
	{
		$args['inc_file'] = 'plugins/tags/tag';
		$args['default'] = false;
	}
}

function tags_blog_handle_data_end(&$args)
{
	global $user, $phpbb_root_path, $phpEx;

	$tags = array();
	foreach (get_tags_from_text(blog_data::$blog[$args['ID']]['blog_tags']) as $tag)
	{
		$tags[] = '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", 'page=tag&amp;tag=' . $tag) . '">' . $tag . '</a>';
	}

	$args['EXTRA'] .= '<br /><strong>' . $user->lang['TAGS'] . '</strong>: ' . implode(' &#8226; ', $tags);
}

function tags_function_handle_basic_posting_data(&$args)
{
	global $blog_id, $template, $user;

	if ($args['page'] != 'blog')
	{
		return;
	}

	$args['panels']['tags-panel'] = $user->lang['TAGS'];

	$tags = '';
	if ($args['mode'] == 'edit' && isset(blog_data::$blog[$blog_id]['blog_tags']))
	{
		$tags = implode("\n", get_tags_from_text(blog_data::$blog[$blog_id]['blog_tags']));
	}

	$template->assign_vars(array(
		'TAGS'		=> request_var('tags', $tags, true),
	));

	$args['panel_data'] = blog_plugins::parse_template('blog/plugins/tags/tags_panel.html');
}

function tags_blog_add_sql(&$args, $tags = false)
{
	global $cache, $db;

	if ($tags === false)
	{
		$tags = request_var('tags', '', true);
	}

	$tag_ary = get_tags_from_text($tags);

	if (!sizeof($tag_ary))
	{
		$args['blog_tags'] = '';
		return;
	}

	$args['blog_tags'] = '[tag_delim]' . implode('[tag_delim]', $tag_ary) . '[tag_delim]';

	$all_tags = get_blog_tags();

	foreach ($tag_ary as $tag)
	{
		if (isset($all_tags[$tag]))
		{
			$db->sql_query('UPDATE ' . BLOGS_TAGS_TABLE . ' SET tag_count = tag_count + 1 WHERE tag_id = ' . $all_tags[$tag]['tag_id']);
		}
		else
		{
			$sql_ary = array(
				'tag_name'		=> $tag,
				'tag_count'		=> 1,
			);
			$db->sql_query('INSERT INTO ' . BLOGS_TAGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
		}
	}

	$cache->destroy('_blog_tags');
}

function tags_blog_edit_sql(&$args)
{
	global $blog_id, $cache, $db;

	// If there were not tags before we edited we simply need to add the new tags
	if (!isset(blog_data::$blog[$blog_id]['blog_tags']) || blog_data::$blog[$blog_id]['blog_tags'] == '')
	{
		tags_blog_add_sql($args);
		return;
	}

	$all_tags = get_blog_tags();
	$old_tags = get_tags_from_text(blog_data::$blog[$blog_id]['blog_tags']);
	$tags = request_var('tags', '', true);
	$tag_ary = get_tags_from_text($tags);
	$new_tags = '';

	foreach ($old_tags as $tag)
	{
		if (!in_array($tag, $tag_ary))
		{
			if ($all_tags[$tag]['tag_count'] == 1)
			{
				$db->sql_query('DELETE FROM ' . BLOGS_TAGS_TABLE . ' WHERE tag_id = ' . $all_tags[$tag]['tag_id']);
			}
			else
			{
				$db->sql_query('UPDATE ' . BLOGS_TAGS_TABLE . ' SET tag_count = tag_count - 1 WHERE tag_id = ' . $all_tags[$tag]['tag_id']);
			}
		}
	}

	foreach ($tag_ary as $tag)
	{
		if (!in_array($tag, $old_tags))
		{
			$new_tags .= $tag . "\n";
		}
	}

	$cache->destroy('_blog_tags');
	tags_blog_add_sql($args, $new_tags);
	$args['blog_tags'] = '[tag_delim]' . implode('[tag_delim]', $tag_ary) . '[tag_delim]';
}
?>