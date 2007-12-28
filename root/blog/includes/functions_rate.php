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
* Gets the ratings a user has given
*/
function get_user_blog_rating_data($user_id)
{
	global $cache, $config, $db;

	if (!$config['user_blog_enable_ratings'])
	{
		return false;
	}

	// This function will be called upon quite often, so I will be storing the data in a static array after it is gotten.
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

		$cache->put('_user_blog_rating_' . $user_id, $rating_data);
	}

	$ratings[$user_id] = $rating_data;
	return $rating_data;
}

/**
* gets the star rating of the file from the int rating
*
* @param int $int_rating The current rating of the blog
* @param int $blog_id The blog_id of the blog (for the rate links)
* @param bool $average If you want to force it to display the average score without the links to submit the rating
*/
function get_star_rating($int_rating, $blog_id, $average = false)
{
	global $auth, $blog_data, $config, $phpbb_root_path, $phpEx, $user;

	if (!$config['user_blog_enable_ratings'])
	{
		return false;
	}

	// We will need the blog_url function
	if (!function_exists('blog_url'))
	{
		include("{$phpbb_root_path}blog/includes/functions_url.$phpEx");
	}

	// Make sure we have the language we need
	if (!isset($user->lang['RATE_ME']))
	{
		$user->add_lang('mods/blog/view');
	}

	// Get the rating data for this user
	if ($user->data['is_registered'])
	{
		$rating_data = get_user_blog_rating_data($user->data['user_id']);
	}

	// If it has not had any ratings yet, give it 1/2 the max for the rating
	if ($blog_data->blog[$blog_id]['num_ratings'] == 0)
	{
		$int_rating = ceil($config['user_blog_max_rating'] / 2);
	}

	// Some variables we'll need
	$category_id = request_var('c', 0);
	$star_green = $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/blog/star_green.gif';
	$star_grey = $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/blog/star_grey.gif';
	$star_orange = $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/blog/star_orange.gif';
	$star_red = $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/blog/star_red.gif';
	$star_remove = $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/blog/star_remove.gif';

	$average = ($average || $user->data['user_id'] == $blog_data->blog[$blog_id]['user_id']) ? true : false;
	$can_rate = ($user->data['is_registered'] && !$average && !isset($rating_data[$blog_id])) ? true : false;

	$final_code = ($average) ? $user->lang['AVERAGE_RATING'] . ': ' : '';

	// A unique string that will get added to the rating.  So if a blog is shown more than once, hovering over and trying to rate one doesn't mess up the other list.
	$unique_str = md5(microtime());
	$unique_str = "b_{$blog_id}_u_{$unique_str}_s_";

	// If the user has rated this already, and we are not just getting the average, get the average as well.
	if (isset($rating_data[$blog_id]) && !$average)
	{
		$final_code = get_star_rating($int_rating, $blog_id, true) . '';
		$int_rating = $rating_data[$blog_id];
	}

	$final_code .= ($user->data['is_registered'] && !$average) ? $user->lang['MY_RATING'] . ': ' : '';

	$final_code .= '<div>';
	for ($i = $config['user_blog_min_rating']; $i <= $config['user_blog_max_rating']; $i++)
	{
		$final_code .= ($can_rate) ? '<a href="' . blog_url(false, $blog_id, false, array('page' => 'rate', 'rating' => $i, 'c' => (($category_id) ? $category_id : '*skip*'))) . '">' : '';
		$final_code .= '<img id="' . $unique_str . $i . '" ';
		if (isset($rating_data[$blog_id]) && $i <= $rating_data[$blog_id] && !$average)
		{
			$final_code .= 'src="' . $star_green . '" ';
		}
		else if ($i <= $int_rating)
		{
			$final_code .= 'src="' . $star_orange . '" ';
		}
		else
		{
			$final_code .= 'src="' . $star_grey . '" ';
		}
		$final_code .= ($can_rate) ? "onmouseover=\"ratingHover('{$i}', '{$unique_str}')\"  onmouseout=\"ratingUnHover('{$int_rating}', '{$unique_str}')\"  onmousedown=\"ratingDown('{$i}', '{$unique_str}')\"" : '';
		$final_code .= ' alt="' . sprintf($user->lang['RATE_ME'], $i, $config['user_blog_max_rating']) . '" title="' . sprintf($user->lang['RATE_ME'], $i, $config['user_blog_max_rating']) . '" />';
		$final_code .= ($can_rate) ? '</a>' : '';
	}

	// If required, we will add the remove rating icon at the end
	if (isset($rating_data[$blog_id]) && !$average)
	{
		$final_code .= ' <a href="' . blog_url(false, $blog_id, false, array('page' => 'rate', 'delete' => $blog_id, 'c' => (($category_id) ? $category_id : '*skip*'))) . '"><img id="' . $unique_str . 'remove" src="' . $star_remove . '"  alt="' . $user->lang['REMOVE_RATING'] . '" title="' . $user->lang['REMOVE_RATING'] . '" /></a>';
	}

	$final_code .= '</div>';

	return $final_code;
}

?>