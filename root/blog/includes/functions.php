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

	// Add the blogstyle setting if required
	if (isset($_GET['blogstyle']) && !isset($url_data['blogstyle']))
	{
		$url_data['blogstyle'] = $_GET['blogstyle'];
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
			else if (class_exists('blog_data') && isset(blog_data::$user[$user_id]))
			{
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

		// Add the Session ID if required.
		if ($_SID)
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

		'self'				=> blog_url(false, false, false, $self_data),
		'self_minus_print'	=> blog_url(false, false, false, array_merge($self_data, array('view' => '*skip*'))),
		'self_minus_start'	=> blog_url(false, false, false, array_merge($self_data, array('start' => '*skip*'))),
		'self_print'		=> blog_url(false, false, false, array_merge($self_data, array('view' => 'print'))),
		'start_zero'		=> blog_url(false, false, false, array_merge($self_data, array('start' => '*start*'))),
		'subscribe'			=> (($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != ANONYMOUS && $config['user_blog_subscription_enabled']) ? blog_url($user_id, $blog_id, false, array('page' => 'subscribe')) : '',

		'unsubscribe'		=> (($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != ANONYMOUS && $config['user_blog_subscription_enabled']) ? blog_url($user_id, $blog_id, false, array('page' => 'unsubscribe')) : '',

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

/**
* Updates user settings
*/
function update_user_blog_settings($user_id, $data, $resync = false)
{
	global $cache, $db, $user_settings, $blog_plugins;

	if (!isset($user_settings[$user_id]))
	{
		get_user_settings($user_id);
	}

	if (isset($data['blog_css']))
	{
		$data['blog_css'] = str_replace(array('java', 'script', 'eval'), '', $data['blog_css']);
	}

	if (!isset($user_settings[$user_id]))
	{
		$sql_array = array(
			'user_id'							=> $user_id,
			'perm_guest'						=> (isset($data['perm_guest'])) ? $data['perm_guest'] : 1,
			'perm_registered'					=> (isset($data['perm_registered'])) ? $data['perm_registered'] : 2,
			'perm_foe'							=> (isset($data['perm_foe'])) ? $data['perm_foe'] : 0,
			'perm_friend'						=> (isset($data['perm_friend'])) ? $data['perm_friend'] : 2,
			'title'								=> (isset($data['title'])) ? $data['title'] : '',
			'description'						=> (isset($data['description'])) ? $data['description'] : '',
			'description_bbcode_bitfield'		=> (isset($data['description_bbcode_bitfield'])) ? $data['description_bbcode_bitfield'] : '',
			'description_bbcode_uid'			=> (isset($data['description_bbcode_uid'])) ? $data['description_bbcode_uid'] : '',
			'instant_redirect'					=> (isset($data['instant_redirect'])) ? $data['instant_redirect'] : 0,
			'blog_subscription_default'			=> (isset($data['blog_subscription_default'])) ? $data['blog_subscription_default'] : 0,
			'blog_style'						=> (isset($data['blog_style'])) ? $data['blog_style'] : '',
			'blog_css'							=> (isset($data['blog_css'])) ? $data['blog_css'] : '',
		);

		$temp = compact('sql_array', 'user_id', 'data');
		blog_plugins::plugin_do_ref('function_get_user_settings_insert', $temp);
		extract($temp);

		$sql = 'INSERT INTO ' . BLOGS_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
		$db->sql_query($sql);
	}
	else
	{
		blog_plugins::plugin_do_ref('function_get_user_settings_update', $data);

		$sql = 'UPDATE ' . BLOGS_USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE user_id = ' . intval($user_id);
		$db->sql_query($sql);
	}

	if ($resync && (array_key_exists('perm_guest', $data) || array_key_exists('perm_registered', $data) || array_key_exists('perm_foe', $data) || array_key_exists('perm_friend', $data)))
	{
		$sql_array = array(
			'perm_guest'						=> (isset($data['perm_guest'])) ? $data['perm_guest'] : 1,
			'perm_registered'					=> (isset($data['perm_registered'])) ? $data['perm_registered'] : 2,
			'perm_foe'							=> (isset($data['perm_foe'])) ? $data['perm_foe'] : 0,
			'perm_friend'						=> (isset($data['perm_friend'])) ? $data['perm_friend'] : 2,
		);

		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id = \'' . intval($user_id) . '\'';
		$db->sql_query($sql);
	}

	blog_plugins::plugin_do('function_get_user_settings', compact('data', 'user_id', 'resync'));

	$cache->destroy('_blog_settings_' . $user_id);
}

/**
* Blog Error Handler
*/
function blog_error_handler($errno, $msg_text, $errfile, $errline)
{
	if ($errno == E_USER_NOTICE)
	{
		global $user, $template;
		if (!isset($user->lang['CLICK_INSTALL_BLOG']))
		{
			$user->setup('mods/blog/common');
		}
		else
		{
			$template->set_template();	
		}
	}

	msg_handler($errno, $msg_text, $errfile, $errline);
}

/**
* Setup the blog search system
*/
function setup_blog_search()
{
	global $config, $phpbb_root_path, $phpEx;

	if (file_exists($phpbb_root_path . 'blog/search/' . $config['user_blog_search_type'] . '.' . $phpEx))
	{
		include($phpbb_root_path . 'blog/search/' . $config['user_blog_search_type'] . '.' . $phpEx);
		$class = 'blog_' . $config['user_blog_search_type'];
		return new $class();
	}
	else
	{
		trigger_error('BLOG_SEARCH_BACKEND_NOT_EXIST');
	}
}

/**
* Handles updates to the cache
*
* @param string $mode
* @param int $user_id
*/
function handle_blog_cache($mode, $user_id = 0)
{
	global $cache;

	$temp = compact('mode', 'user_id');
	blog_plugins::plugin_do_arg('function_handle_blog_cache', $temp);

	if (!$mode && $user_id)
	{
		$cache->destroy("_blog_settings_{$user_id}");
		$cache->destroy("_blog_subscription_{$user_id}");
		$cache->destroy("_blog_rating_{$user_id}");
	}

	switch ($mode)
	{
/*			Not currently used
		case 'new_blog' :
		case 'approve_blog' :
		case 'report_blog' :
		case 'delete_blog' :
		case 'undelete_blog' :
		case 'new_reply' :
		case 'approve_reply' :
		case 'report_reply' :
		case 'delete_reply' :
		case 'undelete_reply' :
*/
		case 'plugins' :
			$cache->destroy('_blog_plugins');
		break;
		case 'extensions' :
			$cache->destroy('_blog_extensions');
		break;
		case 'categories' :
			$cache->destroy('_blog_categories');
		break;
		default :
			blog_plugins::plugin_do_arg('function_handle_blog_cache_mode', $mode);
	}
}

/**
* Blog Meta Refresh (the normal one does not work with the SEO Url's)
*/
function blog_meta_refresh($time, $url)
{
	global $template, $user, $user_settings;

	if ($time == 0 || (isset($user_settings[$user->data['user_id']]['instant_redirect']) && $user_settings[$user->data['user_id']]['instant_redirect']))
	{
		$time = 0;
		header('Location: ' . str_replace('&amp;', '&', $url));
	}

	$template->assign_vars(array(
		'META' => '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '" />')
	);
}

/**
* Get subscription types
*/
function get_blog_subscription_types()
{
	global $config, $blog_plugins;

	if (!$config['user_blog_subscription_enabled'])
	{
		return;
	}

	// First is the subscription ID (which will use the bitwise operator), the second is the language variable.
	$subscription_types = array(1 => 'PRIVATE_MESSAGE', 2 => 'EMAIL');

	/* Remember, we use the bitwise operator to find out what subscription type is the users default, like the bbcode options.
	So if you add more, use 1,2,4,8,16,32,64,etc and make sure to use the next available number, don't assume 4 is available! */
	blog_plugins::plugin_do_ref('function_get_subscription_types', $subscription_types);

	return $subscription_types;
}

/**
* Fix Where SQL function
*
* Checks to make sure there is a WHERE if there are any AND sections in the SQL and fixes them if needed
*
* @param string $sql The (possibly) broken SQL query to check
* @return The fixed SQL query.
*/
function fix_where_sql($sql)
{
	if (!strpos($sql, 'WHERE') && strpos($sql, 'AND'))
	{
		return substr($sql, 0, strpos($sql, 'AND')) . 'WHERE' . substr($sql, strpos($sql, 'AND') + 3);
	}

	return $sql;
}
?>