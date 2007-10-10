<?php
/** 
* @package language(permissions)
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*/

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Create a new category named Blog
$lang['permission_cat']['blog'] = 'Blog';

// User Permissions
$lang = array_merge($lang, array(
	'acl_u_blogattach'			=> array('lang' => 'Can post attachments in blogs and replies', 'cat' => 'blog'),
	'acl_u_blognolimitattach'	=> array('lang' => 'Can ignore attachment size and amount limits', 'cat' => 'blog'),
));
?>