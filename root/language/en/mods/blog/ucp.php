<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: ucp.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
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
	'BLOG_CSS'								=> 'Blog CSS',
	'BLOG_CSS_EXPLAIN'						=> 'Here you may enter any CSS code you would like to format and change the style of your own blog to make it look the way you want.',
	'BLOG_INSTANT_REDIRECT'					=> 'Instant Redirect',
	'BLOG_INSTANT_REDIRECT_EXPLAIN'			=> 'This will set the User Blog Mod to instantly redirect to the next page instead of displaying the Information page.',
	'BLOG_STYLE'							=> 'Blog Style',
	'BLOG_STYLE_EXPLAIN'					=> 'Select the style you want displayed for your blog.<br />If the style has a * after the name you may enter your own CSS to customize it further (if you have permission).',

	'NONE'									=> 'None',
	'NO_PERMISSIONS'						=> 'Can not read or reply to your blog entriess.',

	'REPLY_PERMISSIONS'						=> 'Can read and reply to your blog entries.',
	'RESYNC_PERMISSIONS'					=> 'Resync Permissions',
	'RESYNC_PERMISSIONS_EXPLAIN'			=> 'Check this if you want to resync all blog entries to have the permissions set above.',

	'SUBSCRIPTION_DEFAULT'					=> 'Default Subscription:',
	'SUBSCRIPTION_DEFAULT_EXPLAIN'			=> 'Select which subscription types you would like to recieve by default when someone comments on a blog entry you posted or commented on.  You can set this on each post you make as well.',

	'UCP_BLOG_PERMISSIONS_EXPLAIN'			=> 'Here you can change the permission settings for your Blog.<br />Note that the global board permissions override all permissions set here.',
	'UCP_BLOG_SETTINGS_EXPLAIN'				=> '',
	'UCP_BLOG_TITLE_DESCRIPTION_EXPLAIN'	=> 'Here you can set the title and description for your blog.',
	'USER_PERMISSIONS_DISABLED'				=> 'The User Permissions System has been disabled by the Administrators.',

	'VIEW_PERMISSIONS'						=> 'Can read your blog entries.',
));

?>