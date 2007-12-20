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
	case 'get_hooks' :
		get_hooks(); // Gets all available hooks and lists them in a file.  For more detail, check the function directly.
		break;
	case 'build_install' :
		build_install(); // Builds the schema files/etc for the db install.
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

				if (strpos($line, '$blog_plugins->plugin_do') !== false)
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

	$last_name = '';
	foreach($lang as $name => $value)
	{
		if ($name == $last_name)
		{
			echo 'Lang Duplicate: ' . $name . '<br/>';
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

	$file = ($file === false) ? request_var('file', '') : $file;

	if (substr($file, -1) == '/')
	{
		$file = substr($file, 0, -1);
	}

	// make sure they have a file name
	if ($file == '' && !$skip_errors)
	{
		trigger_error('No File Specified.');
	}
	else if ($skip_errors)
	{
		return;
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
					organize_lang($file . '/' . substr($file1, 0, strpos($file1, ".$phpEx")));
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
	if ((!isset($lang) || !is_array($lang)) && !$skip_errors)
	{
		trigger_error('Bad Language File.');
	}
	else if ($skip_errors)
	{
		return;
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

		if (!$stopped && !$skip_errors)
		{
			trigger_error('Please make sure you are using UNIX line endings.');
		}
		else if ($skip_errors)
		{
			echo 'Bad line endings in ' . $phpbb_root_path . 'language/' . $file . '.' . $phpEx . '<br/>';
			return;
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

/**
* Build Install
*
* Builds the Schema files for the database installation.  Most of the code was taken from develop/create_schema_files.php
*/
function build_install()
{
	global $phpbb_root_path;

	$schema_path = $phpbb_root_path . 'blog/install/schemas/';

	if (!is_writable($schema_path))
	{
		die('Schema path not writable');
	}

	$schema_data = get_schema_struct();
	$dbms_type_map = array(
		'mysql_41'	=> array(
			'INT:'		=> 'int(%d)',
			'BINT'		=> 'bigint(20)',
			'UINT'		=> 'mediumint(8) UNSIGNED',
			'UINT:'		=> 'int(%d) UNSIGNED',
			'TINT:'		=> 'tinyint(%d)',
			'USINT'		=> 'smallint(4) UNSIGNED',
			'BOOL'		=> 'tinyint(1) UNSIGNED',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'text',
			'XSTEXT_UNI'=> 'varchar(100)',
			'STEXT'		=> 'text',
			'STEXT_UNI'	=> 'varchar(255)',
			'TEXT'		=> 'text',
			'TEXT_UNI'	=> 'text',
			'MTEXT'		=> 'mediumtext',
			'MTEXT_UNI'	=> 'mediumtext',
			'TIMESTAMP'	=> 'int(11) UNSIGNED',
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar(255)',
			'VARBINARY'	=> 'varbinary(255)',
		),

		'mysql_40'	=> array(
			'INT:'		=> 'int(%d)',
			'BINT'		=> 'bigint(20)',
			'UINT'		=> 'mediumint(8) UNSIGNED',
			'UINT:'		=> 'int(%d) UNSIGNED',
			'TINT:'		=> 'tinyint(%d)',
			'USINT'		=> 'smallint(4) UNSIGNED',
			'BOOL'		=> 'tinyint(1) UNSIGNED',
			'VCHAR'		=> 'varbinary(255)',
			'VCHAR:'	=> 'varbinary(%d)',
			'CHAR:'		=> 'binary(%d)',
			'XSTEXT'	=> 'blob',
			'XSTEXT_UNI'=> 'blob',
			'STEXT'		=> 'blob',
			'STEXT_UNI'	=> 'blob',
			'TEXT'		=> 'blob',
			'TEXT_UNI'	=> 'blob',
			'MTEXT'		=> 'mediumblob',
			'MTEXT_UNI'	=> 'mediumblob',
			'TIMESTAMP'	=> 'int(11) UNSIGNED',
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'blob',
			'VCHAR_UNI:'=> array('varbinary(%d)', 'limit' => array('mult', 3, 255, 'blob')),
			'VCHAR_CI'	=> 'blob',
			'VARBINARY'	=> 'varbinary(255)',
		),

		'firebird'	=> array(
			'INT:'		=> 'INTEGER',
			'BINT'		=> 'DOUBLE PRECISION',
			'UINT'		=> 'INTEGER',
			'UINT:'		=> 'INTEGER',
			'TINT:'		=> 'INTEGER',
			'USINT'		=> 'INTEGER',
			'BOOL'		=> 'INTEGER',
			'VCHAR'		=> 'VARCHAR(255) CHARACTER SET NONE',
			'VCHAR:'	=> 'VARCHAR(%d) CHARACTER SET NONE',
			'CHAR:'		=> 'CHAR(%d) CHARACTER SET NONE',
			'XSTEXT'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'STEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'TEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'MTEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'XSTEXT_UNI'=> 'VARCHAR(100) CHARACTER SET UTF8',
			'STEXT_UNI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
			'TEXT_UNI'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET UTF8',
			'MTEXT_UNI'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET UTF8',
			'TIMESTAMP'	=> 'INTEGER',
			'DECIMAL'	=> 'DOUBLE PRECISION',
			'DECIMAL:'	=> 'DOUBLE PRECISION',
			'PDECIMAL'	=> 'DOUBLE PRECISION',
			'PDECIMAL:'	=> 'DOUBLE PRECISION',
			'VCHAR_UNI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
			'VCHAR_UNI:'=> 'VARCHAR(%d) CHARACTER SET UTF8',
			'VCHAR_CI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
			'VARBINARY'	=> 'CHAR(255) CHARACTER SET NONE',
		),

		'mssql'		=> array(
			'INT:'		=> '[int]',
			'BINT'		=> '[float]',
			'UINT'		=> '[int]',
			'UINT:'		=> '[int]',
			'TINT:'		=> '[int]',
			'USINT'		=> '[int]',
			'BOOL'		=> '[int]',
			'VCHAR'		=> '[varchar] (255)',
			'VCHAR:'	=> '[varchar] (%d)',
			'CHAR:'		=> '[char] (%d)',
			'XSTEXT'	=> '[varchar] (1000)',
			'STEXT'		=> '[varchar] (3000)',
			'TEXT'		=> '[varchar] (8000)',
			'MTEXT'		=> '[text]',
			'XSTEXT_UNI'=> '[varchar] (100)',
			'STEXT_UNI'	=> '[varchar] (255)',
			'TEXT_UNI'	=> '[varchar] (4000)',
			'MTEXT_UNI'	=> '[text]',
			'TIMESTAMP'	=> '[int]',
			'DECIMAL'	=> '[float]',
			'DECIMAL:'	=> '[float]',
			'PDECIMAL'	=> '[float]',
			'PDECIMAL:'	=> '[float]',
			'VCHAR_UNI'	=> '[varchar] (255)',
			'VCHAR_UNI:'=> '[varchar] (%d)',
			'VCHAR_CI'	=> '[varchar] (255)',
			'VARBINARY'	=> '[varchar] (255)',
		),

		'oracle'	=> array(
			'INT:'		=> 'number(%d)',
			'BINT'		=> 'number(20)',
			'UINT'		=> 'number(8)',
			'UINT:'		=> 'number(%d)',
			'TINT:'		=> 'number(%d)',
			'USINT'		=> 'number(4)',
			'BOOL'		=> 'number(1)',
			'VCHAR'		=> 'varchar2(255)',
			'VCHAR:'	=> 'varchar2(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'varchar2(1000)',
			'STEXT'		=> 'varchar2(3000)',
			'TEXT'		=> 'clob',
			'MTEXT'		=> 'clob',
			'XSTEXT_UNI'=> 'varchar2(300)',
			'STEXT_UNI'	=> 'varchar2(765)',
			'TEXT_UNI'	=> 'clob',
			'MTEXT_UNI'	=> 'clob',
			'TIMESTAMP'	=> 'number(11)',
			'DECIMAL'	=> 'number(5, 2)',
			'DECIMAL:'	=> 'number(%d, 2)',
			'PDECIMAL'	=> 'number(6, 3)',
			'PDECIMAL:'	=> 'number(%d, 3)',
			'VCHAR_UNI'	=> 'varchar2(765)',
			'VCHAR_UNI:'=> array('varchar2(%d)', 'limit' => array('mult', 3, 765, 'clob')),
			'VCHAR_CI'	=> 'varchar2(255)',
			'VARBINARY'	=> 'raw(255)',
		),

		'sqlite'	=> array(
			'INT:'		=> 'int(%d)',
			'BINT'		=> 'bigint(20)',
			'UINT'		=> 'INTEGER UNSIGNED', //'mediumint(8) UNSIGNED',
			'UINT:'		=> 'INTEGER UNSIGNED', // 'int(%d) UNSIGNED',
			'TINT:'		=> 'tinyint(%d)',
			'USINT'		=> 'INTEGER UNSIGNED', //'mediumint(4) UNSIGNED',
			'BOOL'		=> 'INTEGER UNSIGNED', //'tinyint(1) UNSIGNED',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'text(65535)',
			'STEXT'		=> 'text(65535)',
			'TEXT'		=> 'text(65535)',
			'MTEXT'		=> 'mediumtext(16777215)',
			'XSTEXT_UNI'=> 'text(65535)',
			'STEXT_UNI'	=> 'text(65535)',
			'TEXT_UNI'	=> 'text(65535)',
			'MTEXT_UNI'	=> 'mediumtext(16777215)',
			'TIMESTAMP'	=> 'INTEGER UNSIGNED', //'int(11) UNSIGNED',
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar(255)',
			'VARBINARY'	=> 'blob',
		),

		'postgres'	=> array(
			'INT:'		=> 'INT4',
			'BINT'		=> 'INT8',
			'UINT'		=> 'INT4', // unsigned
			'UINT:'		=> 'INT4', // unsigned
			'USINT'		=> 'INT2', // unsigned
			'BOOL'		=> 'INT2', // unsigned
			'TINT:'		=> 'INT2',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'varchar(1000)',
			'STEXT'		=> 'varchar(3000)',
			'TEXT'		=> 'varchar(8000)',
			'MTEXT'		=> 'TEXT',
			'XSTEXT_UNI'=> 'varchar(100)',
			'STEXT_UNI'	=> 'varchar(255)',
			'TEXT_UNI'	=> 'varchar(4000)',
			'MTEXT_UNI'	=> 'TEXT',
			'TIMESTAMP'	=> 'INT4', // unsigned
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar_ci',
			'VARBINARY'	=> 'bytea',
		),
	);

	// A list of types being unsigned for better reference in some db's
	$unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');
	$supported_dbms = array('firebird', 'mssql', 'mysql_40', 'mysql_41', 'oracle', 'postgres', 'sqlite');

	foreach ($supported_dbms as $dbms)
	{
		$fp = fopen($schema_path . $dbms . '_schema.sql', 'wt');

		$line = '';

		// Write Header
		switch ($dbms)
		{
			case 'mysql_40':
			case 'mysql_41':
				$line = "# User Blogs Mod Database Schema\n\n";
			break;

			case 'firebird':
				$line = "# User Blogs Mod Database Schema\n\n";
				$line .= custom_data('firebird') . "\n";
			break;

			case 'sqlite':
				$line = "# User Blogs Mod Database Schema\n\n";
				$line .= "BEGIN TRANSACTION;\n\n";
			break;

			case 'mssql':
				$line = "/*\nUser Blogs Mod Database Schema\n*/\n\n";
				$line .= "BEGIN TRANSACTION\nGO\n\n";
			break;

			case 'oracle':
				$line = "/*\nUser Blogs Mod Database Schema\n*/\n\n";
				$line .= custom_data('oracle') . "\n";
			break;

			case 'postgres':
				$line = "/*\nUser Blogs Mod Database Schema\n*/\n\n";
				$line .= "BEGIN;\n\n";
				$line .= custom_data('postgres') . "\n";
			break;
		}

		fwrite($fp, $line);

		foreach ($schema_data as $table_name => $table_data)
		{
			// Write comment about table
			switch ($dbms)
			{
				case 'mysql_40':
				case 'mysql_41':
				case 'firebird':
				case 'sqlite':
					fwrite($fp, "# Table: '{$table_name}'\n");
				break;

				case 'mssql':
				case 'oracle':
				case 'postgres':
					fwrite($fp, "/*\n\tTable: '{$table_name}'\n*/\n");
				break;
			}

			// Create Table statement
			$generator = $textimage = false;
			$line = '';

			switch ($dbms)
			{
				case 'mysql_40':
				case 'mysql_41':
				case 'firebird':
				case 'oracle':
				case 'sqlite':
				case 'postgres':
					$line = "CREATE TABLE {$table_name} (\n";
				break;

				case 'mssql':
					$line = "CREATE TABLE [{$table_name}] (\n";
				break;
			}

			// Table specific so we don't get overlap
			$modded_array = array();

			// Write columns one by one...
			foreach ($table_data['COLUMNS'] as $column_name => $column_data)
			{
				// Get type
				if (strpos($column_data[0], ':') !== false)
				{
					list($orig_column_type, $column_length) = explode(':', $column_data[0]);
					if (!is_array($dbms_type_map[$dbms][$orig_column_type . ':']))
					{
						$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'], $column_length);
					}
					else
					{
						if (isset($dbms_type_map[$dbms][$orig_column_type . ':']['rule']))
						{
							switch ($dbms_type_map[$dbms][$orig_column_type . ':']['rule'][0])
							{
								case 'div':
									$column_length /= $dbms_type_map[$dbms][$orig_column_type . ':']['rule'][1];
									$column_length = ceil($column_length);
									$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
								break;
							}
						}

						if (isset($dbms_type_map[$dbms][$orig_column_type . ':']['limit']))
						{
							switch ($dbms_type_map[$dbms][$orig_column_type . ':']['limit'][0])
							{
								case 'mult':
									$column_length *= $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][1];
									if ($column_length > $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][2])
									{
										$column_type = $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][3];
										$modded_array[$column_name] = $column_type;
									}
									else
									{
										$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
									}
								break;
							}
						}
					}
					$orig_column_type .= ':';
				}
				else
				{
					$orig_column_type = $column_data[0];
					$column_type = $dbms_type_map[$dbms][$column_data[0]];
					if ($column_type == 'text' || $column_type == 'blob')
					{
						$modded_array[$column_name] = $column_type;
					}
				}

				// Adjust default value if db-dependant specified
				if (is_array($column_data[1]))
				{
					$column_data[1] = (isset($column_data[1][$dbms])) ? $column_data[1][$dbms] : $column_data[1]['default'];
				}

				switch ($dbms)
				{
					case 'mysql_40':
					case 'mysql_41':
						$line .= "\t{$column_name} {$column_type} ";

						// For hexadecimal values do not use single quotes
						if (!is_null($column_data[1]) && substr($column_type, -4) !== 'text' && substr($column_type, -4) !== 'blob')
						{
							$line .= (strpos($column_data[1], '0x') === 0) ? "DEFAULT {$column_data[1]} " : "DEFAULT '{$column_data[1]}' ";
						}
						$line .= 'NOT NULL';

						if (isset($column_data[2]))
						{
							if ($column_data[2] == 'auto_increment')
							{
								$line .= ' auto_increment';
							}
							else if ($dbms === 'mysql_41' && $column_data[2] == 'true_sort')
							{
								$line .= ' COLLATE utf8_unicode_ci';
							}
						}

						$line .= ",\n";
					break;

					case 'sqlite':
						if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
						{
							$line .= "\t{$column_name} INTEGER PRIMARY KEY ";
							$generator = $column_name;
						}
						else
						{
							$line .= "\t{$column_name} {$column_type} ";
						}

						$line .= 'NOT NULL ';
						$line .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}'" : '';
						$line .= ",\n";
					break;

					case 'firebird':
						$line .= "\t{$column_name} {$column_type} ";

						if (!is_null($column_data[1]))
						{
							$line .= 'DEFAULT ' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
						}

						$line .= 'NOT NULL';

						// This is a UNICODE column and thus should be given it's fair share
						if (preg_match('/^X?STEXT_UNI|VCHAR_(CI|UNI:?)/', $column_data[0]))
						{
							$line .= ' COLLATE UNICODE';
						}

						$line .= ",\n";

						if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
						{
							$generator = $column_name;
						}
					break;

					case 'mssql':
						if ($column_type == '[text]')
						{
							$textimage = true;
						}

						$line .= "\t[{$column_name}] {$column_type} ";

						if (!is_null($column_data[1]))
						{
							// For hexadecimal values do not use single quotes
							if (strpos($column_data[1], '0x') === 0)
							{
								$line .= 'DEFAULT (' . $column_data[1] . ') ';
							}
							else
							{
								$line .= 'DEFAULT (' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ') ';
							}
						}

						if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
						{
							$line .= 'IDENTITY (1, 1) ';
						}

						$line .= 'NOT NULL';
						$line .= " ,\n";
					break;

					case 'oracle':
						$line .= "\t{$column_name} {$column_type} ";
						$line .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';

						// In Oracle empty strings ('') are treated as NULL.
						// Therefore in oracle we allow NULL's for all DEFAULT '' entries
						$line .= ($column_data[1] === '') ? ",\n" : "NOT NULL,\n";

						if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
						{
							$generator = $column_name;
						}
					break;

					case 'postgres':
						$line .= "\t{$column_name} {$column_type} ";

						if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
						{
							$line .= "DEFAULT nextval('{$table_name}_seq'),\n";

							// Make sure the sequence will be created before creating the table
							$line = "CREATE SEQUENCE {$table_name}_seq;\n\n" . $line;
						}
						else
						{
							$line .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';
							$line .= "NOT NULL";

							// Unsigned? Then add a CHECK contraint
							if (in_array($orig_column_type, $unsigned_types))
							{
								$line .= " CHECK ({$column_name} >= 0)";
							}

							$line .= ",\n";
						}
					break;
				}
			}

			switch ($dbms)
			{
				case 'firebird':
					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n);;\n\n";
				break;

				case 'mssql':
					$line = substr($line, 0, -2);
					$line .= "\n) ON [PRIMARY]" . (($textimage) ? ' TEXTIMAGE_ON [PRIMARY]' : '') . "\n";
					$line .= "GO\n\n";
				break;
			}

			// Write primary key
			if (isset($table_data['PRIMARY_KEY']))
			{
				if (!is_array($table_data['PRIMARY_KEY']))
				{
					$table_data['PRIMARY_KEY'] = array($table_data['PRIMARY_KEY']);
				}

				switch ($dbms)
				{
					case 'mysql_40':
					case 'mysql_41':
					case 'postgres':
						$line .= "\tPRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
					break;

					case 'firebird':
						$line .= "ALTER TABLE {$table_name} ADD PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ");;\n\n";
					break;

					case 'sqlite':
						if ($generator === false || !in_array($generator, $table_data['PRIMARY_KEY']))
						{
							$line .= "\tPRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
						}
					break;

					case 'mssql':
						$line .= "ALTER TABLE [{$table_name}] WITH NOCHECK ADD \n";
						$line .= "\tCONSTRAINT [PK_{$table_name}] PRIMARY KEY  CLUSTERED \n";
						$line .= "\t(\n";
						$line .= "\t\t[" . implode("],\n\t\t[", $table_data['PRIMARY_KEY']) . "]\n";
						$line .= "\t)  ON [PRIMARY] \n";
						$line .= "GO\n\n";
					break;

					case 'oracle':
						$line .= "\tCONSTRAINT pk_{$table_name} PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
					break;
				}
			}

			switch ($dbms)
			{
				case 'oracle':
					// UNIQUE contrains to be added?
					if (isset($table_data['KEYS']))
					{
						foreach ($table_data['KEYS'] as $key_name => $key_data)
						{
							if (!is_array($key_data[1]))
							{
								$key_data[1] = array($key_data[1]);
							}

							if ($key_data[0] == 'UNIQUE')
							{
								$line .= "\tCONSTRAINT u_phpbb_{$key_name} UNIQUE (" . implode(', ', $key_data[1]) . "),\n";
							}
						}
					}

					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n)\n/\n\n";
				break;

				case 'postgres':
					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n);\n\n";
				break;

				case 'sqlite':
					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n);\n\n";
				break;
			}

			// Write Keys
			if (isset($table_data['KEYS']))
			{
				foreach ($table_data['KEYS'] as $key_name => $key_data)
				{
					if (!is_array($key_data[1]))
					{
						$key_data[1] = array($key_data[1]);
					}

					switch ($dbms)
					{
						case 'mysql_40':
						case 'mysql_41':
							$line .= ($key_data[0] == 'INDEX') ? "\tKEY" : '';
							$line .= ($key_data[0] == 'UNIQUE') ? "\tUNIQUE" : '';
							foreach ($key_data[1] as $key => $col_name)
							{
								if (isset($modded_array[$col_name]))
								{
									switch ($modded_array[$col_name])
									{
										case 'text':
										case 'blob':
											$key_data[1][$key] = $col_name . '(255)';
										break;
									}
								}
							}
							$line .= ' ' . $key_name . ' (' . implode(', ', $key_data[1]) . "),\n";
						break;

						case 'firebird':
							$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
							$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

							$line .= ' ' . $table_name . '_' . $key_name . ' ON ' . $table_name . '(' . implode(', ', $key_data[1]) . ");;\n";
						break;

						case 'mssql':
							$line .= ($key_data[0] == 'INDEX') ? 'CREATE  INDEX' : '';
							$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE  UNIQUE  INDEX' : '';
							$line .= " [{$key_name}] ON [{$table_name}]([" . implode('], [', $key_data[1]) . "]) ON [PRIMARY]\n";
							$line .= "GO\n\n";
						break;

						case 'oracle':
							if ($key_data[0] == 'UNIQUE')
							{
								continue;
							}

							$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
							
							$line .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ")\n";
							$line .= "/\n";
						break;

						case 'sqlite':
							$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
							$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

							$line .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ");\n";
						break;

						case 'postgres':
							$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
							$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

							$line .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ");\n";
						break;
					}
				}
			}

			switch ($dbms)
			{
				case 'mysql_40':
					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n);\n\n";
				break;

				case 'mysql_41':
					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n) CHARACTER SET `utf8` COLLATE `utf8_bin`;\n\n";
				break;

				// Create Generator
				case 'firebird':
					if ($generator !== false)
					{
						$line .= "\nCREATE GENERATOR {$table_name}_gen;;\n";
						$line .= 'SET GENERATOR ' . $table_name . "_gen TO 0;;\n\n";

						$line .= 'CREATE TRIGGER t_' . $table_name . ' FOR ' . $table_name . "\n";
						$line .= "BEFORE INSERT\nAS\nBEGIN\n";
						$line .= "\tNEW.{$generator} = GEN_ID({$table_name}_gen, 1);\nEND;;\n\n";
					}
				break;

				case 'oracle':
					if ($generator !== false)
					{
						$line .= "\nCREATE SEQUENCE {$table_name}_seq\n/\n\n";

						$line .= "CREATE OR REPLACE TRIGGER t_{$table_name}\n";
						$line .= "BEFORE INSERT ON {$table_name}\n";
						$line .= "FOR EACH ROW WHEN (\n";
						$line .= "\tnew.{$generator} IS NULL OR new.{$generator} = 0\n";
						$line .= ")\nBEGIN\n";
						$line .= "\tSELECT {$table_name}_seq.nextval\n";
						$line .= "\tINTO :new.{$generator}\n";
						$line .= "\tFROM dual;\nEND;\n/\n\n";
					}
				break;
			}

			fwrite($fp, $line . "\n");
		}

		$line = '';

		// Write custom function at the end for some db's
		switch ($dbms)
		{
			case 'mssql':
				$line = "\nCOMMIT\nGO\n\n";
			break;

			case 'sqlite':
				$line = "\nCOMMIT;";
			break;

			case 'postgres':
				$line = "\nCOMMIT;";
			break;
		}

		fwrite($fp, $line);
		fclose($fp);
	}
}

