<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
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
	'USER_BLOG_MOD'						=> 'User Blog Mod',

	'INSTALL_USER_BLOG_MOD'				=> 'Install User Blog Mod',
	'INSTALL_USER_BLOG_MOD_CONFIRM'		=> 'Are you ready to install the User Blog Mod?',
	'UPDATE_USER_BLOG_MOD'				=> 'Update User Blog Mod',
	'UPDATE_USER_BLOG_MOD_CONFIRM'		=> 'Are you ready to update the User Blog Mod?',
	'UNINSTALL_USER_BLOG_MOD'			=> 'Uninstall User Blog Mod',
	'UNINSTALL_USER_BLOG_MOD_CONFIRM'	=> 'Are you ready to uninstall the User Blog Mod?  All settings and data saved by this mod will be removed!',

	'INSTALLING_ARCHIVE_PLUGIN'			=> 'Installing Archive Plugin',
	'SETTING_DEFAULT_PERMISSIONS'		=> 'Setting Default Permissions',
	'ADDING_FIRST_BLOG'					=> 'Adding the first Blog Entry',
	'FIXING_MAX_POLL_OPTIONS'			=> 'Fixing Max Poll Options',
	'FIXING_MISSING_STYLES'				=> 'Resetting any styles which no longer exist.',
	'USE_OLD_UPDATE_SCRIPT'				=> 'Versions prior to 0.9.0 can not be updated using this method, you must use the old update script first, then come back to this to do any further updates.<br />The old update script is located <a href="%s">here</a>.',

	'SUCCESSFULLY_UPDATED_UMIL_RETURN'	=> 'You have successfully updated to 1.0.7.  Because of the new install system for 1.0.8 and beyond, you must finish the update process by going <a href="%s">here</a>.',
));

?>