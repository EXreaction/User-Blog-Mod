<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: setup.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
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
	'ALREADY_INSTALLED'				=> 'You have already installed the user blog mod.<br /><br />Click %shere%s to return to the main blog page.',
	'ALREADY_UPDATED'				=> 'You are running the latest version of the User Blog Mod.<br /><br />Click %shere%s to return to the main blog page.',

	'BACKUP_NOTICE'					=> 'Make sure you back up ALL of your data from BOTH forums BEFORE you attempt the upgrade.  If anything goes wrong during the upgrade process and you did not back the data up you may lose everything.',

	'CLEANUP'						=> 'Cleanup',
	'CLEANUP_COMPLETE'				=> 'The tables have been cleaned successfully.',
	'CONVERTED_BLOG_IDS_MISSING'	=> 'The converted blog ids are missing from the cache.  Please restore your backup data to the database and retry the upgrade.',
	'CONVERT_COMPLETE'				=> 'The conversion process has been completed successfully.',
	'CONVERT_FOES'					=> 'Convert Foes',
	'CONVERT_FOES_EXPLAIN'			=> 'Converts the users in the weblog_blocked to foes.',
	'CONVERT_FRIENDS'				=> 'Convert Friends',
	'CONVERT_FRIENDS_EXPLAIN'		=> 'Converts the users in the weblog_friends to friends.',

	'DB_TABLE_NOT_EXIST'			=> 'The %s table is missing from the selected database.',

	'FINAL'							=> 'Final',

	'INDEX_COMPLETE'				=> 'The blogs and replies have now been indexed for the search system.',
	'INSTALL_BLOG_DB'				=> 'Install User Blog Mod',
	'INSTALL_BLOG_DB_CONFIRM'		=> 'Are you ready to install the database section of this mod?',
	'INSTALL_BLOG_DB_FAIL'			=> 'Installation of the User Blog Mod failed.<br />Please report the following errors to EXreaction:<br />',
	'INSTALL_BLOG_DB_SUCCESS'		=> 'You have successfully installed the database section of the User Blog mod.<br /><br />Click %shere%s to return to the main User Blogs page.',

	'LIMIT_EXPLAIN'					=> 'Number of items to do at one time.  If you set this too high you may have to redo the entire upgrade, but, the lower this is set the longer the upgrade will take.',
	'LIMIT_INCORRECT'				=> 'You must give a limit of at least 1.  It is highly recommended that you use at least 100 for this, but probably not more than 1000, depending on your PHP settings.',

	'NEXT_PART'						=> 'Proceed to next part',
	'NOT_INSTALLED'					=> 'You must install the User Blog Mod before you run the upgrade script.',
	'NO_STAGE'						=> 'You have not given a stage to work on.',

	'PRE_UPGRADE_COMPLETE'			=> 'All pre-conversion steps have successfully been completed. You may now begin the actual conversion process. Please note that you may have to manually do and adjust several things after the automatic conversion, especially check the permissions assigned.',

	'REINDEX'						=> 'Re-Index',
	'RESYNC'						=> 'Resync',
	'RESYNC_COMPLETE'				=> 'The User Blog Mod has been Resyncronized.',
	'RETURN_LAST_STEP'				=> 'Click here to return to the last step.',

	'SCHEMA_NOT_EXIST'				=> 'The database install schema file is missing.  Please download a fresh copy of this mod and reupload all required files.  If that does not fix the problem, contact EXreaction.',
	'SEARCH_BREAK_CONTINUE_NOTICE'	=> 'Section %1$s of %2$s, Part %3$s of %4$s has been completed, but there are more sections and/or parts that need to be finished before everything is finished.<br />Click continue below if you are not automatically redirected to the next page.',
	'SUCCESS'						=> 'Success',
	'SUCCESSFULLY_UPDATED'			=> 'User blog mod has been updated to %1$s.<br /><br />Click %2$shere%3$s to return to the main blog page.',

	'TRUNCATE_TABLES'				=> 'Truncate existing tables',
	'TRUNCATE_TABLES_EXPLAIN'		=> 'This will delete all of the data in the existing User Blog Mod tables.  If you select no the new data will be added along with your existing blogs and replies.',

	'UNINSTALL_BLOG_DB'				=> 'Uninstall User Blog Mod',
	'UNINSTALL_BLOG_DB_CONFIRM'		=> 'Are you sure you want to remove the User Blog Mod data?<br /><br /><strong>If you do this ALL data from the User Blog Mod will be lost.</strong>',
	'UNINSTALL_BLOG_DB_SUCCESS'		=> 'The User Blog Mod data has been removed from the database.  To completely remove the User Blog Mod you must undo any edits and remove any files you added during the installation.',
	'UPDATE_INSTRUCTIONS'			=> 'Update',
	'UPDATE_INSTRUCTIONS_CONFIRM'	=> 'Make sure you read the upgrade instructions in the MOD History section of the main mod install document first <b>before</b> you do this.<br /><br />Are you ready to upgrade the database for the User Blog Mod?',
	'UPGRADE_BLOGS'					=> 'Upgrade Blogs',
	'UPGRADE_BREAK_CONTINUE_NOTICE'	=> 'Stage %1$s, Section %2$s of %3$s, Part %4$s of %5$s has been completed, but there are more sections and/or parts that need to be finished before the converter is finished for this stage.<br />Click continue below if you are not automatically redirected to the next page.',
	'UPGRADE_COMPLETE'				=> 'The upgrade process has completed successfully!<br />Please make sure you get backups of your finished conversion and check over the converted settings and data to be sure it is correct.',
	'UPGRADE_LIST'					=> 'Upgrade List',
	'UPGRADE_PHP'					=> 'You are running an unsupported PHP version. You must be running PHP 5.1.0 or higher to use this modification.',
	'UPGRADE_REPLIES'				=> 'Upgrade Replies',

	'WELCOME_MESSAGE'				=> 'Welcome to the User Blog Mod!

Release Topic:
http://lithiumstudios.org/forum/viewtopic.php?f=41&t=433

Support by the author will only be given at lithiumstudios.org.

If you have any comments or need support post in this forum:
http://www.lithiumstudios.org/forum/viewforum.php?f=57

Please check the User Blog Mod forum for information before asking for support.
http://www.lithiumstudios.org/forum/viewforum.php?f=41',
	'WELCOME_SUBJECT'				=> 'Welcome to the User Blog Mod!',
));

?>