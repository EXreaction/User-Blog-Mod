<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: dev.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/*
 // Stuff required to work with phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup('common');

$mode = request_var('mode', '');
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

switch ($mode)
{
	case 'organize_lang' :
		/**
		* to input the file name use HTTP Get vars.
		*  For example, to organize language/en/common.php you type in en/common.  Do not include language/ or .php
		*  You may also send the directory name and it will organize all language files in it and any subdirectories.
		*/
		organize_lang();
	break;

	case 'check_lang' :
		/**
		* Checks language files for unused language keys.
		*/
		check_lang();
	break;

	case 'get_hooks' :
		get_hooks(); // Gets all available hooks and lists them in a file.  For more detail, check the function directly.
	break;

	case 'build_install' : // Build the Installation Schema Files
		build_blog_install();
	break;

	default :
		trigger_error('NO_MODE');
}

/**
* Checks for unused language keys
*/
function check_lang()
{
	global $phpbb_root_path, $phpEx;

	$lang = $used_lang = array();
	// The list of language files we will check for extra keys
	$lang_list = array(
		'mods/info_acp_blogs', 'mods/info_mcp_blogs', 'mods/info_ucp_blogs',
		'mods/blog/acp', 'mods/blog/common', 'mods/blog/mcp', 'mods/blog/misc', 'mods/blog/posting', 'mods/blog/setup', 'mods/blog/ucp', 'mods/blog/view',
	);
	foreach ($lang_list as $file)
	{
		require($phpbb_root_path . 'language/en/' . $file . '.' . $phpEx);
	}

	if ($handle = opendir($phpbb_root_path . 'root'))
	{
	    while (false !== ($file = readdir($handle)))
		{
			if (strpos($file, '.'))
			{
				$file_list[] = $file;
			}
			else
			{
				$dir_list[] = $file;
			}
	    }
	    closedir($handle);
	}

	// Looks a little strange, but it is being done this way so folders get listed first, then files (otherwise it just lists everything alphabetical file)
	if (sizeof($dir_list))
	{
		foreach ($dir_list as $file)
		{
			check_lang_recusive($lang, $used_lang, $file, $phpbb_root_path . 'root', $phpbb_root_path . 'root');
		}
	}

	if (sizeof($file_list))
	{
		foreach ($file_list as $file)
		{
			check_lang_recusive($lang, $used_lang, $file, $phpbb_root_path . 'root', $phpbb_root_path . 'root');
		}
	}

	var_dump(array_diff(array_keys($lang), $used_lang));
	die();
}

