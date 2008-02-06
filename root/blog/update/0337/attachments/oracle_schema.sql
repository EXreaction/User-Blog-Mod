/*
	Table: 'phpbb_blogs_attachment'
*/
CREATE TABLE phpbb_blogs_attachment (
	attach_id number(8) NOT NULL,
	blog_id number(8) DEFAULT '0' NOT NULL,
	reply_id number(8) DEFAULT '0' NOT NULL,
	poster_id number(8) DEFAULT '0' NOT NULL,
	is_orphan number(1) DEFAULT '1' NOT NULL,
	physical_filename varchar2(255) DEFAULT '' ,
	real_filename varchar2(255) DEFAULT '' ,
	download_count number(8) DEFAULT '0' NOT NULL,
	attach_comment clob DEFAULT '' ,
	extension varchar2(100) DEFAULT '' ,
	mimetype varchar2(100) DEFAULT '' ,
	filesize number(20) DEFAULT '0' NOT NULL,
	filetime number(11) DEFAULT '0' NOT NULL,
	thumbnail number(1) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_attachment PRIMARY KEY (attach_id)
)
/

CREATE INDEX phpbb_blogs_attachment_blog_id ON phpbb_blogs_attachment (blog_id)
/
CREATE INDEX phpbb_blogs_attachment_reply_id ON phpbb_blogs_attachment (reply_id)
/
CREATE INDEX phpbb_blogs_attachment_filetime ON phpbb_blogs_attachment (filetime)
/
CREATE INDEX phpbb_blogs_attachment_poster_id ON phpbb_blogs_attachment (poster_id)
/
CREATE INDEX phpbb_blogs_attachment_is_orphan ON phpbb_blogs_attachment (is_orphan)
/

CREATE SEQUENCE phpbb_blogs_attachment_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs_attachment
BEFORE INSERT ON phpbb_blogs_attachment
FOR EACH ROW WHEN (
	new.attach_id IS NULL OR new.attach_id = 0
)
BEGIN
	SELECT phpbb_blogs_attachment_seq.nextval
	INTO :new.attach_id
	FROM dual;
END;
/


