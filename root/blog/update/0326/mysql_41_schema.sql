# User Blogs Mod Database Schema

# Table: 'phpbb_blog_search_results'
CREATE TABLE phpbb_blog_search_results (
	search_key varchar(32) DEFAULT '' NOT NULL,
	search_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	search_keywords mediumtext NOT NULL,
	search_authors mediumtext NOT NULL,
	PRIMARY KEY (search_key)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_blog_search_wordlist'
CREATE TABLE phpbb_blog_search_wordlist (
	word_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	word_text varchar(255) DEFAULT '' NOT NULL,
	word_common tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	word_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (word_id),
	UNIQUE wrd_txt (word_text),
	KEY wrd_cnt (word_count)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_blog_search_wordmatch'
CREATE TABLE phpbb_blog_search_wordmatch (
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	reply_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	word_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	title_match tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	UNIQUE unq_mtch (blog_id, reply_id, word_id, title_match),
	KEY word_id (word_id),
	KEY blog_id (blog_id),
	KEY reply_id (reply_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


