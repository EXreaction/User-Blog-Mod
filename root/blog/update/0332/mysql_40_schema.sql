# User Blogs Mod Database Schema

# Table: 'phpbb_blogs_categories'
CREATE TABLE phpbb_blogs_categories (
	category_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	parent_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	left_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	right_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	category_name blob NOT NULL,
	category_description mediumblob NOT NULL,
	category_description_bitfield varbinary(255) DEFAULT '' NOT NULL,
	category_description_uid varbinary(8) DEFAULT '' NOT NULL,
	category_description_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	rules mediumblob NOT NULL,
	rules_bitfield varbinary(255) DEFAULT '' NOT NULL,
	rules_uid varbinary(8) DEFAULT '' NOT NULL,
	rules_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	blog_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (category_id),
	KEY left_right_id (left_id, right_id)
);


# Table: 'phpbb_blogs_in_categories'
CREATE TABLE phpbb_blogs_in_categories (
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	category_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (blog_id, category_id)
);


