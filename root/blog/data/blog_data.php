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

/**
 * Blog data class
 *
 * For grabbing/handling all blog data
 */
class blog_data
{
	// this is our large array holding all the data
	var $blog = array();

	/**
	* Get Blogs
	*
	* To select blog information
	*
	* @param string $mode The mode we want
	* @param int $id To input the wanted blog_id, this may be an array if you want to select more than 1
	* @param array $selection_data For extras, like start, limit, order by, order direction, etc, all of the options are listed a few lines below
	*/
	function get_blog_data($mode, $id = 0, $selection_data = array())
	{
		global $db, $user, $phpbb_root_path, $phpEx, $auth, $cache;
		global $blog_data, $reply_data, $user_data, $blog_plugins;

		$blog_plugins->plugin_do_arg_ref('blog_data_start', $selection_data);

		// input options for selection_data
		$start		= (isset($selection_data['start'])) ? $selection_data['start'] :			0;			// the start used in the Limit sql query
		$limit		= (isset($selection_data['limit'])) ? $selection_data['limit'] :			5;			// the limit on how many blogs we will select
		$order_by	= (isset($selection_data['order_by'])) ? $selection_data['order_by'] :		'blog_id';	// the way we want to order the request in the SQL query
		$order_dir	= (isset($selection_data['order_dir'])) ? $selection_data['order_dir'] :	'DESC';		// the direction we want to order the request in the SQL query
		$sort_days	= (isset($selection_data['sort_days'])) ? $selection_data['sort_days'] : 	0;			// the sort days selection
		$deleted	= (isset($selection_data['deleted'])) ? $selection_data['deleted'] : 		false;		// to view only deleted blogs

		// Setup some variables...
		$blog_ids = array(); // this is what get's returned
		$view_unapproved_sql = (check_blog_permissions('blog', 'approve', true)) ? '' : ' AND ( blog_approved = \'1\' OR user_id = \'' . $user->data['user_id'] . '\' )';
		$sort_days_sql = ($sort_days != 0) ? ' AND blog_time >= \'' . (time() - ($sort_days * 86400)) . '\'' : '';
		$user_permission_sql = build_permission_sql($user->data['user_id']);
		$order_by_sql = ' ORDER BY ' . $order_by . ' ' . $order_dir;
		$limit_sql = ($limit > 0) ? ' LIMIT ' . $start . ', ' . $limit : '';

		if (check_blog_permissions('blog', 'undelete', true) && $deleted)
		{
			$view_deleted_sql = ' AND blog_deleted != \'0\'';
		}
		else if (check_blog_permissions('blog', 'undelete', true))
		{
			$view_deleted_sql = '';
		}
		else
		{
			$view_deleted_sql = ' AND ( blog_deleted = \'0\' OR blog_deleted = \'' . $user->data['user_id'] . '\' )';
		}

		// make sure $id is an array for consistency
		if (!is_array($id))
		{
			$id = array($id);
		}

		// Switch for the modes
		switch ($mode)
		{
			case 'user' : // select all the blogs by user(s)
				$sql = 'SELECT * FROM ' . BLOGS_TABLE . '
							WHERE ' . $db->sql_in_set('user_id', $id) .
								 $view_deleted_sql .
									$view_unapproved_sql .
										$sort_days_sql .
											$user_permission_sql .
												$order_by_sql .
													$limit_sql;
				break;
			case 'user_deleted' : // select all the deleted blogs by user(s)
				$order_by_sql = ($order_by_sql != ' ORDER BY blog_id DESC') ? $order_by_sql : ' ORDER BY blog_deleted_time DESC';
				$sql = 'SELECT * FROM ' . BLOGS_TABLE . '
							WHERE ' . $db->sql_in_set('user_id', $id) . '
								AND blog_deleted != \'0\'' .
									$view_unapproved_sql .
										$sort_days_sql .
											$user_permission_sql .
												$order_by_sql .
													$limit_sql;
				break;
			case 'blog' : // select a single blog or blogs (if ID is an array) by the blog_id(s)
				$blogs_to_query = array();

				// check if the blog already exists
				foreach ($id as $i)
				{
					if (!array_key_exists($i, $this->blog) && !in_array($i, $blogs_to_query))
					{
						array_push($blogs_to_query, $i);
					}
					else
					{
						// since the blog was already queried once lets put it on the list of blog_ids that we grabbed that we will return later
						array_push($blog_ids, $i);
					}
				}

				if (count($blogs_to_query) == 0)
				{
					return $blog_ids;
				}

				$sql = 'SELECT * FROM ' . BLOGS_TABLE . '
						WHERE ' . $db->sql_in_set('blog_id', $blogs_to_query) .
							$view_deleted_sql .
								$view_unapproved_sql .
									$sort_days_sql .
										$user_permission_sql .
											$order_by_sql;
				break;
			case 'recent' : // select recent blogs
				$sql = 'SELECT * FROM ' . BLOGS_TABLE .
					$view_deleted_sql .
						$view_unapproved_sql .
							$sort_days_sql .
								$user_permission_sql .
									' ORDER BY blog_time DESC' .
										$limit_sql;
				$sql = fix_where_sql($sql);
				break;
			case 'random' : // select random blogs
				$random_ids = $this->get_blog_info('random_blog_ids', 0, $selection_data);

				if ($random_ids === false)
				{
					return false;
				}

				$this->get_blog_data('blog', $random_ids);
				return $random_ids;
				break;
			case 'popular' : // select popular blogs.
				$sql = 'SELECT * FROM ' . BLOGS_TABLE .
					$view_deleted_sql .
						$view_unapproved_sql .
							$sort_days_sql .
								$user_permission_sql .
									' ORDER BY blog_reply_count DESC, blog_read_count DESC' .
									$limit_sql;
				$sql = fix_where_sql($sql);
				break;
			case 'reported' : // select reported blogs
				if (!$auth->acl_get('m_blogreport'))
				{
					return false;
				}

				$sql = 'SELECT * FROM ' . BLOGS_TABLE . '
					WHERE blog_reported = \'1\'' .
						$sort_days_sql .
							$order_by_sql .
								$limit_sql;
				break;
			case 'disapproved' : // select disapproved blogs
				if (!$auth->acl_get('m_blogapprove'))
				{
					return false;
				}

				$sql = 'SELECT * FROM ' . BLOGS_TABLE . '
					WHERE blog_approved = \'0\'' .
						$sort_days_sql .
							$order_by_sql .
								$limit_sql;
				break;
			default :
				return false;
		}

		$blog_plugins->plugin_do_arg_ref('blog_data_sql', $sql);

		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$blog_plugins->plugin_do_arg_ref('blog_data_while', $row);

			// now put all the data in the blog array
			$this->blog[$row['blog_id']] = $row;

			// add the blog owners' user_ids to the user_queue
			array_push($user_data->user_queue, $row['user_id']);

			// Add the edit user to the user_queue, if there is one
			if ($row['blog_edit_count'] != 0)
			{
				array_push($user_data->user_queue, $row['blog_edit_user']);
			}

			// Add the deleter user to the user_queue, if there is one
			if ($row['blog_deleted'] != 0)
			{
				array_push($user_data->user_queue, $row['blog_deleted']);
			}

			// make sure we don't record the same blog id in the list that we return more than once
			if (!in_array($row['blog_id'], $blog_ids))
			{
				array_push($blog_ids, $row['blog_id']);
			}
		}
		$db->sql_freeresult($result);

