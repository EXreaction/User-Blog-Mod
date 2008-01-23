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
* URL Replace
*
* Replaces tags and other items that could break the SEO URL's
*/
function url_replace($url)
{
	$match = array('-', '?', '/', '\\', '\'', '&amp;', '&lt;', '&gt;', '&quot;', ':');

	// First replace all the above items with nothing, then replace spaces with _, then replace 3 _ in a row with a 1 _
	return str_replace(array(' ', '___'), '_', str_replace($match, '', $url));
}

/**
* URL handler
*/
function blog_url($user_id, $blog_id = false, $reply_id = false, $url_data = array(), $extra_data = array(), $force_no_seo = false)
{
	global $config, $user, $_SID;

	blog_plugins::plugin_do('function_blog_url');

	// don't call the generate_board_url function a whole bunch of times, get it once and keep using it!
	static $start_url = '';
	$start_url = ($start_url == '') ? ((defined('BLOG_USE_ROOT')) ? generate_board_url(true) : generate_board_url()) . '/' : $start_url;
	$extras = $anchor = '';

	// Add the category stuff if c is in the url
	static $blog_categories = false;
	if (isset($_GET['c']) && !isset($url_data['c']))
	{
		$category_id = request_var('c', 0);
		if ($blog_categories === false)
		{
			$blog_categories = get_blog_categories('category_id');
		}

		if (!isset($url_data['page']) && isset($blog_categories[$category_id]) && isset($config['user_blog_seo']) && $config['user_blog_seo'] && !$force_no_seo)
		{
			$url_data['page'] = $blog_categories[$category_id]['category_name'];
		}

		$url_data['c'] = $category_id;
	}

	// Handle the anchor
	if (isset($url_data['anchor']))
	{
		$anchor = '#' . $url_data['anchor'];
		unset($url_data['anchor']);
	}
	else if ($reply_id)
	{
		$anchor = '#r' . $reply_id;
	}

	if (isset($config['user_blog_seo']) && $config['user_blog_seo'] && !$force_no_seo)
	{
		$title_match ='/([^a-zA-Z0-9\s_])/'; // Replace HTML Entities, and non alphanumeric/space/underscore characters
		$replace_page = true; // match everything except the page if this is set to false

		if (!isset($url_data['page']) && $user_id !== false)
		{
			// Do not do the str_replace for the username, it would break it! :P
			$replace_page = false;

			if ($user_id == $user->data['user_id'])
			{
				$url_data['page'] = $user->data['username'];
			}
			else if (isset($extra_data['username']))
			{
				$url_data['page'] = $extra_data['username'];
			}
			else if (class_exists('blog_data'))
			{
				if (!isset(blog_data::$user[$user_id]))
				{
					global $blog_data;
					$blog_data->get_user_data($user_id);
				}
				$url_data['page'] = blog_data::$user[$user_id]['username'];
			}
			else
			{
				$url_data['u'] = $user_id;
			}
		}
		else if (isset($url_data['page']) && $user_id !== false)
		{
			$url_data['u'] = $user_id;
		}

		if ($reply_id)
		{
			$url_data['r'] = $reply_id;
			if (!isset($url_data['mode']))
			{
				if (class_exists('blog_data') && array_key_exists($reply_id, blog_data::$reply))
				{
					$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', blog_data::$reply[$reply_id]['reply_subject']));
				}
				else if (array_key_exists('reply_subject', $extra_data))
				{
					$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', $extra_data['reply_subject']));
				}
			}
		}
		else if ($blog_id)
		{
			$url_data['b'] = $blog_id;
			if (!isset($url_data['mode']))
			{
				if (class_exists('blog_data') && array_key_exists($blog_id, blog_data::$blog))
				{
					$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', blog_data::$blog[$blog_id]['blog_subject']));
				}
				else if (array_key_exists('blog_subject', $extra_data))
				{
					$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', $extra_data['blog_subject']));
				}
			}
		}

		// Add style= to the url data if it is in there
		if (isset($_GET['style']) && !isset($url_data['style']))
		{
			$url_data['style'] = $_GET['style'];
		}

		if (count($url_data))
		{
			foreach ($url_data as $name => $value)
			{
				if ($name == 'page' || $name == 'mode' || $value == '*skip*')
				{
					continue;
				}

				$extras .= '_' . url_replace($name) . '-' . url_replace($value);
			}
		}

		// Add the Session ID if required, do not add it for bots.  Used to remove it for guests, but once in a while the session_id is required for guests, like for the captcha
		if ($_SID && !$user->data['is_bot'])
		{
			$extras .= "_sid-{$_SID}";
		}

		if (isset($url_data['page']) && $url_data['page'])
		{
			if ($replace_page)
			{
				$url_data['page'] = url_replace($url_data['page']);
			}

			if (isset($url_data['mode']) && $url_data['mode'])
			{
				$url_data['mode'] = url_replace($url_data['mode']);
				return $start_url . "blog/{$url_data['page']}/{$url_data['mode']}{$extras}.html{$anchor}";
			}
			else
			{
				if ($extras || $anchor)
				{
					return $start_url . "blog/{$url_data['page']}/index{$extras}.html{$anchor}";
				}
				else
				{
					return $start_url . "blog/{$url_data['page']}/";
				}
			}
		}
		else
		{
			if ($extras || $anchor)
			{
				return $start_url . "blog/index{$extras}.html{$anchor}";
			}
			else
			{
				return $start_url . 'blog/';
			}
		}
	}

	// No SEO Url's :(
	global $phpbb_root_path, $phpEx;

	// add this stuff first
	$extras .= (($user_id) ? '&amp;u=' . $user_id : '');
	$extras .= (($blog_id) ? '&amp;b=' . $blog_id : '');
	$extras .= (($reply_id) ? '&amp;r=' . $reply_id : '');

	if (count($url_data))
	{
		foreach ($url_data as $name => $var)
		{
			// Do not add the blog/reply/user id to the url string, they've already been added
			if ($name == 'b' || $name == 'u' || $name == 'r' || $var == '*skip*')
			{
				continue;
			}

			$extras .= '&amp;' . $name . '=' . $var;
		}
	}

	$extras = substr($extras, 5); // remove the first &amp;
	return append_sid($phpbb_root_path . 'blog.' . $phpEx, $extras) . $anchor;
}

