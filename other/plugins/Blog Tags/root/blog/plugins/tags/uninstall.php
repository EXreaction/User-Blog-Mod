<?php
/**
*
* @package phpBB3 User Blog Tags
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$db->sql_query('DROP TABLE ' . $table_prefix . 'blogs_tags');
$db_tool->sql_column_remove(BLOGS_TABLE, 'blog_tags');

?>