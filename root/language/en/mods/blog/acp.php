<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	'ACP_BLOGS'								=> 'User Blog Mod',
	'ACP_BLOG_PLUGINS'						=> 'Blog Plugins',
	'ACP_BLOG_PLUGINS_EXPLAIN'				=> 'Here you can enable/disable/install/uninstall plugins for the User Blog Mod.',
	'ACP_BLOG_SEARCH'						=> 'Blog Search',

	'BLOG_ALWAYS_SHOW_URL'					=> 'Always show blog link in profile',
	'BLOG_ALWAYS_SHOW_URL_EXPLAIN'			=> 'If this is set to no it will not show the Blog link in each users\'s profile unless they have posted a blog.',
	'BLOG_ENABLE_FEEDS'						=> 'Enable RSS/ATOM/Javascript output feeds',
	'BLOG_ENABLE_SEARCH'					=> 'Search',
	'BLOG_ENABLE_SEARCH_EXPLAIN'			=> 'Enable the search system for the User Blog Mod (this search system is separate from the forum search system).',
	'BLOG_ENABLE_SEO'						=> 'SEO Url\'s',
	'BLOG_ENABLE_SEO_EXPLAIN'				=> 'You MUST have mod rewrite enabled in order for this to work.  If the blog url\'s do not work, disable this.',
	'BLOG_ENABLE_USER_PERMISSIONS'			=> 'User Permissions',
	'BLOG_ENABLE_USER_PERMISSIONS_EXPLAIN'	=> 'Enable the User Permissions system to allow users to specify permissions on a per blog basis (for guests, registered users, foes, and friends).  Administrators and moderators are always allowed to view/reply to blogs.',
	'BLOG_ENABLE_ZEBRA'						=> 'Friend/Foe Sections',
	'BLOG_ENABLE_ZEBRA_EXPLAIN'				=> 'If you disable this, users will not be able to set permissions for friends/foes who view their blog, and a few other things may be disabled.',
	'BLOG_FORCE_STYLE'						=> 'Force Style',
	'BLOG_FORCE_STYLE_EXPLAIN'				=> 'If you would like to force the User Blog Mod to use a certain style for viewing blogs, you may enter the style_id here.  Set to 0 to not force any style.',
	'BLOG_GUEST_CAPTCHA'					=> 'Require Guests to fill out Captcha before posting',
	'BLOG_INFORM'							=> 'Users to inform of reports or posts needing approval via PM',
	'BLOG_INFORM_EXPLAIN'					=> 'Enter the user_id\'s of the users you want to receive a Private Message when a blog or reply is reported, or a blog or reply is newly posted and needs approval.  Separate multiple users by a comma, do not add spaces.',
	'BLOG_SETTINGS'							=> 'User Blog Settings',
	'BLOG_SETTINGS_EXPLAIN'					=> 'Here you can set the settings for the User Blog mod.',
	'BREAK_CONTINUE_NOTICE'					=> 'Section %1$s of %2$s, Part %3$s of %4$s has been completed, but there are more sections and/or parts that need to be finished before before we are done.<br/>Click continue below if you are not automatically redirected to the next page.',

	'CLICK_CHECK_NEW_VERSION'				=> 'Click %shere%s to check for an updated version of the User Blog Mod',
	'CONTINUE'								=> 'Continue',

	'DATABASE_VERSION'						=> 'Database Version',
	'DEFAULT_TEXT_LIMIT'					=> 'Default text limit for main blog pages',
	'DEFAULT_TEXT_LIMIT_EXPLAIN'			=> 'After this amount it will trim the rest of the text out of a message (to shorten it)',

	'ENABLE_BLOG_CUSTOM_PROFILES'			=> 'Display custom profile fields in the User Blog pages',
	'ENABLE_SUBSCRIPTIONS'					=> 'User/Blog Subscriptions',
	'ENABLE_SUBSCRIPTIONS_EXPLAIN'			=> 'Allows registered users to subscribe to blogs or users and recieve notifications when a new blog/reply is added where they are subscribed.',
	'ENABLE_USER_BLOG'						=> 'User Blog Mod',
	'ENABLE_USER_BLOG_EXPLAIN'				=> 'Note that the ACP and UCP sections of the User Blog Mod will always stay enabled as long as it is installed (unless you disable or remove those modules).',
	'ENABLE_USER_BLOG_PLUGINS'				=> 'Plugins System',
	'ENABLE_USER_BLOG_PLUGINS_EXPLAIN'		=> 'If you disable this, all currently installed plugins will be disabled, however, note that the Plugins ACP section will still show even if this is disabled.',

	'FILE_VERSION'							=> 'Files Version',

	'INSTALLED_PLUGINS'						=> 'Installed Plugins',

	'NO_UNINSTALLED_PLUGINS'				=> 'No Uninstalled Plugins',

	'PLUGINS_DISABLED'						=> 'Plugins are disabled.',
	'PLUGINS_NAME'							=> 'Plugin Name',
	'PLUGIN_ACTIVATE'						=> 'Activate',
	'PLUGIN_ALREADY_INSTALLED'				=> 'The selected plugin is already installed.',
	'PLUGIN_ALREADY_UPDATED'				=> 'The selected plugin is already updated to the latest version.',
	'PLUGIN_DEACTIVATE'						=> 'Deactivate',
	'PLUGIN_INSTALL'						=> 'Install',
	'PLUGIN_NOT_EXIST'						=> 'The selected plugin does not exist.',
	'PLUGIN_NOT_INSTALLED'					=> 'The selected plugin is not installed.',
	'PLUGIN_UNINSTALL'						=> 'Uninstall',
	'PLUGIN_UNINSTALL_CONFIRM'				=> 'Are you sure you want to uninstall this plugin?<br/><strong>This will remove all added data by this mod from the database (so any saved data by it will be lost)!</strong><br/><br/>You must manually uninstall any file changes made by this plugin and delete the plugin files to completely remove this plugin.',
	'PLUGIN_UPDATE'							=> 'Update DB',

	'UNINSTALLED_PLUGINS'					=> 'Uninstalled Plugins',
	'USER_TEXT_LIMIT'						=> 'Default text limit for user blog page',
	'USER_TEXT_LIMIT_EXPLAIN'				=> 'Same as Default text limit, except this is for the limit on the View User page',

	'VERSION'								=> 'Version',
));

?>