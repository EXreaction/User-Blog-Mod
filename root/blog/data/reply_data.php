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
		global $blog_data, $user_data, $blog_plugins;

		$blog_plugins->plugin_do_arg_ref('reply_data_start', $selection_data);

		// input options for selection_data
		$start		= (isset($selection_data['start'])) ? $selection_data['start'] :			0;			// the start used in the Limit sql query
		$limit		= (isset($selection_data['limit'])) ? $selection_data['limit'] :			10;			// the limit on how many blogs we will select
		$order_by	= (isset($selection_data['order_by'])) ? $selection_data['order_by'] :		'reply_id';	// the way we want to order the request in the SQL query
		$order_dir	= (isset($selection_data['order_dir'])) ? $selection_data['order_dir'] :	'DESC';		// the direction we want to order the request in the SQL query
		$sort_days	= (isset($selection_data['sort_days'])) ? $selection_data['sort_days'] : 	0;			// the sort days selection
		$custom_sql	= (isset($selection_data['custom_sql'])) ? $selection_data['custom_sql'] : 	'';			// add your own custom WHERE part to the query

		// Setup some variables...
		$reply_ids = array();

		// make sure $id is an array for consistency
		if (!is_array($id))
		{
			$id = array(intval($id));
		}

		$sql_array = array(
			'SELECT'	=> '*',
			'FROM'		=> array(
				BLOGS_REPLY_TABLE	=> array('r'),
			),
			'ORDER_BY'	=> $order_by . ' ' . $order_dir
		);
		$sql_where = array();

		if (!$auth->acl_get('m_blogreplyapprove'))
		{
			$sql_where[] = 'reply_approved = 1';
		}
		if (!$auth->acl_gets('m_blogreplydelete', 'a_blogreplydelete'))
		{
			$sql_where[] = 'reply_deleted = 0';
		}
		if ($sort_days != 0)
		{
			$sql_where[] = 'reply_time >= ' . (time() - $sort_days * 86400);
		}
		if ($custom_sql)
		{
			$sql_where[] = $custom_sql;
		}

		switch ($mode)
		{
			case 'blog' : // view all replys by a blog_id
				$sql_where[] = $db->sql_in_set('blog_id', $id);
				break;
			case 'reply' : // select replies by reply_id(s)
				$sql_where[] = $db->sql_in_set('reply_id', $id);
				$limit = 0;
				break;
			case 'reported' : // select reported replies
				if (!$auth->acl_get('m_blogreplyreport'))
				{
					return false;
				}

				$sql_where[] = 'reply_reported = 1';
				break;
			case 'disapproved' : // select disapproved replies
				if (!$auth->acl_get('m_blogreplyapprove'))
				{
					return false;
				}

				$sql_where[] = 'reply_approved = 0';
				break;
			case 'reply_count' : // for counting how many replies there are for a blog
				if ($blog_data->blog[$id[0]]['blog_real_reply_count'] == 0 || $blog_data->blog[$id[0]]['blog_real_reply_count'] == $blog_data->blog[$id[0]]['blog_reply_count'])
				{
					return $blog_data->blog[$id[0]]['blog_real_reply_count'];
				}

				if ($sort_days == 0 && ($auth->acl_get('m_blogreplyapprove') && $auth->acl_gets('m_blogreplydelete', 'a_blogreplydelete')))
				{
					return $blog_data->blog[$id[0]]['blog_real_reply_count'];
				}
				else if ($auth->acl_get('m_blogreplyapprove') || $auth->acl_gets('m_blogreplydelete', 'a_blogreplydelete') || $sort_days != 0)
				{
					$sql_array['SELECT'] = 'count(reply_id) AS total';
					$sql_where[] = 'blog_id = ' . $id[0] . '';
					$sql_array['WHERE'] = implode(' AND ', $sql_where);
					$sql = $db->sql_build_query('SELECT', $sql_array);
					$result = $db->sql_query($sql);
					$total = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
					return $total['total'];
				}
				else
				{
					return $blog_data->blog[$id[0]]['blog_reply_count'];
				}
				break;
			case 'page' :
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
			default :
				return false;
		}

		$temp = compact('sql_array', 'sql_where');
		$blog_plugins->plugin_do_arg_ref('reply_data_sql', $temp);
		extract($temp);

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
			$blog_plugins->plugin_do_arg_ref('reply_data_while', $row);

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
		global $blog_data, $user_data, $blog_plugins, $category_id;

		static $blog_categories = false;

		if ($blog_categories === false)
		{
			$blog_categories = get_blog_categories('category_id');
		}

		$reply = &$this->reply[$id];
		$blog_id = $reply['blog_id'];
		$user_id = $reply['user_id'];

		$blog_plugins->plugin_do('reply_handle_data_start');

		// censor the text of the subject
		$reply_subject = censor_text($reply['reply_subject']);

		// Parse BBCode and prepare the message for viewing
		$bbcode_options = (($reply['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($reply['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($reply['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
		$reply_text = generate_text_for_display($reply['reply_text'], $reply['bbcode_uid'], $reply['bbcode_bitfield'], $bbcode_options);

		// For Highlighting
		if ($highlight_match)
		{
			$reply_subject = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $reply_subject);
			$reply_text = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $reply_text);
		}

		$replyrow = array(
			'ID'				=> $id,
			'TITLE'				=> $reply_subject,
			'DATE'				=> $user->format_date($reply['reply_time']),
			'REPLY_EXTRA'		=> '',

			'REPLY_MESSAGE'		=> $reply_text,

			'EDITED_MESSAGE'	=> $reply['edited_message'],
			'EDIT_REASON'		=> $reply['edit_reason'],
			'DELETED_MESSAGE'	=> $reply['deleted_message'],

			'U_VIEW'			=> ($category_id && isset($blog_categories[$category_id])) ? blog_url(false, $blog_id, $id, array('page' => $blog_categories[$category_id]['category_name'], 'c' => $category_id)) : blog_url($user_id, $blog_id, $id),
			'U_VIEW_PERMANENT'	=> blog_url($user_id, $blog_id, $id, array(), array(), true),

			'U_APPROVE'			=> ($reply['reply_approved'] == 0) ? blog_url($user_id, $blog_id, $id, array('page' => 'reply', 'mode' => 'approve', 'c' => (($category_id && isset($blog_categories[$category_id])) ? $category_id : '*skip*'))) : '',
			'U_DELETE'			=> (check_blog_permissions('reply', 'delete', true, $blog_id, $id)) ? blog_url($user_id, $blog_id, $id, array('page' => 'reply', 'mode' => 'delete', 'c' => (($category_id && isset($blog_categories[$category_id])) ? $category_id : '*skip*'))) : '',
			'U_EDIT'			=> (check_blog_permissions('reply', 'edit', true, $blog_id, $id)) ? blog_url($user_id, $blog_id, $id, array('page' => 'reply', 'mode' => 'edit', 'c' => (($category_id && isset($blog_categories[$category_id])) ? $category_id : '*skip*'))) : '',
			'U_QUOTE'			=> (check_blog_permissions('reply', 'quote', true, $blog_id, $id)) ? blog_url($user_id, $blog_id, $id, array('page' => 'reply', 'mode' => 'quote', 'c' => (($category_id && isset($blog_categories[$category_id])) ? $category_id : '*skip*'))) : '',
			'U_REPORT'			=> (check_blog_permissions('reply', 'report', true, $blog_id, $id)) ? blog_url($user_id, $blog_id, $id, array('page' => 'reply', 'mode' => 'report', 'c' => (($category_id && isset($blog_categories[$category_id])) ? $category_id : '*skip*'))) : '',
			'U_WARN'			=> (($auth->acl_get('m_warn')) && $reply['user_id'] != $user->data['user_id'] && $reply['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=warn&amp;mode=warn_user&amp;u=$user_id") : '',

			'S_DELETED'			=> ($reply['reply_deleted'] != 0) ? true : false,
			'S_UNAPPROVED'		=> ($reply['reply_approved'] == 0) ? true : false,
			'S_REPORTED'		=> ($reply['reply_reported'] && $auth->acl_get('m_blogreplyreport')) ? true : false,
		);

		$blog_plugins->plugin_do_arg_ref('reply_handle_data_end', $replyrow);

		return $replyrow;
	}
}
?>