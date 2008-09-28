/*
User Blogs Mod Database Schema
*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_blog_search_results'
*/
CREATE TABLE [phpbb_blog_search_results] (
	[search_key] [varchar] (32) DEFAULT ('') NOT NULL ,
	[search_time] [int] DEFAULT (0) NOT NULL ,
	[search_keywords] [text] DEFAULT ('') NOT NULL ,
	[search_authors] [text] DEFAULT ('') NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_blog_search_results] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blog_search_results] PRIMARY KEY  CLUSTERED 
	(
		[search_key]
	)  ON [PRIMARY] 
GO


/*
	Table: 'phpbb_blog_search_wordlist'
*/
CREATE TABLE [phpbb_blog_search_wordlist] (
	[word_id] [int] IDENTITY (1, 1) NOT NULL ,
	[word_text] [varchar] (255) DEFAULT ('') NOT NULL ,
	[word_common] [int] DEFAULT (0) NOT NULL ,
	[word_count] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_blog_search_wordlist] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blog_search_wordlist] PRIMARY KEY  CLUSTERED 
	(
		[word_id]
	)  ON [PRIMARY] 
GO

CREATE  UNIQUE  INDEX [wrd_txt] ON [phpbb_blog_search_wordlist]([word_text]) ON [PRIMARY]
GO

CREATE  INDEX [wrd_cnt] ON [phpbb_blog_search_wordlist]([word_count]) ON [PRIMARY]
GO


/*
	Table: 'phpbb_blog_search_wordmatch'
*/
CREATE TABLE [phpbb_blog_search_wordmatch] (
	[blog_id] [int] DEFAULT (0) NOT NULL ,
	[reply_id] [int] DEFAULT (0) NOT NULL ,
	[word_id] [int] DEFAULT (0) NOT NULL ,
	[title_match] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

CREATE  UNIQUE  INDEX [unq_mtch] ON [phpbb_blog_search_wordmatch]([blog_id], [reply_id], [word_id], [title_match]) ON [PRIMARY]
GO

CREATE  INDEX [word_id] ON [phpbb_blog_search_wordmatch]([word_id]) ON [PRIMARY]
GO

CREATE  INDEX [blog_id] ON [phpbb_blog_search_wordmatch]([blog_id]) ON [PRIMARY]
GO

CREATE  INDEX [reply_id] ON [phpbb_blog_search_wordmatch]([reply_id]) ON [PRIMARY]
GO



COMMIT
GO

