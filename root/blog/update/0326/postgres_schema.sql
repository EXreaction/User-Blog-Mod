/*
User Blogs Mod Database Schema
*/

BEGIN;


/*
	Table: 'phpbb_blog_search_results'
*/
CREATE TABLE phpbb_blog_search_results (
	search_key varchar(32) DEFAULT '' NOT NULL,
	search_time INT4 DEFAULT '0' NOT NULL CHECK (search_time >= 0),
	search_keywords TEXT DEFAULT '' NOT NULL,
	search_authors TEXT DEFAULT '' NOT NULL,
	PRIMARY KEY (search_key)
);


/*
	Table: 'phpbb_blog_search_wordlist'
*/
CREATE SEQUENCE phpbb_blog_search_wordlist_seq;

CREATE TABLE phpbb_blog_search_wordlist (
	word_id INT4 DEFAULT nextval('phpbb_blog_search_wordlist_seq'),
	word_text varchar(255) DEFAULT '' NOT NULL,
	word_common INT2 DEFAULT '0' NOT NULL CHECK (word_common >= 0),
	word_count INT4 DEFAULT '0' NOT NULL CHECK (word_count >= 0),
	PRIMARY KEY (word_id)
);

CREATE UNIQUE INDEX phpbb_blog_search_wordlist_wrd_txt ON phpbb_blog_search_wordlist (word_text);
CREATE INDEX phpbb_blog_search_wordlist_wrd_cnt ON phpbb_blog_search_wordlist (word_count);

/*
	Table: 'phpbb_blog_search_wordmatch'
*/
CREATE TABLE phpbb_blog_search_wordmatch (
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	reply_id INT4 DEFAULT '0' NOT NULL CHECK (reply_id >= 0),
	word_id INT4 DEFAULT '0' NOT NULL CHECK (word_id >= 0),
	title_match INT2 DEFAULT '0' NOT NULL CHECK (title_match >= 0)
);

CREATE UNIQUE INDEX phpbb_blog_search_wordmatch_unq_mtch ON phpbb_blog_search_wordmatch (blog_id, reply_id, word_id, title_match);
CREATE INDEX phpbb_blog_search_wordmatch_word_id ON phpbb_blog_search_wordmatch (word_id);
CREATE INDEX phpbb_blog_search_wordmatch_blog_id ON phpbb_blog_search_wordmatch (blog_id);
CREATE INDEX phpbb_blog_search_wordmatch_reply_id ON phpbb_blog_search_wordmatch (reply_id);


COMMIT;