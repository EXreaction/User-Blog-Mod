<?php
/**
*
* @package phpBB3 User Blog Archives
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$user->add_lang('mods/blog/plugins/' . $name);

$this->available_plugins[$name]['plugin_title'] = $user->lang['ARCHIVES'];
$this->available_plugins[$name]['plugin_description'] = $user->lang['BLOG_ARCHIVES_DESCRIPTION'];

$this->available_plugins[$name]['plugin_copyright'] = 'EXreaction';
$this->available_plugins[$name]['plugin_version'] = '0.7.1';

if ($plugin_enabled)
{
	$to_do = array(
		'function_generate_menu'	=> array('archive_function_generate_menu'),
	);

	foreach($to_do as $do => $what)
	{
		if (!array_key_exists($do, $this->to_do))
		{
			$this->to_do[$do] = $what;
		}
		else
		{
			$this->to_do[$do] = array_merge($this->to_do[$do], $what);
		}
	}

	include($blog_plugins_path . $name . '/functions.' . $phpEx);
}
?>