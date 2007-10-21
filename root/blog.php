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
* Waiting on UCP
*	custom CSS coding allowed?
*	customizable blog message to be displayed like forum rules for each user
*	External blog link? (so if the user has a blog somewhere else they can put the URL in to it and it will direct the users there to view the blog).
*
* add in a section for gallery display - make gallery.php hold the main code for it so it can be replaced with the core code later by the user (by just uploading 1 file to install the add on).  All the other code needs to be in place and call the gallery.php file to check.
* Integrate with search - make as an add-on - to enable have one of the instructions for the add-on to be editing a config file in the includes/blog directory
*
* new table to record blog reads (maybe add option to record reads by anonymous users  via IP address as well?)
*	new blogs/replies needing approval notice by Blog MCP link (use the record blog reads for this)
*
* Make My Blogs link check to see if the user has any blogs posted already (this requires a lot more work permissions side than you'd think).  Make sure to check for the same kind of thing in permissions for the view user page.
*
* Finish upgrade page
*/

/*
* Translators:
* To potential translators - ask EXreaction on http://www.lithiumstudios.org about translations if you would like to translate this mod for other languages.
* Spanish - ecwpa
*
*/

// The Version # - later move this to initial_data.php
$user_blog_version = 'A19_dev';

// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = ((isset($phpbb_root_path)) ? $phpbb_root_path : './');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
if ($config['user_blog_force_prosilver'])
{
	$user->setup('mods/blog/blog', 1);
}
else
{
	$user->setup('mods/blog/blog');
}

// check if the User Blog Mod is enabled
if ((isset($config['user_blog_enable']) && !$config['user_blog_enable']) || (!isset($config['user_blog_enable']) && $user->data['user_type'] != USER_FOUNDER))
{
	trigger_error('USER_BLOG_MOD_DISABLED');
}

// checked for later in the header.php file
define('IN_BLOG', true);

// We will set all of the initial data by including this file
include($phpbb_root_path . 'blog/data/initial_data.' . $phpEx);

// check the permissions and see if the user can access this page
check_blog_permissions($page, $mode, false, $blog_id, $reply_id);

$default = false;

switch ($page)
{
	case 'blog' :
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include($phpbb_root_path . 'blog/post_options.' . $phpEx);

		$message_parser = new parse_message();

		switch ($mode)
		{
			case 'add' :
			case 'edit' :
			case 'delete' :
			case 'undelete' :
			case 'report' :
			case 'approve' :
				include($phpbb_root_path . "blog/blog/{$mode}.$phpEx");
				break;
			default :
				$default = true;
		}
		break;
	case 'reply' :
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include($phpbb_root_path . 'blog/post_options.' . $phpEx);

		$message_parser = new parse_message();

		switch ($mode)
		{
			case 'add' :
			case 'edit' :
			case 'delete' :
			case 'undelete' :
			case 'report' :
			case 'approve' :
				include($phpbb_root_path . "blog/reply/{$mode}.$phpEx");
				break;
			case 'quote' :
				include($phpbb_root_path . "blog/reply/add.$phpEx");
				break;
			default :
				$default = true;
		}
		break;
	case 'mcp' : // moderator control panel
		include($phpbb_root_path . 'blog/view/mcp.' . $phpEx);
		break;
	case 'subscribe' : // subscribe to users/blogs
	case 'unsubscribe' : // unsubscribe from users/blogs
	case 'install' : // to install the User Blog Mod
	case 'update' : // for updating from previous versions of the User Blog Mod
	case 'upgrade' : // for upgrading from other blog modifications
	case 'dev' : // used for developmental purposes
	case 'resync' : // to resync the blog data
		include($phpbb_root_path . "blog/{$page}.$phpEx");
		break;
	default :
		$default = true;
}

if ($default)
{
	// If you are adding your own page with this, make sure to set $default to false, otherwise it will load the default page below
	$blog_plugins->plugin_do_arg('blog_page_switch', $default);
}

if ($default)
{
	if ($blog_id != 0 || $reply_id != 0)
	{
		include($phpbb_root_path . 'blog/view/blog.' . $phpEx);
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

// setup the page footer
page_footer();
?>