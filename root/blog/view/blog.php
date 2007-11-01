<?php
/**
 *
 * @package phpBB3 User Blog
 * @copyright (c) 2007 EXreaction, Lithium Studios
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 *
 */

$phpbb_root_path = './../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

$page = (isset($_GET['page'])) ? $_GET['page'] : '';
$extras = (isset($_GET['mode'])) ? $_GET['mode'] : '';
$extras = explode('_', $extras);
$_GET['mode'] = $_REQUEST['mode'] = $mode = array_shift($extras);

if (count($extras))
{
	foreach ($extras as $extra)
	{
		$var = explode('-', $extra);

		if (count($var) != 2 || isset($_REQUEST[$var[0]]))
		{
			continue;
		}

		$_GET[$var[0]] = $var[1];
		$_REQUEST[$var[0]] = $var[1];
	}
}

include("{$phpbb_root_path}blog.$phpEx");
?>