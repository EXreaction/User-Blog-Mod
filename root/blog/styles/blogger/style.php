<?php
/**
*
* @package phpBB3 User Blog
* @version $Id:
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/*
* To add a new user selectable style, you must add just the following line and put it in a file named style.php
* The name field is what is shown to the user, the value field is the location of the style off of the blog/styles/ directory.
*/
$available_styles[] = array('name' => 'Blogger Clone', 'value' => 'blogger', 'blog_css' => true, 'demo' => $phpbb_root_path . 'blog/styles/blogger/demo.png');

?>