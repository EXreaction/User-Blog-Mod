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
	default :
		trigger_error('NO_MODE');
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
* For outputting the lines for the oganize_lang function
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