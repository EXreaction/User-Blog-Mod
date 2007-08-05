<?php
/** 
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class acp_blogs_info
{
	function module()
	{
		global $user;

		return array(
			'filename'	=> 'acp_blogs',
			'title'		=> 'ACP_BLOGS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'default'	=> array('title' => 'ACP_BLOGS', 'auth' => 'acl_a_blogmanage', 'cat' => array('ACP_DOT_MODS')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>