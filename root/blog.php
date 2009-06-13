<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: blog.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
// Do not do $user->setup here!

// Get some variables
$page = utf8_normalize_nfc(request_var('page', '', true)); // Normalize for usernames
$mode = request_var('mode', '');
$user_id = request_var('u', 0);
$blog_id = request_var('b', 0);
$reply_id = request_var('r', 0);
$category_id = request_var('c', 0);
$submit = (isset($_POST['post']) || isset($_POST['submit'])) ? true : false;
$preview = (isset($_POST['preview'])) ? true : false;
$print = (request_var('view', '') == 'print') ? true : false;
$refresh = (isset($_POST['add_file']) || isset($_POST['delete_file']) || isset($_POST['cancel_unglobalise'])) ? true : false;
$cancel = (isset($_POST['cancel'])) ? true : false;

$feed = request_var('feed', '');
$feed = ($feed && ($feed == 'explain' || $feed == 'RSS_0.91' || $feed == 'RSS_1.0' || $feed == 'RSS_2.0' || $feed == 'ATOM' || $feed == 'JAVASCRIPT') && $config['user_blog_enable_feeds']) ? $feed : false;
$hilit_words = request_var('hilit', '', true);
$start = request_var('start', 0);
$limit = request_var('limit', (($page == 'search') ? 20 : (($blog_id || $reply_id) ? 10 : 5)));

$sort_days = request_var('st', ((!empty($user->data['user_post_show_days'])) ? $user->data['user_post_show_days'] : 0));
$sort_key = request_var('sk', 't');
$sort_dir = request_var('sd', ($blog_id || $reply_id) ? 'a' : 'd');
$order_dir = ($sort_dir == 'a') ? 'ASC' : 'DESC';

// set some initial variables that we will use
$s_hidden_fields = $subscribed_title = $username = '';
$default = $inc_file = $user_style = $subscribed = false;
$error = $blog_urls = $zebra_list = $user_settings = array();

// include some files
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'blog/functions.' . $phpEx);
$blog_data = new blog_data();

// We need to use our own error handler which resets the template when trigger_error is called.
set_error_handler('blog_error_handler');

// check if the User Blog Mod is installed/enabled
if (!isset($config['user_blog_enable']) && $user->data['user_type'] == USER_FOUNDER)
{
	// Now we will just redirect to the install.php file.  Otherwise we have problems with some stuff trying to get data from non-existing tables.
	redirect(append_sid("{$phpbb_root_path}blog/database.$phpEx"));
}
else if ((!isset($config['user_blog_enable']) || !$config['user_blog_enable']) && $user->data['user_type'] != USER_FOUNDER)
{
	trigger_error('USER_BLOG_MOD_DISABLED');
}

blog_plugins::plugin_do('blog_start');

switch ($page)
{
	case 'vote'	: // Vote in a poll
		$default = true; // Setting default to true so that the blog is shown again after voting.
	case 'subscribe' : // subscribe to users/blogs
	case 'unsubscribe' : // unsubscribe from users/blogs
	case 'search' : // blogs search
	case 'resync' : // to resync the blog data
	case 'rate' : // to rate a blog
	case 'download' : // to download an attachment
	case 'feed' : // To view the feed options
		$add_lang = 'mods/blog/misc';
		$inc_file = $page;
	break;
	case 'update' : // for updating from previous versions of the User Blog Mod
	case 'upgrade' : // for upgrading from other blog modifications
	case 'dev' : // used for developmental purposes
		$add_lang = 'mods/blog/setup';
		$inc_file = $page;
	break;
	case 'blog' :
	case 'reply' :
		$add_lang = array('posting', 'mods/blog/posting');
		include($phpbb_root_path . 'blog/includes/functions_attachments.' . $phpEx);
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include($phpbb_root_path . 'blog/includes/functions_posting.' . $phpEx);
		$blog_attachment = new blog_attachment();
		$blog_search = setup_blog_search();
		$message_parser = new parse_message();

		switch ($mode)
		{
			case 'add' :
				if ($page == 'blog')
				{
					$user_id = $user->data['user_id'];
				}
			case 'edit' :
			case 'delete' :
			case 'undelete' :
			case 'report' :
			case 'approve' :
				$inc_file = $page . '/' . $mode;
			break;
			case 'quote' :
				$inc_file = 'reply/add';
			break;
			default :
				$default = true;
		}
	break;
	default :
		$default = true;
}

