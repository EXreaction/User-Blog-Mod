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

/*
* Blog data class
*
* Mainly for holding and grabbing all of the data for the blogs, replies, and users requested at any time for this single page view/session
*/
class blog_data
{
	// this is our large arrays holding all the blog, reply, and user data
	var $blog = array();
	var $reply = array();
	var $user = array();

	// this holds a user_queue of the user's data when requesting replies so we can cut down on queries
	var $user_queue = array();

	/*
	* -------------------------- BLOG DATA SECTION ----------------------------------------------------------------------------------------------------------------------------------------------------
	*/

	/*
	* get blogs
	* $mode is to input the wanted mode
	* $id is to input the wanted blog/user/etc id
	* $selection_data is for extras, and is submitted as an array input options for selection data are listed a few lines below
	*/
	function get_blog_data($mode, $id = 0, $selection_data = array())
	{
		global $db, $user, $phpbb_root_path, $phpEx, $auth, $user_founder;

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
			$view_deleted_sql = ' AND ( blog_deleted = \'0\' OR user_id = \'' . $user->data['user_id'] . '\' )';
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
											$order_by_sql .
												$limit_sql;
				break;
			case 'user_count' : // this only counts the total number of blogs a single user has and returns the count
				if ($auth->acl_gets('m_blogapprove', 'm_blogdelete', 'a_blogdelete') || $user_founder || $sort_days_sql != '')
				{
					$sql = 'SELECT count(blog_id) AS total FROM ' . BLOGS_TABLE . '
						WHERE user_id = \'' . $id[0] . '\'' .
							$view_deleted_sql .
								$view_unapproved_sql .
									$sort_days_sql .
										$limit_sql;
					$result = $db->sql_query($sql);
					$total = $db->sql_fetchrow($result);
					return $total['total'];
				}
				else
				{
					return $this->user[$id[0]]['blog_count'];
				}
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
										$order_by_sql;
				break;
			case 'recent' : // select recent blogs
				$sql = 'SELECT * FROM ' . BLOGS_TABLE .
					$view_deleted_sql .
						$view_unapproved_sql .
							$sort_days_sql .
								' ORDER BY blog_time DESC' .
									$limit_sql;
				$sql = $this->fix_sql($sql);
				break;
			case 'random' : // select random blogs
				$random_ids = array();
				$all_blog_ids = $this->get_blog_data('all_ids');
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

				$this->get_blog_data('blog', $random_ids);

				return $random_ids;
				break;
			case 'popular' : // select popular blogs.
				$sql = 'SELECT * FROM ' . BLOGS_TABLE .
					$view_deleted_sql .
						$view_unapproved_sql .
							$sort_days_sql . '
								ORDER BY blog_reply_count DESC, blog_read_count DESC' .
									$limit_sql;
				$sql = $this->fix_sql($sql);
				break;
			case 'reported' : // select reported blogs
				if (!$auth->acl_get('m_blogreport') && !$user_founder)
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
				if (!$auth->acl_get('m_blogapprove') && !$user_founder)
				{
					return false;
				}

				$sql = 'SELECT * FROM ' . BLOGS_TABLE . '
					WHERE blog_approved = \'0\'' .
						$sort_days_sql .
							$order_by_sql .
								$limit_sql;
				break;
			case 'all_ids' : // select and return all ID's.  This does not get any data other than the blog_id's.
				$all_ids = array();
				// have to add in a WHERE in here because the rest are AND, so if either are active it would give an error
				$sql = 'SELECT blog_id FROM ' . BLOGS_TABLE .
					$view_deleted_sql .
						$view_unapproved_sql .
							$sort_days_sql .
								$order_by_sql;
				$sql = $this->fix_sql($sql);
				$result = $db->sql_query($sql);
				while($row = $db->sql_fetchrow($result))
				{
					$all_ids[] = $row['blog_id'];
				}
				return $all_ids;
			default :
				return false;
		}

		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// now put all the data in the blog array
			$this->blog[$row['blog_id']] = $row;

			// add the blog owners' user_ids to the user_queue
			array_push($this->user_queue, $row['user_id']);

