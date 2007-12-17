<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB') || !defined('IN_BLOG_INSTALL'))
{
	exit;
}

/*
* Add config data
*/
set_config('user_blog_enable', 1);
set_config('user_blog_custom_profile_enable', 0);
set_config('user_blog_text_limit', '50');
set_config('user_blog_user_text_limit', '500');
set_config('user_blog_inform', '2');
set_config('user_blog_always_show_blog_url', 0);
set_config('user_blog_force_style', 0);
set_config('user_blog_subscription_enabled', 1);
set_config('user_blog_enable_zebra', 1);
set_config('user_blog_enable_feeds', 1);
set_config('user_blog_enable_plugins', 1);
set_config('user_blog_seo', 0);
set_config('user_blog_guest_captcha', 1);
set_config('user_blog_user_permissions', 1);
set_config('user_blog_search', 1);
set_config('user_blog_search_type', 'fulltext_native');

set_config('user_blog_version', $user_blog_version);
?>