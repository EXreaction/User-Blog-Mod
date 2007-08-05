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

// set some initial variables that we will use
$error = $blog_urls = array();
$blog_data = new blog_data();
$bbcode = new bbcode();
$cp = new custom_profile();
$s_hidden_fields = '';

// get some initial data
$submit = (isset($_POST['post'])) ? true : false;
$preview = (isset($_POST['preview'])) ? true : false;
$print = (request_var('view', '') == 'print') ? true : false;
$refresh = (isset($_POST['add_file']) || isset($_POST['delete_file']) || isset($_POST['cancel_unglobalise'])) ? true : false;
$cancel = (isset($_POST['cancel'])) ? true : false;

// get some more initial data
$page = request_var('page', '');
$mode = request_var('mode', '');
$user_id = intval(request_var('u', 0));
$blog_id = intval(request_var('b', 0));
$reply_id = intval(request_var('r', 0));
$feed = request_var('feed', '');
$hilit_words = request_var('hilit', '', true);
$start = intval(request_var('start', 0));
$limit = intval(request_var('limit', ($blog_id || $reply_id) ? ($print) ? 99999 : 10 : 5 ));
$sort_days = request_var('st', ((!empty($user->data['user_post_show_days'])) ? $user->data['user_post_show_days'] : 0));
$sort_key = request_var('sk', 't');
$sort_dir = request_var('sd', ($blog_id || $reply_id) ? 'a' : 'd');
$user_founder = ($user->data['user_type'] == USER_FOUNDER && $config['user_blog_founder_all_perm']) ? true : false;

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
		trigger_error('NO_REPLY');
	}

	$reply_user_id = $blog_data->reply[$reply_id]['user_id'];
	$blog_id = $blog_data->reply[$reply_id]['blog_id'];
}

// get the blog's data if it was requested
if ($blog_id != 0)
{
	if ($blog_data->get_blog_data('blog', $blog_id) === false)
	{
		trigger_error('NO_BLOG');
	}

	$user_id = $blog_data->blog[$blog_id]['user_id'];
}

// add the user_id to the queue
if ($user_id != 0)
{
	array_push($blog_data->user_queue, $user_id);
}

// get the user data for what we have and update the edit and delete info
$blog_data->get_user_data(false, true);
$blog_data->update_edit_delete();

// now that we got the user data, let us set another variable to shorten things up later
$username = ($user_id) ? $blog_data->user[$user_id]['username'] : ($page == 'blog') ? $user->data['username'] : '';

// generate the blog urls
generate_blog_urls();

// check to see if they are trying to view a feed, and make sure they used a variable that we accept for the format
$feed = ( ($feed == 'RSS_0.91') || ($feed == 'RSS_1.0') || ($feed == 'RSS_2.0') || ($feed == 'ATOM') || ($feed == 'JAVASCRIPT') ) ? $feed : false;

// Lets add credits for the User Blog mod...this is not the best way to do it, but it makes it so the person installing it has 1 less edit to do per style
$user->lang['TRANSLATION_INFO'] = (!empty($user->lang['TRANSLATION_INFO'])) ? $user->lang['BLOG_CREDITS'] . '<br/>' . $user->lang['TRANSLATION_INFO'] : $user->lang['BLOG_CREDITS'];

// Add some data to the template
$template->assign_vars(array(
	'MODE'					=> $mode,
	'PAGE'					=> $page,

	'U_ADD_BLOG'			=> (check_blog_permissions('blog', 'add', true)) ? $blog_urls['add_blog'] : '',
	'U_BLOG'				=> ($print) ? generate_board_url() . "/blog.{$phpEx}?b=$blog_id" : $blog_urls['self'],
	'U_BLOG_MCP'			=> ($auth->acl_gets('m_blogapprove', 'm_blogreport', 'm_blogreplyapprove', 'm_blogreplyreport') || $user_founder) ? append_sid("{$phpbb_root_path}blog.$phpEx", 'page=mcp') : '',
 	'U_REPLY_BLOG'			=> ($blog_id != 0 && check_blog_permissions('reply', 'add', true, $blog_id)) ? $blog_urls['add_reply'] : '',

	'S_POST_ACTION'			=> $blog_urls['self'],
	'S_PRINT_MODE'			=> $print,

	'ADD_BLOG_IMG'			=> $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/' . $user->data['user_lang'] . '/button_blog_new.gif',
	'AIM_IMG'				=> $user->img('icon_contact_aim', 'AIM'),
	'DELETE_IMG'			=> $user->img('icon_post_delete', 'DELETE_POST'),
	'EDIT_IMG'				=> $user->img('icon_post_edit', 'EDIT_POST'),
	'EMAIL_IMG'				=> $user->img('icon_contact_email', 'SEND_EMAIL'),
	'ICQ_IMG'				=> $user->img('icon_contact_icq', 'ICQ'),
	'JABBER_IMG'			=> $user->img('icon_contact_jabber', 'JABBER'),
	'MINI_POST_IMG'			=> $user->img('icon_post_target', 'POST'),
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
));
?>