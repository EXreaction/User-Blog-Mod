/*
User Blogs Mod Database Schema
*/


/*
	Table: 'phpbb_blog_search_results'
*/
CREATE TABLE phpbb_blog_search_results (
	search_key varchar2(32) DEFAULT '' ,
	search_time number(11) DEFAULT '0' NOT NULL,
	search_keywords clob DEFAULT '' ,
	search_authors clob DEFAULT '' ,
	CONSTRAINT pk_phpbb_blog_search_results PRIMARY KEY (search_key)
)
/


/*
	Table: 'phpbb_blog_search_wordlist'
*/
CREATE TABLE phpbb_blog_search_wordlist (
	word_id number(8) NOT NULL,
	word_text varchar2(765) DEFAULT '' ,
	word_common number(1) DEFAULT '0' NOT NULL,
	word_count number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blog_search_wordlist PRIMARY KEY (word_id),
	CONSTRAINT u_phpbb_wrd_txt UNIQUE (word_text)
)
/

CREATE INDEX phpbb_blog_search_wordlist_wrd_cnt ON phpbb_blog_search_wordlist (word_count)
/

CREATE SEQUENCE phpbb_blog_search_wordlist_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blog_search_wordlist
BEFORE INSERT ON phpbb_blog_search_wordlist
FOR EACH ROW WHEN (
	new.word_id IS NULL OR new.word_id = 0
)
BEGIN
	SELECT phpbb_blog_search_wordlist_seq.nextval
	INTO :new.word_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blog_search_wordmatch'
*/
CREATE TABLE phpbb_blog_search_wordmatch (
	blog_id number(8) DEFAULT '0' NOT NULL,
	reply_id number(8) DEFAULT '0' NOT NULL,
	word_id number(8) DEFAULT '0' NOT NULL,
	title_match number(1) DEFAULT '0' NOT NULL,
	CONSTRAINT u_phpbb_unq_mtch UNIQUE (blog_id, reply_id, word_id, title_match)
)
/

CREATE INDEX phpbb_blog_search_wordmatch_word_id ON phpbb_blog_search_wordmatch (word_id)
/
CREATE INDEX phpbb_blog_search_wordmatch_blog_id ON phpbb_blog_search_wordmatch (blog_id)
/
CREATE INDEX phpbb_blog_search_wordmatch_reply_id ON phpbb_blog_search_wordmatch (reply_id)
/