/**
* Helper for check_lang()
*/
function check_lang_recusive(&$lang, &$used_lang, $file, $dir, $original_dir)
{
	global $phpEx;

	if ($file == '.' || $file == '..')
	{
		return;
	}

	$file_list = $dir_list = array();

	if (is_dir($dir . '/' . $file))
	{
		if ($handle = opendir($dir . '/' . $file))
		{
		    while (false !== ($file1 = readdir($handle)))
			{
				if (substr($file, -3) == $phpEx)
				{
					$file_list[] = $file1;
				}
				else
				{
					$dir_list[] = $file1;
				}
		    }

		    closedir($handle);
		}

		if (sizeof($dir_list))
		{
			foreach ($dir_list as $file1)
			{
				check_lang_recusive($lang, $used_lang, $file1, $dir . '/' . $file, $original_dir);
			}
		}

		if (sizeof($file_list))
		{
			foreach ($file_list as $file1)
			{
				check_lang_recusive($lang, $used_lang, $file1, $dir . '/' . $file, $original_dir);
			}
		}
	}
	else if (substr($file, -3) == $phpEx || substr($file, -4) == 'html') // Skip non .php/.html files
	{
		//echo $dir . '/' . $file . '<br />';
		$patterns = array(
			'#{L_([A-Z0-9_]+)}#',
			'#lang\[\'([A-Z0-9_]+)\'\]#',
			'#trigger_error\(\'([A-Z0-9_]+)\'#',
			'#confirm_box\(false, \'([A-Z0-9_]+)\'#',
			'#blog_confirm\(\'([A-Z0-9_]+)\'#',
			'#this->page_title =([\s|\t]+)\'([A-Z0-9_]+)\'#',
			'#\'legend([a-zA-Z0-9\'\s\t]+)=> \'([A-Z0-9_]+)\'#',
			'#\'lang\'([\s|\t]+)=> \'([A-Z0-9_]+)\'#',
			'#\'title\'([\s|\t]+)=> \'([A-Z0-9_]+)\'#',
			'#add_log\(\'admin\', \'([A-Z0-9_]+)\'#',
			'#add_log\(\'mod\', \'([A-Z0-9_]+)\'#',
		);
		$handle = @fopen($dir . '/' . $file, "r");
		if ($handle)
		{
			while (!feof($handle))
			{
				$line = fgets($handle, 4096);

				foreach ($patterns as $pattern)
				{
					$matches = array();
					preg_match_all($pattern, $line, $matches);

					if (sizeof($matches[1]))
					{
						foreach ($matches[1] as $key)
						{
							if (trim($key) != '' && !in_array($key, $used_lang))
							{
								// Add the key and explain, confirm to the list
								$used_lang[] = $key;
								$used_lang[] = $key . '_EXPLAIN';
								$used_lang[] = $key . '_CONFIRM';
							}
						}
					}
					if (isset($matches[2]) && sizeof($matches[2]))
					{
						foreach ($matches[2] as $key)
						{
							if (trim($key) != '' && !in_array($key, $used_lang))
							{
								// Add the key and explain, confirm to the list
								$used_lang[] = $key;
								$used_lang[] = $key . '_EXPLAIN';
								$used_lang[] = $key . '_CONFIRM';
							}
						}
					}
				}
			}
			fclose($handle);
		}
	}
}

/**
* Build Blog Install Schemas
*/
function build_blog_install()
{
	global $phpbb_root_path, $phpEx;

	$schema_path = $phpbb_root_path . 'blog/install/schemas/';

	include($phpbb_root_path . 'blog/includes/create_schema_files.' . $phpEx);
}

/**
* Gets the available hooks and lists them in a text file.
*
* You must put all of the files from the root/ folder in the mod package into a folder named blog_hooks in the root phpBB3 directory.
* The hook list will be outputted to a file named blog_hooks.txt in the phpBB3 root directory.
*/
function get_hooks()
{
	global $phpbb_root_path;

	$hook_list = '';
	$file_list = $dir_list = array();

	if ($handle = opendir($phpbb_root_path . 'blog_hooks'))
	{
	    while (false !== ($file = readdir($handle)))
		{
			if (strpos($file, '.'))
			{
				$file_list[] = $file;
			}
			else
			{
				$dir_list[] = $file;
			}
	    }
	    closedir($handle);
	}

	// Looks a little strange, but it is being done this way so folders get listed first, then files (otherwise it just lists everything alphabetical file)
	if (sizeof($dir_list))
	{
		foreach ($dir_list as $file)
		{
			get_hooks_recusive($hook_list, $file, $phpbb_root_path . 'blog_hooks', $phpbb_root_path . 'blog_hooks');
		}
	}

	if (sizeof($file_list))
	{
		foreach ($file_list as $file)
		{
			get_hooks_recusive($hook_list, $file, $phpbb_root_path . 'blog_hooks', $phpbb_root_path . 'blog_hooks');
		}
	}

	if ($fp = @fopen($phpbb_root_path . 'blog_hooks.txt', 'wb'))
	{
		@flock($fp, LOCK_EX);
		fwrite($fp, $hook_list);
		@flock($fp, LOCK_UN);
		fclose($fp);
	}
}

