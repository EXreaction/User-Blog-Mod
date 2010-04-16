<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: functions.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
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
	$match = array('#', '-', '?', '/', '\\', '\'', '&amp;', '&lt;', '&gt;', '&quot;', ':');

	// First replace all the above items with nothing, then replace spaces with _, then replace 3 _ in a row with a 1 _
	$url = str_replace(array(' ', '___'), '_', str_replace($match, '', (string) $url));
	$url = urlencode($url);
	$url = str_replace('%2A', '*', $url);
	return $url;
}

/**
* URL handler
*
* @param int $user_id - The user_id
* @param int $blog_id - The blog_id
* @param int $reply_id - The reply_id
* @param array $url_data - Data to put in the url's.  Everything will get built in the url from this array.
*	For exmaple, if array('page' => 'blog', 'mode' => 'add') is sent it would be built as blog.php?page=blog&mode=add
*	Send 'anchor' (if needed) as the anchor for the page which will get added to the end of the url as # . $url_data['anchor']
* @param array $extra_data - Extra data that will be used in the URL when required.
*	When building the url this function checks the blog_data::$(user|blog|reply) arrays to see if the username, blog title, and/or reply title exist for that (user|blog|reply)_id.
*	If they do not exist in that array and you would like to manually send it you can send it in this array.  array('username' => (the username), 'blog_subject' => (blog title), 'reply_subject' => (reply title))
*	These are not required to be sent, just send them if you want/need to.
* @param bool $force_no_seo - If set to true this will build a normal url (needed for some places), not the pretty ones with the username, title, etc in.
*/
function blog_url($user_id, $blog_id = false, $reply_id = false, $url_data = array(), $extra_data = array(), $force_no_seo = false)
{
	global $config, $user, $_SID;

	blog_plugins::plugin_do('function_blog_url');

	// don't call the generate_board_url function a whole bunch of times, get it once and keep using it
	static $start_url = '';
	$start_url = ($start_url == '') ? ((defined('BLOG_ROOT')) ? generate_board_url(true) . BLOG_ROOT : generate_board_url()) . '/' : $start_url;
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
		$url_data['blogstyle'] = request_var('blogstyle', '');
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
			$username_check = '#&+/\:?"<>%|';

			if ($user_id == $user->data['user_id'] && !strpbrk($user->data['username'], $username_check))
			{
				$url_data['page'] = urlencode($user->data['username']);
			}
			else if (isset($extra_data['username']) && !strpbrk($extra_data['username'], $username_check))
			{
				$url_data['page'] = urlencode($extra_data['username']);
			}
			else if (class_exists('blog_data') && isset(blog_data::$user[$user_id]) && !strpbrk(blog_data::$user[$user_id]['username'], $username_check))
			{
				$url_data['page'] = urlencode(blog_data::$user[$user_id]['username']);
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
					$url_data['mode'] = utf8_clean_string(blog_data::$reply[$reply_id]['reply_subject']);
				}
				else if (array_key_exists('reply_subject', $extra_data))
				{
					$url_data['mode'] = utf8_clean_string($extra_data['reply_subject']);
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
					$url_data['mode'] = utf8_clean_string(blog_data::$blog[$blog_id]['blog_subject']);
				}
				else if (array_key_exists('blog_subject', $extra_data))
				{
					$url_data['mode'] = utf8_clean_string($extra_data['blog_subject']);
				}
			}
		}

		// Add style= to the url data if it is in there
		if (isset($_GET['style']) && !isset($url_data['style']))
		{
			$url_data['style'] = request_var('style', '');
		}

		// Add the Session ID if required.
		if ($_SID)
		{
			$url_data['sid'] = $_SID;
		}

		if (sizeof($url_data))
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

		if (isset($url_data['page']) && $url_data['page'])
		{
			if (isset($url_data['mode']) && $url_data['mode'])
			{
				$url_data['mode'] = url_replace($url_data['mode']);
				//return $start_url . "blog/{$url_data['page']}/{$url_data['mode']}{$extras}{$anchor}";
				return $start_url . "blog/{$url_data['page']}/{$url_data['mode']}{$extras}.html{$anchor}";
			}
			else
			{
				if ($extras || $anchor)
				{
					//return $start_url . "blog/{$url_data['page']}/index{$extras}{$anchor}";
					return $start_url . "blog/{$url_data['page']}/index{$extras}.html{$anchor}";
				}
				else
				{
					//return $start_url . "blog/{$url_data['page']}";
					return $start_url . "blog/{$url_data['page']}/";
				}
			}
		}
		else if (isset($url_data['mode']) && $url_data['mode'])
		{
			$url_data['mode'] = url_replace($url_data['mode']);
			//return $start_url . "blog/view/{$url_data['mode']}{$extras}{$anchor}";
			return $start_url . "blog/view/{$url_data['mode']}{$extras}.html{$anchor}";
		}
		else
		{
			if ($extras || $anchor)
			{
				//return $start_url . "blog/index{$extras}{$anchor}";
				return $start_url . "blog/index{$extras}.html{$anchor}";
			}
			else
			{
				return $start_url . 'blog/';
			}
		}
	}

	// No SEO Url's :(
	global $phpEx;

	// Do not add the sid multiple times
	unset($url_data['sid']);

	// add this stuff first
	$extras .= (($user_id) ? '&amp;u=' . $user_id : ((isset($url_data['u'])) ? '&amp;u=' . $url_data['u'] : ''));
	$extras .= (($blog_id) ? '&amp;b=' . $blog_id : ((isset($url_data['b'])) ? '&amp;b=' . $url_data['b'] : ''));
	$extras .= (($reply_id) ? '&amp;r=' . $reply_id : ((isset($url_data['r'])) ? '&amp;r=' . $url_data['r'] : ''));

	if (sizeof($url_data))
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
	return append_sid($start_url . 'blog.' . $phpEx, $extras) . $anchor;
}

