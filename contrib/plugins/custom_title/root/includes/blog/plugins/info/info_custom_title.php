<?php
/**
*
* @package phpBB3 User Blog Custom Title
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// setup some basic information about the plugin
$this->available_plugins[$name]['plugin_copyright'] = '2007 EXreaction';
$this->available_plugins[$name]['plugin_version'] = '0.7.0';

// Only do this if the plugin is enabled (set in the load_plugins function right before this file is loaded)
if ($plugin_enabled)
{
	/**
	* Setup the To Do list and add it to the plugin's to-do list
	*/
	$attach_to_do = array(
		'user_handle_data'			=> array('custom_title_user_handle_data'),
	);

	foreach($attach_to_do as $do => $what)
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

	/**
	* Include the necessary files
	*/
	include($blog_plugins_path . 'custom_title/functions.' . $phpEx);
}
?>