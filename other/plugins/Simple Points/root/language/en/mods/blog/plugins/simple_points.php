<?php
/**
*
* @package phpBB3 User Blog Simple Points
* @copyright (c) 2008 EXreaction, Lithium Studios
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
	'BLOG_SIMPLE_POINTS_DESCRIPTION'		=> 'Allows users to gain points by posting a new blog or posting a reply.<br />Options for the amount of points given will be shown in ACP->Blog Settings.<br /><strong>You MUST have the <a href="http://www.phpbb.com/community/viewtopic.php?f=70&t=543803">Simple Points Mod</a> installed before you install this plugin.</strong>',
	'BLOG_SIMPLE_POINTS_TITLE'				=> 'Simple Points',

	'POINTS'								=> 'Points',

	'SIMPLE_POINTS_BLOG_POINTS'				=> 'Points Per Blog',
	'SIMPLE_POINTS_BLOG_POINTS_EXPLAIN'		=> 'The number of points a user will gain per approved blog.',
	'SIMPLE_POINTS_CP_POINTS'				=> 'Points display in CP fields',
	'SIMPLE_POINTS_CP_POINTS_EXPLAIN'		=> 'Yes to display the number of points in the user\'s profile on the User Blog pages.',
	'SIMPLE_POINTS_PLUGIN'					=> 'Simple Points Plugin',
	'SIMPLE_POINTS_REPLY_POINTS'			=> 'Points Per Comment',
	'SIMPLE_POINTS_REPLY_POINTS_EXPLAIN'	=> 'The number of points a user will gain per approved comment on a blog.',
));

?>