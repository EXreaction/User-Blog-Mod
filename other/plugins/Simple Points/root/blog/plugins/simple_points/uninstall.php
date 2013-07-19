<?php
/**
*
* @package phpBB3 User Blog Simple Points
* @copyright (c) 2008 EXreaction
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

$db->sql_query('DELETE FROM ' . CONFIG_TABLE . ' WHERE config_name = \'user_blog_sp_blog_points\'');
$db->sql_query('DELETE FROM ' . CONFIG_TABLE . ' WHERE config_name = \'user_blog_sp_reply_points\'');
$db->sql_query('DELETE FROM ' . CONFIG_TABLE . ' WHERE config_name = \'user_blog_cp_points\'');

?>