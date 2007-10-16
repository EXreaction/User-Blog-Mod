<?php
/**
 *
 * @package phpBB3 User Blog Custom Title
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
	'BLOG_CUSTOM_TITLE_TITLE'			=> 'Custom Titles',
	'BLOG_CUSTOM_TITLE_DESCRIPTION'		=> 'Adds display for Custom Titles to the User Blog Mod',
));

?>