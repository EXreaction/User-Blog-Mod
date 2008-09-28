<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: functions_categories.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Handles putting blogs into categories
*/
function put_blogs_in_cats($blog_id, $category_ids, $no_approve = true, $mode = false)
{
	global $cache, $db;
	$cache->destroy('_blog_categories');

	if (!is_array($category_ids))
	{
		$category_ids = array($category_ids);
	}

	if ($mode != 'approve' && $mode != 'undelete')
	{
		// Update all of the cats and parents and add -1 to the blog count for each.
		$to_query = $parent_list = array();
		$sql = 'SELECT category_id FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . intval($blog_id);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$to_query[] = $row['category_id'];
		}
		$db->sql_freeresult($result);

		while (sizeof($to_query))
		{
			$sql = 'SELECT category_id, parent_id FROM ' . BLOGS_CATEGORIES_TABLE . '
				WHERE ' . $db->sql_in_set('category_id', $to_query);
			$result = $db->sql_query($sql);
			$to_query = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$parent_list[] = $row['category_id'];
				if ($row['parent_id'] && !in_array($row['parent_id'], $to_query))
				{
					$to_query[] = $row['parent_id'];
				}
			}
			$db->sql_freeresult($result);
		}

		if (sizeof($parent_list))
		{
			$db->sql_query('UPDATE ' . BLOGS_CATEGORIES_TABLE . ' SET blog_count = blog_count - 1 WHERE ' . $db->sql_in_set('category_id', array_unique($parent_list)));

			if ($mode != 'soft_delete')
			{
				// Delete the blogs in cats
				$db->sql_query('DELETE FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . intval($blog_id));
			}
		}
	}

	if (sizeof($category_ids) > 1 || (isset($category_ids[0]) && $category_ids[0] != 0))
	{
		if ($mode != 'approve' && $mode != 'undelete')
		{
			// Insert into the blogs_in_categories table
			$sql_ary = array();
			foreach ($category_ids as $category_id)
			{
				$sql_ary[] = array('blog_id' => intval($blog_id), 'category_id' => intval($category_id));
			}
			$db->sql_multi_insert(BLOGS_IN_CATEGORIES_TABLE, $sql_ary);
		}

		if ($no_approve)
		{
			// Update all of the cats parents and add +1 to the blog count for each.
			$to_query = $category_ids;
			$parent_list = array();
			while(sizeof($to_query))
			{
				$sql = 'SELECT category_id, parent_id FROM ' . BLOGS_CATEGORIES_TABLE . '
					WHERE ' . $db->sql_in_set('category_id', $to_query);
				$result = $db->sql_query($sql);
				$to_query = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$parent_list[] = $row['category_id'];
					if ($row['parent_id'] && !in_array($row['parent_id'], $to_query))
					{
						$to_query[] = $row['parent_id'];
					}
				}
				$db->sql_freeresult($result);
			}
			$db->sql_query('UPDATE ' . BLOGS_CATEGORIES_TABLE . ' SET blog_count = blog_count + 1 WHERE ' . $db->sql_in_set('category_id', $parent_list));
		}
	}
}

/**
* Handle the categories
*
* @param int $parent_id If this is set to something other than 0 it will only list categories under the category_id given
* @param string $block Set the name of the block to output it to.
* @param bool $ignore_subcats True to ignore subcategories, false to display them.
* @param array $category_list If you want to send the already gotten category list...
*/
function handle_categories($parent_id = 0, $block = 'category_row', $ignore_subcats = false, $category_list = false)
{
	global $config, $template, $user;

	if (!is_array($category_list))
	{
		$category_list = get_blog_categories('left_id');
	}

	blog_plugins::plugin_do('function_handle_categories');

	foreach ($category_list as $left_id => $row)
	{
		if ($parent_id == $row['category_id'] && !$ignore_subcats)
		{
			$template->assign_vars(array(
				'U_CURRENT_CATEGORY'	=> blog_url(false, false, false, array('page' => (($config['user_blog_seo']) ? $row['category_name'] : '*skip*'), 'c' => $row['category_id'])),
				'CURRENT_CATEGORY'		=> $row['category_name'],
				'CATEGORY_RULES'		=> generate_text_for_display($row['rules'], $row['rules_uid'], $row['rules_bitfield'], $row['rules_options']),
			));
		}

		if ($parent_id == $row['parent_id'])
		{
			$template->assign_block_vars($block, array(
				'CATEGORY_NAME'			=> $row['category_name'],
				'CATEGORY_DESCRIPTION'	=> generate_text_for_display($row['category_description'], $row['category_description_uid'], $row['category_description_bitfield'], $row['category_description_options']),
				'BLOGS'					=> $row['blog_count'],

				'U_CATEGORY'			=> blog_url(false, false, false, array('page' => (($config['user_blog_seo']) ? $row['category_name'] : '*skip*'), 'c' => $row['category_id'])),

				'S_SUBCATEGORY'			=> ($row['right_id'] > ($row['left_id'] + 1) && !$ignore_subcats),

				'L_SUBCATEGORY'			=> ($row['right_id'] > ($row['left_id'] + 3)) ? $user->lang['SUBCATEGORIES'] : $user->lang['SUBCATEGORY'],
			));

			// If not, then there are subcategories
			if ($row['right_id'] > ($row['left_id'] + 1) && !$ignore_subcats)
			{
				handle_categories($row['category_id'], 'category_row.subcategory', true, $category_list);
			}
		}
	}
}