/**
* Helper for get_hooks()
*/
function get_hooks_recusive(&$hook_list, $file, $dir, $original_dir)
{
	global $phpEx;

	if ($file == '.' || $file == '..')
	{
		return;
	}

	$file_list = $dir_list = array();

	if (is_dir($dir . '/' . $file))
	{
		if ($handle = opendir($dir . '/' . $file))
		{
		    while (false !== ($file1 = readdir($handle)))
			{
				if (substr($file, -3) == $phpEx)
				{
					$file_list[] = $file1;
				}
				else
				{
					$dir_list[] = $file1;
				}
		    }

		    closedir($handle);
		}

		if (sizeof($dir_list))
		{
			foreach ($dir_list as $file1)
			{
				get_hooks_recusive($hook_list, $file1, $dir . '/' . $file, $original_dir);
			}
		}

		if (sizeof($file_list))
		{
			foreach ($file_list as $file1)
			{
				get_hooks_recusive($hook_list, $file1, $dir . '/' . $file, $original_dir);
			}
		}
	}
	else if (substr($file, -3) == $phpEx && $file != "dev.$phpEx") // Skip non .php files
	{
		$handle = @fopen($dir . '/' . $file, "r");
		if ($handle)
		{
			$tmp_hook_list = '';
			while (!feof($handle))
			{
				$line = fgets($handle, 4096);

				if (strpos($line, 'blog_plugins::plugin_do') !== false)
				{
					$start_pos = strpos($line, "('") + 2;
					$tmp_hook_list .= "\t" . substr($line, $start_pos, strpos($line, "'", $start_pos) - $start_pos) . "\n";
				}
			}
			fclose($handle);

			if ($tmp_hook_list != '')
			{
				$hook_list .= substr($dir, strpos($dir, $original_dir) + strlen($original_dir)) . '/' . $file . "\n";
				$hook_list .= $tmp_hook_list;
				$hook_list .= "\n";
			}
		}
	}

}

/**
* For finding the max string length for the organize_lang function
*/
function find_max_length($lang, &$max_length, $start = 0)
{
	$start_length = $start * 4;

	foreach($lang as $name => $value)
	{
		if (is_array($value))
		{
			find_max_length($value, $max_length, ($start + 1));
		}

		if ((utf8_strlen($name) + $start_length) > $max_length)
		{
			$max_length = (utf8_strlen($name) + $start_length);
		}
	}
}

/**
* For outputting the lines for the organize_lang function
*/
function lang_lines($lang, $max_length, &$output, $start = 0)
{
	$last_letter = '';
	$total_tabs = ceil(($max_length + 3) / 4) - $start;

	if ($start != 0)
	{
		//ksort($lang);
	}

	$last_name = '';
	foreach($lang as $name => $value)
	{
		if ($name == $last_name)
		{
			echo 'Lang Duplicate: ' . $name . '<br />';
		}
		$last_name = $name;

		// make sure to add slashes to single quotes!
		$name = addcslashes($name, "'");

		// add an extra end line if the next word starts with a different letter then the last
		if (substr($name, 0, 1) != $last_letter && $start == 0)
		{
			$output .= "\n";
			$last_letter = substr($name, 0, 1);
		}

		// add the beggining tabs
		for ($i=0; $i <= $start; $i++)
		{
			$output .= "\t";
		}

		// add the beginning of the lang section and add slashes to single quotes for the name
		$output .= "'" . $name . "'";

		// figure out the number of tabs we need to add to the middle, then add them
		$tabs = ($total_tabs - ceil((utf8_strlen($name) + 3) / 4));

		for($i=0; $i <= $tabs; $i++)
		{
			$output .= "\t";
		}

		if (is_array($value))
		{
			$output .= "=> array(\n";
			lang_lines($value, $max_length, $output, ($start + 1));

			for ($i=0; $i <= $start; $i++)
			{
				$output .= "\t";
			}
			$output .= "),\n\n";
		}
		else
		{
			// add =>, then slashes to single quotes and add to the output
			$output .= "=> '" . addcslashes($value, "'") . "',\n";
		}
	}
}

