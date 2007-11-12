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
	'BACKUP_NOTICE'						=> 'Make sure you back up ALL of your data from BOTH forums BEFORE you attempt the upgrade.  If anything goes wrong during the upgrade process and you did not back the data up you may lose everything.',
	'BLOG_CONVERT_COMPLETE'				=> 'The blogs have been successfully converted.',
	'BLOG_PRE_CONVERT_COMPLETE'			=> 'All pre-conversion steps have successfully been completed. You may now begin the actual conversion process. Please note that you may have to manually do and adjust several things after the automatic conversion, especially check the permissions assigned.',
	'BREAK_CONTINUE_NOTICE'				=> 'Step %1$s Part %1$s has been completed, but there are more parts that need to be finished before the converter is finished for this step.<br/>Click continue below if you are not automatically redirected to the next page.',

	'CONVERTED_BLOG_IDS_MISSING'		=> 'The converted blog ids are missing from the cache.  Please restore your backup data to the database and retry the upgrade.',
	'CONVERT_BLOGS'						=> 'Convert Blogs',
	'CONVERT_COMPLETE'					=> 'The conversion process has been completed successfully!  Please make sure you get backups of your finished conversion and check over the converted settings and data to be sure it is correct.',
	'CONVERT_REMAINING'					=> 'Convert Remaining',

	'DATA_CONFIRMED'					=> 'The submitted information has been confirmed and we have successfully connected to the database!',
	'DB_TABLE_NOT_EXIST'				=> 'The %s table is missing from the selected database.',

	'FINAL'								=> 'Final',

	'INDEX_CONVERT_COMPLETE'			=> 'The blogs and replies have now been indexed for the search system.',

	'NEXT_PART'							=> 'Proceed to next part',
	'NO_STAGE'							=> 'You have not given a stage to work on.',

	'REINDEX'							=> 'Re-Index',
	'REMAINING_CONVERT_COMPLETE'		=> 'The remaining parts have been successfully converted.',
	'RESYNC'							=> 'Re-Sync',
	'RESYNC_CONVERT_COMPLETE'			=> 'The User Blog Mod has been Resyncronized.',
	'RETURN_LAST_STEP'					=> 'Click here to return to the last step.',

	'STEP'								=> 'Step %s',
	'SUCCESS'							=> 'Success',

	'TBM_CONVERT_FRIEND_FOE'			=> 'Convert Friends/Foes',
	'TBM_CONVERT_FRIEND_FOE_EXPLAIN'	=> 'Converts the users in the weblog_friends to friends and the weblog_blocked to foes for each user.',
	'TRUNCATE_TABLES'					=> 'Truncate existing tables',
	'TRUNCATE_TABLES_EXPLAIN'			=> 'This will delete all of the data in the existing User Blog Mod tables.  If you select no the new data will be added along with your existing blogs and replies.',

	'UPGRADE_BLOGS'						=> 'Upgrade Blogs',
	'UPGRADE_INFO'						=> 'Version: %1$s; Built by: %2$s',
	'UPGRADE_LIST'						=> 'Upgrade List',
	'UPGRADE_REPLIES'					=> 'Upgrade Replies',
));

?>