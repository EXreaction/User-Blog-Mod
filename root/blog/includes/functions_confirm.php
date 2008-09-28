<?php
/**
*
* @package phpBB3 User Blog
* @version $Id: functions_confirm.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Build Confirm
*
* @param string $title The title of the page
* @param string $explain The explanation of the page
* @param array $display_vars The array holding all of the settings/information - like how admin_board is setup
* @param string $submit_type The type of submit buttons you want shown. Currently supported: 'submit/reset' or 'yes/no' 
* @param string $action The page you want to submit to.  Leave as self to be the current page.
*/
function blog_confirm($title, $explain, $display_vars, $submit_type = 'submit/reset', $action = 'self')
{
	global $template, $user;

	$submit = (isset($_POST['submit'])) ? true : false;
	$error = $settings = array();

	if ($submit)
	{
		// check the form key
		if (!check_form_key('confirm'))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}

		$settings = request_var('setting', array('' => ''));
		validate_config_vars($display_vars, $settings, $error);

		if (!sizeof($error))
		{
			return $settings;
		}
		else
		{
			$template->assign_vars(array(
				'S_ERROR'		=> true,
				'ERROR_MSG'		=> implode($error, '<br />'),
			));
		}
	}

	// Add the form key
	add_form_key('confirm');

	if ($action === 'self')
	{
		global $blog_urls;
		if (isset($blog_urls['self']))
		{
			$action = $blog_urls['self'];
		}
		else
		{
			$action = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
		}
	}

	$template->assign_vars(array(
		'L_TITLE'			=> (isset($user->lang[$title])) ? $user->lang[$title] : $title,
		'L_TITLE_EXPLAIN'	=> (isset($user->lang[$explain])) ? $user->lang[$explain] : $explain,

		'U_ACTION'			=> $action,

		'S_YES_NO'			=> ($submit_type == 'submit/reset') ? false : true,
	));

	foreach ($display_vars as $key => $vars)
	{
		if (strpos($key, 'legend') !== false)
		{
			$template->assign_block_vars('options', array(
				'S_LEGEND'		=> true,
				'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
			);

			continue;
		}

		$default = (isset($settings[$key])) ? $settings[$key] : $vars['default'];

		$type = explode(':', $vars['type']);
		$l_explain = '';
		if ($vars['explain'] && isset($vars['lang_explain']))
		{
			$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
		}
		else if ($vars['explain'])
		{
			$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
		}
		$template->assign_block_vars('options', array(
			'KEY'			=> $key,
			'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
			'S_EXPLAIN'		=> $vars['explain'],
			'TITLE_EXPLAIN'	=> $l_explain,
			'CONTENT'		=> build_blog_cfg_template($type, $key, $default),
		));
	}

	$template->set_filenames(array(
		'body'		=> 'blog/blog_confirm.html',
	));

	return 'build';
}

/**
* Build configuration template for confirm pages
*
* Originally from adm/index.php
*/
function build_blog_cfg_template($tpl_type, $name, $default)
{
	global $user;

	$tpl = '';
	$name = 'setting[' . $name . ']';

	switch ($tpl_type[0])
	{
		case 'text' :
		case 'password' :
			$size = (int) $tpl_type[1];
			$maxlength = (int) $tpl_type[2];

			$tpl = '<input id="' . $name . '" type="' . $tpl_type[0] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $name . '" value="' . $default . '" />';
		break;

		case 'textarea' :
			$rows = (int) $tpl_type[1];
			$cols = (int) $tpl_type[2];

			$tpl = '<textarea id="' . $name . '" name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '">' . $default . '</textarea>';
		break;

		case 'radio' :
			$name_yes	= ($default) ? ' checked="checked"' : '';
			$name_no		= (!$default) ? ' checked="checked"' : '';

			$tpl_type_cond = explode('_', $tpl_type[1]);
			$type_no = ($tpl_type_cond[0] == 'disabled' || $tpl_type_cond[0] == 'enabled') ? false : true;

			$tpl_no = '<label><input type="radio" name="' . $name . '" value="0"' . $name_no . ' class="radio" /> ' . (($type_no) ? $user->lang['NO'] : $user->lang['DISABLED']) . '</label>';
			$tpl_yes = '<label><input type="radio" id="' . $name . '" name="' . $name . '" value="1"' . $name_yes . ' class="radio" /> ' . (($type_no) ? $user->lang['YES'] : $user->lang['ENABLED']) . '</label>';

			$tpl = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . ' ' . $tpl_no : $tpl_no . ' ' . $tpl_yes;
		break;

		case 'checkbox' :
			$tpl = '<input type="checkbox" name="' . $name . '"  id="' . $name . '"' . (($default) ? ' checked="checked"' : '') . ' />';
		break;

		default :
			$temp = compact('tpl_type', 'name', 'default', 'tpl');
			blog_plugins::plugin_do_ref('function_build_blog_cfg_template', $temp);
			extract($temp);
		break;
	}

	return $tpl;
}

/**
* Going through a config array and validate values.
*
* From adm/index.php
*/
function validate_config_vars($config_vars, &$cfg_array, &$error)
{
	foreach ($config_vars as $config_name => $config_definition)
	{
		if (!isset($config_definition['validate']) || strpos($config_name, 'legend') !== false)
		{
			continue;
		}

		// Validate a bit. ;) String is already checked through request_var(), therefore we do not check this again.  Also, if the variables are not set, set them to their default setting (or, for bool, false)
		switch ($config_definition['validate'])
		{
			case 'bool':
				$cfg_array[$config_name] = ((isset($cfg_array[$config_name])) ? (($cfg_array[$config_name]) ? true : false) : false);
			break;
			case 'int':
				$cfg_array[$config_name] = ((isset($cfg_array[$config_name])) ? intval($cfg_array[$config_name]) : $config_definition['default']);
			break;
			default :
				if (!isset($cfg_array[$config_name]))
				{
					$cfg_array[$config_name] = $config_definition['default'];
				}
		}
	}
}
?>