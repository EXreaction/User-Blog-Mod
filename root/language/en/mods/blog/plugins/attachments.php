<?php
/**
 *
 * @package phpBB3 User Blog Attachments
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
	'BLOG_ATTACHMENT_SETTINGS'			=> 'Attachment Settings (plugin)',
	'BLOG_ENABLE_ATTACHMENTS'			=> 'Enable Attachments in Blogs and Replies',
	'BLOG_MAX_ATTACHMENTS'				=> 'Maximum amount of attachments allowed per post',
	'BLOG_MAX_ATTACHMENTS_EXPLAIN'		=> 'Note that this can be over ridden per user in user permissions.',
));

?>