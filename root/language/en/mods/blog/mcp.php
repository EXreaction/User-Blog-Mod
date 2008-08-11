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
	'MCP_BLOG_DISAPPROVED_BLOGS_EXPLAIN'	=> 'Here you can view a list of blog entries that need approval.',
	'MCP_BLOG_DISAPPROVED_REPLIES_EXPLAIN'	=> 'Here you can view a list of comments that need approval.',
	'MCP_BLOG_REPORTED_BLOGS_EXPLAIN'		=> 'Here you can view a list of reported blog entries.',
	'MCP_BLOG_REPORTED_REPLIES_EXPLAIN'		=> 'Here you can view a list of reported comments.',
));

?>