if ($default)
{
	// for highlighting
	$highlight_match = $highlight = '';
	if ($hilit_words)
	{
		foreach (explode(' ', trim($hilit_words)) as $word)
		{
			if (trim($word))
			{
				$word = str_replace('\*', '\w+?', preg_quote($word, '#'));
				$word = preg_replace('#(^|\s)\\\\w\*\?(\s|$)#', '$1\w+?$2', $word);
				$highlight_match .= (($highlight_match != '') ? '|' : '') . $word;
			}
		}

		$highlight = urlencode($hilit_words);
	}

	// If you are adding your own page with this, make sure to set $default to false if the page matches yours, otherwise it will load the default page below
	$temp = compact('page', 'mode', 'default', 'inc_file', 'user_style');
	blog_plugins::plugin_do_ref('blog_page_switch', $temp);
	extract($temp);

	// Check again since a plugin could have used it's own page.
	if ($default)
	{
		$user->add_lang('mods/blog/view');

		// With SEO urls, we make it so that the page could be the username name of the user we want to view...
		if (!$user_id && $page && !$category_id && !in_array($page, array('last_visit_blogs', 'random_blogs', 'recent_blogs', 'popular_blogs', 'recent_comments')))
		{
			$user_id = $blog_data->get_user_data(false, false, $page);
		}

		if ($blog_id || $reply_id)
		{
			$user_style = true; // Here and the view user page are the two places where users can view with their own custom style
			$inc_file = ($inc_file) ? array($inc_file, 'view/single') : 'view/single';
		}
		else if ($user_id)
		{
			$user_style = true;
			$inc_file = ($inc_file) ? array($inc_file, 'view/user') : 'view/user';
		}
		else
		{
			$inc_file = ($inc_file) ? array($inc_file, 'view/main') : 'view/main';
		}
	}
}

if ($reply_id)
{
	if ($blog_data->get_reply_data('reply', $reply_id) === false)
	{
		trigger_error('REPLY_NOT_EXIST');
	}

	$reply_user_id = blog_data::$reply[$reply_id]['user_id'];
	$blog_id = blog_data::$reply[$reply_id]['blog_id'];

	// Now let us try to figure out what page the requested reply is on and show that set of replies.
	if (request_var('start', -1) == -1)
	{
		$total_replies = $blog_data->get_reply_data('page', array($blog_id, $reply_id), array('order_dir' => $order_dir, 'sort_days' => $sort_days));
		$start = (intval($total_replies / $limit) * $limit);
	}
}

if ($blog_id)
{
	if ($blog_data->get_blog_data('blog', $blog_id) === false)
	{
		trigger_error('BLOG_NOT_EXIST');
	}

	$user_id = blog_data::$blog[$blog_id]['user_id'];
}

if ($user_id)
{
	blog_data::$user_queue[] = $user_id;
	$blog_data->get_user_data(false, true); // do it this way so we get user data on editors/deleters

	if (!array_key_exists($user_id, blog_data::$user))
	{
		trigger_error('NO_USER');
	}

	$username = blog_data::$user[$user_id]['username'];
}

get_user_settings(array($user_id, $user->data['user_id']));
get_zebra_info(array($user_id, $user->data['user_id']));

// Make sure the user can view this blog by checking the blog's individual permissions
if ($blog_id && !handle_user_blog_permissions($blog_id))
{
	trigger_error('NO_PERMISSIONS_READ');
}

// Put the template we want in $blog_template for easier access/use
// style= to use a board style, blogstyle= to use a custom blog style, otherwise it is set to the user's style or blank if none set
$blog_template = ((isset($_GET['style'])) ? request_var('style', 0) : ((isset($_GET['blogstyle'])) ? request_var('blogstyle', '') : (($user_id && isset($user_settings[$user_id])) ? $user_settings[$user_id]['blog_style'] : '')));


