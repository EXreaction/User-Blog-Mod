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
'SUBSCRIPTION_DEFAULT'	=> 'Default Subscription',
'SUBSCRIPTION_DEFAULT_EXPLAIN' => 'Select which subscription types you would like to recieve by default when someone replies to a blog you posted or replied to.  You can set this on each post you make as well.',
	'BLOG_INSTANT_REDIRECT'					=> 'Instant Redirect',
	'BLOG_INSTANT_REDIRECT_EXPLAIN'			=> 'This will set the User Blog Mod to instantly redirect to the next page instead of displaying the Information page.',

	'FOE_PERMISSIONS'						=> 'Foe Permissions',
	'FRIEND_PERMISSIONS'					=> 'Friend Permissions',

	'GUEST_PERMISSIONS'						=> 'Guest Permissions',

	'NO_PERMISSIONS'						=> 'Can not read or reply to your blogs.',

	'REGISTERED_PERMISSIONS'				=> 'Members Permissions',
	'REPLY_PERMISSIONS'						=> 'Can read and reply to your blogs.',
	'RESYNC_PERMISSIONS'					=> 'Resync Permissions',
	'RESYNC_PERMISSIONS_EXPLAIN'			=> 'Check this if you want to resync all blogs to have the permissions set above.',

	'UCP_BLOG'								=> 'Blog',
	'UCP_BLOG_PERMISSIONS'					=> 'Blog Permissions',
	'UCP_BLOG_PERMISSIONS_EXPLAIN'			=> 'Here you can change the permission settings for your Blog.<br/>Note that the global board permissions override all permissions set here.',
	'UCP_BLOG_SETTINGS'						=> 'Blog Settings',
	'UCP_BLOG_SETTINGS_EXPLAIN'				=> '',
	'UCP_BLOG_TITLE_DESCRIPTION'			=> 'Blog Title and Description',
	'UCP_BLOG_TITLE_DESCRIPTION_EXPLAIN'	=> 'Here you can set the title and description for your blog.',
	'USER_PERMISSIONS_DISABLED'				=> 'The User Permissions System has been disabled by the Administrators.',

	'VIEW_PERMISSIONS'						=> 'Can read your blogs.',
));

?>