/**
* Get all blog categories
*/
function get_blog_categories($order = 'left_id')
{
	global $cache;

	$blog_categories = $cache->get('_blog_categories');

	if ($blog_categories === false)
	{
		global $db;
		$blog_categories = array();
		$sql = 'SELECT * FROM ' . BLOGS_CATEGORIES_TABLE . "
			ORDER BY left_id ASC";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$blog_categories[$row['left_id']] = $row;
		}
		$db->sql_freeresult($result);

		$cache->put('_blog_categories', $blog_categories);
	}

	if ($order != 'left_id')
	{
		$blog_cats = $blog_categories;
		$blog_categories = array();
		foreach ($blog_cats as $left_id => $row)
		{
			$blog_categories[$row[$order]] = $row;
		}
	}

	blog_plugins::plugin_do_ref('function_get_blog_categories', $blog_categories);

	return $blog_categories;
}

/**
* Simple version of jumpbox, just lists categories
* Copied from make_forum_select
*/
function make_category_select($select_id = false, $ignore_id = false, $return_array = false)
{
	global $db;

	// This query is identical to the jumpbox one
	$sql = 'SELECT category_id, category_name, parent_id, left_id, right_id
		FROM ' . BLOGS_CATEGORIES_TABLE . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql);

	$right = 0;
	$padding_store = array('0' => '');
	$padding = '';
	$category_list = ($return_array) ? array() : '';

	// Sometimes it could happen that forums will be displayed here not be displayed within the index page
	// This is the result of forums not displayed at index, having list permissions and a parent of a forum with no permissions.
	// If this happens, the padding could be "broken"

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['left_id'] < $right)
		{
			$padding .= '&nbsp; &nbsp;';
			$padding_store[$row['parent_id']] = $padding;
		}
		else if ($row['left_id'] > $right + 1)
		{
			$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
		}

		$right = $row['right_id'];
		$disabled = false;

		if ((is_array($ignore_id) && in_array($row['category_id'], $ignore_id)) || $row['category_id'] == $ignore_id)
		{
			$disabled = true;
		}

		if ($return_array)
		{
			// Include some more information...
			$selected = (is_array($select_id)) ? ((in_array($row['category_id'], $select_id)) ? true : false) : (($row['category_id'] == $select_id) ? true : false);
			$category_list[$row['category_id']] = array_merge(array('padding' => $padding, 'selected' => ($selected && !$disabled), 'disabled' => $disabled), $row);
		}
		else
		{
			$selected = (is_array($select_id)) ? ((in_array($row['category_id'], $select_id)) ? ' selected="selected"' : '') : (($row['category_id'] == $select_id) ? ' selected="selected"' : '');
			$category_list .= '<option value="' . $row['category_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $row['category_name'] . '</option>';
		}
	}
	$db->sql_freeresult($result);
	unset($padding_store);

	return $category_list;
}

/**
* Get category branch
* From get_forum_branch
*/
function get_category_branch($category_id, $type = 'all', $order = 'descending', $include_forum = true)
{
	global $db;

	switch ($type)
	{
		case 'parents':
			$condition = 'f1.left_id BETWEEN f2.left_id AND f2.right_id';
		break;

		case 'children':
			$condition = 'f2.left_id BETWEEN f1.left_id AND f1.right_id';
		break;

		default:
			$condition = 'f2.left_id BETWEEN f1.left_id AND f1.right_id OR f1.left_id BETWEEN f2.left_id AND f2.right_id';
		break;
	}

	$rows = array();

	$sql = 'SELECT f2.*
		FROM ' . BLOGS_CATEGORIES_TABLE . ' f1
		LEFT JOIN ' . BLOGS_CATEGORIES_TABLE . " f2 ON ($condition)
		WHERE f1.category_id = " . intval($category_id) . "
		ORDER BY f2.left_id " . (($order == 'descending') ? 'ASC' : 'DESC');
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (!$include_forum && $row['category_id'] == $category_id)
		{
			continue;
		}

		$rows[] = $row;
	}
	$db->sql_freeresult($result);

	return $rows;
}
?>