/**
* Ok, now lets actually start setting up the page.
*/


/*
* A slightly (weird) way it is that I have set this up.  Only on the view blog/user page can the user set a custom style except if that custom style is also a board style.
* If the style they selected is also a board style we will also show that style on the posting/etc pages.  This is to keep it easier on the custom template developers.
*/
if ($user_style && $blog_template && !is_numeric($blog_template) && is_dir($phpbb_root_path . 'blog/styles/' . $blog_template))
{
	$user->setup('mods/blog/common');

	// Do note style developers that dots and slashes in your style names are not allowed.
	if (strpos($blog_template, '.') !== false || strpos($blog_template, '/') !== false)
	{
		trigger_error('You may not have a . or / in the blog template name.');
	}

	// Lets use our own custom template path so we can have our own templates
	$template->set_custom_template($phpbb_root_path . 'blog/styles/' . $blog_template, $blog_template);

	// Some template links we will need...
	$template->assign_vars(array(
		'T_BLOG_TEMPLATE_PATH'			=> $phpbb_root_path . 'blog/styles/' . $blog_template . '/',
		'T_BLOG_IMAGESET_PATH'			=> $phpbb_root_path . 'blog/styles/' . $blog_template . '/images/',
		'T_BLOG_IMAGESET_LANG_PATH'		=> $phpbb_root_path . 'blog/styles/' . $blog_template . '/images/' . $user->data['user_lang'] . '/',
	));

	$blog_style = true;
	$blog_images_path = $phpbb_root_path . 'blog/styles/' . $blog_template . '/images/';

	/*
	* We now allow blog styles to have special plugins used for that specific style.
	* This should not be used as a normal plugin, but mostly to alter outputted data to fit the way the style author wants it to.
	* For example, the style author may want to force the date/time outputted to a specific format, etc for this specific style.
	*/
	if (file_exists($phpbb_root_path . 'blog/styles/' . $blog_template . '/style_plugin.' . $phpEx))
	{
		include($phpbb_root_path . 'blog/styles/' . $blog_template . '/style_plugin.' . $phpEx);
	}
}
else
{
	if ($blog_template && is_numeric($blog_template))
	{
		$user->setup('mods/blog/common', $blog_template);
	}
	else
	{
		$user->setup('mods/blog/common');
	}

	$blog_style = false;
	$blog_images_path = $phpbb_root_path . 'styles/' . $user->theme['theme_path'] . '/theme/images/blog/';
}

// Update the edit and delete fields for the blogs and replies we've gotten so far
update_edit_delete();

// Check to make sure the user has permission to get to this page
check_blog_permissions($page, $mode, false, $blog_id, $reply_id);

// If some of the pages needed extra language files included, add them now.
if (isset($add_lang))
{
	$user->add_lang($add_lang);
}

if ($blog_id)
{
	$subscribed = get_subscription_info($blog_id);
	$subscribed_title = ($subscribed) ? $user->lang['UNSUBSCRIBE_BLOG'] : $user->lang['SUBSCRIBE_BLOG'];
}
else if ($user_id)
{
	$subscribed = get_subscription_info(false, $user_id);
	$subscribed_title = ($subscribed) ? $user->lang['UNSUBSCRIBE_USER'] : $user->lang['SUBSCRIBE_USER'];
}

// Generate the common URL's
generate_blog_urls();

// Include the file(s) we need for the page.
if (!is_array($inc_file))
{
	include($phpbb_root_path . 'blog/' . $inc_file . '.' . $phpEx);
}
else
{
	foreach ($inc_file as $file)
	{
		include($phpbb_root_path . 'blog/' . $file . '.' . $phpEx);
	}
}

// Lets add credits for the User Blog Mod.  This is not the best way to do it, but it makes it so the person installing it has 1 less edit to do per style
// Sounds like the mod team will not accept this, so we are commenting it out for now and having the user just do the extra edit for each style.
//$user->lang['TRANSLATION_INFO'] = (!empty($user->lang['TRANSLATION_INFO'])) ? $user->lang['BLOG_CREDITS'] . '<br />' . $user->lang['TRANSLATION_INFO'] : $user->lang['BLOG_CREDITS'];

