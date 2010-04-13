<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: blog_data.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

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
	public static $reply = array();
	public static $user = array();

	// this holds a user_queue of the user's data when requesting replies so we can cut down on queries
	public static $user_queue = array();

	/**
	* --------------------------------------------------------------------------------------------------------------------------- BLOGS -----------------------------------------------------------------------------------------------------------------
	*/

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

		blog_plugins::plugin_do_ref('blog_data_start', $selection_data);

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
			'WHERE'		=> array(),
			'ORDER_BY'	=> (($order_by != 'default') ? $order_by : 'b.blog_id') . ' ' . $order_dir,
		);

		if ($category_id)
		{
			$sql_array['LEFT_JOIN'] = array(array(
				'FROM'		=> array(BLOGS_IN_CATEGORIES_TABLE => 'bc'),
				'ON'		=> 'bc.blog_id = b.blog_id',
			));
			$sql_array['WHERE'][] = (is_array($category_id)) ? $db->sql_in_set('bc.category_id', $category_id) : 'bc.category_id = ' . intval($category_id) . '';
		}
		if (!$auth->acl_get('m_blogapprove'))
		{
			if ($user->data['is_registered'])
			{
				$sql_array['WHERE'][] = '(b.blog_approved = 1 OR b.user_id = ' . $user->data['user_id'] . ')';
			}
			else
			{
				$sql_array['WHERE'][] = 'b.blog_approved = 1';
			}
		}
		if ($auth->acl_gets('m_blogdelete', 'a_blogdelete') && $deleted)
		{
			$sql_array['WHERE'][] = 'b.blog_deleted != 0';
		}
		else if (!$auth->acl_gets('m_blogdelete', 'a_blogdelete'))
		{
			$sql_array['WHERE'][] = '(b.blog_deleted = 0 OR b.blog_deleted = ' . $user->data['user_id'] . ')';
		}
		if ($sort_days)
		{
			$sql_array['WHERE'][] = 'b.blog_time >= ' . (time() - $sort_days * 86400);
		}
		if ($custom_sql)
		{
			$sql_array['WHERE'][] = $custom_sql;
		}
		if (build_permission_sql($user->data['user_id']))
		{
			// remove the first AND
			$sql_array['WHERE'][] = substr(build_permission_sql($user->data['user_id'], false, 'b.'), 5);
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
				$sql_array['WHERE'][] =  $db->sql_in_set('b.user_id', $id);
			break;

			case 'user_deleted' : // select all the deleted blogs by user(s)
				if ($order_by == 'default')
				{
					$sql_array['ORDER_BY'] = 'b.blog_deleted_time' . ' ' . $order_dir;
				}
				$sql_array['WHERE'][] =  $db->sql_in_set('b.user_id', $id);
				$sql_array['WHERE'][] =  'b.blog_deleted != 0';
			break;

			case 'blog' : // select a single blog or blogs (if ID is an array) by the blog_id(s)
				// Check to see if the blog has been queried already, if so we can skip it.
				foreach ($id as $i)
				{
					if (!isset(self::$blog[$i]) && !in_array($id, $to_query))
					{
						$to_query[] = $i;
					}
				}

				if (!sizeof($to_query))
				{
					return;
				}

				$sql_array['WHERE'][] =  $db->sql_in_set('b.blog_id', $to_query);
				$limit = 0;
			break;

			case 'last_visit' :
				$sql_array['WHERE'][] = 'b.blog_time >= ' . $user->data['session_last_visit'];
			case 'recent' : // select recent blogs
				if ($order_by == 'default')
				{
					$sql_array['ORDER_BY'] = 'b.blog_time DESC';
				}
			break;

			case 'random' : // select random blogs
				$random_ids = $this->get_blog_data('random_blog_ids', 0, $selection_data);

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

				$sql_array['WHERE'][] =  'b.blog_reported = 1';
			break;

			case 'disapproved' : // select disapproved blogs
				if (!$auth->acl_get('m_blogapprove'))
				{
					return false;
				}

				$sql_array['WHERE'][] =  'b.blog_approved = 0';
			break;

			case 'random_blog_ids' : // this gets a few random blog_ids
				$random_ids = array();
				$all_blog_ids = $this->get_blog_data('all_ids', 0, $selection_data);
				$total = sizeof($all_blog_ids);

				if ($total == 0)
				{
					return false;
				}

				// if the limit is higher than the total number of blogs, just give them what we have (and shuffle it so it looks random)
				if ($limit > sizeof($all_blog_ids))
				{
					shuffle($all_blog_ids);
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

			case 'count' : // this just does a count of the number of blogs
				$sql_array['SELECT'] = 'count(b.blog_id) AS total';
				$sql_array['WHERE'] = implode(' AND ', $sql_array['WHERE']);
				unset($sql_array['ORDER_BY']);
				$sql = $db->sql_build_query('SELECT', $sql_array);

				$result = $db->sql_query($sql);
				$total = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				return $total['total'];
			break;

			case 'all_ids' : // select and return all available ID's.  This does not get any data other than the blog_id's.
				$all_ids = array();
				$sql_array['SELECT'] = 'b.blog_id';
				$sql_array['WHERE'] = implode(' AND ', $sql_array['WHERE']);
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

		blog_plugins::plugin_do_ref('blog_data_sql', $sql_array);

		$sql_array['WHERE'] = implode(' AND ', $sql_array['WHERE']);
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
			blog_plugins::plugin_do_ref('blog_data_while', $row);

			// Initialize the poll data
			$row['poll_options'] = $row['poll_votes'] = array();

			// Initialize the attachment data
			$row['attachment_data'] = array();

			// now put all the data in the blog array
			self::$blog[$row['blog_id']] = $row;

			// add the blog owners' user_ids to the user_queue
			self::$user_queue[] = $row['user_id'];

			// Add the edit user to the user_queue, if there is one
			if ($row['blog_edit_count'])
			{
				self::$user_queue[] = $row['blog_edit_user'];
			}

			// Add the deleter user to the user_queue, if there is one
			if ($row['blog_deleted'])
			{
				self::$user_queue[] = $row['blog_deleted'];
			}

			// make sure we don't record the same blog id in the list that we return more than once
			if (!in_array($row['blog_id'], $blog_ids))
			{
				$blog_ids[] = $row['blog_id'];
			}
		}
		$db->sql_freeresult($result);

		// if there are no blogs, return false
		if (!sizeof($blog_ids))
		{
			return false;
		}

		return $blog_ids;
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
		global $blog_attachment, $category_id;

		if (!isset(self::$blog[$id]))
		{
			return array();
		}

		$blog = &self::$blog[$id];
		$user_id = $blog['user_id'];

		blog_plugins::plugin_do('blog_handle_data_start');

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

		if ($config['user_blog_enable_ratings'])
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

		$reply_count = $this->get_reply_data('reply_count', $id);

		$blog['blog_read_count'] = ($user->data['user_id'] != $user_id) ? $blog['blog_read_count'] + 1 : $blog['blog_read_count'];

		// Polls
		$poll_options = $my_vote = array();
		$total_votes = 0;
		foreach ($blog['poll_votes'] as $option_id => $poll_row)
		{
			if ($option_id != 'my_vote')
			{
				$total_votes += $poll_row['votes'];
			}
			else
			{
				$my_vote = $poll_row;
			}
		}
		foreach ($blog['poll_options'] as $option_id => $poll_row)
		{
			$option_pct = ($total_votes > 0 && isset($blog['poll_votes'][$option_id]['votes'])) ? $blog['poll_votes'][$option_id]['votes'] / $total_votes : 0;
			$option_pct_txt = sprintf("%.1d%%", ($option_pct * 100));

			$poll_options[] = array(
				'POLL_OPTION_ID' 		=> $option_id,
				'POLL_OPTION_CAPTION' 	=> generate_text_for_display($poll_row['poll_option_text'], $blog['bbcode_uid'], $blog['bbcode_bitfield'], $bbcode_options),
				'POLL_OPTION_RESULT' 	=> (isset($blog['poll_votes'][$option_id]['votes'])) ? $blog['poll_votes'][$option_id]['votes'] : 0,
				'POLL_OPTION_PERCENT' 	=> $option_pct_txt,
				'POLL_OPTION_PCT'		=> round($option_pct * 100),
				'POLL_OPTION_IMG' 		=> $user->img('poll_center', $option_pct_txt, round($option_pct * 250)),
				'POLL_OPTION_VOTED'		=> (in_array($option_id, $my_vote)) ? true : false,
			);
		}
		$s_can_vote = (((!sizeof($my_vote) && check_blog_permissions('blog', 'vote', true, $id)) ||
			($auth->acl_get('u_blog_vote_change') && $blog['poll_vote_change'])) &&
			(($blog['poll_length'] != 0 && $blog['poll_start'] + $blog['poll_length'] > time()) || $blog['poll_length'] == 0)) ? true : false;

		// Attachments
		$update_count = $attachments = array();
		parse_attachments_for_view($blog_text, $blog['attachment_data'], $update_count);
		foreach ($blog['attachment_data'] as $i => $attachment)
		{
			$attachments[]['DISPLAY_ATTACHMENT'] = $attachment;
		}

		$blog_row = array(
			'ID'					=> $id,
			'MESSAGE'				=> $blog_text,
			'DATE'					=> $user->format_date($blog['blog_time']),
			'DELETED_MESSAGE'		=> $blog['deleted_message'],
			'EDIT_REASON'			=> $blog['edit_reason'],
			'EDITED_MESSAGE'		=> $blog['edited_message'],
			'EXTRA'					=> '',
			'POLL_QUESTION'			=> generate_text_for_display($blog['poll_title'], $blog['bbcode_uid'], $blog['bbcode_bitfield'], $bbcode_options),
			'RATING_STRING'			=> ($config['user_blog_enable_ratings']) ? get_star_rating($rate_url, $delete_rate_url, $blog['rating'], $blog['num_ratings'], ((isset($rating_data[$id])) ? $rating_data[$id] : false), (($user->data['user_id'] == $user_id) ? true : false)) : false,
			'NUM_REPLIES'			=> $reply_count,
			'REPLIES'				=> '<a href="' . blog_url($user_id, $id, false, array('anchor' => 'replies')) . '">' . (($reply_count == 1) ? $user->lang['ONE_COMMENT'] : sprintf($user->lang['CNT_COMMENTS'], $reply_count)) . '</a>',
			'TITLE'					=> $blog_subject,
			'TOTAL_VOTES'			=> $total_votes,
			'USER_FULL'				=> self::$user[$user_id]['username_full'],
			'VIEWS'					=> ($blog['blog_read_count'] == 1) ? $user->lang['ONE_VIEW'] : sprintf($user->lang['CNT_VIEWS'], $blog['blog_read_count']),

			'L_MAX_VOTES'			=> ($blog['poll_max_options'] == 1) ? $user->lang['MAX_OPTION_SELECT'] : sprintf($user->lang['MAX_OPTIONS_SELECT'], $blog['poll_max_options']),
			'L_POLL_LENGTH'			=> ($blog['poll_length']) ? sprintf($user->lang[($blog['poll_length'] > time()) ? 'POLL_RUN_TILL' : 'POLL_ENDED_AT'], $user->format_date($blog['poll_length'])) : '',

			'U_APPROVE'				=> (check_blog_permissions('blog', 'approve', true, $id) && $blog['blog_approved'] == 0 && !$shortened) ? blog_url(false, $id, false, array('page' => 'blog', 'mode' => 'approve')) : '',
			'U_DELETE'				=> (check_blog_permissions('blog', 'delete', true, $id)) ? blog_url(false, $id, false, array('page' => 'blog', 'mode' => 'delete')) : '',
			'U_DIGG'				=> 'http://digg.com/submit?phase=2&amp;url=' . urlencode(generate_board_url() . '/blog.' . $phpEx . '?b=' . $blog['blog_id']),
			'U_EDIT'				=> (check_blog_permissions('blog', 'edit', true, $id)) ? blog_url(false, $id, false, array('page' => 'blog', 'mode' => 'edit')) : '',
			'U_QUOTE'				=> (check_blog_permissions('reply', 'quote', true, $id)) ? blog_url(false, $id, false, array('page' => 'reply', 'mode' => 'quote')) : '',
			'U_REPORT'				=> (check_blog_permissions('blog', 'report', true, $id) ) ? blog_url(false, $id, false, array('page' => 'blog', 'mode' => 'report')) : '',
			'U_REPLY'				=> (check_blog_permissions('reply', 'add', true, $id) ) ? blog_url(false, $id, false, array('page' => 'reply', 'mode' => 'add')) : '',
			'U_VIEW'				=> blog_url($user_id, $id),
			'U_VIEW_PERMANENT'		=> blog_url(false, $id, false, array(), array(), true),
			'U_WARN'				=> (($auth->acl_get('m_warn')) && $user_id != $user->data['user_id'] && $user_id != ANONYMOUS) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=warn&amp;mode=warn_user&amp;u=$user_id", true, $user->session_id) : '',

			'S_CAN_VOTE'			=> $s_can_vote,
			'S_DELETED'				=> ($blog['blog_deleted']) ? true : false,
			'S_DISPLAY_NOTICE'		=> (!$auth->acl_get('u_download') && $blog['blog_attachment'] && sizeof($blog['attachment_data'])) ? true : false,
			'S_DISPLAY_RESULTS'		=> (!$s_can_vote || ($s_can_vote && sizeof($my_vote)) || (isset($_GET['view']) && $_GET['view'] == 'viewpoll')) ? true : false,
			'S_HAS_ATTACHMENTS'		=> ($blog['blog_attachment']) ? true : false,
			'S_HAS_POLL'			=> ($blog['poll_title']) ? true : false,
			'S_IS_MULTI_CHOICE'		=> ($blog['poll_max_options'] > 1) ? true : false,
			'S_REPORTED'			=> ($blog['blog_reported'] && ($auth->acl_get('m_blogreport'))) ? true : false,
			'S_SHORTENED'			=> $shortened,
			'S_UNAPPROVED'			=> (!$blog['blog_approved'] && ($user_id == $user->data['user_id'] || $auth->acl_get('m_blogapprove'))) ? true : false,

			'attachment'			=> $attachments,
			'poll_option'			=> $poll_options,
		);

		blog_plugins::plugin_do_ref('blog_handle_data_end', $blog_row);

		return $blog_row;
	}

	/**
	* --------------------------------------------------------------------------------------------------------------------------- POLLS -----------------------------------------------------------------------------------------------------------------
	*/

	/**
	* Get polls
	*
	* The gotten data will be put in $blog['poll_options'] and $blog['poll_votes']
	*
	* @param array $blog_ids The blog ID's you would like to look up.
	*/
	public function get_polls($blog_ids)
	{
		global $config, $db, $user;

		if (!is_array($blog_ids))
		{
			$blog_ids = array($blog_ids);
		}

		$ids = $blog_ids;
		$blog_ids = array();
		foreach ($ids as $blog_id)
		{
			if (!isset(self::$blog[$blog_id]) || self::$blog[$blog_id]['poll_title'])
			{
				$blog_ids[] = $blog_id;
			}
		}

		if (!sizeof($blog_ids))
		{
			return;
		}

		// Get the options and store it in $blog[$blog_id]['poll_options'][$poll_option_id]
		$sql = 'SELECT * FROM ' . BLOGS_POLL_OPTIONS_TABLE . ' WHERE ' . $db->sql_in_set('blog_id', $blog_ids);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			self::$blog[$row['blog_id']]['poll_options'][$row['poll_option_id']] = $row;
		}

		foreach ($blog_ids as $id)
		{
			self::$blog[$id]['poll_options'] = array_reverse(self::$blog[$id]['poll_options']);
		}

		// Get the votes and store it in $blog[$blog_id]['poll_votes'][$poll_option_id]
		// votes are in ['votes'], voter info is in ['voters']
		$sql = 'SELECT * FROM ' . BLOGS_POLL_VOTES_TABLE . ' WHERE ' . $db->sql_in_set('blog_id', $blog_ids);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['vote_user_id'] == $user->data['user_id'] && $user->data['is_registered'])
			{
				self::$blog[$row['blog_id']]['poll_votes']['my_vote'][] = $row['poll_option_id'];
			}

			if (!isset(self::$blog[$row['blog_id']]['poll_votes'][$row['poll_option_id']]['votes']))
			{
				self::$blog[$row['blog_id']]['poll_votes'][$row['poll_option_id']] = array(
					'votes' => 1,
					'voters' => array('vote_user_id' => $row['vote_user_id'], 'vote_user_ip' => $row['vote_user_ip']),
				);
			}
			else
			{
				self::$blog[$row['blog_id']]['poll_votes'][$row['poll_option_id']]['votes']++;
				self::$blog[$row['blog_id']]['poll_votes'][$row['poll_option_id']]['voters'][] = array('vote_user_id' => $row['vote_user_id'], 'vote_user_ip' => $row['vote_user_ip']);
			}
		}

		if (!$user->data['is_registered'])
		{
			foreach ($blog_ids as $blog_id)
			{
				// Cookie based guest tracking ... I don't like this but hum ho
				// it's oft requested. This relies on "nice" users who don't feel
				// the need to delete cookies to mess with results.
				if (isset($_COOKIE[$config['cookie_name'] . '_poll_' . $blog_id]))
				{
					self::$blog[$blog_id]['poll_votes']['my_vote'] = explode(',', $_COOKIE[$config['cookie_name'] . '_poll_' . $blog_id]);
					self::$blog[$blog_id]['poll_votes']['my_vote'] = array_map('intval', self::$blog[$blog_id]['poll_votes']['my_vote']);
				}
			}
		}
	}

	/**
	* --------------------------------------------------------------------------------------------------------------------------- REPLIES -----------------------------------------------------------------------------------------------------------------
	*/

	/**
	* Get reply data
	*
	* To select reply data from the database
	*
	* @param string $mode The mode we want
	* @param int $id To input the wanted blog_id, this may be an array if you want to select more than 1
	* @param array $selection_data For extras, like start, limit, order by, order direction, etc, all of the options are listed a few lines below
	*/
	public function get_reply_data($mode, $id = 0, $selection_data = array())
	{
		global $db, $user, $phpbb_root_path, $phpEx, $auth;

		blog_plugins::plugin_do_ref('reply_data_start', $selection_data);

		// input options for selection_data
		$start			= (isset($selection_data['start'])) ? $selection_data['start'] :			0;			// the start used in the Limit sql query
		$limit			= (isset($selection_data['limit'])) ? $selection_data['limit'] :			10;			// the limit on how many blogs we will select
		$order_by		= (isset($selection_data['order_by'])) ? $selection_data['order_by'] :		'reply_id';	// the way we want to order the request in the SQL query
		$order_dir		= (isset($selection_data['order_dir'])) ? $selection_data['order_dir'] :	'DESC';		// the direction we want to order the request in the SQL query
		$sort_days		= (isset($selection_data['sort_days'])) ? $selection_data['sort_days'] : 	0;			// the sort days selection
		$custom_sql		= (isset($selection_data['custom_sql'])) ? $selection_data['custom_sql'] : 	'';			// add your own custom WHERE part to the query
		$category_id	= (isset($selection_data['category_id'])) ? $selection_data['category_id'] : 0;			// The category ID, if selecting replies only from blogs from a certain category

		// Setup some variables...
		$reply_ids = array();

		// make sure $id is an array for consistency
		if (!is_array($id))
		{
			$id = array(intval($id));
		}

		$sql_array = array(
			'SELECT'	=> 'r.*',
			'FROM'		=> array(
				BLOGS_REPLY_TABLE	=> array('r'),
			),
			'WHERE'		=> array(),
			'ORDER_BY'	=> $order_by . ' ' . $order_dir,
		);

		if ($category_id)
		{
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'		=> array(BLOGS_IN_CATEGORIES_TABLE => 'bc'),
				'ON'		=> 'bc.blog_id = r.blog_id',
			);
			$sql_array['WHERE'][] = (is_array($category_id)) ? $db->sql_in_set('bc.category_id', $category_id) : 'bc.category_id = ' . $category_id;
		}
		if (!$auth->acl_get('m_blogreplyapprove'))
		{
			if ($user->data['is_registered'])
			{
				$sql_array['WHERE'][] = '(r.reply_approved = 1 OR r.reply_id = ' . $user->data['user_id'] . ')';
			}
			else
			{
				$sql_array['WHERE'][] = 'r.reply_approved = 1';
			}
		}
		if (!$auth->acl_gets('m_blogreplydelete', 'a_blogreplydelete'))
		{
			$sql_array['WHERE'][] = '(r.reply_deleted = 0 OR r.reply_deleted = ' . $user->data['user_id'] . ')';

			// Make sure we do not select replies from blogs that have been deleted (if the user isn't allowed to view deleted items)
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'		=> array(BLOGS_TABLE => 'b'),
				'ON'		=> 'b.blog_id = r.blog_id',
			);
			$sql_array['WHERE'][] = '(b.blog_deleted = 0 OR b.blog_deleted = ' . $user->data['user_id'] . ')';
			if (build_permission_sql($user->data['user_id'], false))
			{
				// remove the first AND
				$sql_array['WHERE'][] = substr(build_permission_sql($user->data['user_id'], false, 'b.'), 5);
			}
		}
		if ($sort_days)
		{
			$sql_array['WHERE'][] = 'r.reply_time >= ' . (time() - $sort_days * 86400);
		}
		if ($custom_sql)
		{
			$sql_array['WHERE'][] = $custom_sql;
		}

		switch ($mode)
		{
			case 'blog' : // view all replys by a blog_id
				$sql_array['WHERE'][] = $db->sql_in_set('r.blog_id', $id);
			break;

			case 'reply' : // select replies by reply_id(s)
				$sql_array['WHERE'][] = $db->sql_in_set('r.reply_id', $id);
				$limit = 0;
			break;

			case 'reported' : // select reported replies
				if (!$auth->acl_get('m_blogreplyreport'))
				{
					return false;
				}

				$sql_array['WHERE'][] = 'r.reply_reported = 1';
			break;

			case 'disapproved' : // select disapproved replies
				if (!$auth->acl_get('m_blogreplyapprove'))
				{
					return false;
				}

				$sql_array['WHERE'][] = 'r.reply_approved = 0';
			break;

			case 'reply_count' : // for counting how many replies there are for a blog
				if (self::$blog[$id[0]]['blog_real_reply_count'] == 0 || self::$blog[$id[0]]['blog_real_reply_count'] == self::$blog[$id[0]]['blog_reply_count'])
				{
					return self::$blog[$id[0]]['blog_real_reply_count'];
				}

				if (!$sort_days && ($auth->acl_get('m_blogreplyapprove') && $auth->acl_gets('m_blogreplydelete', 'a_blogreplydelete')))
				{
					return self::$blog[$id[0]]['blog_real_reply_count'];
				}
				else if ($auth->acl_get('m_blogreplyapprove') || $auth->acl_gets('m_blogreplydelete', 'a_blogreplydelete') || $sort_days || (self::$blog[$id[0]]['user_id'] == $user->data['user_id'] && $auth->acl_get('u_blogmoderate')))
				{
					$sql_array['SELECT'] = 'count(r.reply_id) AS total';
					$sql_array['WHERE'][] = 'r.blog_id = ' . $id[0];
					$sql_array['WHERE'] = implode(' AND ', $sql_array['WHERE']);
					$sql = $db->sql_build_query('SELECT', $sql_array);
					$result = $db->sql_query($sql);
					$total = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
					return $total['total'];
				}
				else
				{
					return self::$blog[$id[0]]['blog_reply_count'];
				}
			break;

			case 'page' : // Special mode for trying to find out what page the reply is on
				$cnt = 0;
				$sql = 'SELECT reply_id FROM ' . BLOGS_REPLY_TABLE . '
					WHERE blog_id = ' . $id[0] .
						(($sort_days != 0) ? ' AND reply_time >= ' . (time() - ($sort_days * 86400)) : '') .
							' ORDER BY ' . $order_by . ' ' . $order_dir;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					if ($row['reply_id'] == $id[1])
					{
						break;
					}

					$cnt++;
				}
				$db->sql_freeresult($result);

				return $cnt;
			break;

			case 'count' : // this just does a count of the number of replies
				$sql_array['SELECT'] = 'count(r.reply_id) AS total';
				$sql_array['WHERE'] = implode(' AND ', $sql_array['WHERE']);
				unset($sql_array['ORDER_BY']);
				$sql = $db->sql_build_query('SELECT', $sql_array);

				$result = $db->sql_query($sql);
				$total = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				return $total['total'];
			break;

			case 'recent' :
				if (!isset($selection_data['order_by']))
				{
					$sql_array['ORDER_BY'] = 'r.reply_time DESC';
				}
			break;
		}

		$temp = compact('sql_array', 'sql_where');
		blog_plugins::plugin_do_ref('reply_data_sql', $temp);
		extract($temp);

		$sql_array['WHERE'] = implode(' AND ', $sql_array['WHERE']);
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
			blog_plugins::plugin_do_ref('reply_data_while', $row);

			// Initialize the attachment data
			$row['attachment_data'] = array();

			// now put all the data in the reply array
			self::$reply[$row['reply_id']] = $row;

			// Add this user's ID to the user_queue
			self::$user_queue[] = $row['user_id'];

			// has the reply been edited?  If so add that user to the user_queue
			if ($row['reply_edit_count'] != 0)
			{
				self::$user_queue[] = $row['reply_edit_user'];
			}

			// has the reply been deleted?  If so add that user to the user_queue
			if ($row['reply_deleted'] != 0)
			{
				self::$user_queue[] = $row['reply_deleted'];
			}

			// make sure we don't record the same ID more than once
			if (!in_array($row['reply_id'], $reply_ids))
			{
				$reply_ids[] = $row['reply_id'];
			}
		}
		$db->sql_freeresult($result);

		// if there are no replys, return false
		if (sizeof($reply_ids) == 0)
		{
			return false;
		}

		return $reply_ids;
	}

	/**
	* Handle reply data
	*
	* To handle the raw data gotten from the database
	*
	* @param int $id The id of the reply we want to handle
	* @param int|bool $trim_text If we want to trim the text or not(if true we will trim with the setting in $config['user_blog_user_text_limit'], else if it is an integer we will trim the text to that length)
	*/
	public function handle_reply_data($id, $trim_text = false)
	{
		global $user, $phpbb_root_path, $config, $phpEx, $auth, $highlight_match;
		global $blog_attachment, $category_id;

		if (!isset(self::$reply[$id]))
		{
			return array();
		}

		$reply = &self::$reply[$id];
		$blog_id = $reply['blog_id'];
		$user_id = $reply['user_id'];

		blog_plugins::plugin_do('reply_handle_data_start');

		if ($trim_text !== false)
		{
			$reply_text = trim_text_length(false, $id, ($trim_text === true) ? $config['user_blog_user_text_limit'] : intval($trim_text));
			$shortened = ($reply_text === false) ? false : true;
			$reply_text = ($reply_text === false) ? $reply['reply_text'] : $reply_text;
		}
		else
		{
			$reply_text = $reply['reply_text'];
			$shortened = false;
		}

		// censor the text of the subject
		$reply_subject = censor_text($reply['reply_subject']);

		// Parse BBCode and prepare the message for viewing
		$bbcode_options = (($reply['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($reply['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($reply['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
		$reply_text = generate_text_for_display($reply_text, $reply['bbcode_uid'], $reply['bbcode_bitfield'], $bbcode_options);

		// For Highlighting
		if ($highlight_match)
		{
			$reply_subject = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $reply_subject);
			$reply_text = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $reply_text);
		}

		// Attachments
		$update_count = $attachments = array();
		parse_attachments_for_view($reply_text, $reply['attachment_data'], $update_count);
		foreach ($reply['attachment_data'] as $i => $attachment)
		{
			$attachments[]['DISPLAY_ATTACHMENT'] = $attachment;
		}

		$replyrow = array(
			'ID'					=> $id,
			'TITLE'					=> $reply_subject,
			'DATE'					=> $user->format_date($reply['reply_time']),
			'MESSAGE'				=> $reply_text,
			'EDITED_MESSAGE'		=> $reply['edited_message'],
			'EDIT_REASON'			=> $reply['edit_reason'],
			'DELETED_MESSAGE'		=> $reply['deleted_message'],
			'EXTRA'					=> '',

			'U_VIEW'				=> blog_url($user_id, $blog_id, $id),
			'U_VIEW_PERMANENT'		=> blog_url($user_id, $blog_id, $id, array(), array(), true),

			'U_APPROVE'				=> ($reply['reply_approved'] == 0) ? blog_url($user_id, $blog_id, $id, array('page' => 'reply', 'mode' => 'approve')) : '',
			'U_DELETE'				=> (check_blog_permissions('reply', 'delete', true, $blog_id, $id)) ? blog_url(false, false, $id, array('page' => 'reply', 'mode' => 'delete')) : '',
			'U_EDIT'				=> (check_blog_permissions('reply', 'edit', true, $blog_id, $id)) ? blog_url(false, false, $id, array('page' => 'reply', 'mode' => 'edit')) : '',
			'U_QUOTE'				=> (check_blog_permissions('reply', 'quote', true, $blog_id, $id)) ? blog_url(false, false, $id, array('page' => 'reply', 'mode' => 'quote')) : '',
			'U_REPLY'				=> (check_blog_permissions('reply', 'add', true, $blog_id) ) ? blog_url(false, $blog_id, false, array('page' => 'reply', 'mode' => 'add')) : '',
			'U_REPORT'				=> (check_blog_permissions('reply', 'report', true, $blog_id, $id)) ? blog_url(false, false, $id, array('page' => 'reply', 'mode' => 'report')) : '',
			'U_WARN'				=> (($auth->acl_get('m_warn')) && $reply['user_id'] != $user->data['user_id'] && $reply['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=warn&amp;mode=warn_user&amp;u=$user_id") : '',

			'S_DELETED'				=> ($reply['reply_deleted'] != 0) ? true : false,
			'S_UNAPPROVED'			=> ($reply['reply_approved'] == 0) ? true : false,
			'S_REPORTED'			=> ($reply['reply_reported'] && $auth->acl_get('m_blogreplyreport')) ? true : false,
			'S_DISPLAY_NOTICE'		=> (!$auth->acl_get('u_download') && $reply['reply_attachment'] && sizeof($reply['attachment_data'])) ? true : false,
			'S_HAS_ATTACHMENTS'		=> ($reply['reply_attachment']) ? true : false,

			'attachment'			=> $attachments,
		);

		blog_plugins::plugin_do_ref('reply_handle_data_end', $replyrow);

		return $replyrow;
	}

	/**
	* --------------------------------------------------------------------------------------------------------------------------- USERS -----------------------------------------------------------------------------------------------------------------
	*/

	/**
	* Get user data
	*
	* grabs the data on the user and places it in the self::$user array
	*
	* @param int|bool $id The user_id (or multiple user_ids if given an array) of the user we want to grab the data for
	* @param bool $user_queue If user_queue is true then we just grab the user_ids from the user_queue, otherwise we select data from $id.
	*/
	public function get_user_data($id, $user_queue = false, $username = false)
	{
		global $user, $db, $phpbb_root_path, $phpEx, $config, $auth, $cp;

		// if we are using the user_queue, set $user_id as that for consistency
		if ($user_queue)
		{
			$id = self::$user_queue;
		}

		blog_plugins::plugin_do('user_data_start');

		// this holds the user_id's we will query
		$users_to_query = array();

		if (!$username)
		{
			// if the $user_id isn't an array, make it one for consistency
			if (!is_array($id))
			{
				$id = array(intval($id));
			}

			if (!sizeof($id))
			{
				return;
			}

			$id[] = 1;

			foreach ($id as $i)
			{
				if ($i && !isset(self::$user[$i]) && !in_array($i, $users_to_query))
				{
					$users_to_query[] = $i;
				}
			}

			if (!sizeof($users_to_query))
			{
				return;
			}

			// Grab all profile fields from users in id cache for later use - similar to the poster cache
			if ($config['user_blog_custom_profile_enable'])
			{
				if (!class_exists('custom_profile'))
				{
					include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
					$cp = new custom_profile();
				}

				$profile_fields_cache = $cp->generate_profile_fields_template('grab', $users_to_query);
			}

			// Grab user status information
			$status_data = array();
			$sql = 'SELECT session_user_id, MAX(session_time) AS online_time, MIN(session_viewonline) AS viewonline
				FROM ' . SESSIONS_TABLE . '
					WHERE ' . $db->sql_in_set('session_user_id', $users_to_query) . '
						GROUP BY session_user_id';
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result))
			{
				$status_data[$row['session_user_id']] = $row;
			}
			$db->sql_freeresult($result);
			$update_time = $config['load_online_time'] * 60;

			// Get the rest of the data on the users and parse everything we need
			$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $db->sql_in_set('user_id', $users_to_query);
			blog_plugins::plugin_do_ref('user_data_sql', $sql);
			$result = $db->sql_query($sql);
		}
		else
		{
			$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE username_clean = \'' . $db->sql_escape(utf8_clean_string($username)) . '\'';
			blog_plugins::plugin_do_ref('user_data_sql', $sql);
			$result = $db->sql_query($sql);
		}

		while ($row = $db->sql_fetchrow($result))
		{
			$user_id = $row['user_id'];

			blog_plugins::plugin_do_ref('user_data_while', $row);

			// view profile link
			$row['view_profile'] = append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=viewprofile&amp;u=" . $user_id);

			// Full username, with colour
			$row['username_full'] = get_username_string('full', $user_id, $row['username'], $row['user_colour']);

			// format the color correctly
			$row['user_colour'] = get_username_string('colour', $user_id, $row['username'], $row['user_colour']);

			// Avatar
			$row['avatar'] = get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']);

			// Rank
			get_user_rank($row['user_rank'], $row['user_posts'], $row['rank_title'], $row['rank_img'], $row['rank_img_src']);

			if ($row['user_type'] != USER_IGNORE && $row['user_id'] != ANONYMOUS)
			{
				// Online/Offline Status
				$row['status'] = (isset($status_data[$user_id]) && time() - $update_time < $status_data[$user_id]['online_time'] && (($status_data[$user_id]['viewonline'] && $row['user_allow_viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;

				// IM Links
				$row['aim_url'] = ($row['user_aim']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=aim&amp;u=$user_id") : '';
				$row['icq_url'] = ($row['user_icq']) ? 'http://www.icq.com/people/webmsg.php?to=' . $row['user_icq'] : '';
				$row['jabber_url'] = ($row['user_jabber']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=jabber&amp;u=$user_id") : '';
				$row['msn_url'] = ($row['user_msnm']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=msnm&amp;u=$user_id") : '';
				$row['yim_url'] = ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg' : '';

				// PM and email links
				$row['email_url'] = ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;u=$user_id")  : (($config['board_hide_emails'] && !$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']);
				$row['pm_url'] = ($row['user_id'] != ANONYMOUS && $config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($row['user_allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))) ? append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&amp;mode=compose&amp;u=$user_id") : '';

				// get the custom profile fields if the admin wants them
				if ($config['user_blog_custom_profile_enable'])
				{
					$row['cp_row'] = (isset($profile_fields_cache[$user_id])) ? $cp->generate_profile_fields_template('show', false, $profile_fields_cache[$user_id]) : array();
				}
			}
			else
			{
				$row = array_merge($row, array(
					'status'		=> false,
					'aim_url'		=> '',
					'icq_url'		=> '',
					'jabber_url'	=> '',
					'msn_url'		=> '',
					'yim_url'		=> '',
					'email_url'		=> '',
					'pm_url'		=> '',
				));
			}

			// now lets put everything in the user array
			self::$user[$user_id] = $row;
		}
		$db->sql_freeresult($result);
		unset($status_data, $row);

		// if we did use the user_queue, reset it
		if ($user_queue)
		{
			self::$user_queue = array();
		}

		if ($username)
		{
			if (isset($user_id) && $user_id != ANONYMOUS)
			{
				// Grab all profile fields from users in id cache for later use - similar to the poster cache
				if ($config['user_blog_custom_profile_enable'])
				{
					if (!class_exists('custom_profile'))
					{
						include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
						$cp = new custom_profile();
					}

					$profile_fields_cache = $cp->generate_profile_fields_template('grab', $user_id);
				}

				// Grab user status information
				$status_data = array();
				$sql = 'SELECT session_user_id, MAX(session_time) AS online_time, MIN(session_viewonline) AS viewonline
					FROM ' . SESSIONS_TABLE . '
						WHERE session_user_id = ' . intval($user_id) . '
							GROUP BY session_user_id';
				$result = $db->sql_query($sql);
				while($row = $db->sql_fetchrow($result))
				{
					$status_data[$row['session_user_id']] = $row;
				}
				$db->sql_freeresult($result);
				$update_time = $config['load_online_time'] * 60;

				self::$user[$user_id]['status'] = (isset($status_data[$user_id]) && time() - $update_time < $status_data[$user_id]['online_time'] && (($status_data[$user_id]['viewonline'] && $row['user_allow_viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
				unset($status_data);

				return $user_id;
			}
			else
			{
				return false;
			}
		}
		else
		{
			// replace any non-existing users with the anonymous user.
			foreach ($id as $i)
			{
				if ($i && !array_key_exists($i, self::$user))
				{
					self::$user[$i] = self::$user[1];
				}
			}
		}
	}

	/**
	* Get user ID by the Username
	*/
	public function get_id_by_username($username)
	{
		global $db;

		$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE username_clean = \'' . $db->sql_escape(utf8_clean_string($username)) . '\'';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return $row['user_id'];
	}

	/**
	* Handle User Data
	*
	* @param int $user_id The user_id of the user we will setup data for
	*/
	public function handle_user_data($user_id)
	{
		global $phpbb_root_path, $phpEx, $user, $auth, $config, $template;
		global $blog_data, $zebra_list;

		if (!isset(self::$user[$user_id]))
		{
			return array();
		}

		$custom_fields = array();
		if ($config['user_blog_custom_profile_enable'])
		{
			// output the custom profile fields
			if (isset(self::$user[$user_id]['cp_row']['blockrow']))
			{
				foreach (self::$user[$user_id]['cp_row']['blockrow'] as $row)
				{
					$custom_fields[] = array(
						'PROFILE_FIELD_NAME'	=> $row['PROFILE_FIELD_NAME'],
						'PROFILE_FIELD_VALUE'	=> $row['PROFILE_FIELD_VALUE'],
					);
				}
			}
		}
		// add the blog links in the custom fields
		if ($user_id != ANONYMOUS)
		{
			$custom_fields[] = add_blog_links($user_id, '', self::$user[$user_id], false, true, true);
		}

		$output_data = array(
			'USER_ID'			=> $user_id,

			'AVATAR'			=> ($user->optionget('viewavatars')) ? self::$user[$user_id]['avatar'] : '',
			'POSTER_FROM'		=> self::$user[$user_id]['user_from'],
			'POSTER_JOINED'		=> $user->format_date(self::$user[$user_id]['user_regdate']),
			'POSTER_POSTS'		=> self::$user[$user_id]['user_posts'],
			'RANK_IMG'			=> self::$user[$user_id]['rank_img'],
			'RANK_IMG_SRC'		=> self::$user[$user_id]['rank_img_src'],
			'RANK_TITLE'		=> self::$user[$user_id]['rank_title'],
			'SIGNATURE'			=> ($config['allow_sig'] && $user->optionget('viewsigs') && self::$user[$user_id]['user_sig']) ? generate_text_for_display(self::$user[$user_id]['user_sig'], self::$user[$user_id]['user_sig_bbcode_uid'], self::$user[$user_id]['user_sig_bbcode_bitfield'], 7) : '',
			'STATUS_IMG'		=> ((self::$user[$user_id]['status']) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
			'USERNAME'			=> self::$user[$user_id]['username'],
			'USER_COLOUR'		=> self::$user[$user_id]['user_colour'],
			'USER_FULL'			=> self::$user[$user_id]['username_full'],
			'USER_FOE'			=> (isset($zebra_list[$user->data['user_id']]['foe']) && in_array($user_id, $zebra_list[$user->data['user_id']]['foe'])) ? true : false,

			'L_USER_FOE'		=> sprintf($user->lang['POSTED_BY_FOE'], self::$user[$user_id]['username_full']),

			'U_AIM'				=> self::$user[$user_id]['aim_url'],
			'U_EMAIL'			=> self::$user[$user_id]['email_url'],
			'U_ICQ'				=> self::$user[$user_id]['icq_url'],
			'U_JABBER'			=> self::$user[$user_id]['jabber_url'],
			'U_MSN'				=> self::$user[$user_id]['msn_url'],
			'U_PM'				=> self::$user[$user_id]['pm_url'],
			'U_PROFILE'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=viewprofile&amp;u=$user_id"),
			'U_WWW'				=> self::$user[$user_id]['user_website'],
			'U_YIM'				=> self::$user[$user_id]['yim_url'],

			'S_CUSTOM_FIELDS'	=> (isset(self::$user[$user_id]['cp_row']['blockrow'])) ? true : false,
			'S_ONLINE'			=> self::$user[$user_id]['status'],

			'ONLINE_IMG'		=> (self::$user[$user_id]['status']) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE'),
			'USER_EXTRA'		=> '',

			'custom_fields'		=> $custom_fields,
		);

		blog_plugins::plugin_do_ref('user_handle_data', $output_data);

		return ($output_data);
	}
}
?>