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
* HIGH PRIORITY -------------------------------
*
* LOW PRIORITY --------------------
* Make attachments option an add-on.
*
* In Blog ACP -> add option to remove orphan blog attachments
*
* Resetup the MCP page to have URLS/etc like the Main page
*
* Finish Javascript Output Feed & icons - perhaps use the blog_confirm page for the confirm feed page
*
* give option to control users who reply to blogs - all - friends - none
* Polls
*
* new table to record blog reads (maybe add option to record reads by anonymous users  via IP address as well?)
*	new blogs/replies needing approval notice by Blog MCP link (use the record blog reads for this)
*
* Make a plugin function to automatically load files in the plugins/ folder, then put all the following things like search/SEO/gallery in the plugins - Also make a way for this to check for plugins to enable/disable in ACP.
* add in a section for gallery display - make gallery.php hold the main code for it so it can be replaced with the core code later by the user (by just uploading 1 file to install the add on).  All the other code needs to be in place and call the gallery.php file to check.
* Integrate with search - make as an add-on - to enable have one of the instructions for the add-on to be editing a config file in the includes/blog directory
* SEO Url's make as an add-on (this will come sometime after Handyman finishes his SEO mod)
*
* Make My Blogs link check to see if the user has any blogs posted already (this requires a lot more work permissions side than you'd think).  Make sure to check for the same kind of thing in permissions for the view user page.
*
* OTHER ---------------------------------
* Comments - update function and class comments to the better style, like used in functions.php
*
* Main Blog Page - sections - latest replied to - latest reply?
*
* custom CSS coding allowed?
* customizable blog message to be displayed like forum rules for each user
*
* In late Beta or early RC finish upgrade page
*/

/*
* BUG LIST
* javascript archives in safari 3 & konqueror - I hate Javascript so this is not one of my priorities ATM. :P
*/

/*
* Translators:
* To potential translators - ask EXreaction on http://www.lithiumstudios.org about translations if you would like to translate this mod for other languages.
* Spanish - ecwpa
*
*/

// The Version # - later move this to initial_data.php
$user_blog_version = 'A14';

// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
if ($config['user_blog_force_prosilver'])
{
	$user->setup('mods/blog', 1);
}
else
{
	$user->setup('mods/blog');
}

// check if the User Blog Mod is enabled
if ((isset($config['user_blog_enable']) && !$config['user_blog_enable']) || (!isset($config['user_blog_enable']) && $user->data['user_type'] != USER_FOUNDER))
{
	trigger_error('USER_BLOG_MOD_DISABLED');
}

// include the files that we shall need
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);

// We will set all of the initial data by including this file
include($phpbb_root_path . 'includes/blog/data/initial_data.' . $phpEx);

// check the permissions and see if the user can access this page
check_blog_permissions($page, $mode, false, $blog_id, $reply_id);

switch ($page)
{
	case 'blog' :
		switch ($mode)
		{
			case 'add' :
			case 'edit' :
			case 'delete' :
			case 'undelete' :
			case 'report' :
			case 'approve' :
				include($phpbb_root_path . "includes/blog/blog/{$mode}.$phpEx");
				break;
			default :
				include($phpbb_root_path . 'includes/blog/view/main.' . $phpEx);
		}
		break;
	case 'reply' :
		switch ($mode)
		{
			case 'add' :
			case 'edit' :
			case 'delete' :
			case 'undelete' :
			case 'report' :
			case 'approve' :
				include($phpbb_root_path . "includes/blog/reply/{$mode}.$phpEx");
				break;
			case 'quote' :
				include($phpbb_root_path . "includes/blog/reply/add.$phpEx");
				break;
			default :
				include($phpbb_root_path . 'includes/blog/view/main.' . $phpEx);
		}
		break;
	case 'mcp' : // moderator control panel
		include($phpbb_root_path . 'includes/blog/view/mcp.' . $phpEx);
		break;
	case 'subscribe' : // subscribe to users/blogs
	case 'unsubscribe' : // unsubscribe from users/blogs
	case 'install' : // to install the User Blog Mod
	case 'update' : // for updating from previous versions of the User Blog Mod
	case 'upgrade' : // for upgrading from other blog modifications
	case 'dev' : // used for special developmental purposes
	case 'resync' : // to resync the blog data
	case 'download' : // download a file
		include($phpbb_root_path . "includes/blog/{$page}.$phpEx");
		break;
	default :
		if ($blog_id != 0 || $reply_id != 0)
		{
			include($phpbb_root_path . 'includes/blog/view/blog.' . $phpEx);
		}
		else if ($user_id != 0)
		{
			include($phpbb_root_path . 'includes/blog/view/user.' . $phpEx);
		}
		else
		{
			include($phpbb_root_path . 'includes/blog/view/main.' . $phpEx);
		}
}

// assign some common variables before the end of the page
$template->assign_vars(array(
	'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
));

if ($page != 'download')
{
	// setup the page footer
	page_footer();
}
?>