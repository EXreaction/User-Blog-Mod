<?php
/**
*
* @package phpBB3 User Blog
* @version $Id:
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* If you are confused at why this page is here, it is to trick the SEO Urls.
* I want my SEO urls to be like blog/(username)/b(blog_id).html
* If I were to just have that and use the main blog.php file, the $phpbb_root_path would work for the relative path for PHP files, but would not
*  work when it tells the browser the relative path for links (so the page would be broken).  So this is just a trick to make the relative paths work.
*/

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

		if (count($var) > 1)
		{
			$_GET[$var[0]] = $_REQUEST[$var[0]] = $var[1];

			$last = $var;
		}
	}
	unset($last, $var);
}

define('PHPBB_ROOT_PATH', './../../');
include(PHPBB_ROOT_PATH . 'blog.' . substr(strrchr(__FILE__, '.'), 1));
?>