/**
* Organize the language file by the lang keys, then re-output the data to the file
*
* The Filename is inputted by sending it via a HTTP Get variable
*/
function organize_lang($file = false, $skip_errors = false)
{
	global $phpbb_root_path, $phpEx;

	$file = ($file === false) ? request_var('file', 'en/mods/') : $file;

	if (substr($file, -1) == '/')
	{
		$file = substr($file, 0, -1);
	}

	// make sure they have a file name
	if ($file == '')
	{
		if ($skip_errors)
		{
			return;
		}

		trigger_error('No File Specified.');
	}

	// make sure they are not trying to get out of the language directory, otherwise this would be a security risk. ;)
	if (strpos($file, '.'))
	{
		trigger_error('You are not allowed out of the language/ directory.');
	}

	// If the user submitted a directory, do every language file in that directory
	if (is_dir($phpbb_root_path . 'language/' . $file))
	{
		if ($handle = opendir($phpbb_root_path . 'language/' . $file))
		{
		    while (false !== ($file1 = readdir($handle)))
			{
				if ($file1 == '.' || $file1 == '..' || $file1 == '.svn')
				{
					continue;
				}

				if (strpos($file1, ".$phpEx"))
				{
					organize_lang($file . '/' . substr($file1, 0, strpos($file1, ".$phpEx")), true);
				}
				else if (is_dir($phpbb_root_path . 'language/' . $file . '/' . $file1))
				{
					organize_lang($file . '/' . $file1);
				}
		    }
		    closedir($handle);
		}

		// if we went to a subdirectory, return
		if ($file != request_var('file', '') && $file . '/' != request_var('file', ''))
		{
			return;
		}

		trigger_error('Done organizing all of the language files in language/' . $file . '.');
	}

	// include the file
	@include($phpbb_root_path . 'language/' . $file . '.' . $phpEx);

	// make sure it is a valid language file
	if (!isset($lang) || !is_array($lang))
	{
		if ($skip_errors)
		{
			return;
		}

		trigger_error('Bad Language File. language/' . $file);
	}

	// setup the $output var
	$output = '';

	// lets get the header of the file...
	$handle = @fopen($phpbb_root_path . 'language/' . $file . '.' . $phpEx, "r");
	if ($handle)
	{
		$stopped = false;

		while (!feof($handle))
		{
			$line = fgets($handle, 4096);

			// if the line is $lang = array_merge($lang, array( break out of the while loop
			if ($line == '$lang = array_merge($lang, array(' . "\n")
			{
				$stopped = true;
				break;
			}

			$output .= $line;
		}
		fclose($handle);

		if (!$stopped)
		{
			if ($skip_errors)
			{
				echo 'Bad line endings in ' . $phpbb_root_path . 'language/' . $file . '.' . $phpEx . '<br />';
				return;
			}

			trigger_error('Please make sure you are using UNIX line endings.');
		}
	}

	// sort the languages by keys
	ksort($lang);

	// get the maximum length of the name string so we can format the page nicely when we output it
	$max_length = 1;

	find_max_length($lang, $max_length);

	// now add $lang = array_merge($lang, array( to the output
	$output .= '$lang = array_merge($lang, array(';

	lang_lines($lang, $max_length, $output);

	// add the end
	$output .= '));

?>';

	// write the contents to the specified file
	file_put_contents($phpbb_root_path . 'language/' . $file . '.' . $phpEx, $output);
}

