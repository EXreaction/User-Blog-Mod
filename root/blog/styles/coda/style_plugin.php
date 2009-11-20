<?php
if (!defined('IN_PHPBB'))
{
	exit;
}

blog_plugins::add_to_do(array(
	'blog_handle_data_end'		=> array('style_blog_handle_data_end'),
	'reply_handle_data_end'		=> array('style_reply_handle_data_end'),
	'blog_end'					=> array('style_blog_end'),
));


function style_blog_handle_data_end(&$args)
{
	global $user;

	$args['DATE_TOP'] = $user->format_date(blog_data::$blog[$args['ID']]['blog_time'], "d F Y");
	$args['DATE_BOTTOM'] = $user->format_date(blog_data::$blog[$args['ID']]['blog_time'], "h:i a");
}

function style_reply_handle_data_end(&$args)
{
	global $user;

	$args['DATE_TOP'] = $user->format_date(blog_data::$reply[$args['ID']]['reply_time'], "d F Y");
	$args['DATE_BOTTOM'] = $user->format_date(blog_data::$reply[$args['ID']]['reply_time'], "h:i a");
}

function style_blog_end()
{
	global $blog_urls, $template, $user_id;

	$template->assign_vars(array(
		'SHARE_URL'			=> urlencode($blog_urls['self']),
		'USER_BLOG_URL'		=> blog_url($user_id),
	));
}
?>