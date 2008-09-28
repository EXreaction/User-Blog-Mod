# User Blogs Mod Database Schema

BEGIN TRANSACTION;

# Table: 'phpbb_blog_search_results'
CREATE TABLE phpbb_blog_search_results (
	search_key varchar(32) NOT NULL DEFAULT '',
	search_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	search_keywords mediumtext(16777215) NOT NULL DEFAULT '',
	search_authors mediumtext(16777215) NOT NULL DEFAULT '',
	PRIMARY KEY (search_key)
);


# Table: 'phpbb_blog_search_wordlist'
CREATE TABLE phpbb_blog_search_wordlist (
	word_id INTEGER PRIMARY KEY NOT NULL ,
	word_text varchar(255) NOT NULL DEFAULT '',
	word_common INTEGER UNSIGNED NOT NULL DEFAULT '0',
	word_count INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE UNIQUE INDEX phpbb_blog_search_wordlist_wrd_txt ON phpbb_blog_search_wordlist (word_text);
CREATE INDEX phpbb_blog_search_wordlist_wrd_cnt ON phpbb_blog_search_wordlist (word_count);

# Table: 'phpbb_blog_search_wordmatch'
CREATE TABLE phpbb_blog_search_wordmatch (
	blog_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	reply_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	word_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	title_match INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE UNIQUE INDEX phpbb_blog_search_wordmatch_unq_mtch ON phpbb_blog_search_wordmatch (blog_id, reply_id, word_id, title_match);
CREATE INDEX phpbb_blog_search_wordmatch_word_id ON phpbb_blog_search_wordmatch (word_id);
CREATE INDEX phpbb_blog_search_wordmatch_blog_id ON phpbb_blog_search_wordmatch (blog_id);
CREATE INDEX phpbb_blog_search_wordmatch_reply_id ON phpbb_blog_search_wordmatch (reply_id);


COMMIT;