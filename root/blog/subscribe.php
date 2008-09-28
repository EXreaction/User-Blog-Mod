<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: subscribe.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (!$config['user_blog_subscription_enabled'])
{
	blog_meta_refresh(0, $blog_urls['main'], true);
}

// generate the header
page_header($user->lang['SUBSCRIBE']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['SUBSCRIBE']);

if ($subscribed)
{
	trigger_error('ALREADY_SUBSCRIBED');
}

if (!$user_id && !$blog_id)
{
	trigger_error($user->lang['BLOG_USER_NOT_PROVIDED']);
}

$subscription_types = get_blog_subscription_types();

$display_vars = array(
	'legend1'			=> 'SUBSCRIBE',
);

foreach ($subscription_types as $type => $name)
{
	$display_vars[$type] = array('lang' => $name, 'validate' => 'bool', 'type' => 'checkbox', 'default' => false, 'explain' => false);
}

// Do not add subscription types here.  Add them with the function_get_subscription_types hook.
blog_plugins::plugin_do_ref('subscribe', $display_vars);

include("{$phpbb_root_path}blog/includes/functions_confirm.$phpEx");

$settings = blog_confirm('SUBSCRIBE_BLOG_TITLE', 'SUBSCRIBE_BLOG_CONFIRM', $display_vars);

if (is_array($settings))
{
	blog_plugins::plugin_do('subscribe_confirm');

	$cache->destroy("_blog_subscription_{$user->data['user_id']}");

	foreach ($settings as $mode => $yn)
	{
		if ($yn && array_key_exists($mode, $display_vars))
		{
			$sql_data = array(
				'sub_user_id'	=> $user->data['user_id'],
				'sub_type'		=> (int) $mode,
				'blog_id'		=> (int) $blog_id,
				'user_id'		=> (int) $user_id,
			);

			blog_plugins::plugin_do_ref('subscription_add', $sql_data);

			$sql = 'INSERT INTO ' . BLOGS_SUBSCRIPTION_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);
			$db->sql_query($sql);
		}
	}

	$message = $user->lang['SUBSCRIPTION_ADDED'] . '<br /><br />';
	if ($blog_id)
	{
		$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br />';
		$redirect = $blog_urls['view_blog'];
	}
	else
	{
		$redirect = $blog_urls['view_user'];
	}

	if ($user_id == $user->data['user_id'])
	{
		$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
	}
	else
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', blog_data::$user[$user_id]['username'], '</a>') . '<br />';
		$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
	}

	blog_plugins::plugin_do('subscribe_user_confirm_end');

	blog_meta_refresh(3, $redirect);

	trigger_error($message);
}

?>