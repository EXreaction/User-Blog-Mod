<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

// get some initial data
$submit = (isset($_POST['post']) || isset($_POST['submit'])) ? true : false;
$preview = (isset($_POST['preview'])) ? true : false;
$print = (request_var('view', '') == 'print') ? true : false;
$refresh = (isset($_POST['add_file']) || isset($_POST['delete_file']) || isset($_POST['cancel_unglobalise'])) ? true : false;
$cancel = (isset($_POST['cancel'])) ? true : false;

// get some more initial data
$user_id = (!isset($user_id)) ? request_var('u', 0) : intval($user_id);
$blog_id = request_var('b', 0);
$reply_id = request_var('r', 0);
$feed = request_var('feed', '');
$hilit_words = request_var('hilit', '', true);
$start = request_var('start', 0);
$sort_days = request_var('st', ((!empty($user->data['user_post_show_days'])) ? $user->data['user_post_show_days'] : 0));
$sort_key = request_var('sk', 't');
$sort_dir = request_var('sd', ($blog_id || $reply_id) ? 'a' : 'd');

if ($page == 'search')
{
	$limit = request_var('limit', 20);
}
else if ($blog_id || $reply_id)
{
	$limit = request_var('limit', 10);
}
else
{
	$limit = request_var('limit', 5);
}

// setting some variables for sorting
$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
$order_dir = ($sort_dir == 'a') ? 'ASC' : 'DESC';

// for highlighting
if ($hilit_words)
{
	$highlight_match = $highlight = '';
	foreach (explode(' ', trim($hilit_words)) as $word)
	{
		if (trim($word))
		{
			$highlight_match .= (($highlight_match != '') ? '|' : '') . str_replace('*', '\w*?', preg_quote($word, '#'));
		}
	}
	$highlight = urlencode($hilit_words);
}
else
{
	$highlight_match = false;
}

// get the replies data if it was requested
if ($reply_id != 0)
{
	if ($blog_data->get_reply_data('reply', $reply_id) === false)
	{
		trigger_error('REPLY_NOT_EXIST');
	}

	$reply_user_id = blog_data::$reply[$reply_id]['user_id'];
	$blog_id = blog_data::$reply[$reply_id]['blog_id'];

	if (intval(request_var('start', -1)) == -1)
	{
		$total_replies = $blog_data->get_reply_data('page', array($blog_id, $reply_id), array('start' => $start, 'limit' => $limit, 'order_dir' => $order_dir, 'sort_days' => $sort_days));
		$start = (intval($total_replies / $limit) * $limit);
	}
}

// get the blog's data if it was requested
if ($blog_id != 0)
{
	if ($blog_data->get_blog_data('blog', $blog_id) === false)
	{
		trigger_error('BLOG_NOT_EXIST');
	}

	$user_id = blog_data::$blog[$blog_id]['user_id'];
	get_zebra_info(array($user->data['user_id'], $user_id));
	//get_user_settings(array($user_id, $user->data['user_id']));

	if (!handle_user_blog_permissions($blog_id))
	{
		trigger_error('NO_PERMISSIONS_READ');
	}

	$subscribed = get_subscription_info($blog_id);
	$subscribed_title = ($subscribed) ? $user->lang['UNSUBSCRIBE_BLOG'] : $user->lang['SUBSCRIBE_BLOG'];
}

if ($user_id != 0)
{
	array_push(blog_data::$user_queue, $user_id);
}

if ($user_id != 0 && $blog_id == 0)
{
	$subscribed = get_subscription_info(false, $user_id);
	$subscribed_title = ($subscribed) ? $user->lang['UNSUBSCRIBE_USER'] : $user->lang['SUBSCRIBE_USER'];

	get_user_settings(array($user_id, $user->data['user_id']));
	get_zebra_info(array($user->data['user_id'], $user_id));
}
else
{
	get_user_settings($user->data['user_id']);
}

// get the user data for what we have and update the edit and delete info
$blog_data->get_user_data(false, true);
update_edit_delete();

// make sure they user they requested exists
if ($user_id != 0 && !array_key_exists($user_id, blog_data::$user))
{
	trigger_error('NO_USER');
}

// now that we got the user data, let us set another variable to shorten things up later
$username = ($user_id != 0) ? blog_data::$user[$user_id]['username'] : '';

// generate the blog urls
generate_blog_urls();

// check to see if they are trying to view a feed, and make sure they used a variable that we accept for the format
$feed = ((($feed == 'RSS_0.91') || ($feed == 'RSS_1.0') || ($feed == 'RSS_2.0') || ($feed == 'ATOM') || ($feed == 'JAVASCRIPT')) && $config['user_blog_enable_feeds']) ? $feed : false;

