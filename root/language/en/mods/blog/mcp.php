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
	'MCP_BLOG'								=> 'Blog',
	'MCP_BLOG_DISAPPROVED_BLOGS'			=> 'Unapproved Blogs',
	'MCP_BLOG_DISAPPROVED_BLOGS_EXPLAIN'	=> 'Here you can view a list of blogs that need approval.',
	'MCP_BLOG_DISAPPROVED_REPLIES'			=> 'Unaproved Replies',
	'MCP_BLOG_DISAPPROVED_REPLIES_EXPLAIN'	=> 'Here you can view a list of replies that need approval.',
	'MCP_BLOG_REPORTED_BLOGS'				=> 'Reported Blogs',
	'MCP_BLOG_REPORTED_BLOGS_EXPLAIN'		=> 'Here you can view a list of reported blogs.',
	'MCP_BLOG_REPORTED_REPLIES'				=> 'Reported Replies',
	'MCP_BLOG_REPORTED_REPLIES_EXPLAIN'		=> 'Here you can view a list of reported replies.',
));

?>