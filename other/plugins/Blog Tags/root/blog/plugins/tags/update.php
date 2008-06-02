<?php

switch (self::$plugins[$which]['plugin_version'])
{
	case '0.7.0' :
	case '0.7.1' :
	default :
		if (!defined('BLOGS_TAGS_TABLE'))
		{
			define('BLOGS_TAGS_TABLE', $table_prefix . 'blogs_tags');
		}
		if (!function_exists('get_tags_from_text'))
		{
			include($blog_plugins_path . 'tags/functions.' . $phpEx);
		}

		$db->sql_query('DELETE FROM ' . BLOGS_TAGS_TABLE);

		$all_tags = get_blog_tags();
		$sql = 'SELECT blog_id, blog_tags FROM ' . BLOGS_TABLE;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$tag_ary = get_tags_from_text($row['blog_tags']);

			if (!sizeof($tag_ary))
			{
				continue;
			}

			$db->sql_query('UPDATE ' . BLOGS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array('blog_tags' => '[tag_delim]' . implode('[tag_delim]', $tag_ary) . '[tag_delim]')) . ' WHERE blog_id = ' . $row['blog_id']);

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
					$all_tags[$tag] = true;
				}
			}
		}

		$cache->destroy('_blog_tags');
	break;
}
?>