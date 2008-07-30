<?php

function userlist_blog_page_switch(&$args)
{
	if ($args['default'] && $args['page'] == 'userlist')
	{
		$args['default'] = false;
		$args['inc_file'] = 'view/userlist';
	}
}

function userlist_function_generate_menu(&$args)
{
	global $user;

	if (!$args['user_id'])
	{
		$args['links'][] = array(
			'URL'		=> blog_url(false, false, false, array('page' => 'userlist')),
			'NAME'		=> $user->lang['USERLIST'],
			'IMG'		=> 'icon_mini_register.gif',
			'CLASS'		=> 'icon-register',
		);
	}
}
?>