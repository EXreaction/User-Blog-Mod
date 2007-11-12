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
	'BACKUP_NOTICE'					=> 'Make sure you back up ALL of your data from BOTH forums BEFORE you attempt the upgrade.  If anything goes wrong during the upgrade process and you did not back the data up you may lose everything.',
	'BLOG_CLEANUP_CONVERT_COMPLETE'	=> 'The tables have been cleaned successfully.',
	'BLOG_CONVERT_COMPLETE'			=> 'The blogs have been successfully converted.',
	'BLOG_PRE_CONVERT_COMPLETE'		=> 'All pre-conversion steps have successfully been completed. You may now begin the actual conversion process. Please note that you may have to manually do and adjust several things after the automatic conversion, especially check the permissions assigned.',
	'BREAK_CONTINUE_NOTICE'			=> 'Step %1$s Part %2$s has been completed, but there are more parts that need to be finished before the converter is finished for this step.<br/>Click continue below if you are not automatically redirected to the next page.',

	'CLEANUP'						=> 'Cleanup',
	'CONVERTED_BLOG_IDS_MISSING'	=> 'The converted blog ids are missing from the cache.  Please restore your backup data to the database and retry the upgrade.',
	'CONVERT_BLOGS'					=> 'Convert Blogs',
	'CONVERT_COMPLETE'				=> 'The conversion process has been completed successfully!  Please make sure you get backups of your finished conversion and check over the converted settings and data to be sure it is correct.',
	'CONVERT_FOE'					=> 'Convert Foes',
	'CONVERT_FOES'					=> 'Convert Foes',
	'CONVERT_FOE_EXPLAIN'			=> 'Converts the users in the weblog_blocked to foes.',
	'CONVERT_FRIEND'				=> 'Convert Friends',
	'CONVERT_FRIENDS'				=> 'Convert Friends',
	'CONVERT_FRIEND_EXPLAIN'		=> 'Converts the users in the weblog_friends to friends.',
	'CONVERT_REMAINING'				=> 'Convert Remaining',
	'CONVERT_REPLIES'				=> 'Convert Replies',

	'DATA_CONFIRMED'				=> 'The submitted information has been confirmed and we have successfully connected to the database!',
	'DB_TABLE_NOT_EXIST'			=> 'The %s table is missing from the selected database.',

	'FINAL'							=> 'Final',
	'FOE_CONVERT_COMPLETE'			=> 'The foes list has been successfully converted.',
	'FRIEND_CONVERT_COMPLETE'		=> 'The friends list has been successfully converted.',

	'INDEX_BLOG_CONVERT_COMPLETE'	=> 'The blogs have now been indexed for the search system.',
	'INDEX_REPLY_CONVERT_COMPLETE'	=> 'The replies have now been indexed for the search system.',

	'LIMIT_EXPLAIN'					=> 'Number of items to do at one time.  If you set this too high you may have to redo the entire upgrade, but, the lower this is set the longer the upgrade will take.',
	'LIMIT_INCORRECT'				=> 'You must give a limit of at least 1.  It is highly recommended that you use at least 100 for this, but probably not more than 1000, depending on your PHP settings.',

	'NEXT_PART'						=> 'Proceed to next part',
	'NO_STAGE'						=> 'You have not given a stage to work on.',

	'REINDEX_BLOGS'					=> 'Re-Index Blogs',
	'REINDEX_REPLIES'				=> 'Re-Index Replies',
	'REMAINING_CONVERT_COMPLETE'	=> 'The remaining parts have been successfully converted.',
	'REPLY_CONVERT_COMPLETE'		=> 'The replies have been successfully converted.',
	'RESYNC'						=> 'Re-Sync',
	'RESYNC_CONVERT_COMPLETE'		=> 'The User Blog Mod has been Resyncronized.',
	'RETURN_LAST_STEP'				=> 'Click here to return to the last step.',

	'STEP'							=> 'Step %s',
	'SUCCESS'						=> 'Success',

	'TRUNCATE_TABLES'				=> 'Truncate existing tables',
	'TRUNCATE_TABLES_EXPLAIN'		=> 'This will delete all of the data in the existing User Blog Mod tables.  If you select no the new data will be added along with your existing blogs and replies.',

	'UPGRADE_BLOGS'					=> 'Upgrade Blogs',
	'UPGRADE_INFO'					=> 'Version: %1$s; Built by: %2$s',
	'UPGRADE_LIST'					=> 'Upgrade List',
	'UPGRADE_REPLIES'				=> 'Upgrade Replies',
));

?>