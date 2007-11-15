<?php
/**
*
* @package phpBB3 User Blog Attachments
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB') || !defined('PLUGIN_UPDATE'))
{
	exit;
}

switch ($this->plugins[$which]['plugin_version'])
{
	case '0.7.0' :
	case '0.7.1' :
	case '0.7.2' :
	case '0.7.3' :
	case '0.7.4' :
	case '0.7.5' :
	case '0.7.6' :
	break;
}
?>