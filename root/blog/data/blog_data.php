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
	public static $blog = array();

	/**
	* Get Blogs
	*
	* To select blog information
	*
	* @param string $mode The mode we want
	* @param int $id To input the wanted blog_id, this may be an array if you want to select more than 1
	* @param array $selection_data For extras, like start, limit, order by, order direction, etc, all of the options are listed a few lines below
	*/
	public function get_blog_data($mode, $id = 0, $selection_data = array())
	{
		global $db, $user, $phpbb_root_path, $phpEx, $auth;
		global $blog_data, $reply_data, $user_data, $blog_plugins;

		$blog_plugins->plugin_do_arg_ref('blog_data_start', $selection_data);

		// input options for selection_data
		$category_id	= (isset($selection_data['category_id'])) ? $selection_data['category_id'] : 	0;			// The category ID
		$start			= (isset($selection_data['start'])) ? $selection_data['start'] :				0;			// the start used in the Limit sql query
		$limit			= (isset($selection_data['limit'])) ? $selection_data['limit'] :				5;			// the limit on how many blogs we will select
		$order_by		= (isset($selection_data['order_by'])) ? $selection_data['order_by'] :			'default';	// the way we want to order the request in the SQL query
		$order_dir		= (isset($selection_data['order_dir'])) ? $selection_data['order_dir'] :		'DESC';		// the direction we want to order the request in the SQL query
		$sort_days		= (isset($selection_data['sort_days'])) ? $selection_data['sort_days'] : 		0;			// the sort days selection
		$deleted		= (isset($selection_data['deleted'])) ? $selection_data['deleted'] : 			false;		// to view only deleted blogs
		$custom_sql		= (isset($selection_data['custom_sql'])) ? $selection_data['custom_sql'] : 		'';			// here you can add in a custom section to the WHERE part of the query

		// Setup some variables...
		$blog_ids = $to_query = array();

		$sql_array = array(
			'SELECT'	=> '*',
			'FROM'		=> array(
				BLOGS_TABLE	=> array('b'),
			),
			'ORDER_BY'	=> (($order_by != 'default') ? $order_by : 'b.blog_id') . ' ' . $order_dir,
		);
		$sql_where = array();

		if ($category_id)
		{
			$sql_array['LEFT_JOIN'] = array(array(
				'FROM'		=> array(BLOGS_IN_CATEGORIES_TABLE => 'bc'),
				'ON'		=> 'bc.blog_id = b.blog_id',
			));
			$sql_where[] = (is_array($category_id)) ? $db->sql_in_set('bc.category_id', $category_id) : 'bc.category_id = ' . intval($category_id) . '';
		}
		if (!$auth->acl_get('m_blogapprove'))
		{
			$sql_where[] = '(b.blog_approved = 1 OR b.user_id = ' . $user->data['user_id'] . ')';;
		}
		if ($auth->acl_gets('m_blogdelete', 'a_blogdelete') && $deleted)
		{
			$sql_where[] = 'b.blog_deleted != 0';
		}
		else if (!$auth->acl_gets('m_blogdelete', 'a_blogdelete'))
		{
			$sql_where[] = '(b.blog_deleted = 0 OR b.blog_deleted = \'' . $user->data['user_id'] . '\' )';
		}
		if ($sort_days != 0)
		{
			$sql_where[] = 'b.blog_time >= ' . (time() - $sort_days * 86400);
		}
		if ($custom_sql)
		{
			$sql_where[] = $custom_sql;
		}
		if (build_permission_sql($user->data['user_id']))
		{
			$sql_where[] = substr(build_permission_sql($user->data['user_id']), 5);
		}

		// make sure $id is an array for consistency
		if (!is_array($id))
		{
			$id = array(intval($id));
		}

		// Switch for the modes
		switch ($mode)
		{
			case 'user' : // select all the blogs by user(s)
				$sql_where[] =  $db->sql_in_set('b.user_id', $id);
				break;
			case 'user_deleted' : // select all the deleted blogs by user(s)
				if ($order_by == 'default')
				{
					$sql_array['ORDER_BY'] = 'b.blog_deleted_time' . ' ' . $order_dir;
				}
				$sql_where[] =  $db->sql_in_set('b.user_id', $id);
				$sql_where[] =  'b.blog_deleted != 0';
				break;
			case 'blog' : // select a single blog or blogs (if ID is an array) by the blog_id(s)
				foreach ($id as $i)
				{
					if (!array_key_exists($i, self::$blog) && !in_array($id, $to_query))
					{
						$to_query[] = $i;
					}
				}

				if (!count($to_query))
				{
					return;
				}

				$sql_where[] =  $db->sql_in_set('b.blog_id', $to_query);
				$limit = 0;
				break;
			case 'recent' : // select recent blogs
				$sql_array['ORDER_BY'] = 'b.blog_time DESC';
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
				$sql_array['ORDER_BY'] = 'b.blog_reply_count DESC, b.blog_read_count DESC';
				break;
			case 'reported' : // select reported blogs
				if (!$auth->acl_get('m_blogreport'))
				{
					return false;
				}

				$sql_where[] =  'b.blog_reported = 1';
				break;
			case 'disapproved' : // select disapproved blogs
				if (!$auth->acl_get('m_blogapprove'))
				{
					return false;
				}

				$sql_where[] =  'b.blog_approved = 0';
				break;
			default :
				return false;
		}

		$temp = compact('sql_array', 'sql_where');
		$blog_plugins->plugin_do_arg_ref('blog_data_sql', $temp);
		extract($temp);
		unset($temp);

		$sql_array['WHERE'] = implode(' AND ', $sql_where);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		if ($limit)
		{
			$result = $db->sql_query_limit($sql, $limit, $start);
		}
		else
		{
			$result = $db->sql_query($sql);
		}

		while ($row = $db->sql_fetchrow($result))
		{
			$blog_plugins->plugin_do_arg_ref('blog_data_while', $row);

			// Initialize the attachment data
			$row['attachment_data'] = array();

			// now put all the data in the blog array
			self::$blog[$row['blog_id']] = $row;

			// add the blog owners' user_ids to the user_queue
			array_push(user_data::$user_queue, $row['user_id']);

			// Add the edit user to the user_queue, if there is one
			if ($row['blog_edit_count'] != 0)
			{
				array_push(user_data::$user_queue, $row['blog_edit_user']);
			}

			// Add the deleter user to the user_queue, if there is one
			if ($row['blog_deleted'] != 0)
			{
				array_push(user_data::$user_queue, $row['blog_deleted']);
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
	public function get_blog_info($mode, $id = 0, $selection_data = array())
	{
		global $db, $user, $auth;
		global $reply_data, $user_data, $blog_plugins;

		$blog_plugins->plugin_do_arg_ref('blog_info_start', $selection_data);

		// input options for selection_data
		$category_id	= (isset($selection_data['category_id'])) ? $selection_data['category_id'] : 	0;			// The category ID
		$start			= (isset($selection_data['start'])) ? $selection_data['start'] :				0;			// the start used in the Limit sql query
		$limit			= (isset($selection_data['limit'])) ? $selection_data['limit'] :				5;			// the limit on how many blogs we will select
		$order_by		= (isset($selection_data['order_by'])) ? $selection_data['order_by'] :			'default';	// the way we want to order the request in the SQL query
		$order_dir		= (isset($selection_data['order_dir'])) ? $selection_data['order_dir'] :		'DESC';		// the direction we want to order the request in the SQL query
		$sort_days		= (isset($selection_data['sort_days'])) ? $selection_data['sort_days'] :	 	0;			// the sort days selection
		$deleted		= (isset($selection_data['deleted'])) ? $selection_data['deleted'] : 			false;		// to view only deleted blogs
		$custom_sql		= (isset($selection_data['custom_sql'])) ? $selection_data['custom_sql'] : 		'';			// here you can add in a custom section to the WHERE part of the query

		// Setup some variables...
		$blog_ids = array(); // this is what get's returned

		$sql_array = array(
			'SELECT'	=> '*',
			'FROM'		=> array(
				BLOGS_TABLE	=> array('b'),
			),
			'ORDER_BY'	=> (($order_by != 'default') ? $order_by : 'b.blog_id') . ' ' . $order_dir,
		);
		$sql_where = array();

		if ($category_id)
		{
			$sql_array['LEFT_JOIN'] = array(array(
				'FROM'		=> array(BLOGS_IN_CATEGORIES_TABLE => 'bc'),
				'ON'		=> 'bc.blog_id = b.blog_id',
			));
			$sql_where[] = (is_array($category_id)) ? $db->sql_in_set('bc.category_id', $category_id) : 'bc.category_id = \'' . $category_id . '\'';
		}
		if (!$auth->acl_get('m_blogapprove'))
		{
			$sql_where[] = '(b.blog_approved = 1 OR b.user_id = ' . $user->data['user_id'] . ')';;
		}
		if ($auth->acl_gets('m_blogdelete', 'a_blogdelete') && $deleted)
		{
			$sql_where[] = 'b.blog_deleted != 0';
		}
		else if (!$auth->acl_gets('m_blogdelete', 'a_blogdelete'))
		{
			$sql_where[] = '(b.blog_deleted = 0 OR b.blog_deleted = ' . $user->data['user_id'] . ')';
		}
		if ($sort_days != 0)
		{
			$sql_where[] = 'b.blog_time >= ' . (time() - $sort_days * 86400);
		}
		if ($custom_sql)
		{
			$sql_where[] = $custom_sql;
		}
		if (build_permission_sql($user->data['user_id']))
		{
			$sql_where[] = substr(build_permission_sql($user->data['user_id']), 5);
		}

		$temp = compact('sql_array', 'sql_where');
		$blog_plugins->plugin_do_arg_ref('blog_info_sql', $temp);
		extract($temp);

		// Switch for the modes
		switch ($mode)
		{
			case 'random_blog_ids' : // this gets a few random blog_ids
				$random_ids = array();
				$all_blog_ids = $this->get_blog_info('all_ids', 0, $selection_data);
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

				$sql_array['SELECT'] = 'count(b.blog_id) AS total';
				$sql_where[] = 'b.user_id = ' . intval($id);

				$sql_array['WHERE'] = implode(' AND ', $sql_where);
				$sql = $db->sql_build_query('SELECT', $sql_array);

				$result = $db->sql_query($sql);
				$total = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				return $total['total'];
			break;
			case 'all_ids' : // select and return all ID's.  This does not get any data other than the blog_id's.
				$all_ids = array();
				$sql_array['SELECT'] = 'b.blog_id';
				$sql_array['WHERE'] = implode(' AND ', $sql_where);
				$sql = $db->sql_build_query('SELECT', $sql_array);

				$result = $db->sql_query($sql);
				while($row = $db->sql_fetchrow($result))
				{
					$all_ids[] = $row['blog_id'];
				}
				$db->sql_freeresult($result);

				return $all_ids;
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
	public function handle_blog_data($id, $trim_text = false)
	{
		global $config, $user, $phpbb_root_path, $phpEx, $auth, $highlight_match;
		global $blog_attachment, $reply_data, $user_data, $blog_plugins, $category_id;

		$blog = &self::$blog[$id];
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
		$blog_subject = censor_text($blog['blog_subject']);

		// Parse BBCode and prepare the message for viewing
		$bbcode_options = (($blog['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($blog['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($blog['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
		$blog_text = generate_text_for_display($blog_text, $blog['bbcode_uid'], $blog['bbcode_bitfield'], $bbcode_options);

		if (!$shortened && $config['user_blog_enable_ratings'])
		{
			$rating_data = get_user_blog_rating_data($user->data['user_id']);
			$rate_url = blog_url($user_id, $id, false, array('page' => 'rate', 'rating' => '*rating*'));
			$delete_rate_url = blog_url($user_id, $id, false, array('page' => 'rate', 'delete' => $id));
		}

		// For Highlighting
		if ($highlight_match)
		{
			$blog_subject = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $blog_subject);
			$blog_text = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $blog_text);
		}

		$reply_count = $reply_data->get_reply_data('reply_count', $id);

		$blog['blog_read_count'] = ($user->data['user_id'] != $user_id) ? $blog['blog_read_count'] + 1 : $blog['blog_read_count'];

		// Attachments
		$update_count = array();
		$blog_attachment->parse_attachments_for_view($blog_text, $blog['attachment_data'], $update_count);

		$blog_row = array(	
			'BLOG_ID'				=> $id,
			'BLOG_MESSAGE'			=> $blog_text,
			'DATE'					=> $user->format_date($blog['blog_time']),
			'DELETED_MESSAGE'		=> $blog['deleted_message'],
			'EDIT_REASON'			=> $blog['edit_reason'],
			'EDITED_MESSAGE'		=> $blog['edited_message'],
			'BLOG_EXTRA'			=> '',
			'PUB_DATE'				=> date('r', $blog['blog_time']),
			'REPLIES'				=> '<a href="' . blog_url($user_id, $id, false, array('anchor' => 'replies')) . '">' . (($reply_count == 1) ? $user->lang['ONE_COMMENT'] : sprintf($user->lang['CNT_COMMENTS'], $reply_count)) . '</a>',
			'TITLE'					=> $blog_subject,
			'USER_FULL'				=> user_data::$user[$user_id]['username_full'],
			'VIEWS'					=> ($blog['blog_read_count'] == 1) ? $user->lang['ONE_VIEW'] : sprintf($user->lang['CNT_VIEWS'], $blog['blog_read_count']),
			'RATING_STRING'			=> (!$shortened && $config['user_blog_enable_ratings']) ? get_star_rating($rate_url, $delete_rate_url, $blog['rating'], $blog['num_ratings'], ((isset($rating_data[$id])) ? $rating_data[$id] : false), (($user->data['user_id'] == $user_id) ? true : false)) : false,

			'U_APPROVE'				=> (check_blog_permissions('blog', 'approve', true, $id) && $blog['blog_approved'] == 0 && !$shortened) ? blog_url(false, $id, false, array('page' => 'blog', 'mode' => 'approve')) : '',
			'U_DELETE'				=> (check_blog_permissions('blog', 'delete', true, $id) && !$shortened) ? blog_url($user_id, $id, false, array('page' => 'blog', 'mode' => 'delete')) : '',
			'U_DIGG'				=> (!$shortened) ? 'http://digg.com/submit?phase=2&amp;url=' . urlencode(generate_board_url() . '/blog.' . $phpEx . '?b=' . $blog['blog_id']) : '',
			'U_EDIT'				=> (check_blog_permissions('blog', 'edit', true, $id) && !$shortened) ? blog_url(false, $id, false, array('page' => 'blog', 'mode' => 'edit')) : '',
			'U_QUOTE'				=> (check_blog_permissions('reply', 'quote', true, $id) && !$shortened) ? blog_url(false, $id, false, array('page' => 'reply', 'mode' => 'quote')) : '',
			'U_REPORT'				=> (check_blog_permissions('blog', 'report', true, $id) && !$shortened) ? blog_url(false, $id, false, array('page' => 'blog', 'mode' => 'report')) : '',
			'U_VIEW'				=> blog_url($user_id, $id),
			'U_VIEW_PERMANENT'		=> blog_url(false, $id, false, array(), array(), true),
			'U_WARN'				=> (($auth->acl_get('m_warn')) && $user_id != $user->data['user_id'] && $user_id != ANONYMOUS && !$shortened) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=warn&amp;mode=warn_user&amp;u=$user_id", true, $user->session_id) : '',

			'S_DELETED'				=> ($blog['blog_deleted']) ? true : false,
			'S_REPORTED'			=> ($blog['blog_reported'] && ($auth->acl_get('m_blogreport'))) ? true : false,
			'S_SHORTENED'			=> $shortened,
			'S_UNAPPROVED'			=> (!$blog['blog_approved'] && ($user_id == $user->data['user_id'] || $auth->acl_get('m_blogapprove'))) ? true : false,
			'S_DISPLAY_NOTICE'		=> (!$auth->acl_get('u_download') && $blog['blog_attachment'] && count($blog['attachment_data'])) ? true : false,
			'S_HAS_ATTACHMENTS'		=> ($blog['blog_attachment']) ? true : false,
		);

		$blog_plugins->plugin_do_arg_ref('blog_handle_data_end', $blog_row);

		return $blog_row;
	}
}
?>