/**
* Define the basic structure
* The format:
*		array('{TABLE_NAME}' => {TABLE_DATA})
*		{TABLE_DATA}:
*			COLUMNS = array({column_name} = array({column_type}, {default}, {auto_increment}))
*			PRIMARY_KEY = {column_name(s)}
*			KEYS = array({key_name} = array({key_type}, {column_name(s)})),
*
*	Column Types:
*	INT:x		=> SIGNED int(x)
*	BINT		=> BIGINT
*	UINT		=> mediumint(8) UNSIGNED
*	UINT:x		=> int(x) UNSIGNED
*	TINT:x		=> tinyint(x)
*	USINT		=> smallint(4) UNSIGNED (for _order columns)
*	BOOL		=> tinyint(1) UNSIGNED
*	VCHAR		=> varchar(255)
*	CHAR:x		=> char(x)
*	XSTEXT_UNI	=> text for storing 100 characters (topic_title for example)
*	STEXT_UNI	=> text for storing 255 characters (normal input field with a max of 255 single-byte chars) - same as VCHAR_UNI
*	TEXT_UNI	=> text for storing 3000 characters (short text, descriptions, comments, etc.)
*	MTEXT_UNI	=> mediumtext (post text, large text)
*	VCHAR:x		=> varchar(x)
*	TIMESTAMP	=> int(11) UNSIGNED
*	DECIMAL		=> decimal number (5,2)
*	DECIMAL:	=> decimal number (x,2)
*	PDECIMAL	=> precision decimal number (6,3)
*	PDECIMAL:	=> precision decimal number (x,3)
*	VCHAR_UNI	=> varchar(255) BINARY
*	VCHAR_CI	=> varchar_ci for postgresql, others VCHAR
*/
function get_schema_struct()
{
	$schema_data = array();
/*
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
			'blog_read_count'		=> array('UINT', 0),
			'blog_reply_count'		=> array('UINT', 0),
			'blog_real_reply_count'	=> array('UINT', 0),
			'perm_guest'			=> array('TINT:1', 1),
			'perm_registered'		=> array('TINT:1', 2),
			'perm_foe'				=> array('TINT:1', 0),
			'perm_friend'			=> array('TINT:1', 2),
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
		),
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
			'sub_type'				=> array('TINT:1', 1),
			'blog_id'				=> array('UINT', 0),
			'user_id'				=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'sub_user_id, sub_type, blog_id, user_id',
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
			'instant_redirect'		=> array('BOOL', 1),
		),
		'PRIMARY_KEY'	=> 'user_id',
	);

	/*
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

	$schema_data['phpbb_blog_search_wordlist'] = array(
		'COLUMNS'		=> array(
			'word_id'			=> array('UINT', NULL, 'auto_increment'),
			'word_text'			=> array('VCHAR_UNI', ''),
			'word_common'		=> array('BOOL', 0),
			'word_count'		=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'word_id',
		'KEYS'			=> array(
			'wrd_txt'			=> array('UNIQUE', 'word_text'),
			'wrd_cnt'			=> array('INDEX', 'word_count'),
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
			'unq_mtch'			=> array('UNIQUE', array('blog_id', 'reply_id', 'word_id', 'title_match')),
			'word_id'			=> array('INDEX', 'word_id'),
			'blog_id'			=> array('INDEX', 'blog_id'),
			'reply_id'			=> array('INDEX', 'reply_id'),
		),
	);

	return $schema_data;
}


/**
* Data put into the header for various dbms
*/
function custom_data($dbms)
{
	return '';
}

trigger_error('Done.');
?>