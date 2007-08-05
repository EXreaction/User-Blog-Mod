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
* PRIORITY -------------------------------
* option to force prosilver by default
* Resetup the MCP page to have URLS/etc like the Main page
*
* Finish RSS/ATOM/Javascript Output Feed & icons
*
* Add option to subscribe to blogs/users
* follow friend/foe rules to automatically hide or show the replies with the javascript open/close button used for deleted replies
* give option to control users who reply to blogs - all - friends - none
* Attachments
* Polls
*
* LOW PRIORITY --------------------
* Make a plugin function to automatically load files in the plugins/ folder, then put all the following things like search/SEO/gallery in the plugins - Also make a way for this to check for plugins to enable/disable in ACP.
* add in a section for gallery display - make gallery.php hold the main code for it so it can be replaced with the core code later by the user (by just uploading 1 file to install the add on).  All the other code needs to be in place and call the gallery.php file to check.
* Integrate with search - make as an add-on - to enable have one of the instructions for the add-on to be editing a config file in the includes/blog directory
* SEO Url's make as an add-on
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
$user_blog_version = 'A11';

// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/blog');

// check if the User Blog Mod is enabled
if (isset($config['user_blog_enable']) && !$config['user_blog_enable'])
{
	trigger_error('USER_BLOG_MOD_DISABLED');
}

// include the files that we shall need
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
include($phpbb_root_path . 'includes/blog/functions.' . $phpEx);
include($phpbb_root_path . 'includes/blog/permissions.' . $phpEx);
include($phpbb_root_path . 'includes/blog/post_options.' . $phpEx);
include($phpbb_root_path . 'includes/blog/data/blog_data.' . $phpEx);

// Set all of the initial data
require($phpbb_root_path . 'includes/blog/data/initial_data.' . $phpEx);

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
	case 'install' : // to install the User Blog Mod
	case 'update' : // for updating from previous versions of the User Blog Mod
	case 'upgrade' : // for upgrading from other blog modifications
	case 'dev' : // used for special developmental purposes
	case 'resync' : // to resync the blog data
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

// setup the page footer
page_footer();
?>