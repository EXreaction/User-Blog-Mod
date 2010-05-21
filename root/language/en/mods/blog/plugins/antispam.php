<?php
/**
*
* @package phpBB3 User Blog Anti-Spam
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
	'BLOG_ANTISPAM'				=> 'Anti-Spam ACP plugin',
	'BLOG_ANTISPAM_EXPLAIN'		=> 'Anti-Spam ACP plugin for the User Blog Mod.<br /><br /><strong>This plugin requires that the <a href="http://www.lithiumstudios.org/forum/viewtopic.php?f=31&t=941">Anti-Spam ACP</a> modification is installed.</strong>',

));

?>