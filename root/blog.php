<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/*
* TODO List
*
* HIGH PRIORITY -----------------------------------------------------------------------------------
* finish cache cleanup.
* subcategory list wrong
* option to hard delete on delete page
* truncate functions - check if works with DB type
* super cache - cache entire pages
* add place to allow subscription notices when posting a blog/reply (drop down box)
* auto-subscriptions in the UCP (which get selected in the add subscription on add blog/reply page automatically)
* Move email/pm sender to other script so the user does not have to wait for the subscription notices to be sent out.
*  possibly use custom password to verify the script is being called from the board (perhaps the board owner's pw hash or a hash of it)
* force style for UCP/MCP
* option to cut off text at space or endline
*
* LOW PRIORITY ------------------------------------------------------------------------------------
* Information section - MCP
*
* Polls
*
* In Blog ACP -> add option to remove orphan blog attachments
*
* Finish Javascript Output Feed & icons - perhaps use the blog_confirm page for the confirm feed page
*
* OTHER -------------------------------------------------------------------------------------------
* Memorable entry (like a sticky)?
* Left Menu Order
*
* UCP
*	custom CSS coding allowed?
*	External blog link? (so if the user has a blog somewhere else they can put the URL in to it and it will direct the users there to view the blog).
*
* cash mod plugin
* Integrate with phpbb search - plugin
*
*/

define('IN_BLOG', true);

// The Version #
$user_blog_version = '0.3.38_dev';

// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = ((isset($phpbb_root_path)) ? $phpbb_root_path : './');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
if (isset($config['user_blog_force_style']) && $config['user_blog_force_style'] != 0)
{
	$user->setup('mods/blog/common', $config['user_blog_force_style']);
}
else
{
	$user->setup('mods/blog/common');
}

// Get some variables
$page = (!isset($page)) ? request_var('page', '') : $page;
$mode = (!isset($mode)) ? request_var('mode', '') : $mode;
$blog_id = request_var('b', 0);
$reply_id = request_var('r', 0);
$category_id = request_var('c', 0);

// check if the User Blog Mod is installed/enabled
if (!isset($config['user_blog_enable']) && $user->data['user_type'] == USER_FOUNDER && $page != 'install')
{
	trigger_error(sprintf($user->lang['CLICK_INSTALL_BLOG'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", 'page=install') . '">', '</a>'));
}
else if ((!isset($config['user_blog_enable']) || !$config['user_blog_enable']) && $page != 'update' && $page != 'install' && $user->data['user_type'] != USER_FOUNDER)
{
	trigger_error('USER_BLOG_MOD_DISABLED');
}

// include some files
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'blog/functions.' . $phpEx);
include($phpbb_root_path . 'blog/data/blog_data.' . $phpEx);
include($phpbb_root_path . 'blog/data/reply_data.' . $phpEx);
include($phpbb_root_path . 'blog/data/user_data.' . $phpEx);

// set some initial variables that we will use
$blog_data = new blog_data();
$reply_data = new reply_data();
$user_data = new user_data();
$blog_attachment = new blog_attachment();
$error = $blog_urls = $zebra_list = $user_settings = array();
$s_hidden_fields = $subscribed_title = '';
$subscribed = false;

// Start loading the plugins
setup_blog_plugins();
$blog_plugins->plugin_do('blog_start');

$default = false;
switch ($page)
{
	case 'send_subscriptions' : // sends out the subscription notices
		
	break;
	case 'subscribe' : // subscribe to users/blogs
	case 'unsubscribe' : // unsubscribe from users/blogs
	case 'search' : // blogs search
	case 'resync' : // to resync the blog data
	case 'rate' : // to rate a blog
		$user->add_lang('mods/blog/misc');
		include($phpbb_root_path . 'blog/data/initial_data.' . $phpEx);
	// no break
	case 'download' : // to download an attachment
		check_blog_permissions($page, $mode, false, $blog_id, $reply_id);
		include($phpbb_root_path . "blog/{$page}.$phpEx");
	break;
	case 'install' : // to install the User Blog Mod
	case 'update' : // for updating from previous versions of the User Blog Mod
	case 'upgrade' : // for upgrading from other blog modifications
		check_blog_permissions($page, $mode, false, $blog_id, $reply_id);
		$user->add_lang('mods/blog/setup');
		include($phpbb_root_path . "blog/{$page}.$phpEx");
	break;
	case 'dev' : // used for developmental purposes
		check_blog_permissions($page, $mode, false, $blog_id, $reply_id);
		$user->add_lang('mods/blog/setup');
		include($phpbb_root_path . "blog/dev/{$page}.$phpEx");
	break;
	case 'blog' :
	case 'reply' :
		include($phpbb_root_path . 'blog/data/initial_data.' . $phpEx);
		check_blog_permissions($page, $mode, false, $blog_id, $reply_id);
		$user->add_lang(array('posting', 'mods/blog/posting'));

		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include($phpbb_root_path . 'blog/includes/functions_posting.' . $phpEx);

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
				include($phpbb_root_path . "blog/{$page}/{$mode}.$phpEx");
				break;
			case 'quote' :
				include($phpbb_root_path . "blog/reply/add.$phpEx");
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
	// If you are adding your own page with this, make sure to set $default to false if the page matches yours, otherwise it will load the default page below
	$blog_plugins->plugin_do_arg_ref('blog_page_switch', $default);
}

if ($default)
{
	// With SEO urls, we make it so that the page could be the username name of the user we want to view...
	if ($page != '' && $page != 'index' && !$category_id)
	{
		$user_id = $user_data->get_user_data(false, false, $page);

		if ($user_id === false)
		{
			unset($user_id);
		}
	}

	include($phpbb_root_path . 'blog/data/initial_data.' . $phpEx);
	check_blog_permissions($page, $mode, false, $blog_id, $reply_id);
	$user->add_lang('mods/blog/view');

	if ($blog_id || $reply_id)
	{
		include($phpbb_root_path . 'blog/view/single.' . $phpEx);
	}
	else if ($user_id)
	{
		include($phpbb_root_path . 'blog/view/user.' . $phpEx);
	}
	else
	{
		include($phpbb_root_path . 'blog/view/main.' . $phpEx);
	}
}

// assign some common variables before the end of the page
$template->assign_vars(array(
	'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
));

$blog_plugins->plugin_do('blog_end');

//$db->sql_report('display');

// setup the page footer
page_footer();
?>