<?php
// Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

include($phpbb_root_path . 'blog/functions.' . $phpEx);
include($phpbb_root_path . 'blog/includes/functions_admin.' . $phpEx);

resync_blog('reply_count');
resync_blog('real_reply_count');
resync_blog('user_blog_count');

trigger_error('Done!');
?>