// Lets add credits for the User Blog mod...this is not the best way to do it, but it makes it so the person installing it has 1 less edit to do per style
$user->lang['TRANSLATION_INFO'] = (!empty($user->lang['TRANSLATION_INFO'])) ? $user->lang['BLOG_CREDITS'] . '<br/>' . $user->lang['TRANSLATION_INFO'] : $user->lang['BLOG_CREDITS'];

// Add some data to the template
$initial_data = array(
	'MODE'					=> $mode,
	'PAGE'					=> $page,
	'BLOG_TITLE'			=> (isset($user_settings[$user_id])) ? censor_text($user_settings[$user_id]['title']) : false,
	'BLOG_DESCRIPTION'		=> (isset($user_settings[$user_id])) ? generate_text_for_display($user_settings[$user_id]['description'], $user_settings[$user_id]['description_bbcode_uid'], $user_settings[$user_id]['description_bbcode_bitfield'], 7) : false,

	'U_ADD_BLOG'			=> (check_blog_permissions('blog', 'add', true)) ? $blog_urls['add_blog'] : '',
	'U_BLOG'				=> $blog_urls['self_minus_print'],
	'U_BLOG_MCP'			=> ($auth->acl_gets('m_blogapprove', 'm_blogreport', 'm_blogreplyapprove', 'm_blogreplyreport')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=blog') : '',
 	'U_REPLY_BLOG'			=> ($blog_id != 0 && check_blog_permissions('reply', 'add', true, $blog_id)) ? $blog_urls['add_reply'] : '',

	'S_POST_ACTION'			=> $blog_urls['self'],
	'S_PRINT_MODE'			=> $print,
	'S_WATCH_FORUM_TITLE'	=> $subscribed_title,
	'S_WATCH_FORUM_LINK'	=> ($subscribed) ? $blog_urls['unsubscribe'] : (($user->data['user_id'] != $user_id || $blog_id) ? $blog_urls['subscribe'] : ''),
	'S_WATCHING_FORUM'		=> $subscribed,

	'UA_GREY_STAR_SRC'		=> $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/blog/star_grey.gif',
	'UA_GREEN_STAR_SRC'		=> $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/blog/star_green.gif',
	'UA_RED_STAR_SRC'		=> $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/blog/star_red.gif',
	'UA_ORANGE_STAR_SRC'	=> $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/blog/star_orange.gif',
	'UA_MAX_RATING'			=> $config['user_blog_max_rating'],
	'UA_MIN_RATING'			=> $config['user_blog_min_rating'],

	'ADD_BLOG_IMG'			=> $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/' . $user->data['user_lang'] . '/button_blog_new.gif',
	'DIGG_IMG'				=> $phpbb_root_path . 'styles/' . $user->theme['theme_path'] . '/theme/images/icon_digg.png',
	'AIM_IMG'				=> $user->img('icon_contact_aim', 'AIM'),
	'DELETE_IMG'			=> $user->img('icon_post_delete', 'DELETE_POST'),
	'EDIT_IMG'				=> $user->img('icon_post_edit', 'EDIT_POST'),
	'EMAIL_IMG'				=> $user->img('icon_contact_email', 'SEND_EMAIL'),
	'ICQ_IMG'				=> $user->img('icon_contact_icq', 'ICQ'),
	'JABBER_IMG'			=> $user->img('icon_contact_jabber', 'JABBER'),
	'MINI_POST_IMG'			=> $user->img('icon_post_target', 'PERMANENT_LINK'),
	'MSN_IMG'				=> $user->img('icon_contact_msnm', 'MSNM'),
	'PM_IMG'				=> $user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
	'PROFILE_IMG'			=> $user->img('icon_user_profile', 'READ_PROFILE'),
	'QUOTE_IMG'				=> $user->img('icon_post_quote', 'QUOTE'),
	'REPLY_BLOG_IMG'		=> $user->img('button_topic_reply', 'REPLY_TO_TOPIC'),
	'REPORT_IMG'			=> $user->img('icon_post_report', 'REPORT_POST'),
	'REPORTED_IMG'			=> $user->img('icon_topic_reported', 'POST_REPORTED'),
	'UNAPPROVED_IMG'		=> $user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),
	'WARN_IMG'				=> $user->img('icon_user_warn', 'WARN_USER'),
	'WWW_IMG'				=> $user->img('icon_contact_www', 'VISIT_WEBSITE'),
	'YIM_IMG'				=> $user->img('icon_contact_yahoo', 'YIM'),
);

$blog_plugins->plugin_do_ref('initial_output', $initial_data);

$template->assign_vars($initial_data);
?>