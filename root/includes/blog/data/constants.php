<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

define('BLOGS_TABLE',				$table_prefix . 'blogs');
define('BLOGS_REPLY_TABLE',			$table_prefix . 'blogs_reply');
define('BLOGS_SUBSCRIPTION_TABLE',	$table_prefix . 'blogs_subscription');
define('BLOGS_ATTACHMENT_TABLE',	$table_prefix . 'blogs_attachment');

?>