/**
* generates the basic URL's used by this mod
*
* It is setup this way to allow easy changing of the url of some common pages
*/
function generate_blog_urls()
{
	global $config, $user, $blog_urls;
	global $blog_id, $reply_id, $user_id;

	$self_data = $_GET;

	$blog_urls = array(
		'add_blog'			=> blog_url(false, false, false, array('page' => 'blog', 'mode' => 'add')),
		'add_reply'			=> ($blog_id) ? blog_url($user_id, $blog_id, false, array('page' => 'reply', 'mode' => 'add')) : false,

		'main'				=> blog_url(false, false, false, array('c' => '*skip*')),

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
* Gets user settings
*
* @param int $user_ids array of user_ids to get the settings for
*/
function get_user_settings($user_ids)
{
	global $cache, $config, $user_settings;

	if (!is_array($user_settings))
	{
		$user_settings = array();
	}

	if (!is_array($user_ids))
	{
		$user_ids = array($user_ids);
	}

	// Only run the query if we have to.
	$to_query = array();
	foreach ($user_ids as $id)
	{
		if ($id && !array_key_exists($id, $user_settings))
		{
			$cache_data = $cache->get('_blog_settings_' . intval($id));
			if ($cache_data === false)
			{
				$to_query[] = (int) $id;
			}
			else
			{
				$user_settings[$id] = $cache_data;
			}
		}
	}

	if (sizeof($to_query))
	{
		global $db;
		$sql = 'SELECT * FROM ' . BLOGS_USERS_TABLE . ' WHERE ' . $db->sql_in_set('user_id', $to_query);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$cache->put('_blog_settings_' . $row['user_id'], $row);

			$user_settings[$row['user_id']] = $row;
		}
		$db->sql_freeresult($result);
	}

	blog_plugins::plugin_do('function_get_user_settings');
}

/**
* Updates user settings
*
* ALWAYS use this function if you would like to update a user's blog settings on a different page!  Otherwise there may be security problems.
*/
function update_user_blog_settings($user_id, $data, $resync = false)
{
	global $cache, $db, $user_settings, $blog_plugins;

	if (!isset($user_settings[$user_id]))
	{
		get_user_settings($user_id);
	}

	// Filter the Blog CSS.
	if (isset($data['blog_css']))
	{
		// Check for valid images if the user put in any urls.
		/* This just does not seem to work correctly all the time, so I am removing it.
		It really isn't that important anyways, since someone could link to an image, then after they submit the page replace the image with whatever they want.
		$urls = array();
		preg_match_all('#([a-zA-Z]+):((//)|(\\\\))+[\w\d:\#%/;$~_?\\-=\\\.&]*#', $data['blog_css'], $urls);
		foreach ($urls[0] as $img)
		{
			if (@getimagesize($img) === false)
			{
				$data['blog_css'] = str_replace($img, ' ', $data['blog_css']);
			}
		}*/

		// Replace quotes so they can be used.
		$data['blog_css'] = str_replace('&quot;', '"', $data['blog_css']);

		// Now we shall run our main filters.
		$script_matches = array('#javascript#', '#vbscript#', '#manuscript#', "#[^a-zA-Z]java#", "#java[^a-zA-Z]#", "#[^a-zA-Z]script#", "#script[^a-zA-Z]#", "#[^a-zA-Z]expression#", "#expression[^a-zA-Z]#", "#[^a-zA-Z]eval#", "#eval[^a-zA-Z]#");
		if (preg_replace($script_matches, ' ', strtolower($data['blog_css'])) != strtolower($data['blog_css']))
		{
			// If they are going to try something so obvious, instead of trying to filter it I'll just delete everything.
			$data['blog_css'] = '';
		}
		else
		{
			// Remove CSS/HTML comments, HTML ASCII/HEX, and any other characters I do not think are needed.
			$matches = array('#/\*.+\*/#', '#<!--.+-->#', '$&#?([a-zA-Z0-9]+);?$', '$([^a-zA-Z0-9",\*+%!_\.#{}()/:;-\s])$');
			$data['blog_css'] = preg_replace($matches, ' ', $data['blog_css']);
		}
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
			'blog_style'						=> (isset($data['blog_style'])) ? $data['blog_style'] : 0,
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

	// Resyncronise the Blog Permissions
	if ($resync && (array_key_exists('perm_guest', $data) || array_key_exists('perm_registered', $data) || array_key_exists('perm_foe', $data) || array_key_exists('perm_friend', $data)))
	{
		$sql_array = array(
			'perm_guest'						=> (isset($data['perm_guest'])) ? $data['perm_guest'] : 1,
			'perm_registered'					=> (isset($data['perm_registered'])) ? $data['perm_registered'] : 2,
			'perm_foe'							=> (isset($data['perm_foe'])) ? $data['perm_foe'] : 0,
			'perm_friend'						=> (isset($data['perm_friend'])) ? $data['perm_friend'] : 2,
		);

		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id = ' . intval($user_id);
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

		// If we don't have the language setting needed we probably have not setup the page yet, so we must do it before we can continue.
		if (!isset($user->lang['CLICK_INSTALL_BLOG']))
		{
			$user->setup('mods/blog/common');
		}
		else
		{
			// Set the template back to the user's default.  So custom style authors do not need to make a message_body template
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

	$search_type = basename($config['user_blog_search_type']);
	if (file_exists($phpbb_root_path . 'blog/search/' . $search_type . '.' . $phpEx))
	{
		include($phpbb_root_path . 'blog/search/' . $search_type . '.' . $phpEx);
		$class = 'blog_' . $search_type;
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
/*		Not currently used...
		case 'new_blog' :
		case 'edit_blog' :
		case 'approve_blog' :
		case 'delete_blog' :
		case 'undelete_blog' :
		case 'report_blog' :
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
		redirect($url);
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
		return array();
	}

	// First is the subscription ID (which will use the bitwise operator), the second is the language variable.
	$subscription_types = array();

	if ($config['allow_privmsg'])
	{
		$subscription_types[1] = 'PRIVATE_MESSAGE';
	}

	if ($config['email_enable'])
	{
		$subscription_types[2] = 'EMAIL';
	}

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