// Add some data to the template
$template->assign_vars(array(
	'MODE'					=> $mode,
	'PAGE'					=> $page,
	'BLOG_TITLE'			=> (isset($user_settings[$user_id])) ? censor_text($user_settings[$user_id]['title']) : false,
	'BLOG_DESCRIPTION'		=> (isset($user_settings[$user_id])) ? generate_text_for_display($user_settings[$user_id]['description'], $user_settings[$user_id]['description_bbcode_uid'], $user_settings[$user_id]['description_bbcode_bitfield'], 7) : false,
	'BLOG_CREDITS'			=> $user->lang['BLOG_CREDITS'],

	'U_ADD_BLOG'			=> (check_blog_permissions('blog', 'add', true)) ? $blog_urls['add_blog'] : '',
	'U_BLOG_MCP'			=> ($auth->acl_gets('m_blogapprove', 'm_blogreport', 'm_blogreplyapprove', 'm_blogreplyreport')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=blog') : '',
	'U_BLOG_SELF'			=> $blog_urls['self_minus_print'],
 	'U_REPLY_BLOG'			=> ($blog_id && check_blog_permissions('reply', 'add', true, $blog_id)) ? $blog_urls['add_reply'] : '',
	'U_VIEW_RESULTS'		=> $blog_urls['viewpoll'],

	'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
	'S_POST_ACTION'			=> $blog_urls['self'],
	'S_POLL_ACTION'			=> $blog_urls['vote'],
	'S_PRINT_MODE'			=> $print,
	'S_WATCH_FORUM_TITLE'	=> $subscribed_title,
	'S_WATCH_FORUM_LINK'	=> ($subscribed) ? $blog_urls['unsubscribe'] : (($user->data['user_id'] != $user_id || $blog_id) ? $blog_urls['subscribe'] : ''),
	'S_WATCHING_FORUM'		=> $subscribed,

	'L_USERNAMES_BLOGS'		=> ($username == $user->data['username']) ? $user->lang['MY_BLOG'] : sprintf($user->lang['USERNAMES_BLOGS'], $username),

	'UA_GREY_STAR_SRC'		=> $blog_images_path . 'star_grey.gif',
	'UA_GREEN_STAR_SRC'		=> $blog_images_path . 'star_green.gif',
	'UA_RED_STAR_SRC'		=> $blog_images_path . 'star_red.gif',
	'UA_ORANGE_STAR_SRC'	=> $blog_images_path . 'star_orange.gif',
	'UA_MAX_RATING'			=> $config['user_blog_max_rating'],
	'UA_MIN_RATING'			=> $config['user_blog_min_rating'],

	// Stuff required for subsilver2 based styles
	'REPLY_IMG'				=> $user->img('button_topic_reply', 'POST_A_NEW_REPLY'),
	'POLL_LEFT_CAP_IMG'		=> $user->img('poll_left'),
	'POLL_RIGHT_CAP_IMG'	=> $user->img('poll_right'),
	'REPORT_IMG'			=> $user->img('icon_post_report', 'REPORT_POST'),
	'WARN_IMG'				=> $user->img('icon_user_warn', 'WARN_USER'),
	'DELETE_IMG' 			=> $user->img('icon_post_delete', 'DELETE_POST'),
	'PROFILE_IMG'			=> $user->img('icon_user_profile', 'READ_PROFILE'),
	'PM_IMG' 				=> $user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
	'EMAIL_IMG' 			=> $user->img('icon_contact_email', 'SEND_EMAIL'),
	'EDIT_IMG' 				=> $user->img('icon_post_edit', 'EDIT_POST'),
	'QUOTE_IMG' 			=> $user->img('icon_post_quote', 'REPLY_WITH_QUOTE'),
));

blog_plugins::plugin_do('blog_end');

// setup the page footer
page_footer();
?>