		// if there are no blogs, return false
		if (count($blog_ids) == 0)
		{
			return false;
		}

		return $blog_ids;
	}

	/**
	* Get Blog Info
	*
	*  A lot like Get Blog Data, except this handles counting of blog_id's, finding all the blog_id's, etc
	*
	* @param string $mode The mode we want
	* @param int $id The ID we will select (used for misc things like the user_count mode, where we count the # of blogs by a user_id (in that case $id would be the $user_id))
	* @param array $selection_data For extras, like start, limit, order by, order direction, etc, all of the options are listed a few lines below
	*/
	function get_blog_info($mode, $id = 0, $selection_data = array())
	{
		global $db, $cache, $user, $auth;
		global $reply_data, $user_data, $blog_plugins;

		$blog_plugins->plugin_do_arg_ref('blog_info_start', $selection_data);

		// input options for selection_data
		$start		= (isset($selection_data['start'])) ? $selection_data['start'] :			0;			// the start used in the Limit sql query
		$limit		= (isset($selection_data['limit'])) ? $selection_data['limit'] :			5;			// the limit on how many blogs we will select
		$order_by	= (isset($selection_data['order_by'])) ? $selection_data['order_by'] :		'blog_id';	// the way we want to order the request in the SQL query
		$order_dir	= (isset($selection_data['order_dir'])) ? $selection_data['order_dir'] :	'DESC';		// the direction we want to order the request in the SQL query
		$sort_days	= (isset($selection_data['sort_days'])) ? $selection_data['sort_days'] : 	0;			// the sort days selection
		$deleted	= (isset($selection_data['deleted'])) ? $selection_data['deleted'] : 		false;		// to view only deleted blogs

		// Setup some variables...
		$blog_ids = array(); // this is what get's returned
		$view_unapproved_sql = (check_blog_permissions('blog', 'approve', true)) ? '' : ' AND ( blog_approved = \'1\' OR user_id = \'' . $user->data['user_id'] . '\' )';
		$sort_days_sql = ($sort_days != 0) ? ' AND blog_time >= \'' . (time() - ($sort_days * 86400)) . '\'' : '';
		$user_permission_sql = build_permission_sql($user->data['user_id']);
		$order_by_sql = ' ORDER BY ' . $order_by . ' ' . $order_dir;
		$limit_sql = ($limit > 0) ? ' LIMIT ' . $start . ', ' . $limit : '';
		$custom_sql = '';

		if (check_blog_permissions('blog', 'undelete', true) && $deleted)
		{
			$view_deleted_sql = ' AND blog_deleted != \'0\'';
		}
		else if (check_blog_permissions('blog', 'undelete', true))
		{
			$view_deleted_sql = '';
		}
		else
		{
			$view_deleted_sql = ' AND ( blog_deleted = \'0\' OR user_id = \'' . $user->data['user_id'] . '\' )';
		}

		$blog_plugins->plugin_do_arg_ref('blog_info_sql', $custom_sql);

		// Switch for the modes
		switch ($mode)
		{
			case 'random_blog_ids' : // this gets a few random blog_ids
				$random_ids = array();
				$all_blog_ids = $this->get_blog_info('all_ids');
				$total = count($all_blog_ids);

				if ($total == 0)
				{
					return false;
				}

				// if the limit is higher than the total number of blogs, just give them what we have (and shuffle it so it looks random)
				if ($limit > count($all_blog_ids))
				{
					shuffle($all_blog_ids);
					$this->get_blog_data('blog', $all_blog_ids);
					return $all_blog_ids;
				}
				else
				{
					// this is not the most efficient way to do it...but as long as the limit doesn't get too close to the total number of blogs it's fine
					// If the limit is near the total number of blogs we just hope it doesn't take too long (the user should not be requesting many random blogs anyways)
					for ($j = 0; $j < $limit; $j++)
					{
						$random_id = rand(0, $total - 1);

						// make sure the random_id can only be picked once...
						if (!in_array($all_blog_ids[$random_id], $random_ids))
						{
							array_push($random_ids, $all_blog_ids[$random_id]);
						}
						else
						{
							$j--;
						}
					}
				}
				return $random_ids;
			break;
			case 'user_count' : // this only counts the total number of blogs a single user has and returns the count
				$user_permission_sql = build_permission_sql($user->data['user_id']);

				$sql = 'SELECT count(blog_id) AS total FROM ' . BLOGS_TABLE . '
					WHERE user_id = \'' . $id . '\'' .
						$view_deleted_sql .
							$view_unapproved_sql .
								$sort_days_sql .
									$user_permission_sql .
										$custom_sql;
				$result = $db->sql_query($sql);
				$total = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				return $total['total'];
			break;
			case 'all_ids' : // select and return all ID's.  This does not get any data other than the blog_id's.
				$user_permission_sql = build_permission_sql($user->data['user_id']);

				$all_ids = array();
				$sql = 'SELECT blog_id FROM ' . BLOGS_TABLE . '
					WHERE blog_deleted = \'0\'
						AND blog_approved = \'1\'' . 
							$user_permission_sql .
								$custom_sql;
				$result = $db->sql_query($sql);

				while($row = $db->sql_fetchrow($result))
				{
					$all_ids[] = $row['blog_id'];
				}
				$db->sql_freeresult($result);

				return $all_ids;
			break;
			case 'all_deleted_ids' : // get all of the deleted blog_ids
				if ($cache->get('all_deleted_blog_ids') !== false)
				{
					return $cache->get('all_deleted_blog_ids');
				}

				$all_ids = array();
				$sql = 'SELECT blog_id FROM ' . BLOGS_TABLE . '
					WHERE blog_deleted != \'0\'' .
						$custom_sql;
				$result = $db->sql_query($sql);

				while($row = $db->sql_fetchrow($result))
				{
					$all_ids[] = $row['blog_id'];
				}
				$db->sql_freeresult($result);

				// cache the result
				$cache->put('all_deleted_blog_ids', $all_ids);

				return $all_ids;
			break;
			case 'all_unapproved_ids' : //get all of the unapproved blog ids
				if ($cache->get('all_unapproved_blog_ids') !== false)
				{
					return $cache->get('all_unapproved_blog_ids');
				}

				$all_ids = array();
				$sql = 'SELECT blog_id FROM ' . BLOGS_TABLE . '
					WHERE blog_approved = \'0\'' .
						$custom_sql;
				$result = $db->sql_query($sql);

				while($row = $db->sql_fetchrow($result))
				{
					$all_ids[] = $row['blog_id'];
				}
				$db->sql_freeresult($result);

				// cache the result
				$cache->put('all_unapproved_blog_ids', $all_ids);

				return $all_ids;
			break;
			case 'count_blog_ids' : // count the number of blog_ids
				$sql = 'SELECT count(blog_id) AS TOTAL FROM ' . BLOGS_TABLE .
					$view_deleted_sql .
						$view_unapproved_sql .
							$sort_days_sql .
								$custom_sql .
								$order_by_sql;
				$sql = fix_where_sql($sql);
				$cid = $cache->sql_load($sql);
				if ($cid !== false)
				{
					$total = $cache->sql_fetchrow($cid);
				}
				else
				{
					$result = $db->sql_query($sql);
					$cache->sql_save($sql, $result, 31536000);
					$total = $db->sql_fetchrow($result);
				}

				return $total['total'];
			break;
		}
	}

	/**
	 * Handle blog data
	 *
	 * To handle the raw data gotten from the database
	 *
	 * @param int $id The id of the blog we want to handle
	 * @param int|bool $trim_text If we want to trim the text or not(if true we will trim with the setting in $config['user_blog_user_text_limit'], else if it is an integer we will trim the text to that length)
	 */
	function handle_blog_data($id, $trim_text = false)
	{
		global $config, $user, $phpbb_root_path, $phpEx, $auth, $highlight_match;
		global $reply_data, $user_data, $blog_plugins;

		$blog = &$this->blog[$id];
		$user_id = $blog['user_id'];

		$blog_plugins->plugin_do('blog_handle_data_start');

		if ($trim_text !== false)
		{
			$blog_text = trim_text_length($id, false, ($trim_text === true) ? $config['user_blog_user_text_limit'] : intval($trim_text));
			$shortened = ($blog_text === false) ? false : true;
			$blog_text = ($blog_text === false) ? $blog['blog_text'] : $blog_text;
		}
		else
		{
			$blog_text = $blog['blog_text'];
			$shortened = false;
		}

		// censor the text of the subject
		$blog['blog_subject'] = censor_text($blog['blog_subject']);

		if (!$shortened)
		{
			// Parse BBCode and prepare the message for viewing
			$bbcode_options = (($blog['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($blog['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($blog['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
			$blog_text = generate_text_for_display($blog_text, $blog['bbcode_uid'], $blog['bbcode_bitfield'], $bbcode_options);
		}

		// For Highlighting
		if ($highlight_match)
		{
			$blog_text = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $blog_text);
		}

		$reply_count = $reply_data->get_reply_data('reply_count', $id);

		$blog_row = array(	
			'BLOG_ID'			=> $id,
			'BLOG_MESSAGE'		=> $blog_text,
			'DATE'				=> $user->format_date($blog['blog_time']),
			'DELETED_MESSAGE'	=> $blog['deleted_message'],
			'EDIT_REASON'		=> $blog['edit_reason'],
			'EDITED_MESSAGE'	=> $blog['edited_message'],
			'BLOG_EXTRA'		=> '',
			'PUB_DATE'			=> date('r', $blog['blog_time']),
			'REPLIES'			=> ($reply_count != 1) ? ($reply_count == 0) ? sprintf($user->lang['BLOG_REPLIES'], $reply_count, '', '') : sprintf($user->lang['BLOG_REPLIES'], $reply_count, '<a href="' . blog_url($user_id, $id) . '#replies">', '</a>') : sprintf($user->lang['BLOG_REPLY'], '<a href="' . blog_url($user_id, $id) . '#replies">', '</a>'),
			'TITLE'				=> $blog['blog_subject'],
			'USER_FULL'			=> $user_data->user[$user_id]['username_full'],
			'VIEWS'				=> ($blog['blog_read_count'] != 1) ? sprintf($user->lang['BLOG_VIEWS'], ($user->data['user_id'] != $user_id) ? $blog['blog_read_count'] + 1 : $blog['blog_read_count']) : $user->lang['BLOG_VIEW'],

			'U_APPROVE'			=> (check_blog_permissions('blog', 'approve', true, $id) && $blog['blog_approved'] == 0 && !$shortened) ? blog_url($user_id, $id, false, array('page' => 'blog', 'mode' => 'approve')) : '',
			'U_DELETE'			=> (check_blog_permissions('blog', 'delete', true, $id) && !$shortened) ? blog_url($user_id, $id, false, array('page' => 'blog', 'mode' => 'delete')) : '',
			'U_DIGG'			=> (!$shortened) ? 'http://digg.com/submit?phase=2&amp;url=' . urlencode(generate_board_url() . '/blog.' . $phpEx . '?b=' . $blog['blog_id']) : '',
			'U_EDIT'			=> (check_blog_permissions('blog', 'edit', true, $id) && !$shortened) ? blog_url($user_id, $id, false, array('page' => 'blog', 'mode' => 'edit')) : '',
			'U_QUOTE'			=> (check_blog_permissions('reply', 'quote', true, $id) && !$shortened) ? blog_url($user_id, $id, false, array('page' => 'reply', 'mode' => 'quote')) : '',
			'U_REPORT'			=> (check_blog_permissions('blog', 'report', true, $id) && !$shortened) ? blog_url($user_id, $id, false, array('page' => 'blog', 'mode' => 'report')) : '',
			'U_VIEW'			=> blog_url($user_id, $id),
			'U_VIEW_PERMANENT'	=> blog_url($user_id, $id, false, array(), array(), true),
			'U_WARN'			=> (($auth->acl_get('m_warn')) && $user_id != $user->data['user_id'] && $user_id != ANONYMOUS && !$shortened) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=warn&amp;mode=warn_user&amp;u=$user_id", true, $user->session_id) : '',

			'S_DELETED'			=> ($blog['blog_deleted']) ? true : false,
			'S_REPORTED'		=> ($blog['blog_reported'] && ($auth->acl_get('m_blogreport'))) ? true : false,
			'S_SHORTENED'		=> $shortened,
			'S_UNAPPROVED'		=> (!$blog['blog_approved'] && ($user_id == $user->data['user_id'] || $auth->acl_get('m_blogapprove'))) ? true : false,
		);

		$blog_plugins->plugin_do_arg_ref('blog_handle_data_end', $blog_row);

		return $blog_row;
	}
}
?>