<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Get all blog categories
*/
function get_blog_categories($order = 'left_id')
{
	global $cache, $db;

	$blog_categories = $cache->get('_blog_categories');

	if ($blog_categories === false)
	{
		$blog_categories = array();
		$sql = 'SELECT * FROM ' . BLOGS_CATEGORIES_TABLE . "
			ORDER BY left_id ASC";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$blog_categories[$row['left_id']] = $row;
		}

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

	return $blog_categories;
}

/**
* Simple version of jumpbox, just lists categories
* Copied from make_forum_select
*/
function make_category_select($select_id = false, $ignore_id = false, $return_array = false)
{
	global $db, $user;

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