<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: vote.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

// If they did not include the $blog_id give them an error...
if ($blog_id == 0)
{
	trigger_error('BLOG_NOT_EXIST');
}

$voted_id = request_var('vote_id', array('' => 0));
$blog_data->get_polls($blog_id);

if (sizeof($voted_id) > blog_data::$blog[$blog_id]['poll_max_options'])
{
	trigger_error('TOO_MANY_VOTE_OPTIONS');
}

// Are they trying to re-vote?
if (isset(blog_data::$blog[$blog_id]['poll_votes']['my_vote']) && sizeof(blog_data::$blog[$blog_id]['poll_votes']['my_vote']))
{
	if (!$auth->acl_get('u_blog_vote_change') || !blog_data::$blog[$blog_id]['poll_vote_change'])
	{
		trigger_error('NOT_ALLOWED_CHANGE_VOTE');
	}

	// Let us do this the quickest and easiest way possible.
	$sql = 'UPDATE ' . BLOGS_POLL_OPTIONS_TABLE . '
		SET poll_option_total = poll_option_total - 1
			WHERE blog_id = ' . intval($blog_id) . '
				AND ' . $db->sql_in_set('poll_option_id', blog_data::$blog[$blog_id]['poll_votes']['my_vote']);
	$db->sql_query($sql);

	$sql = 'DELETE FROM ' . BLOGS_POLL_VOTES_TABLE . '
		WHERE blog_id = ' . intval($blog_id) . '
			AND vote_user_id = ' . $user->data['user_id'];
	$db->sql_query($sql);
}

$votes_ary = $vote_ids = array();

foreach ($voted_id as $id)
{
	if (isset(blog_data::$blog[$blog_id]['poll_options'][$id]))
	{
		$vote_ids[] = $id;

		$votes_ary[] = array(
			'blog_id'			=> (int) $blog_id,
			'poll_option_id'	=> (int) $id,
			'vote_user_id'		=> $user->data['user_id'],
			'vote_user_ip'		=> $user->ip,
		);
	}
}

// They might be removing their votes...so check.
if (sizeof($votes_ary))
{
	$db->sql_multi_insert(BLOGS_POLL_VOTES_TABLE, $votes_ary);

	$sql = 'UPDATE ' . BLOGS_POLL_OPTIONS_TABLE . '
		SET poll_option_total = poll_option_total + 1
			WHERE blog_id = ' . intval($blog_id) . '
				AND ' . $db->sql_in_set('poll_option_id', $vote_ids);
	$db->sql_query($sql);

	if ($user->data['user_id'] == ANONYMOUS && !$user->data['is_bot'])
	{
		$user->set_cookie('poll_' . $blog_id, implode(',', $voted_id), time() + 31536000);
	}

	$sql = 'UPDATE ' . BLOGS_TABLE . '
		SET poll_last_vote = ' . time() . '
			WHERE blog_id = ' . intval($blog_id);
	$db->sql_query($sql);
}

// Now reset the options & votes for the blog...so they don't get counted twice.
blog_data::$blog[$blog_id]['poll_options'] = blog_data::$blog[$blog_id]['poll_votes'] = array();

?>