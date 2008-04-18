<?php
/**
*
* @package phpBB3 User Blog Tags
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
	'BLOG_TAGS_DESCRIPTION'		=> 'Allows users to set tags for blogs.',
	'BLOG_TAGS_TITLE'			=> 'Blog Tags',

	'NO_SEARCH_RESULTS'			=> 'No Entries with that tag have been found.',
	'NO_TAGS'					=> 'The requested tag does not exist.',

	'TAGS'						=> 'Tags',
	'TAGS_EXPLAIN'				=> 'Place each tag you would like to apply to this blog on a new line.',
));

?>