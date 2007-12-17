<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* If you are confused at why this page is here, it is to trick the SEO Urls.
* I want my SEO urls to be like blog/(username)/b(blog_id).html
* If I were to just have that and use the main blog.php file, the $phpbb_root_path would work for the relative path for PHP files, but would not
*  work when it tells the browser the relative path for links (so the page would be broken).  So this is just a trick to make the relative paths work.
*/

$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include("{$phpbb_root_path}blog.$phpEx");
?>