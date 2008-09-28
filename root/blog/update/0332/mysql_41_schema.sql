# User Blogs Mod Database Schema

# Table: 'phpbb_blogs_categories'
CREATE TABLE phpbb_blogs_categories (
	category_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	parent_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	left_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	right_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	category_name varchar(255) DEFAULT '' NOT NULL COLLATE utf8_unicode_ci,
	category_description mediumtext NOT NULL,
	category_description_bitfield varchar(255) DEFAULT '' NOT NULL,
	category_description_uid varchar(8) DEFAULT '' NOT NULL,
	category_description_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	rules mediumtext NOT NULL,
	rules_bitfield varchar(255) DEFAULT '' NOT NULL,
	rules_uid varchar(8) DEFAULT '' NOT NULL,
	rules_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	blog_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (category_id),
	KEY left_right_id (left_id, right_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_blogs_in_categories'
CREATE TABLE phpbb_blogs_in_categories (
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	category_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (blog_id, category_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


