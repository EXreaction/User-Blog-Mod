<?php
/**
*
* @package phpBB3 User Blog Archives
* @version $Id$
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
	'ARCHIVES'					=> 'Archives',

	'BLOG_ARCHIVES_DESCRIPTION'	=> 'Adds Archive list to User Blogs',
));

?>