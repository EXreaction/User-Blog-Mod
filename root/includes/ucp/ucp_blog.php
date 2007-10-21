<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class ucp_blog
{
	var $u_action;

	function main($id, $mode)
	{
		global $cache, $template, $user, $db, $config, $phpEx, $phpbb_root_path;

		$submit = (isset($_POST['submit'])) ? true : false;
		$error = array();

		include($phpbb_root_path . 'blog/functions.' . $phpEx);

		switch ($mode)
		{
			case 'ucp_blog_permissions' :
				$sql = 'SELECT * FROM ' . BLOGS_PERMISSIONS_TABLE . ' WHERE user_id = \'' . $user->data['user_id'] . '\'';
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($submit)
				{
					$sql_ary = array(
						'user_id'		=> $user->data['user_id'],
						'guest'			=> request_var('guest_permissions', 2),
						'registered'	=> request_var('registered_permissions', 2),
						'foe'			=> request_var('foe_permissions', 2),
						'friend'		=> request_var('friend_permissions', 2),
					);

					if (!$row)
					{
						$sql = 'INSERT INTO ' . BLOGS_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
						$db->sql_query($sql);
					}
					else
					{
						$sql = 'UPDATE ' . BLOGS_PERMISSIONS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary);
						$db->sql_query($sql);
					}

					$filename = 'sql_' . md5('SELECT * FROM ' . BLOGS_PERMISSIONS_TABLE . ' WHERE user_id = \'' . $user->data['user_id'] . '\'') . '.' . $phpEx;
					if (file_exists($cache->cache_dir . $filename))
					{
						@unlink($cache->cache_dir . $filename);
					}
				}
				else
				{
					if (!$row)
					{
						$row = array(
							'guest'			=> 2,
							'registered'	=> 2,
							'foe'			=> 2,
							'friend'		=> 2,
						);
					}

					$template->assign_vars(array(
						'SET_PERMISSIONS'				=> true,
						'SET_FOE_FRIEND'				=> $config['user_blog_enable_zebra'],
						'S_GUEST_PERMISSIONS'			=> $row['guest'],
						'S_REGISTERED_PERMISSIONS'		=> $row['registered'],
						'S_FOE_PERMISSIONS'				=> $row['foe'],
						'S_FRIEND_PERMISSIONS'			=> $row['friend'],
					));
				}
			break;
			default;
				trigger_error('NO_MODE');
		}

		if ($submit)
		{
			meta_refresh(3, $this->u_action);
			$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
			trigger_error($message);
		}

		$template->assign_vars(array(
			'L_TITLE'				=> $user->lang[strtoupper($mode)],
			'L_TITLE_EXPLAIN'		=> $user->lang[strtoupper($mode) . '_EXPLAIN'],
			'ERROR'					=> (count($error)) ? implode($error, '<br/>') : false,
		));

		$this->tpl_name = 'ucp_blog';
		$this->page_title = strtoupper($mode);
	}
}

?>