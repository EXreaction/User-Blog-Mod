<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$phpbb_root_path = './../../';
define('PHPBB_ROOT_PATH', $phpbb_root_path);
$phpEx = substr(strrchr(__FILE__, '.'), 1);

$page = (isset($_GET['page'])) ? $_GET['page'] : '';
$extras = (isset($_GET['mode'])) ? $_GET['mode'] : '';
$extras = explode('_', $extras);
$_GET['mode'] = $_REQUEST['mode'] = $mode = array_shift($extras);

if (count($extras))
{
	$last = array();
	foreach ($extras as $extra)
	{
		$var = explode('-', $extra, 2);

		if (count($var) == 1 && !empty($last))
		{
			$var[1] = $last[1] . '_' . $var[0];
			$var[0] = $last[0];
		}

		$_GET[$var[0]] = $var[1];
		$_REQUEST[$var[0]] = $var[1];

		$last = $var;
	}
	unset($last, $var);
}

include("{$phpbb_root_path}blog.$phpEx");
?>