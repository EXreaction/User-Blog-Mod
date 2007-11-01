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
*
* LOW PRIORITY ------------------------------------------------------------------------------------
* Search module
*
* Polls
*
* In Blog ACP -> add option to remove orphan blog attachments
*
* Resetup the MCP to actually be in the MCP
*
* Finish Javascript Output Feed & icons - perhaps use the blog_confirm page for the confirm feed page
*
* OTHER -------------------------------------------------------------------------------------------
*
* UCP
*	custom CSS coding allowed?
*	External blog link? (so if the user has a blog somewhere else they can put the URL in to it and it will direct the users there to view the blog).
*
* add in a section for gallery display - plugin
* Integrate with phpbb search - plugin
*
* Finish upgrade page
*/

define('IN_BLOG', true);

// The Version # - later move this to initial_data.php
$user_blog_version = '0.3.23';

// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = ((isset($phpbb_root_path)) ? $phpbb_root_path : './');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
if (isset($config['user_blog_force_prosilver']) && $config['user_blog_force_prosilver'])
{
	$user->setup('mods/blog/blog', 1);
}
else
{
	$user->setup('mods/blog/blog');
}

// Do some stuff that is always required
$page = (!isset($page)) ? request_var('page', '') : $page;
$mode = (!isset($mode)) ? request_var('mode', '') : $mode;

// include some files
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

include($phpbb_root_path . 'blog/functions.' . $phpEx);
include($phpbb_root_path . 'blog/permissions.' . $phpEx);
include($phpbb_root_path . 'blog/data/blog_data.' . $phpEx);
include($phpbb_root_path . 'blog/data/reply_data.' . $phpEx);
include($phpbb_root_path . 'blog/data/user_data.' . $phpEx);
include($phpbb_root_path . 'blog/data/handle_data.' . $phpEx);

// set some initial variables that we will use
$blog_data = new blog_data();
$reply_data = new reply_data();
$user_data = new user_data();
$error = $blog_urls = $zebra_list = $user_settings = array();
$s_hidden_fields = $subscribed_title = '';
$subscribed = false;

// Start loading the plugins
include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);
$blog_plugins = new blog_plugins();
$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';
$blog_plugins->load_plugins();
$blog_plugins->plugin_do('blog_start');

// check if the User Blog Mod is installed/enabled
if (!isset($config['user_blog_enable']) && $user->data['user_type'] == USER_FOUNDER && $page != 'install')
{
	trigger_error(sprintf($user->lang['CLICK_INSTALL_BLOG'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", 'page=install') . '">', '</a>'));
}
else if (isset($config['user_blog_enable']) && !$config['user_blog_enable'])
{
	trigger_error('USER_BLOG_MOD_DISABLED');
}

$default = false;
switch ($page)
{
	case 'subscribe' : // subscribe to users/blogs
	case 'unsubscribe' : // unsubscribe from users/blogs
		include($phpbb_root_path . 'blog/data/initial_data.' . $phpEx);
		check_blog_permissions($page, $mode, false, $blog_id, $reply_id);
	// no break
	case 'install' : // to install the User Blog Mod
	case 'update' : // for updating from previous versions of the User Blog Mod
	case 'upgrade' : // for upgrading from other blog modifications
	case 'dev' : // used for developmental purposes
	case 'resync' : // to resync the blog data
		include($phpbb_root_path . "blog/{$page}.$phpEx");
		break;
	case 'blog' :
	case 'reply' :
		include($phpbb_root_path . 'blog/data/initial_data.' . $phpEx);
		check_blog_permissions($page, $mode, false, $blog_id, $reply_id);

		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include($phpbb_root_path . 'blog/post_options.' . $phpEx);

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
	case 'mcp' : // moderator control panel
		include($phpbb_root_path . 'blog/data/initial_data.' . $phpEx);
		check_blog_permissions($page, $mode, false, $blog_id, $reply_id);

		include($phpbb_root_path . 'blog/view/mcp.' . $phpEx);
		break;
	default :
		$default = true;
}

if ($default)
{
	// If you are adding your own page with this, make sure to set $default to false, otherwise it will load the default page below
	$blog_plugins->plugin_do_arg_ref('blog_page_switch', $default);
}

if ($default)
{
	if ($page != '')
	{
		$user_id = $user_data->get_user_data(false, false, $page);

		if ($user_id === false)
		{
			unset($user_id);
		}
	}

	include($phpbb_root_path . 'blog/data/initial_data.' . $phpEx);
	check_blog_permissions($page, $mode, false, $blog_id, $reply_id);

	if ($blog_id != 0 || $reply_id != 0)
	{
		include($phpbb_root_path . 'blog/view/single.' . $phpEx);
	}
	else if ($user_id != 0)
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