function get_schema_struct()
{
	$schema_data = array();

/* Blog Tags Plugin
	$schema_data['phpbb_blogs_tags'] = array(
		'COLUMNS'		=> array(
			'tag_id'		=> array('UINT', NULL, 'auto_increment'),
			'tag_name'		=> array('MTEXT_UNI', ''),
			'tag_count'		=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'tag_id',
		'KEYS'			=> array(
			'tag_count'		=> array('INDEX', 'tag_count'),
		),
	);

	return $schema_data;
*/

/* Not currently used, probably won't ever be...
	$schema_data['phpbb_blog_search_results'] = array(
		'COLUMNS'		=> array(
			'search_key'			=> array('VCHAR:32', ''),
			'search_time'			=> array('TIMESTAMP', 0),
			'search_keywords'		=> array('MTEXT_UNI', ''),
			'search_authors'		=> array('MTEXT', ''),
		),
		'PRIMARY_KEY'	=> 'search_key',
	);
*/

	$schema_data['phpbb_blogs'] = array(
		'COLUMNS'		=> array(
			'blog_id'				=> array('UINT', NULL, 'auto_increment'),
			'user_id'				=> array('UINT', 0),
			'user_ip'				=> array('VCHAR:40', ''),
			'blog_subject'			=> array('STEXT_UNI', '', 'true_sort'),
			'blog_text'				=> array('MTEXT_UNI', ''),
			'blog_checksum'			=> array('VCHAR:32', ''),
			'blog_time'				=> array('TIMESTAMP', 0),
			'blog_approved'			=> array('BOOL', 1),
			'blog_reported'			=> array('BOOL', 0),
			'enable_bbcode'			=> array('BOOL', 1),
			'enable_smilies'		=> array('BOOL', 1),
			'enable_magic_url'		=> array('BOOL', 1),
			'bbcode_bitfield'		=> array('VCHAR:255', ''),
			'bbcode_uid'			=> array('VCHAR:8', ''),
			'blog_edit_time'		=> array('TIMESTAMP', 0),
			'blog_edit_reason'		=> array('STEXT_UNI', ''),
			'blog_edit_user'		=> array('UINT', 0),
			'blog_edit_count'		=> array('USINT', 0),
			'blog_edit_locked'		=> array('BOOL', 0),
			'blog_deleted'			=> array('UINT', 0),
			'blog_deleted_time'		=> array('TIMESTAMP', 0),
			'blog_read_count'		=> array('UINT', 1),
			'blog_reply_count'		=> array('UINT', 0),
			'blog_real_reply_count'	=> array('UINT', 0),
			'blog_attachment'		=> array('BOOL', 0),
			'perm_guest'			=> array('TINT:1', 1),
			'perm_registered'		=> array('TINT:1', 2),
			'perm_foe'				=> array('TINT:1', 0),
			'perm_friend'			=> array('TINT:1', 2),
			'rating'				=> array('DECIMAL:6', 0),
			'num_ratings'			=> array('UINT', 0),
			'poll_title'			=> array('STEXT_UNI', '', 'true_sort'),
			'poll_start'			=> array('TIMESTAMP', 0),
			'poll_length'			=> array('TIMESTAMP', 0),
			'poll_max_options'		=> array('TINT:4', 1),
			'poll_last_vote'		=> array('TIMESTAMP', 0),
			'poll_vote_change'		=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'blog_id',
		'KEYS'			=> array(
			'user_id'				=> array('INDEX', 'user_id'),
			'user_ip'				=> array('INDEX', 'user_ip'),
			'blog_approved'			=> array('INDEX', 'blog_approved'),
			'blog_deleted'			=> array('INDEX', 'blog_deleted'),
			'perm_guest'			=> array('INDEX', 'perm_guest'),
			'perm_registered'		=> array('INDEX', 'perm_registered'),
			'perm_foe'				=> array('INDEX', 'perm_foe'),
			'perm_friend'			=> array('INDEX', 'perm_friend'),
			'rating'				=> array('INDEX', 'rating'),
		),
	);

	$schema_data['phpbb_blogs_attachment'] = array(
		'COLUMNS'		=> array(
			'attach_id'				=> array('UINT', NULL, 'auto_increment'),
			'blog_id'				=> array('UINT', 0),
			'reply_id'				=> array('UINT', 0),
			'poster_id'				=> array('UINT', 0),
			'is_orphan'				=> array('BOOL', 1),
			'is_orphan'				=> array('BOOL', 1),
			'physical_filename'		=> array('VCHAR', ''),
			'real_filename'			=> array('VCHAR', ''),
			'download_count'		=> array('UINT', 0),
			'attach_comment'		=> array('TEXT_UNI', ''),
			'extension'				=> array('VCHAR:100', ''),
			'mimetype'				=> array('VCHAR:100', ''),
			'filesize'				=> array('UINT:20', 0),
			'filetime'				=> array('TIMESTAMP', 0),
			'thumbnail'				=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'attach_id',
		'KEYS'			=> array(
			'blog_id'				=> array('INDEX', 'blog_id'),
			'reply_id'				=> array('INDEX', 'reply_id'),
			'filetime'				=> array('INDEX', 'filetime'),
			'poster_id'				=> array('INDEX', 'poster_id'),
			'is_orphan'				=> array('INDEX', 'is_orphan'),
		),
	);

	$schema_data['phpbb_blogs_categories'] = array(
		'COLUMNS'		=> array(
			'category_id'					=> array('UINT', NULL, 'auto_increment'),
			'parent_id'						=> array('UINT', 0),
			'left_id'						=> array('UINT', 0),
			'right_id'						=> array('UINT', 0),
			'category_name'					=> array('STEXT_UNI', '', 'true_sort'),
			'category_description'			=> array('MTEXT_UNI', ''),
			'category_description_bitfield'	=> array('VCHAR:255', ''),
			'category_description_uid'		=> array('VCHAR:8', ''),
			'category_description_options'	=> array('UINT:11', 7),
			'rules'							=> array('MTEXT_UNI', ''),
			'rules_bitfield'				=> array('VCHAR:255', ''),
			'rules_uid'						=> array('VCHAR:8', ''),
			'rules_options'					=> array('UINT:11', 7),
			'blog_count'					=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'category_id',
		'KEYS'			=> array(
			'left_right_id'			=> array('INDEX', array('left_id', 'right_id')),
		),
	);

	$schema_data['phpbb_blogs_in_categories'] = array(
		'COLUMNS'		=> array(
			'blog_id'						=> array('UINT', 0),
			'category_id'					=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> array('blog_id', 'category_id'),
	);

	$schema_data['phpbb_blogs_plugins'] = array(
		'COLUMNS'		=> array(
			'plugin_id'				=> array('UINT', NULL, 'auto_increment'),
			'plugin_name'			=> array('STEXT_UNI', '', 'true_sort'),
			'plugin_enabled'		=> array('BOOL', 0),
			'plugin_version'		=> array('XSTEXT_UNI', '', 'true_sort'),
		),
		'PRIMARY_KEY'	=> 'plugin_id',
		'KEYS'			=> array(
			'plugin_name'			=> array('INDEX', 'plugin_name'),
			'plugin_enabled'		=> array('INDEX', 'plugin_enabled'),
		),
	);

	$schema_data['phpbb_blogs_poll_options'] = array(
		'COLUMNS'		=> array(
			'poll_option_id'		=> array('TINT:4', 0),
			'blog_id'				=> array('UINT', 0),
			'poll_option_text'		=> array('TEXT_UNI', ''),
			'poll_option_total'		=> array('UINT', 0),
		),
		'KEYS'			=> array(
			'poll_opt_id'			=> array('INDEX', 'poll_option_id'),
			'blog_id'				=> array('INDEX', 'blog_id'),
		),
	);

	$schema_data['phpbb_blogs_poll_votes'] = array(
		'COLUMNS'		=> array(
			'blog_id'				=> array('UINT', 0),
			'poll_option_id'		=> array('TINT:4', 0),
			'vote_user_id'			=> array('UINT', 0),
			'vote_user_ip'			=> array('VCHAR:40', ''),
		),
		'KEYS'			=> array(
			'blog_id'				=> array('INDEX', 'blog_id'),
			'vote_user_id'			=> array('INDEX', 'vote_user_id'),
			'vote_user_ip'			=> array('INDEX', 'vote_user_ip'),
		),
	);

	$schema_data['phpbb_blogs_ratings'] = array(
		'COLUMNS'		=> array(
			'blog_id'						=> array('UINT', 0),
			'user_id'						=> array('UINT', 0),
			'rating'						=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> array('blog_id', 'user_id'),
	);

	$schema_data['phpbb_blogs_reply'] = array(
		'COLUMNS'		=> array(
			'reply_id'				=> array('UINT', NULL, 'auto_increment'),
			'blog_id'				=> array('UINT', 0),
			'user_id'				=> array('UINT', 0),
			'user_ip'				=> array('VCHAR:40', ''),
			'reply_subject'			=> array('STEXT_UNI', '', 'true_sort'),
			'reply_text'			=> array('MTEXT_UNI', ''),
			'reply_checksum'		=> array('VCHAR:32', ''),
			'reply_time'			=> array('TIMESTAMP', 0),
			'reply_approved'		=> array('BOOL', 1),
			'reply_reported'		=> array('BOOL', 0),
			'enable_bbcode'			=> array('BOOL', 1),
			'enable_smilies'		=> array('BOOL', 1),
			'enable_magic_url'		=> array('BOOL', 1),
			'bbcode_bitfield'		=> array('VCHAR:255', ''),
			'bbcode_uid'			=> array('VCHAR:8', ''),
			'reply_edit_time'		=> array('TIMESTAMP', 0),
			'reply_edit_reason'		=> array('STEXT_UNI', ''),
			'reply_edit_user'		=> array('UINT', 0),
			'reply_edit_count'		=> array('UINT', 0),
			'reply_edit_locked'		=> array('BOOL', 0),
			'reply_deleted'			=> array('UINT', 0),
			'reply_deleted_time'	=> array('TIMESTAMP', 0),
			'reply_attachment'		=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'reply_id',
		'KEYS'			=> array(
			'blog_id'				=> array('INDEX', 'blog_id'),
			'user_id'				=> array('INDEX', 'user_id'),
			'user_ip'				=> array('INDEX', 'user_ip'),
			'reply_approved'		=> array('INDEX', 'reply_approved'),
			'reply_deleted'			=> array('INDEX', 'reply_deleted'),
		),
	);

	$schema_data['phpbb_blogs_subscription'] = array(
		'COLUMNS'		=> array(
			'sub_user_id'			=> array('UINT', 0),
			'sub_type'				=> array('UINT:11', 0),
			'blog_id'				=> array('UINT', 0),
			'user_id'				=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> array('sub_user_id', 'sub_type', 'blog_id', 'user_id'),
	);

	$schema_data['phpbb_blogs_users'] = array(
		'COLUMNS'		=> array(
			'user_id'				=> array('UINT', 0),
			'perm_guest'			=> array('TINT:1', 1),
			'perm_registered'		=> array('TINT:1', 2),
			'perm_foe'				=> array('TINT:1', 0),
			'perm_friend'			=> array('TINT:1', 2),
			'title'					=> array('STEXT_UNI', '', 'true_sort'),
			'description'			=> array('MTEXT_UNI', ''),
			'description_bbcode_bitfield'	=> array('VCHAR:255', ''),
			'description_bbcode_uid'		=> array('VCHAR:8', ''),
			'instant_redirect'				=> array('BOOL', 1),
			'blog_subscription_default'		=> array('UINT:11', 0),
			'blog_style'					=> array('STEXT_UNI', '', 'true_sort'),
			'blog_css'						=> array('MTEXT_UNI', ''),
		),
		'PRIMARY_KEY'	=> 'user_id',
	);

	$schema_data['phpbb_blog_search_wordlist'] = array(
		'COLUMNS'		=> array(
			'word_id'			=> array('UINT', NULL, 'auto_increment'),
			'word_text'			=> array('VCHAR_UNI', ''),
			'word_common'		=> array('BOOL', 0),
			'word_count'		=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'word_id',
		'KEYS'			=> array(
			'word_text'			=> array('UNIQUE', 'word_text'),
			'word_count'		=> array('INDEX', 'word_count'),
		),
	);

	$schema_data['phpbb_blog_search_wordmatch'] = array(
		'COLUMNS'		=> array(
			'blog_id'			=> array('UINT', 0),
			'reply_id'			=> array('UINT', 0),
			'word_id'			=> array('UINT', 0),
			'title_match'		=> array('BOOL', 0),
		),
		'KEYS'			=> array(
			'unique_match'		=> array('UNIQUE', array('blog_id', 'reply_id', 'word_id', 'title_match')),
			'word_id'			=> array('INDEX', 'word_id'),
			'blog_id'			=> array('INDEX', 'blog_id'),
			'reply_id'			=> array('INDEX', 'reply_id'),
		),
	);

	return $schema_data;
}

trigger_error('Done.');
?>