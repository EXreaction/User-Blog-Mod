<?php
/**
*
* @package phpBB3 User Blog Custom Title
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$user->add_lang('mods/blog/plugins/' . $name);

$this->available_plugins[$name]['plugin_title'] = $user->lang['BLOG_CUSTOM_TITLE_TITLE'];
$this->available_plugins[$name]['plugin_description'] = $user->lang['BLOG_CUSTOM_TITLE_DESCRIPTION'];

$this->available_plugins[$name]['plugin_copyright'] = '2007 EXreaction';
$this->available_plugins[$name]['plugin_version'] = '0.7.2';

if ($plugin_enabled)
{
	$to_do = array(
		'user_handle_data'			=> array('custom_title_user_handle_data'),
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

	include($blog_plugins_path . 'custom_title/functions.' . $phpEx);
}
?>