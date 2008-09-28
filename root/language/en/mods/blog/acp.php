<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: acp.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	'ACP_BLOG_CATEGORIES_EXPLAIN'			=> 'Here you can add/edit/manage Blog Categories.',
	'ACP_BLOG_PLUGINS_EXPLAIN'				=> 'Here you can enable/disable/install/uninstall plugins for the User Blog Mod.<br /><br />You can also move plugins up/down in the list, which will move them up/down in priority (which ones get shown first).',
	'ALLOWED_IN_BLOG'						=> 'Allowed in User Blogs',
	'ALLOW_IN_BLOG'							=> 'Allow in User Blogs',

	'BLOG_ALWAYS_SHOW_URL'					=> 'Always show blog link in profile',
	'BLOG_ALWAYS_SHOW_URL_EXPLAIN'			=> 'If this is set to no it will not show the Blog link in each users\'s profile unless they have posted a blog.',
	'BLOG_ATTACHMENT_SETTINGS'				=> 'Attachment Settings',
	'BLOG_ENABLE_ATTACHMENTS'				=> 'Attachments',
	'BLOG_ENABLE_ATTACHMENTS_EXPLAIN'		=> 'Enable or disable the entire attachments system for Blogs and Blog Replies',
	'BLOG_ENABLE_FEEDS'						=> 'RSS/ATOM/Javascript output feeds',
	'BLOG_ENABLE_RATINGS'					=> 'Blog Ratings',
	'BLOG_ENABLE_RATINGS_EXPLAIN'			=> 'Disable to not allow ratings for Blogs.',
	'BLOG_ENABLE_SEARCH'					=> 'Search',
	'BLOG_ENABLE_SEARCH_EXPLAIN'			=> 'Enable the search system for the User Blog Mod (this search system is separate from the forum search system).',
	'BLOG_ENABLE_SEO'						=> 'SEO Urls',
	'BLOG_ENABLE_SEO_EXPLAIN'				=> 'You MUST have mod rewrite enabled in order for this to work.  If the blog url\'s do not work, disable this.',
	'BLOG_ENABLE_USER_PERMISSIONS'			=> 'User Permissions',
	'BLOG_ENABLE_USER_PERMISSIONS_EXPLAIN'	=> 'Enable the User Permissions system to allow users to specify permissions on a per blog basis (for guests, registered users, foes, and friends).  Administrators and moderators are always allowed to view/reply to blogs.',
	'BLOG_ENABLE_ZEBRA'						=> 'Friend/Foe Sections',
	'BLOG_ENABLE_ZEBRA_EXPLAIN'				=> 'If you disable this, users will not be able to set permissions for friends/foes who view their blog, and a few other things may be disabled.',
	'BLOG_GUEST_CAPTCHA'					=> 'Require Guests to fill out Captcha before posting',
	'BLOG_INFORM'							=> 'Users to inform of reports or posts needing approval via PM',
	'BLOG_INFORM_EXPLAIN'					=> 'Enter the user_id\'s of the users you want to receive a Private Message when a blog or reply is reported, or a blog or reply is newly posted and needs approval.  Separate multiple users by a comma, do not add spaces.',
	'BLOG_MAX_ATTACHMENTS'					=> 'Maximum amount of attachments allowed per post',
	'BLOG_MAX_ATTACHMENTS_EXPLAIN'			=> 'Note that this can be over ridden per user in user permissions.',
	'BLOG_MAX_RATING'						=> 'Maximum Blog Rating',
	'BLOG_MAX_RATING_EXPLAIN'				=> 'The maximum rating allowed to be given.',
	'BLOG_MESSAGE_FROM'						=> 'Messages sent from',
	'BLOG_MESSAGE_FROM_EXPLAIN'				=> 'The user_id of the user you want the subscription and notification messages from.  If this user does not exist you will have errors.',
	'BLOG_MIN_RATING'						=> 'Minimum Blog Rating',
	'BLOG_MIN_RATING_EXPLAIN'				=> 'The minimum rating allowed to be given.',
	'BLOG_POST_VIEW_SETTINGS'				=> 'Blog Viewing and Posting Settings',
	'BLOG_QUICK_REPLY'						=> 'Quick Reply',
	'BLOG_QUICK_REPLY_EXPLAIN'				=> 'Enable the display of the quick reply when viewing a blog.',
	'BLOG_SETTINGS'							=> 'User Blog Settings',
	'BLOG_SETTINGS_EXPLAIN'					=> 'Here you can set the settings for the User Blog mod.',

	'CATEGORY_CREATED'						=> 'Category successfully created!',
	'CATEGORY_DELETE'						=> 'Delete Category',
	'CATEGORY_DELETED'						=> 'The category has been successfully deleted!',
	'CATEGORY_DELETE_EXPLAIN'				=> 'Are you sure you want to delete this category?',
	'CATEGORY_DESCRIPTION_EXPLAIN'			=> 'Description of what the category is for.',
	'CATEGORY_EDIT_EXPLAIN'					=> 'Here you can change category settings.',
	'CATEGORY_INDEX'						=> 'Category Index',
	'CATEGORY_NAME_EMPTY'					=> 'You must submit a name for the category',
	'CATEGORY_PARENT'						=> 'Category Parent',
	'CATEGORY_RULES_EXPLAIN'				=> 'Here you may write rules that will be shown above each category.',
	'CATEGORY_SETTINGS'						=> 'Category Settings',
	'CATEGORY_UPDATED'						=> 'Category successfully updated!',
	'CLICK_CHECK_NEW_VERSION'				=> 'Click %shere%s to check for an updated version of the User Blog Mod',
	'CLICK_GET_NEW_VERSION'					=> 'Click %shere%s to get the latest version of the User Blog Mod',
	'CLICK_UPDATE'							=> 'Click %shere%s to update the database for the User Blog Mod',
	'CONTINUE'								=> 'Continue',
	'COPYRIGHT'								=> 'Copyright',
	'CREATE_CATEGORY'						=> 'Create Category',

	'DATABASE_VERSION'						=> 'Database Version',
	'DEFAULT_TEXT_LIMIT'					=> 'Default text limit for main blog pages',
	'DEFAULT_TEXT_LIMIT_EXPLAIN'			=> 'After this amount it will trim the rest of the text out of a message (to shorten it)',
	'DELETE_SUBCATEGORIES'					=> 'Delete Subcategories',

	'EDIT_CATEGORY'							=> 'Edit Category',
	'ENABLE_BLOG_CUSTOM_PROFILES'			=> 'Display custom profile fields in the User Blog pages',
	'ENABLE_SUBSCRIPTIONS'					=> 'User/Blog Subscriptions',
	'ENABLE_SUBSCRIPTIONS_EXPLAIN'			=> 'Allows registered users to subscribe to blogs or users and recieve notifications when a new blog/reply is added where they are subscribed.',
	'ENABLE_USER_BLOG'						=> 'User Blog Mod',
	'ENABLE_USER_BLOG_EXPLAIN'				=> 'Note that the ACP and UCP sections of the User Blog Mod will always stay enabled as long as it is installed (unless you disable or remove those modules).',
	'ENABLE_USER_BLOG_PLUGINS'				=> 'Plugins System',
	'ENABLE_USER_BLOG_PLUGINS_EXPLAIN'		=> 'If you disable this, all currently installed plugins will be disabled, however, note that the Plugins ACP section will still show even if this is disabled.',

	'FILE_VERSION'							=> 'Files Version',

	'INSTALLED_PLUGINS'						=> 'Installed Plugins',

	'LATEST_VERSION'						=> 'Latest Version',

	'MOVE_BLOGS_TO'							=> 'Move Blogs to',
	'MOVE_SUBCATEGORIES_TO'					=> 'Move subcategories to',

	'NOT_ALLOWED_IN_BLOG'					=> 'Not allowed in User Blogs',
	'NO_DESTINATION_CATEGORY'				=> 'No Destination Category',
	'NO_INSTALLED_PLUGINS'					=> 'No Installed Plugins',
	'NO_PARENT'								=> 'No Parent',
	'NO_UNINSTALLED_PLUGINS'				=> 'No Uninstalled Plugins',

	'OUTPUT_CPLINKS_BLOCK'					=> 'Output profile Blog links in Custom Profile Fields',
	'OUTPUT_CPLINKS_BLOCK_EXPLAIN'			=> 'If this is set to No the View Blogs link in each profile will not be outputted using the custom profile fields.  You will need to manually add the links in the template if you wish to display them if this is set to No.',

	'PARENT_NOT_EXIST'						=> 'The selected parent does not exist.',
	'PLUGINS_DISABLED'						=> 'Plugins are disabled.',
	'PLUGINS_NAME'							=> 'Plugin Name',
	'PLUGIN_ACTIVATE'						=> 'Activate',
	'PLUGIN_ALREADY_INSTALLED'				=> 'The selected plugin is already installed.',
	'PLUGIN_DEACTIVATE'						=> 'Deactivate',
	'PLUGIN_INSTALL'						=> 'Install',
	'PLUGIN_NOT_EXIST'						=> 'The selected plugin does not exist.',
	'PLUGIN_NOT_INSTALLED'					=> 'The selected plugin is not installed.',
	'PLUGIN_UNINSTALL'						=> 'Uninstall',
	'PLUGIN_UNINSTALL_CONFIRM'				=> 'Are you sure you want to uninstall this plugin?<br /><strong>This will remove all added data by this mod from the database (so any saved data by it will be lost)!</strong><br /><br />You must manually uninstall any file changes made by this plugin and delete the plugin files to completely remove this plugin.',
	'PLUGIN_UPDATE'							=> 'Update DB',

	'REMOVE_ALL_BLOGS'						=> 'Just delete the category.',

	'SELECT_CATEGORY'						=> 'Select Category',

	'UNINSTALLED_PLUGINS'					=> 'Uninstalled Plugins',
	'USER_TEXT_LIMIT'						=> 'Default text limit for user blog page',
	'USER_TEXT_LIMIT_EXPLAIN'				=> 'Same as Default text limit, except this is for the limit on the View User page',

	'VERSION'								=> 'Version',
));

?>