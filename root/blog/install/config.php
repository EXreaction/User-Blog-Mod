<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
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
set_config('user_blog_text_limit', 200);
set_config('user_blog_user_text_limit', 1000);
set_config('user_blog_inform', '2');
set_config('user_blog_always_show_blog_url', 0);
set_config('user_blog_subscription_enabled', 1);
set_config('user_blog_enable_zebra', 1);
set_config('user_blog_enable_feeds', 1);
set_config('user_blog_enable_plugins', 1);
set_config('user_blog_seo', 0);
set_config('user_blog_guest_captcha', 1);
set_config('user_blog_user_permissions', 1);
set_config('user_blog_search', 1);
set_config('user_blog_search_type', 'fulltext_native');
set_config('user_blog_enable_ratings', 1);
set_config('user_blog_min_rating', 1);
set_config('user_blog_max_rating', 5);
set_config('user_blog_enable_attachments', 1);
set_config('user_blog_max_attachments', 3);
set_config('num_blogs', 1, true);
set_config('num_blog_replies', 0, true);
set_config('user_blog_quick_reply', 1);
set_config('user_blog_links_output_block', 1);
set_config('user_blog_message_from', 2);

?>