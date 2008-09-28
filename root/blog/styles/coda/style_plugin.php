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
	$args['DATE_TOP'] = date("d F Y", blog_data::$blog[$args['ID']]['blog_time']);
	$args['DATE_BOTTOM'] = date("h:i a", blog_data::$blog[$args['ID']]['blog_time']);
}

function style_reply_handle_data_end(&$args)
{
	$args['DATE_TOP'] = date("d F Y", blog_data::$reply[$args['ID']]['reply_time']);
	$args['DATE_BOTTOM'] = date("h:i a", blog_data::$reply[$args['ID']]['reply_time']);
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