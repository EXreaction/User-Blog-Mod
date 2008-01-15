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

// If they did not include the $blog_id give them an error...
if ($blog_id == 0)
{
	trigger_error('BLOG_NOT_EXIST');
}

// Was Cancel pressed? If so then redirect to the appropriate page
if ($cancel)
{
	blog_meta_refresh(0, $blog_urls['view_blog'], true);
}

// check to see if editing this message is locked, or if the one editing it has mod powers
if (blog_data::$blog[$blog_id]['blog_edit_locked'] && !$auth->acl_get('m_blogedit'))
{
	trigger_error('BLOG_EDIT_LOCKED');
}

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['DELETE_BLOG']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['DELETE_BLOG']);

if (blog_data::$blog[$blog_id]['blog_deleted'] != 0 && !$auth->acl_get('a_blogdelete'))
{
	trigger_error('BLOG_ALREADY_DELETED');
}

$display_vars = array();
if ($auth->acl_get('a_blogdelete') && blog_data::$blog[$blog_id]['blog_deleted'] == 0)
{
	$display_vars = array(
		'legend1'			=> 'Hard Delete',
		'hard_delete'		=> array('lang' => 'HARD_DELETE',	'validate' => 'bool',	'type' => 'checkbox',	'default' => false,	'explain' => true),
	);
}
$blog_plugins->plugin_do_ref('blog_delete', $display_vars);

include("{$phpbb_root_path}blog/includes/functions_confirm.$phpEx");

$settings = blog_confirm('DELETE_BLOG', 'DELETE_BLOG_CONFIRM', $display_vars, 'yes/no');

if (is_array($settings))
{
	$blog_plugins->plugin_do('blog_delete_confirm');

	// if it has already been soft deleted, and we want to hard delete it
	if (((isset($settings['hard_delete']) && $settings['hard_delete']) || blog_data::$blog[$blog_id]['blog_deleted'] != 0) && $auth->acl_get('a_blogdelete'))
	{
		// They selected the hard delete checkbox...so we must do a few things.
		if (blog_data::$blog[$blog_id]['blog_deleted'] == 0)
		{
			// Remove the search index
			$blog_search->index_remove($blog_id);

			// Update the blog_count for the user
			$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = blog_count - 1 WHERE user_id = ' . intval($user_id) . ' AND blog_count > 0';
			$db->sql_query($sql);

			// Update the blog_count for all the categories it is in.
			$sql = 'SELECT category_id FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . intval($blog_id);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . ' SET blog_count = blog_count - 1 WHERE category_id = ' . $row['category_id'] . ' AND blog_count > 0';
				$db->sql_query($sql);
			}
		}

		// Delete the Attachments
		$rids = array();
		$sql = 'SELECT reply_id FROM ' . BLOGS_REPLY_TABLE . ' WHERE blog_id = ' . intval($blog_id);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$rids[] = $row['reply_id'];
		}
		$sql = 'SELECT physical_filename FROM ' . BLOGS_ATTACHMENT_TABLE . ' WHERE blog_id = ' . intval($blog_id) . ' OR ' . $db->sql_in_set('reply_id', $rids);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			@unlink($phpbb_root_path . $config['upload_path'] . '/blog_mod/' . $row['physical_filename']);
		}
		$sql = 'DELETE FROM ' . BLOGS_ATTACHMENT_TABLE . ' WHERE blog_id = ' . intval($blog_id) . ' OR ' . $db->sql_in_set('reply_id', $rids);
		$db->sql_query($sql);

		// delete the blog
		$sql = 'DELETE FROM ' . BLOGS_TABLE . ' WHERE blog_id = ' . intval($blog_id);
		$db->sql_query($sql);

		// delete the replies
		$sql = 'DELETE FROM ' . BLOGS_REPLY_TABLE . ' WHERE blog_id = ' . intval($blog_id);
		$db->sql_query($sql);

		// delete from the blogs_in_categories
		$sql = 'DELETE FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . intval($blog_id);
		$db->sql_query($sql);

		// delete from the blogs_ratings
		$sql = 'DELETE FROM ' . BLOGS_RATINGS_TABLE . ' WHERE blog_id = ' . intval($blog_id);
		$db->sql_query($sql);

		// Delete the subscriptions
		$sql = 'DELETE FROM ' . BLOGS_SUBSCRIPTION_TABLE . ' WHERE blog_id = ' . intval($blog_id);
		$db->sql_query($sql);
	}
	else
	{
		// Remove the search index
		$blog_search->index_remove($blog_id);

		// soft delete the blog
		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_deleted = ' . $user->data['user_id'] . ', blog_deleted_time = ' . time() . ' WHERE blog_id = ' . intval($blog_id);
		$db->sql_query($sql);

		// Update the blog_count for the user
		$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = blog_count - 1 WHERE user_id = ' . intval($user_id) . ' AND blog_count > 0';
		$db->sql_query($sql);

		// Update the blog_count for all the categories it is in.
		$sql = 'SELECT category_id FROM ' . BLOGS_IN_CATEGORIES_TABLE . ' WHERE blog_id = ' . intval($blog_id);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . BLOGS_CATEGORIES_TABLE . ' SET blog_count = blog_count - 1 WHERE category_id = ' . $row['category_id'] . ' AND blog_count > 0';
			$db->sql_query($sql);
		}
	}

	handle_blog_cache('delete_blog', $user_id);

	blog_meta_refresh(3, $blog_urls['view_user']);

	$message = $user->lang['BLOG_DELETED'];

	if ($user->data['user_id'] == $user_id)
	{
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
	}
	else
	{
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', $username, '</a>');
		$message .= '<br />' . sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
	}

	trigger_error($message);
}

?>