			// Add the edit user to the user_queue, if there is one
			if ($row['blog_edit_count'] != 0)
			{
				array_push($this->user_queue, $row['blog_edit_user']);
			}

			// Add the deleter user to the user_queue, if there is one
			if ($row['blog_deleted'] != 0)
			{
				array_push($this->user_queue, $row['blog_deleted']);
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

	/*
	* Handle blog data
	* id is the id of the blog we want to setup
	* trim text is if we want to trim the text or not
	*/
	function handle_blog_data($id, $trim_text = false)
	{
		global $config, $user, $phpbb_root_path, $phpEx, $auth, $highlight_match, $user_founder;

		$blog = $this->blog[$id];
		$user_id = $blog['user_id'];

		if ($trim_text !== false)
		{
			$blog_text = $this->trim_text_length($id, false, ($trim_text === true) ? $config['user_blog_user_text_limit'] : intval($trim_text));
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

		$reply_count = $this->get_reply_data('reply_count', $id);

		$blog_row = array(	
			'BLOG_ID'			=> $id,
			'BLOG_MESSAGE'		=> $blog_text,
			'DATE'				=> $user->format_date($blog['blog_time']),
			'DELETED_MESSAGE'	=> $blog['deleted_message'],
			'EDIT_REASON'		=> $blog['edit_reason'],
			'EDITED_MESSAGE'	=> $blog['edited_message'],
			'PUB_DATE'			=> date('r', $blog['blog_time']),
			'REPLIES'			=> ($reply_count != 1) ? ($reply_count == 0) ? sprintf($user->lang['BLOG_REPLIES'], $reply_count, '', '') : sprintf($user->lang['BLOG_REPLIES'], $reply_count, '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "b=$id") . '#replies">', '</a>') : sprintf($user->lang['BLOG_REPLY'], '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "b=$id") . '#replies">', '</a>'),
			'TITLE'				=> $blog['blog_subject'],
			'USER_FULL'			=> $this->user[$user_id]['username_full'],
			'VIEWS'				=> ($blog['blog_read_count'] != 1) ? sprintf($user->lang['BLOG_VIEWS'], ($user->data['user_id'] != $user_id) ? $blog['blog_read_count'] + 1 : $blog['blog_read_count']) : $user->lang['BLOG_VIEW'],

			'U_APPROVE'			=> (check_blog_permissions('blog', 'approve', true, $id) && $blog['blog_approved'] == 0 && !$shortened) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=blog&amp;mode=approve&amp;b=$id") : '',
			'U_DELETE'			=> (check_blog_permissions('blog', 'delete', true, $id) && !$shortened) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=blog&amp;mode=delete&amp;b=$id") : '',
			'U_DELETE_RATING'	=> (isset($this->user_rating[$user->data['user_id']][$id])) ? append_sid("{$phpbb_root_path}blogs.$phpEx", "page=rate&amp;delete=true&amp;b=$id") : '',
			'U_DIGG'			=> (!$shortened) ? 'http://digg.com/submit?phase=2&amp;url=' . urlencode(generate_board_url() . '/blog.' . $phpEx . '?b=' . $blog['blog_id']) : '',
			'U_EDIT'			=> (check_blog_permissions('blog', 'edit', true, $id) && !$shortened) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=blog&amp;mode=edit&amp;b=$id") : '',
			'U_QUOTE'			=> (check_blog_permissions('reply', 'quote', true, $id) && !$shortened) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=quote&amp;b=$id") : '',
			'U_REPORT'			=> (check_blog_permissions('blog', 'report', true, $id) && !$shortened) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=blog&amp;mode=report&amp;b=$id") : '',
			'U_VIEW'			=> append_sid("{$phpbb_root_path}blog.$phpEx", "b=$id"),
			'U_WARN'			=> (($auth->acl_get('m_warn') || $user_founder) && $user_id != $user->data['user_id'] && $user_id != ANONYMOUS && !$shortened) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=warn&amp;mode=warn_user&amp;u=$user_id", true, $user->session_id) : '',

			'S_DELETED'			=> ($blog['blog_deleted'] != 0) ? true : false,
			'S_REPORTED'		=> ($blog['blog_reported'] && ($auth->acl_get('m_blogreport') || $user_founder)) ? true : false,
			'S_SHORTENED'		=> $shortened,
			'S_UNAPPROVED'		=> ($blog['blog_approved'] == 0 && ($user_id == $user->data['user_id'] || $auth->acl_get('m_blogapprove') || $user_founder)) ? true : false,
		);

		return $blog_row;
	}

	/*
	* -------------------------- REPLY DATA SECTION ---------------------------------------------------------------------------------------------------------------------------------------------------
	*/

	/*
	* get reply data
	* $mode is to input the wanted mode
	* $id is to input the wanted reply/user/etc id
	* $selection_data is for extras, and is submitted as an array input options for selection data are listed a few lines below
	*/
	function get_reply_data($mode, $id = 0, $selection_data = array())
	{
		global $db, $user, $phpbb_root_path, $phpEx, $auth, $user_founder;

		// input options for selection_data
		$start		= (isset($selection_data['start'])) ? $selection_data['start'] :			0;			// the start used in the Limit sql query
		$limit		= (isset($selection_data['limit'])) ? $selection_data['limit'] :			10;			// the limit on how many blogs we will select
		$order_by	= (isset($selection_data['order_by'])) ? $selection_data['order_by'] :		'reply_id';	// the way we want to order the request in the SQL query
		$order_dir	= (isset($selection_data['order_dir'])) ? $selection_data['order_dir'] :	'DESC';		// the direction we want to order the request in the SQL query
		$sort_days	= (isset($selection_data['sort_days'])) ? $selection_data['sort_days'] : 	0;			// the sort days selection

		// Setup some variables...
		$reply_ids = array();
		$view_unapproved_sql = ($auth->acl_get('m_blogreplyapprove') || $user_founder) ? '' : ' AND reply_approved = \'1\'';
		$view_deleted_sql = ($auth->acl_gets('m_blogreplydelete', 'a_blogreplydelete') || $user_founder) ? '' : ' AND reply_deleted = \'0\'';
		$sort_days_sql = ($sort_days != 0) ? ' AND reply_time >= \'' . (time() - ($sort_days * 86400)) . '\'' : '';
		$order_by_sql = ' ORDER BY ' . $order_by . ' ' . $order_dir;
		$limit_sql = ($limit > 0) ? ' LIMIT ' . $start . ', ' . $limit : '';

		// make sure $id is an array for consistency
		if (!is_array($id))
		{
			$id = array($id);
		}

		switch ($mode)
		{
			case 'blog' : // view all replys by a blog_id
				$sql = 'SELECT * FROM ' . BLOGS_REPLY_TABLE . '
					WHERE ' . $db->sql_in_set('blog_id', $id) . 
						$view_deleted_sql .
							$view_unapproved_sql .
								$sort_days_sql .
									$order_by_sql .
										$limit_sql;
				break;
			case 'reply' : // select replies by reply_id(s)
				$replies_to_query = array();

				// check if the reply already exists
				foreach ($id as $i)
				{
					if (!array_key_exists($i, $this->reply) && !in_array($i, $replies_to_query))
					{
						array_push($replies_to_query, $i);
					}
					else
					{
						array_push($reply_ids, $i);
					}
				}

				if (count($replies_to_query) == 0)
				{
					return $reply_ids;
				}

				$sql = 'SELECT * FROM ' . BLOGS_REPLY_TABLE . '
					WHERE ' . $db->sql_in_set('reply_id', $replies_to_query) .
						$view_deleted_sql .
							$view_unapproved_sql .
								$sort_days_sql .
									$order_by_sql .
										$limit_sql;
				break;
			case 'reported' : // select reported replies
				if (!$auth->acl_get('m_blogreplyreport') && !$user_founder)
				{
					return false;
				}

				$sql = 'SELECT * FROM ' . BLOGS_REPLY_TABLE . '
					WHERE reply_reported = \'1\'' .
						$view_deleted_sql .
							$sort_days_sql .
								$order_by_sql .
									$limit_sql;
				break;
			case 'disapproved' : // select disapproved replies
				if (!$auth->acl_get('m_blogreplyapprove') && !$user_founder)
				{
					return false;
				}

				$sql = 'SELECT * FROM ' . BLOGS_REPLY_TABLE . '
					WHERE reply_approved = \'0\'' .
						$view_deleted_sql .
							$sort_days_sql .
								$order_by_sql .
									$limit_sql;
				break;
			case 'reply_count' : // for counting how many replies there are for a blog
				if (($auth->acl_get('m_blogreplyapprove') && $auth->acl_gets('m_blogreplydelete', 'a_blogreplydelete')) || $user_founder)
				{
					return $this->blog[$id[0]]['blog_real_reply_count'];
				}
				else if ($auth->acl_get('m_blogreplyapprove') || $auth->acl_gets('m_blogreplydelete', 'a_blogreplydelete'))
				{
					$sql = 'SELECT count(blog_id) AS total FROM ' . BLOGS_REPLY_TABLE . '
						WHERE blog_id = \'' . $id[0] . '\'' .
							$view_deleted_sql .
								$view_unapproved_sql .
									$sort_days_sql;
					$result = $db->sql_query($sql);
					$total = $db->sql_fetchrow($result);
					return $total['total'];
				}
				else
				{
					return $this->blog[$id[0]]['blog_reply_count'];
				}
				break;
			default :
				return false;
		}

		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// now put all the data in the reply array
			$this->reply[$row['reply_id']] = $row;

			// Add this user's ID to the user_queue
			array_push($this->user_queue, $row['user_id']);

			// has the reply been edited?  If so add that user to the user_queue
			if ($row['reply_edit_count'] != 0)
			{
				array_push($this->user_queue, $row['reply_edit_user']);
			}
	
			// has the reply been deleted?  If so add that user to the user_queue
			if ($row['reply_deleted'] != 0)
			{
				array_push($this->user_queue, $row['reply_deleted']);
			}

			// make sure we don't record the same ID more than once
			if (!in_array($row['reply_id'], $reply_ids))
			{
				array_push($reply_ids, $row['reply_id']);
			}
		}
		$db->sql_freeresult($result);

		// if there are no replys, return false
		if (count($reply_ids) == 0)
		{
			return false;
		}

		return $reply_ids;
	}

	/*
	* Handle reply data
	* id is the id of the reply we want to setup
	*/
	function handle_reply_data($id)
	{
		global $user, $phpbb_root_path, $phpEx, $auth, $highlight_match;

		$reply = $this->reply[$id];
		$blog_id = $reply['blog_id'];
		$user_id = $reply['user_id'];

		// censor the text of the subject
		$reply['reply_subject'] = censor_text($reply['reply_subject']);

		// Parse BBCode and prepare the message for viewing
		$bbcode_options = (($reply['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($reply['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($reply['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
		$reply['reply_text'] = generate_text_for_display($reply['reply_text'], $reply['bbcode_uid'], $reply['bbcode_bitfield'], $bbcode_options);

		// For Highlighting
		if ($highlight_match)
		{
			$reply['reply_text'] = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $reply['reply_text']);
		}

		$replyrow = array(
			'TITLE'				=> censor_text($reply['reply_subject']),
			'DATE'				=> $user->format_date($reply['reply_time']),

			'REPLY_MESSAGE'		=> $reply['reply_text'],

			'EDITED_MESSAGE'	=> $reply['edited_message'],
			'EDIT_REASON'		=> $reply['edit_reason'],
			'DELETED_MESSAGE'	=> $reply['deleted_message'],

			'U_VIEW'			=> append_sid("{$phpbb_root_path}blog.$phpEx", "b={$blog_id}r={$id}#r{$id}"),

			'U_QUOTE'			=> (check_blog_permissions('reply', 'quote', true, 0, $id)) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=quote&amp;r=$id") : '',
			'U_EDIT'			=> (check_blog_permissions('reply', 'edit', true, 0, $id)) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=edit&amp;r=$id") : '',
			'U_DELETE'			=> (check_blog_permissions('reply', 'delete', true, 0, $id)) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=delete&amp;r=$id") : '',
			'U_REPORT'			=> (check_blog_permissions('reply', 'report', true, 0, $id)) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=report&amp;r=$id") : '',
			'U_WARN'			=> (($auth->acl_get('m_warn') || $user_founder) && $reply['user_id'] != $user->data['user_id'] && $reply['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=warn&amp;mode=warn_user&amp;u=$user_id") : '',
			'U_APPROVE'			=> ($reply['reply_approved'] == 0) ? append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=approve&amp;r=$id") : '',

			'S_DELETED'			=> ($reply['reply_deleted'] != 0) ? true : false,
			'S_UNAPPROVED'		=> ($reply['reply_approved'] == 0) ? true : false,
			'S_REPORTED'		=> ($reply['reply_reported'] && ($auth->acl_get('m_blogreplyreport') || $user_founder)) ? true : false,

			'ID'				=> $id,
		);

		return $replyrow;
	}

	/*
	* -------------------------- USER DATA SECTION ----------------------------------------------------------------------------------------------------------------------------------------------------
	*/

	/*
	* get user data
	* grabs the data on the user and places it in the $this->user array
	* if user_queue is true then we just grab the user_ids from the user_queue, otherwise we select data from just 1 user at a time.
	*/
	function get_user_data($id, $user_queue = false)
	{
		global $user, $db, $phpbb_root_path, $phpEx, $config, $auth, $cp, $bbcode;

		// if we are using the user_queue, set $user_id as that for consistency
		if ($user_queue)
		{
			$id = $this->user_queue;
		}

		// if the $user_id isn't an array, make it one for consistency
		if (!is_array($id))
		{
			$id = array($id);
		}

		// this holds the user_id's we will query
		$users_to_query = array();

		foreach ($id as $i)
		{
			if ( (!array_key_exists($i, $this->user)) && (!in_array($i, $users_to_query)) )
			{
				array_push($users_to_query, $i);
			}
		}

		if (count($users_to_query) == 0)
		{
			return;
		}

		// Grab all profile fields from users in id cache for later use - similar to the poster cache
		if ($config['user_blog_custom_profile_enable'])
		{
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

		// Get the rest of th data on the users and parse everything we need
		$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $db->sql_in_set('user_id', $users_to_query);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$user_id = $row['user_id'];

			// view profile link
			$row['view_profile'] = append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=viewprofile&amp;u=" . $user_id);
	
			// Full username, with colour
			$row['username_full'] = get_username_string('full', $user_id, $row['username'], $row['user_colour']);
	
			// format the color correctly
			$row['user_colour'] = get_username_string('colour', $user_id, $row['username'], $row['user_colour']);

			// Status
			$row['status'] = (isset($status_data[$user_id]) && time() - $update_time < $status_data[$user_id]['online_time'] && (($status_data[$user_id]['viewonline'] && $row['user_allow_viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
	
			// Avatar
			$row['avatar'] = get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']);
	
			// Rank
			get_user_rank($row['user_rank'], $row['user_posts'], $row['rank_title'], $row['rank_img'], $row['rank_img_src']);
	
			// IM Links
			$row['aim_url'] = ($row['user_aim']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=aim&amp;u=$user_id") : '';
			$row['icq_url'] = ($row['user_icq']) ? 'http://www.icq.com/people/webmsg.php?to=' . $row['user_icq'] : '';
			$row['jabber_url'] = ($row['user_jabber']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=jabber&amp;u=$user_id") : '';
			$row['msn_url'] = ($row['user_msnm']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=msnm&amp;u=$user_id") : '';
			$row['yim_url'] = ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg' : '';
	
			// PM and email links
			$row['email_url'] = ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;u=$user_id")  : (($config['board_hide_emails'] && !$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']);
			$row['pm_url'] = ($row['user_id'] != ANONYMOUS && $config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($row['user_allow_viewemail'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))) ? append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&amp;mode=compose&amp;u=$user_id") : '';

			// Signature
			if ($config['allow_sig'] && $user->optionget('viewsigs') && $row['user_sig'] != '')
			{
				$row['user_sig'] = censor_text($row['user_sig']);
				$row['user_sig'] = str_replace("\n", '<br />', $row['user_sig']);

				if ($row['user_sig_bbcode_bitfield'])
				{
					$bbcode->bbcode_second_pass($row['user_sig'], $row['user_sig_bbcode_uid'], $row['user_sig_bbcode_bitfield']);
				}

				$row['user_sig'] = smiley_text($row['user_sig']);
			}
			else
			{
				$row['user_sig'] = '';
			}

			// get the custom profile fields if the admin wants them
			if ($config['user_blog_custom_profile_enable'])
			{
				$row['cp_row'] = (isset($profile_fields_cache[$user_id])) ? $cp->generate_profile_fields_template('show', false, $profile_fields_cache[$user_id]) : array();
			}

			// now lets put everything in the user array
			$this->user[$user_id] = $row;
		}
		$db->sql_freeresult($result);

		// if we did use the user_queue, reset it
		if ($user_queue)
		{
			unset($this->user_queue);
			$this->user_queue = array();
		}
	}
	
	// prepares the user data for output to the template, and outputs the custom profile rows when requested
	// Mostly for shortenting up code
	function handle_user_data($user_id, $output_custom = false)
	{
		global $phpbb_root_path, $phpEx, $user, $auth, $config, $template, $user_founder;

		if ($output_custom == false)
		{
			$output_data = array(
				'AVATAR'			=> $this->user[$user_id]['avatar'],
				'POSTER_FROM'		=> $this->user[$user_id]['user_from'],
				'POSTER_JOINED'		=> $user->format_date($this->user[$user_id]['user_regdate']),
				'POSTER_POSTS'		=> $this->user[$user_id]['user_posts'],
				'RANK_IMG'			=> $this->user[$user_id]['rank_img'],
				'RANK_IMG_SRC'		=> $this->user[$user_id]['rank_img_src'],
				'RANK_TITLE'		=> $this->user[$user_id]['rank_title'],
				'SIGNATURE'			=> $this->user[$user_id]['user_sig'],
				'STATUS_IMG'		=> (($this->user[$user_id]['status']) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
				'USER_COLOUR'		=> $this->user[$user_id]['user_colour'],
				'USER_FULL'			=> $this->user[$user_id]['username_full'],
				'USERNAME'			=> $this->user[$user_id]['username'],

				'U_AIM'				=> $this->user[$user_id]['aim_url'],
				'U_DELETED_LINK'	=> ($auth->acl_get('m_blogreplydelete') || $user_founder) ? '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "mode=deleted&amp;u=$user_id") . '">' . $user->lang['VIEW_DELETED_BLOGS'] . '</a>' : '',
				'U_EMAIL'			=> $this->user[$user_id]['email_url'],
				'U_ICQ'				=> $this->user[$user_id]['icq_url'],
				'U_JABBER'			=> $this->user[$user_id]['jabber_url'],
				'U_MSN'				=> $this->user[$user_id]['msn_url'],
				'U_PM'				=> $this->user[$user_id]['pm_url'],
				'U_VIEW_PROFILE'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=viewprofile&amp;u=$user_id"),
				'U_WWW'				=> $this->user[$user_id]['user_website'],
				'U_YIM'				=> $this->user[$user_id]['yim_url'],

				'S_CUSTOM_FIELDS'	=> (isset($this->user[$user_id]['cp_row']['blockrow'])) ? true : false,
				'S_ONLINE'			=> $this->user[$user_id]['status'],
			);

			return ($output_data);
		}
		else 
		{
			if ($config['user_blog_custom_profile_enable'])
			{	
				// output the custom profile fields
				if (isset($this->user[$user_id]['cp_row']['blockrow']))
				{
					foreach ($this->user[$user_id]['cp_row']['blockrow'] as $row)
					{
						$template->assign_block_vars($output_custom, array(
							'PROFILE_FIELD_NAME'	=> $row['PROFILE_FIELD_NAME'],
							'PROFILE_FIELD_VALUE'	=> $row['PROFILE_FIELD_VALUE'],
						));
					}
				}
			}

			// add the blog links in the custom fields
			add_blog_links($user_id, $output_custom, $this->user[$user_id]);
		}
	}

	/*
	* -------------------------- OTHER SECTION --------------------------------------------------------------------------------------------------------------------------------------------------------
	*/

	/*
	* trims the length of the text of the requested blog_id and returns it
	* normally we return false if the text was not shortened, but if always_return is true we return the full text if it wasn't shortened
	*/
	function trim_text_length($blog_id, $reply_id, $str_limit, $always_return = false)
	{
		global $phpbb_root_path, $phpEx, $user;

		$bbcode_bitfield = $text_only_message = $text = '';

		if ($blog_id !== false)
		{
			$data = $this->blog[$blog_id];
			$original_text = $data['blog_text'];
		}
		else
		{
			if ($reply_id === false)
			{
				return false;
			}

			$data = $this->reply[$reply_id];
			$blog_id = $data['blog_id'];
			$original_text = $data['reply_text'];
		}

		$text = $original_text;

		decode_message($text, $data['bbcode_uid']);

		if (utf8_strlen($text) > $str_limit)
		{
			// make sure we don't cut off any words, etc
			while (substr($text, $str_limit, 1) != ' ' && substr($text, $str_limit, 1) != "\n" && $str_limit < strlen($text))
			{
				$str_limit+=2;
			}

			// now trim the text
			$text = substr($text, 0, $str_limit);

			// Now lets get the URL's back and nl2br
			$message_parser = new parse_message();
			$message_parser->message = $text;
			$message_parser->parse($data['enable_bbcode'], $data['enable_magic_url'], $data['enable_smilies']);
			$text = $message_parser->format_display($data['enable_bbcode'], $data['enable_magic_url'], $data['enable_smilies'], false);
			unset($message_parser);

			$text .= '...<br/><br/><!-- m --><a href="';
			if ($reply_id !== false)
			{
				$text .= append_sid("{$phpbb_root_path}blog.$phpEx", "b={$blog_id}r={$reply_id}#r{$reply_id}");
			}
			else
			{
				$text .= append_sid("{$phpbb_root_path}blog.$phpEx", "b=$blog_id");
			}
			$text .= '">[ ' . $user->lang['CONTINUED'] . ' ]</a><!-- m -->';

			return $text;
		}
		else
		{
			if ($always_return)
			{
				return $original_text;
			}
			else
			{
				return false;
			}
		}
	}

	/*
	* Updates the blog and reply information to add edit and delete messages.
	* I have this seperate so I can grab the blogs, replies, users, then update the edit and delete data (to cut on SQL queries)
	*/
	function update_edit_delete($mode = 'all')
	{
		global $auth, $user, $phpbb_root_path, $phpEx;

		if (!isset($user->lang['EDITED_TIME_TOTAL']))
		{
			$user->add_lang('viewtopic');
		}

		if ($mode == 'all' || $mode == 'blog')
		{
			foreach ($this->blog as $row)
			{
				if ((!isset($row['edited_message'])) && (!isset($row['deleted_message'])) )
				{
					$blog_id = $row['blog_id'];

					// has the blog been edited?
					if ($row['blog_edit_count'] != 0)
					{	
						if ($row['blog_edit_count'] == 1)
						{
							if ($auth->acl_get('u_viewprofile'))
							{
								$this->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $this->user[$row['blog_edit_user']]['username_full'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
							else
							{
								if ($this->user[$row['blog_edit_user']]['user_colour'] != '')
								{
									$this->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], '<b style="color: ' . $this->user[$row['blog_edit_user']]['user_colour'] . '">' . $this->user[$row['blog_edit_user']]['username'] . '</b>', $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
								}
								else
								{
									$this->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $this->user[$row['blog_edit_user']]['username'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
								}
							}
						}
						else if ($row['blog_edit_count'] > 1)
						{
							if ($auth->acl_get('u_viewprofile'))
							{
								$this->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $this->user[$row['blog_edit_user']]['username_full'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
							else
							{
								if ($this->user[$row['blog_edit_user']]['user_colour'] != '')
								{
									$this->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], '<b style="color: ' . $this->user[$row['blog_edit_user']]['user_colour'] . '">' . $this->user[$row['blog_edit_user']]['username'] . '</b>', $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
								}
								else
								{
									$this->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $this->user[$row['blog_edit_user']]['username'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
								}
							}
						}
			
						$this->blog[$blog_id]['edit_reason'] = censor_text($row['blog_edit_reason']);
					}
					else
					{
						$this->blog[$blog_id]['edited_message'] = '';
						$this->blog[$blog_id]['edit_reason'] = '';
					}
		
					// has the blog been deleted?
					if ($row['blog_deleted'] != 0)
					{
						$this->blog[$blog_id]['deleted_message'] = sprintf($user->lang['BLOG_IS_DELETED'], $this->user[$row['blog_deleted']]['username_full'], $user->format_date($row['blog_deleted_time']), '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "page=blog&amp;mode=undelete&amp;b=$blog_id") . '">', '</a>');
					}
					else
					{
						$this->blog[$blog_id]['deleted_message'] = '';
					}
				}
			}
		}

		if ($mode == 'all' || $mode == 'reply')
		{
			foreach ($this->reply as $row)
			{
				if ((!isset($row['edited_message'])) && (!isset($row['deleted_message'])) )
				{
					$reply_id = $row['reply_id'];

					// has the reply been edited?
					if ($row['reply_edit_count'] != 0)
					{	
						if ($row['reply_edit_count'] == 1)
						{
							if ($auth->acl_get('u_viewprofile'))
							{
								$this->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $this->user[$row['reply_edit_user']]['username_full'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
							else
							{
								if ($this->user[$row['reply_edit_user']]['user_colour'] != '')
								{
									$this->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], '<b style="color: ' . $this->user[$row['reply_edit_user']]['user_colour'] . '">' . $this->user[$row['reply_edit_user']]['username'] . '</b>', $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
								}
								else
								{
									$this->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $this->user[$row['reply_edit_user']]['username'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
								}
							}
						}
						else if ($row['reply_edit_count'] > 1)
						{
							if ($auth->acl_get('u_viewprofile'))
							{
								$this->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $this->user[$row['reply_edit_user']]['username_full'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
							else
							{
								if ($this->user[$row['reply_edit_user']]['user_colour'] != '')
								{
									$this->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], '<b style="color: ' . $this->user[$row['reply_edit_user']]['user_colour'] . '">' . $this->user[$row['reply_edit_user']]['username'] . '</b>', $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
								}
								else
								{
									$this->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $this->user[$row['reply_edit_user']]['username'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
								}
							}
						}
			
						$this->reply[$reply_id]['edit_reason'] = censor_text($row['reply_edit_reason']);
					}
					else
					{
						$this->reply[$reply_id]['edited_message'] = '';
						$this->reply[$reply_id]['edit_reason'] = '';
					}
		
					// has the reply been deleted?
					if ($row['reply_deleted'] != 0)
					{
						$this->reply[$reply_id]['deleted_message'] = sprintf($user->lang['REPLY_IS_DELETED'], $this->user[$row['reply_deleted']]['username_full'], $user->format_date($row['reply_deleted_time']), '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=undelete&amp;r=$reply_id") . '">', '</a>');
					}
					else
					{
						$this->reply[$reply_id]['deleted_message'] = '';
					}
				}
			}
		}
	}

	/*
	* Fix SQL function
	* Checks to make sure there is a WHERE if there are any AND sections in the SQL and fixes them appropriately
	*/
	function fix_sql($sql)
	{
		if (!strpos($sql, 'WHERE') && strpos($sql, 'AND'))
		{
			return substr($sql, 0, strpos($sql, 'AND')) . 'WHERE' . substr($sql, strpos($sql, 'AND') + 3);
		}

		return $sql;
	}
}
?>