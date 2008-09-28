<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: functions.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

// Make sure that if this file is accidently included more than once we don't get errors
if (!defined('BLOG_FUNCTIONS_INCLUDED'))
{
	define('BLOG_FUNCTIONS_INCLUDED', true);

	// This is just a mass include file...it includes everything we could need
	include($phpbb_root_path . 'blog/includes/blog_data.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/constants.' . $phpEx);

	include($phpbb_root_path . 'blog/includes/functions.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_categories.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_permissions.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_rate.' . $phpEx);
	include($phpbb_root_path . 'blog/includes/functions_view.' . $phpEx);

	include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);
	new blog_plugins();
}
?>