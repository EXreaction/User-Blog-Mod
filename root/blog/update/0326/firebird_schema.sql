# User Blogs Mod Database Schema


# Table: 'phpbb_blog_search_results'
CREATE TABLE phpbb_blog_search_results (
	search_key VARCHAR(32) CHARACTER SET NONE DEFAULT '' NOT NULL,
	search_time INTEGER DEFAULT 0 NOT NULL,
	search_keywords BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	search_authors BLOB SUB_TYPE TEXT CHARACTER SET NONE DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_blog_search_results ADD PRIMARY KEY (search_key);;


# Table: 'phpbb_blog_search_wordlist'
CREATE TABLE phpbb_blog_search_wordlist (
	word_id INTEGER NOT NULL,
	word_text VARCHAR(255) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
	word_common INTEGER DEFAULT 0 NOT NULL,
	word_count INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_blog_search_wordlist ADD PRIMARY KEY (word_id);;

CREATE UNIQUE INDEX phpbb_blog_search_wordlist_wrd_txt ON phpbb_blog_search_wordlist(word_text);;
CREATE INDEX phpbb_blog_search_wordlist_wrd_cnt ON phpbb_blog_search_wordlist(word_count);;

CREATE GENERATOR phpbb_blog_search_wordlist_gen;;
SET GENERATOR phpbb_blog_search_wordlist_gen TO 0;;

CREATE TRIGGER t_phpbb_blog_search_wordlist FOR phpbb_blog_search_wordlist
BEFORE INSERT
AS
BEGIN
	NEW.word_id = GEN_ID(phpbb_blog_search_wordlist_gen, 1);
END;;


# Table: 'phpbb_blog_search_wordmatch'
CREATE TABLE phpbb_blog_search_wordmatch (
	blog_id INTEGER DEFAULT 0 NOT NULL,
	reply_id INTEGER DEFAULT 0 NOT NULL,
	word_id INTEGER DEFAULT 0 NOT NULL,
	title_match INTEGER DEFAULT 0 NOT NULL
);;

CREATE UNIQUE INDEX phpbb_blog_search_wordmatch_unq_mtch ON phpbb_blog_search_wordmatch(blog_id, reply_id, word_id, title_match);;
CREATE INDEX phpbb_blog_search_wordmatch_word_id ON phpbb_blog_search_wordmatch(word_id);;
CREATE INDEX phpbb_blog_search_wordmatch_blog_id ON phpbb_blog_search_wordmatch(blog_id);;
CREATE INDEX phpbb_blog_search_wordmatch_reply_id ON phpbb_blog_search_wordmatch(reply_id);;