/**
* generates the basic URL's used by this mod
*/
function generate_blog_urls()
{
	global $config, $user, $blog_urls;
	global $blog_id, $reply_id, $user_id;

	$self_data = $_GET;

	$blog_urls = array(
		'add_blog'			=> blog_url(false, false, false, array('page' => 'blog', 'mode' => 'add')),
		'add_reply'			=> ($blog_id) ? blog_url($user_id, $blog_id, false, array('page' => 'reply', 'mode' => 'add')) : false,

		'main'				=> blog_url(false),

		'self'				=> blog_url($user_id, $blog_id, $reply_id, $self_data),
		'self_minus_print'	=> blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('view' => '*skip*'))),
		'self_minus_start'	=> blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('start' => '*skip*'))),
		'self_print'		=> blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('view' => 'print'))),
		'start_zero'		=> blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('start' => '*start*'))),
		'subscribe'			=> ($config['user_blog_subscription_enabled'] && ($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != ANONYMOUS) ? blog_url($user_id, $blog_id, false, array('page' => 'subscribe')) : '',

		'unsubscribe'		=> ($config['user_blog_subscription_enabled'] && ($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != ANONYMOUS) ? blog_url($user_id, $blog_id, false, array('page' => 'unsubscribe')) : '',

		'view_blog'			=> ($blog_id) ? blog_url($user_id, $blog_id) : false,
		'viewpoll'			=> ($blog_id) ? blog_url($user_id, $blog_id, false, array('view' => 'viewpoll')) : false,
		'view_reply'		=> ($reply_id) ? blog_url($user_id, $blog_id, $reply_id) : false,
		'view_user'			=> ($user_id) ? blog_url($user_id) : false,
		'view_user_deleted'	=> ($user_id) ? blog_url($user_id, false, false, array('mode' => 'deleted')) : false,
		'view_user_self'	=> blog_url($user->data['user_id']),
		'vote'				=> ($blog_id) ? blog_url($user_id, $blog_id, false, array('page' => 'vote')) : false,
	);

	blog_plugins::plugin_do('function_generate_blog_urls');
}
?>