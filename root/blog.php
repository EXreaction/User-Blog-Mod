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
* Rebuild template/style system
* 
* Information section - MCP
*
* In Blog ACP -> add option to remove orphan blog attachments
*
* Finish Javascript Output Feed & icons - perhaps use the blog_confirm page for the confirm feed page
*
* Rename all the permissions to have more descriptive names?
*
* cash mod plugin
* 
* LOW PRIORITY ------------------------------------------------------------------------------------
*
* OTHER -------------------------------------------------------------------------------------------
* 
* Memorable entry (like a sticky)?
* Left Menu Order
*
* UCP
*	custom CSS coding allowed?
*/

// This will be moved into an option for each user soon...
$blog_template = 'prosilver';

define('IN_BLOG', true);

// The Version #
$user_blog_version = '0.3.38_dev';

// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/blog/common');

// Lets use our own custom template path so we can have our own templates
$template->set_custom_template($phpbb_root_path . 'blog/styles/' . $blog_template, $blog_template);
$blog_content = ''; // Put all of what you want displayed on the page in this.  Make sure it is pre-parsed and all ready to be shown.
$blog_stylesheet = ''; // Put any extra stylesheet information you require in here.

// Some template links we will need...
$template->assign_vars(array(
	'T_BLOG_TEMPLATE_PATH'			=> "{$phpbb_root_path}blog/styles/{$blog_template}",
	'T_BLOG_IMAGESET_PATH'			=> "{$phpbb_root_path}blog/styles/{$blog_template}/images",
	'T_BLOG_IMAGESET_LANG_PATH'		=> "{$phpbb_root_path}blog/styles/{$blog_template}/images/" . $user->data['user_lang'],
));

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

// set some initial variables that we will use
$blog_data = new blog_data();
$blog_attachment = new blog_attachment();
$error = $blog_urls = $zebra_list = $user_settings = array();
$s_hidden_fields = $subscribed_title = '';
$subscribed = false;

blog_plugins::plugin_do('blog_start');

$default = false;
switch ($page)
{
	case 'vote'	: // Vote in a poll
		$default = true; // Setting default to true so that the blog is shown again after voting.
	case 'subscribe' : // subscribe to users/blogs
	case 'unsubscribe' : // unsubscribe from users/blogs
	case 'search' : // blogs search
	case 'resync' : // to resync the blog data
	case 'rate' : // to rate a blog
		$user->add_lang('mods/blog/misc');
		include($phpbb_root_path . 'blog/includes/initial_data.' . $phpEx);
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
		include($phpbb_root_path . "blog/dev/dev.$phpEx");
	break;
	case 'blog' :
	case 'reply' :
		include($phpbb_root_path . 'blog/includes/initial_data.' . $phpEx);
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
	$temp = compact('page', 'mode', 'default');
	blog_plugins::plugin_do_ref('blog_page_switch', $default);
	extract($temp);

	// Check again since a plugin could have used it's own page.
	if ($default)
	{
		// With SEO urls, we make it so that the page could be the username name of the user we want to view...
		if ($page != '' && $page != 'index' && !$category_id)
		{
			$user_id = $blog_data->get_user_data(false, false, $page);

			if ($user_id === false)
			{
				unset($user_id);
			}
		}

		include($phpbb_root_path . 'blog/includes/initial_data.' . $phpEx);
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
}

// assign some common variables before the end of the page
$template->assign_vars(array(
	'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
));

blog_plugins::plugin_do('blog_end');

//$db->sql_report('display');

// Set up the stylesheet
$template->set_filenames(array(
	'stylesheet'		=> 'stylesheet.css',
));
$blog_stylesheet .= $template->assign_display('stylesheet');

// Ok now, anything you want outputted needs to be put in the $blog_content variable.  Should be the entire already parsed page.
$template->set_template();
$template->set_filenames(array(
	'body' => 'blog.html',
));
$template->assign_vars(array(
	'BLOG_CONTENT'		=> $blog_content,
	'BLOG_STYLESHEET'	=> $blog_stylesheet,
));
unset($blog_content, $blog_stylesheet);

// setup the page footer
page_footer();
?>