#
# $Id: $
#

# Table: 'phpbb_blogs_tags'
CREATE TABLE phpbb_blogs_tags (
	tag_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	tag_name mediumblob NOT NULL,
	tag_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (tag_id),
	KEY tag_count (tag_count)
);


