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
* Organize the language file by the lang keys, then re-output the data to the file
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

	// make sure they are not trying to get out of the language directory
	if (strstr($file, '.'))
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

	// sort the languages by keys
	ksort($lang);

	// get the maximum length of the name string so we can format the page nicely when we output it
	$max_length = 1;
	foreach($lang as $name => $value)
	{
		if (strlen($name) > $max_length)
		{
			$max_length = strlen($name);
		}
	}
	$total_tabs = ceil(($max_length + 3) / 4);

	// first letter at the beginning of the last word, setting blank for now
	$last_letter = '';

	// setup the $output var
	$output = '';

	// lets get the header of the file...
	$handle = @fopen($phpbb_root_path . 'language/' . $file . '.' . $phpEx, "r");
	if ($handle)
	{
		while (!feof($handle))
		{
			$line = fgets($handle, 4096);

			// if the line is $lang = array_merge($lang, array( break out of the while loop
			if (strstr($line, '$lang = array_merge($lang, array('))
			{
				break;
			}

			$output .= $line;
		}
		fclose($handle);
	}

	// now add $lang = array_merge($lang, array( to the output
	$output .= '$lang = array_merge($lang, array(';

	foreach($lang as $name => $value)
	{
		// this function does not support lang files with arrays as the $value, so if it is an array don't continue
		if (is_array($value))
		{
			// DO NOT REMOVE THIS ERROR MESSAGE!  If the value is an array it may break the output and destroy the file!
			trigger_error('Arrays as the Value are not supported by this function.');
		}

		// add an extra end line if the next word starts with a different letter then the last, do not do this for the sections in arrays
		if ( (substr($name, 0, 1) != $last_letter) )
		{
			$output .= "\n";
			$last_letter = substr($name, 0, 1);
		}

		// add the beginning of the lang section and add slashes to single quotes for the name
		$output .= "\t'" . addcslashes($name, "'") . "'";

		// figure out the number of tabs we need to add to the middle, then add them
		$tabs = ($total_tabs - ceil( (strlen($name) + 3) / 4));

		for($i=0; $i <= $tabs; $i++)
		{
			$output .= "\t";
		}

		// add =>, then slashes to single quotes and add to the output
		$output .= "=> '" . addcslashes($value, "'") . "',\n";
	}

	// add the end
	$output .= '));

?>';

	// write the contents to the specified file
	file_put_contents($phpbb_root_path . 'language/' . $file . '.' . $phpEx, $output);
}

switch ($mode)
{
	case 'organize_lang' :
		organize_lang();
		break;
	default :
		trigger_error('NO_MODE');
}

trigger_error('Done.');
?>