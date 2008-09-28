<?php
// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'BLOG_NAME'					=> 'Blog Name',
	'BLOG_USERLIST_DESCRIPTION'	=> 'Adds a list of users with blogs.',
	'BLOG_USERLIST_TITLE'		=> 'User List',

	'LAST_BLOG_TIME'			=> 'Last Blog Time',

	'USERLIST'					=> 'User List',
));

?>