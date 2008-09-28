<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: functions_rate.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Gets the ratings a user has given
*/
function get_user_blog_rating_data($user_id)
{
	global $cache, $config, $db;

	if (!$config['user_blog_enable_ratings'])
	{
		return false;
	}

	// This function will be called upon quite often, so I will be storing the data in a static array after we get it once
	static $ratings = array();

	$user_id = (int) $user_id;

	if (isset($ratings[$user_id]))
	{
		return $ratings[$user_id];
	}

	$rating_data = $cache->get('_blog_rating_' . $user_id);

	if ($rating_data === false)
	{
		$rating_data = array();
		$sql = 'SELECT * FROM ' . BLOGS_RATINGS_TABLE . ' WHERE user_id = ' . $user_id;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$rating_data[$row['blog_id']] = $row['rating'];
		}
		$db->sql_freeresult($result);

		$cache->put('_blog_rating_' . $user_id, $rating_data);
	}

	$ratings[$user_id] = $rating_data;
	return $rating_data;
}

/**
* gets the star rating of the item from the int rating
*
* @param string $start_url The url which will be used to rate each item (the rating part of the url should be *rating*, EX: blog.php?page=rate&b=1&rating=*rating*)
* @param string $delete_url The url which will be used to remove the rating for the item
* @param int $average_rating The current rating of the item
* @param int $num_ratings The number of times the item has been rated
* @param int $user_rating The rating the user gave for the item
* @param bool $force_average If you want to force it to display the average score without the links to submit the rating
*/
function get_star_rating($start_url, $delete_url, $average_rating, $num_ratings, $user_rating, $force_average = false)
{
	global $auth, $config, $phpbb_root_path, $phpEx, $user, $blog_plugins, $blog_images_path;

	if (!$config['user_blog_enable_ratings'])
	{
		return false;
	}

	$temp = compact('start_url', 'delete_url', 'average_rating', 'num_ratings', 'user_rating', 'force_average');
	blog_plugins::plugin_do_ref('function_get_star_rating', $temp);
	extract($temp);

	$can_rate = ($user->data['is_registered'] && !$force_average && $user_rating === false) ? true : false;

	// If it has not had any ratings yet, give it 1/2 the max for the rating
	if ($num_ratings == 0)
	{
		// If they can not rate the item and there are no ratings, do not show it at all.
		if (!$can_rate)
		{
			return '';
		}

		$average_rating = ceil($config['user_blog_max_rating'] / 2);
	}

	// Some variables we'll need
	$star_green = $blog_images_path . 'star_green.gif';
	$star_grey = $blog_images_path . 'star_grey.gif';
	$star_orange = $blog_images_path . 'star_orange.gif';
	$star_red = $blog_images_path . 'star_red.gif';
	$star_remove = $blog_images_path . 'star_remove.gif';

	$final_code = ($force_average) ? sprintf((($num_ratings == 1) ? $user->lang['AVERAGE_OF_RATING'] : $user->lang['AVERAGE_OF_RATINGS']), $num_ratings) . ':' : '';

	// A unique string that will get added to the rating.  So if the item is shown more than once, hovering over and trying to rate one doesn't mess up the other list.
	$unique_str = md5(microtime());
	$unique_str = "u_{$unique_str}_s_";

	// If the user has rated this already and we are not just getting the average, get the average as well.
	if ($user_rating !== false && !$force_average)
	{
		$final_code = get_star_rating($start_url, $delete_url, $average_rating, $num_ratings, $user_rating, true) . '';
		$average_rating = $user_rating;
	}

	$final_code .= ($user_rating !== false && !$force_average) ? $user->lang['MY_RATING'] . ': ' : '';

	$final_code .= '<div>';
	for ($i = $config['user_blog_min_rating']; $i <= $config['user_blog_max_rating']; $i++)
	{
		$title = ($user_rating === false && !$force_average) ? sprintf($user->lang['RATE_ME'], $i, $config['user_blog_max_rating']) : sprintf($user->lang['RATE_ME'], $average_rating, $config['user_blog_max_rating']);

		$final_code .= ($can_rate) ? '<a href="' . str_replace('*rating*', $i, $start_url) . '">' : '';
		$final_code .= '<img id="' . $unique_str . $i . '" ';
		if ($user_rating !== false && $i <= $user_rating && !$force_average)
		{
			$final_code .= 'src="' . $star_green . '" ';
		}
		else if ($i <= $average_rating)
		{
			$final_code .= 'src="' . $star_orange . '" ';
		}
		else
		{
			$final_code .= 'src="' . $star_grey . '" ';
		}
		$final_code .= ($can_rate) ? "onmouseover=\"ratingHover('{$i}', '{$unique_str}')\"  onmouseout=\"ratingUnHover('{$average_rating}', '{$unique_str}')\"  onmousedown=\"ratingDown('{$i}', '{$unique_str}')\"" : '';
		$final_code .= ' alt="' . $title . '" title="' . $title . '" />';
		$final_code .= ($can_rate) ? '</a>' : '';
	}

	// If required, we will add the remove rating icon at the end
	if ($user_rating !== false && !$force_average)
	{
		$final_code .= ' <a href="' . $delete_url . '"><img id="' . $unique_str . 'remove" src="' . $star_remove . '"  alt="' . $user->lang['REMOVE_RATING'] . '" title="' . $user->lang['REMOVE_RATING'] . '" /></a>';
	}

	$final_code .= '</div>';

	return $final_code;
}

?>