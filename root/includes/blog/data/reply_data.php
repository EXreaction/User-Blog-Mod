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
 * Reply data class
 *
 * For grabbing/handling all reply data
 */
class reply_data
{
	// this is our large array holding all the data
	var $reply = array();

	/**
	 * Get reply data
	 *
	 * To select reply data from the database
	 *
	 * @param string $mode The mode we want
	 * @param int $id To input the wanted blog_id, this may be an array if you want to select more than 1
	 * @param array $selection_data For extras, like start, limit, order by, order direction, etc, all of the options are listed a few lines below
	 */
	function get_reply_data($mode, $id = 0, $selection_data = array())
	{
		global $db, $user, $phpbb_root_path, $phpEx, $auth;
		global $blog_data, $user_data, $user_founder;

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
					return $blog_data->blog[$id[0]]['blog_real_reply_count'];
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
					return $blog_data->blog[$id[0]]['blog_reply_count'];
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
			array_push($user_data->user_queue, $row['user_id']);

			// has the reply been edited?  If so add that user to the user_queue
			if ($row['reply_edit_count'] != 0)
			{
				array_push($user_data->user_queue, $row['reply_edit_user']);
			}
	
			// has the reply been deleted?  If so add that user to the user_queue
			if ($row['reply_deleted'] != 0)
			{
				array_push($user_data->user_queue, $row['reply_deleted']);
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

	/**
	 * Handle reply data
	 *
	 * To handle the raw data gotten from the database
	 *
	 * @param int $id The id of the reply we want to handle
	 */
	function handle_reply_data($id)
	{
		global $user, $phpbb_root_path, $phpEx, $auth, $highlight_match;
		global $blog_data, $user_data, $user_founder;

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
}
?>