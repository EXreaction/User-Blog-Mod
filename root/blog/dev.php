<?php
/**
 *
 * @package phpBB3 User Blog
 * @copyright (c) 2007 EXreaction, Lithium Studios
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
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

switch ($mode)
{
	case 'organize_lang' :
		// to input the file name use HTTP Get vars.  For example, to organize language/en/common.php you type in en/common.  Do not include language/ or .php
		organize_lang();
		break;
	case 'get_hooks' :
		get_hooks();
		break;
	default :
		trigger_error('NO_MODE');
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
	if (count($dir_list))
	{
		foreach ($dir_list as $file)
		{
			get_hooks_recusive($hook_list, $file, $phpbb_root_path . 'blog_hooks', $phpbb_root_path . 'blog_hooks');
		}
	}

	if (count($file_list))
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
				if (strpos($file, '.'))
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

		if (count($dir_list))
		{
			foreach ($dir_list as $file1)
			{
				get_hooks_recusive($hook_list, $file1, $dir . '/' . $file, $original_dir);
			}
		}

		if (count($file_list))
		{
			foreach ($file_list as $file1)
			{
				get_hooks_recusive($hook_list, $file1, $dir . '/' . $file, $original_dir);
			}
		}
	}
	else if (strpos($file, $phpEx) && $file != "dev.$phpEx") // Skip non .php files
	{
		$handle = @fopen($dir . '/' . $file, "r");
		if ($handle)
		{
			$hook_list .= substr($dir, strpos($dir, $original_dir) + strlen($original_dir)) . '/' . $file . "\n";
			while (!feof($handle))
			{
				$line = fgets($handle, 4096);

				if (strpos($line, 'blog_plugins->plugin_do'))
				{
					$start_pos = strpos($line, "('") + 2;
					$hook_list .= "\t" . substr($line, $start_pos, strpos($line, "'", $start_pos) - $start_pos) . "\n";
				}
			}
			fclose($handle);
			$hook_list .= "\n";
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

	foreach($lang as $name => $value)
	{
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
function organize_lang()
{
	global $phpbb_root_path, $phpEx;

	$file = request_var('file', '');

	// make sure they have a file name
	if ($file == '')
	{
		trigger_error('No File Specified.');
	}

	// make sure they are not trying to get out of the language directory, otherwise this would be a security risk. ;)
	if (strpos($file, '.'))
	{
		trigger_error('You are not allowed out of the language/ directory.');
	}

	// include the file
	@include($phpbb_root_path . 'language/' . $file . '.' . $phpEx);

	// make sure it is a valid language file
	if (!isset($lang) || !is_array($lang))
	{
		trigger_error('Bad Language File.');
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
			trigger_error('Only UNIX Line endings are supported.');
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

trigger_error('Done.');
?>