<?php

/************************************************************************/
/* RSS Feed for PHP-Nuke phpBB2 Forums		                        */
/* ===================================		                        */
/*                                                                      */
/* Copyright (c) 2003 by Max Weisel		                        */
/* http://www.redhatresources.com                                       */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

//no direct access
if (!eregi("modules.php", $_SERVER['PHP_SELF'])) {
        die ("You can't access this file directly...");
    }

//pars: area, f (forum id), show, mode (javascript or xml), layout, charset
global $db, $site_forum_prefix, $site_forum_url;
global $lang;

//prepare
$mode = strtolower($mode);
$layout = strtolower($layout);
$charset = strtolower($charset);

//start session
$phpbb_root_path = 'modules/Forums/';
define('IN_PHPBB', true);

//includes
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.' . $phpEx);
include('includes/bbcode.' . $phpEx);
include_once ("includes/functions_selectors.php");
include_once ("includes/topics_select.php");
include_once ("includes/forums_select.php");

//config
$site_name = $board_config['sitename'];
$site_description = $board_config['site_desc'];
$default_show = 10;

//set query parameters
$f = intval ($f);
$auth_view = 0;
$auth_read = 0;
$area = intval($area);
if (is_user($user)) {
	$auth_view = 1;
	$auth_read = 1;
}
$skip_moved = 1;
$posts_details = 1;
if ("$show" == "") {
	$show = $default_show;
} elseif (intval($show) == 0) {
	$show = "";
}

//init
$stop = 0;
$content = "";

//read forum data and set descriptive data
//must be done separately, because we may have items of subforums
if ($f != 0) {
	$result = $db->sql_query("SELECT forum_name, forum_desc FROM ".$site_forum_prefix."_bbforums WHERE forum_id=$f");
	if (!$result) {
		$stop = 1;
	} else {
		list($forum_name, $forum_desc) = $db->sql_fetchrow($result); 
		$uinf = urlinfo($forum_name);
		$forum_url = "$site_forum_url/forum$f-$uinf";
	}
} else {
	$forum_name = $site_name;
	$forum_url = $site_forum_url;
	$forum_description = $site_description;
}

//get results
if ($stop == 0) {
	$topics_array = forumsselecttopics ($f, $auth_view, $auth_read, $area, $skip_moved, $posts_details, $show);
}

//output
if ($stop != 0) {

	//error
	echo "An error occured";

} elseif (stristr($mode,"javascript")) {

	//get output
	if (stristr($mode,"blank")) {
		$target_spec = "target=\"new\"";
	} else {
		$target_spec = "";
	}
	$table_spec = "style=\"margin: 0px; padding: 0px; border-width: 0px; background: none;\"";
	$layout = $layout;
	$message_no_messages = _NO_RECENT_FORUM_POSTS;
	$content .= summarizetopics ($topics_array, $target_spec, $table_spec, $layout, $message_no_messages);

	//actual output
	if ($charset == "utf8") $content = utf8_encode($content);
	if ($charset == "iso") $content = utf8_decode($content);
	$content = str_replace ("'", "\'", $content);
	$output_lines = explode ("\n", $content);
	echo "function loadItems(element_id) {\n"; //set default function
	echo "	loadForumItems(element_id);\n";
	echo "}\n";
	echo "function loadForumItems(element_id) {\n";
	echo "	var output_items = '';\n";
	for ($i=0; $i<count($output_lines); $i++) {
		$output_lines[$i] = str_replace ("\r", " ", $output_lines[$i]);
		echo "	output_items = output_items + '" . $output_lines[$i] . "';\n";
	}
	echo "	document.getElementById(element_id).innerHTML = output_items;\n";
	echo "}\n";
	echo "loadForumItems('latinforum_items');\n";

} else {

	//xml
	header("Content-Type: text/xml");
        echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n\n";
	echo "<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 0.91//EN\"\n";
	echo " \"http://my.netscape.com/publish/formats/rss-0.91.dtd\">\n\n";
        echo "<rss version=\"0.91\">\n\n";
	echo "<channel>\n";
        echo "<title>". htmlspecialchars($forum_name) ."</title>\n";
        echo "<link>$forum_url</link>\n"; //MOD: replaces $nukeurl
        echo "<description>". htmlspecialchars($forum_description) ."</description>\n";
//	echo "<language>$backend_language</language>\n\n"; MOD: not
	for ($i=0; $i < count($topics_array); $i++) {
		$topic_id = $topics_array[$i]['topic_id'];
		$topic_title = $topics_array[$i]['topic_title'];
//		$last_post_time = $topics_array[$i]['last_post_time'];
		echo "<item>\n";
		echo "<title>".htmlspecialchars($topic_title)."</title>\n";
		echo "<link>$site_forum_url/postt$topic_id.html</link>\n"; //MOD: replaced nukeurl and rewrite
		echo "</item>\n\n";
        }
	echo "</channel>\n";
        echo "</rss>";
    }

?>






