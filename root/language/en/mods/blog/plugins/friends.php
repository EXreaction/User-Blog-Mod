<?php
/**
*
* @package phpBB3 User Blog Friends
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
	'BLOG_FRIENDS_TITLE'			=> 'Friends',
	'BLOG_FRIENDS_DESCRIPTION'		=> 'Adds Friends list to User Blogs',

	'FRIENDS'						=> 'Friends',
	'FRIENDS_ONLINE'				=> 'Friends Online',
	'NO_FRIENDS_ONLINE'				=> 'No Friends Online',
	'FRIENDS_OFFLINE'				=> 'Friends Offline',
	'NO_FRIENDS_OFFLINE'			=> 'No Friends Offline',
));

?>