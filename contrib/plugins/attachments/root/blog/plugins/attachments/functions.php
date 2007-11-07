<?php
/**
*
* @package phpBB3 User Blog Attachments
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

function attach_function_handle_basic_posting_data(&$arg)
{
	global $auth, $blog_attachment, $config, $user, $template;

	$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments'] || !$auth->acl_get('u_attach')) ? '' : ' enctype="multipart/form-data"';
	if (($auth->acl_get('u_blogattach')) && $config['allow_attachments'] && $form_enctype)
	{
		$allowed_extensions = $blog_attachment->obtain_blog_attach_extensions();

		if (count($allowed_extensions['_allowed_']))
		{
			$arg['panels']['attach-panel'] = $user->lang['ADD_ATTACHMENT'];

			$template->set_filenames(array(
				'attach_panel'			=> 'blog/plugins/attachments/attach_panel.html',
				'attach_above_submit'	=> 'blog/plugins/attachments/attach_above_submit.html',
			));

			$arg['panel_data'] .= $template->assign_display('attach_panel');
			$arg['above_submit'] .= $template->assign_display('attach_above_submit');
		}
	}
}

function attach_blog_page_switch(&$arg)
{
	global $auth, $config, $db, $page, $phpbb_root_path, $phpEx, $user;
	global $blog_attachment, $blog_plugins;

	if ($page == 'download')
	{
		include($phpbb_root_path . "blog/plugins/attachments/download.$phpEx");
		$arg = false;
	}
}

function attach_blog_add_after_setup(&$arg)
{
	global $blog_attachment;
	global $submit, $preview, $refresh, $error;

	$blog_attachment->get_submitted_attachment_data();

	if ($submit || $preview || $refresh)
	{
		$blog_attachment->parse_attachments('fileupload', $submit, $preview, $refresh, $arg);

		if (sizeof($blog_attachment->warn_msg))
		{
			$error[] = implode('<br />', $blog_attachment->warn_msg);
		}
	}
}

function attach_blog_add_preview(&$args)
{
	global $blog_attachment;
	global $template, $user;

	if (sizeof($blog_attachment->attachment_data))
	{
		$template->assign_var('S_HAS_ATTACHMENTS', true);

		$update_count = array();
		$attachment_data = $blog_attachment->attachment_data;

		$blog_attachment->parse_attachments_for_view($args, $attachment_data, $update_count, true);

		foreach ($attachment_data as $row)
		{
			$template->assign_block_vars('attachment', array(
				'DISPLAY_ATTACHMENT' => $row,
			));
		}

		unset($attachment_data);
	}
}

function attach_blog_add_after_preview($args = false)
{
	global $blog_attachment;
	global $auth, $config, $phpbb_root_path, $phpEx, $template;

	$attachment_data = $blog_attachment->attachment_data;
	$filename_data = $blog_attachment->filename_data;
	$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments'] || !$auth->acl_get('u_attach')) ? '' : ' enctype="multipart/form-data"';

	posting_gen_inline_attachments($attachment_data);

	if (($auth->acl_get('u_blogattach')) && $config['allow_attachments'] && $form_enctype)
	{
		$allowed_extensions = $blog_attachment->obtain_blog_attach_extensions();

		if (count($allowed_extensions['_allowed_']))
		{
			$blog_attachment->posting_gen_attachment_entry($attachment_data, $filename_data);
		}
	}

	$template->assign_vars(array(
		'UA_PROGRESS_BAR'			=> append_sid("{$phpbb_root_path}posting.$phpEx", "mode=popup", false),
		'S_CLOSE_PROGRESS_WINDOW'	=> (isset($_POST['add_file'])) ? true : false,
		'S_FORM_ENCTYPE'			=> $form_enctype,
	));
}

function attach_blog_add_sql(&$args)
{
	global $blog_attachment;

	$args['blog_attachment'] = ((count($blog_attachment->attachment_data)) ? 1 : 0);
}

function attach_blog_add_after_sql($args)
{
	global $blog_attachment;

	$blog_attachment->update_attachment_data($args);
}

function attach_blog_delete_confirm($args = false)
{
	global $blog_attachment;
	global $auth, $phpbb_root_path;
	global $blog_data, $blog_id;

	if ($blog_data->blog[$blog_id]['blog_deleted'] != 0 && $auth->acl_get('a_blogdelete'))
	{
		$blog_attachment->get_attachment_data($blog_id);
		if (count($blog_data->blog[$blog_id]['attachment_data']))
		{
			foreach ($blog_data->blog[$blog_id]['attachment_data'] as $null => $data)
			{
				@unlink($phpbb_root_path . 'files/blog_mod/' . $data['physical_filename']);
				$sql = 'DELETE FROM ' . BLOGS_ATTACHMENT_TABLE . ' WHERE attach_id = \'' . $data['attach_id'] . '\'';
				$db->sql_query($sql);
			}
		}
	}
}

function attach_blog_edit_after_setup(&$arg)
{
	global $blog_attachment;
	global $submit, $preview, $refresh, $error;
	global $blog_data, $blog_id;

	$blog_attachment->get_submitted_attachment_data();

	if (!$submit && !$preview && !$refresh)
	{
		$blog_attachment->get_attachment_data($blog_id);
		$blog_attachment->attachment_data = $blog_data->blog[$blog_id]['attachment_data'];
	}
	else
	{
		$blog_attachment->parse_attachments('fileupload', $submit, $preview, $refresh, $arg);

		if (sizeof($blog_attachment->warn_msg))
		{
			$error[] = implode('<br />', $blog_attachment->warn_msg);
		}
	}
}

function attach_blog_data_while(&$args)
{
	$args['attachment_data'] = array();
}

function attach_blog_handle_data_end(&$args)
{
	global $blog_attachment;
	global $auth, $user, $template;
	global $blog_data;

	$update_count = array();
	$blog_attachment->parse_attachments_for_view($args['BLOG_MESSAGE'], $blog_data->blog[$args['BLOG_ID']]['attachment_data'], $update_count);

	// Include the template file we need and set the handler name to attachment
	if (!isset($template->filename['attachment']))
	{
		$template->set_filenames(array(
			'attachment'        => 'blog/plugins/attachments/attach_view_body.html')
		);
	}

	// Now, send all data to the template parser as usual.  Make sure that if this data is sent more than once, you either unset it later, or always set it to overwrite any existing data.
	$template->assign_vars(array(
		'S_DISPLAY_NOTICE'		=> (!$auth->acl_get('u_download') && $blog_data->blog[$args['BLOG_ID']]['blog_attachment'] && count($blog_data->blog[$args['BLOG_ID']]['attachment_data'])) ? true : false,
		'S_HAS_ATTACHMENTS'		=> ($blog_data->blog[$args['BLOG_ID']]['blog_attachment'] && count($blog_data->blog[$args['BLOG_ID']]['attachment_data'])) ? true : false,
	));

	foreach ($blog_data->blog[$args['BLOG_ID']]['attachment_data'] as $row)
	{
		$template->assign_block_vars('attachment', array(
			'DISPLAY_ATTACHMENT'	=> $row,
		));
	}

	// Now we are adding the output from parsing the attachment handler to a variable which will be outputted later
	$args['BLOG_EXTRA'] .= $template->assign_display('attachment');

	// Now we need to unset the attachment data from the template data (otherwise the attachments from post 1 will show on post 2, 3, etc).
	unset($template->_tpldata['attachment']);
}

function attach_reply_handle_data_end(&$args)
{
	global $blog_attachment;
	global $auth, $user, $template;
	global $reply_data;

	$update_count = array();
	$blog_attachment->parse_attachments_for_view($args['REPLY_MESSAGE'], $reply_data->reply[$args['ID']]['attachment_data'], $update_count);

	// Include the template file we need and set the handler name to attachment
	if (!isset($template->filename['attachment']))
	{
		$template->set_filenames(array(
			'attachment'        => 'blog/plugins/attachments/attach_view_body.html')
		);
	}

	// Now, send all data to the template parser as usual.  Make sure that if this data is sent more than once, you either unset it later, or always set it to overwrite any existing data.
	$template->assign_vars(array(
		'S_DISPLAY_NOTICE'		=> (!$auth->acl_get('u_download') && $reply_data->reply[$args['ID']]['reply_attachment'] && count($reply_data->reply[$args['ID']]['attachment_data'])) ? true : false,
		'S_HAS_ATTACHMENTS'		=> ($reply_data->reply[$args['ID']]['reply_attachment'] && count($reply_data->reply[$args['ID']]['attachment_data'])) ? true : false,
	));

	foreach ($reply_data->reply[$args['ID']]['attachment_data'] as $row)
	{
		$template->assign_block_vars('attachment', array(
			'DISPLAY_ATTACHMENT'	=> $row,
		));
	}

	// Now we are adding the output from parsing the attachment handler to a variable which will be outputted later
	$args['REPLY_EXTRA'] .= $template->assign_display('attachment');

	// Now we need to unset the attachment data from the template data (otherwise the attachments from post 1 will show on post 2, 3, etc).
	unset($template->_tpldata['attachment']);
}

function attach_reply_add_sql(&$args)
{
	global $blog_attachment;

	$args['reply_attachment'] = ((count($blog_attachment->attachment_data)) ? 1 : 0);
}

function attach_reply_add_after_sql(&$args)
{
	global $blog_attachment;

	$blog_attachment->update_attachment_data(0, $args);
}

function attach_reply_edit_after_setup(&$arg)
{
	global $blog_attachment;
	global $submit, $preview, $refresh, $error;
	global $reply_data, $reply_id;

	$blog_attachment->get_submitted_attachment_data();

	if (!$submit && !$preview && !$refresh)
	{
		$blog_attachment->get_attachment_data(0, $reply_id);
		$blog_attachment->attachment_data = $reply_data->reply[$reply_id]['attachment_data'];
	}
	else
	{
		$blog_attachment->parse_attachments('fileupload', $submit, $preview, $refresh, $arg);

		if (sizeof($blog_attachment->warn_msg))
		{
			$error[] = implode('<br />', $blog_attachment->warn_msg);
		}
	}
}

function attach_view_blog_start($args = false)
{
	global $blog_attachment;
	global $blog_id, $reply_ids;

	$blog_attachment->get_attachment_data($blog_id, $reply_ids);
}

function attach_view_user_start($args = false)
{
	global $blog_attachment;
	global $blog_ids;

	$blog_attachment->get_attachment_data($blog_ids);
}

function attach_acp_main_settings(&$args)
{
	global $user;

	$args['legend_attach'] = 'BLOG_ATTACHMENT_SETTINGS';
	$args['user_blog_max_attachments'] = array('lang' => 'BLOG_MAX_ATTACHMENTS',			'validate' => 'int',	'type' => 'text:5:5',					'explain' => true);
}
?>